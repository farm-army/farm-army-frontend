<?php

namespace App\Controller;

use App\Client\NodeClient;
use App\Entity\CrossFarm;
use App\Pools\FarmPools;
use App\Repository\CrossFarmRepository;
use App\Symbol\IconResolver;
use App\Utils\ChainUtil;
use App\Utils\Web3Util;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TokenController extends AbstractController
{
    private FarmPools $farmPools;
    private NodeClient $nodeClient;
    private IconResolver $iconResolver;
    private CrossFarmRepository $crossFarmRepository;

    public function __construct(
        FarmPools $farmPools,
        NodeClient $nodeClient,
        IconResolver $iconResolver,
        CrossFarmRepository $crossFarmRepository
    ) {
        $this->farmPools = $farmPools;
        $this->nodeClient = $nodeClient;
        $this->iconResolver = $iconResolver;
        $this->crossFarmRepository = $crossFarmRepository;
    }

    /**
     * @Route("/token/{chain}/{token}", name="chain_token_address", methods={"GET"}, requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam"
     * })
     */
    public function token(string $token, string $chain)
    {
        $token = strtolower($token);

        if (!Web3Util::isAddress($token)) {
            throw new NotFoundHttpException('invalid token address');
        }

        try {
            ChainUtil::getChain($chain);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('invalid chain');
        }

        $vaults = $this->crossFarmRepository->findFarmIdsByToken($token);

        if (count($vaults) === 0) {
            throw new NotFoundHttpException('token not found');
        }

        $others = array_map(
            fn(array $i) => $this->farmPools->enrichFarmData($i['json']),
            $vaults
        );

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(60 * 10);

        $info = $this->nodeClient->getTokenInfo($chain, $token);

        $samePairs = [];
        foreach ($info['equalLiquidityPools'] ?? [] as $pool) {
            $item = [
                'info' => $this->getTokenCardInfo($pool, $chain),
            ];

            $item['vaults'] = array_map(
                fn(array $i) => $this->farmPools->enrichFarmData($i['json']),
                $this->crossFarmRepository->findFarmIdsByToken(strtolower($pool['address']))
            );

            $samePairs[] = $item;
        }

        $parameters = [
            'token' => $token,
            'vaults' => $others,
            'token_card' => $this->getTokenCard($token, $chain),
            'same_pairs' => $samePairs,
            'chain_context' => ChainUtil::getChain($chain),
        ];

        $candles = [];
        foreach ($info['candles'] ?? [] as $candle) {
            $format = date_create_from_format('U', $candle['time'])->format('m-d H') . ':00';
            $candles[$format] = $candle['close'];
        }

        if (count($candles) > 0) {
            $parameters['chart'] = [
                'label' => array_keys($candles),
                'data' => array_values($candles),
            ];
        }

        return $this->render('vault/token.html.twig', $parameters, $response);
    }

    private function getTokenCardInfo(array $info, string $chain): array
    {
        $symbol = implode('-', array_map(static fn($item) => $item['symbol'], $info['tokens']));

        $icon = $this->iconResolver->getIcon($symbol);

        return [
            'icon' => $icon,
            'price' => $info['price'] ?? null,
            'symbol' => strtoupper($symbol),
            'address'=> $info['address'],
            'chain' => $chain,
        ];
    }

    private function getTokenCard(string $token, string $chain): array
    {
        $info = $this->nodeClient->getTokenInfo($chain, $token);

        $symbol = 'unknown';
        if (isset($info['liquidityPool']['tokens']) && is_array($info['liquidityPool']['tokens'])) {
            $symbol = implode('-', array_map(fn($item) => $item['symbol'], $info['liquidityPool']['tokens']));
        } else if (isset($info['token']['symbol'])) {
            $symbol = $info['token']['symbol'];
        }

        $icon = $this->iconResolver->getIcon($symbol);

        return [
            'icon' => $icon,
            'price' => $info['price'] ?? null,
            'symbol' => strtoupper($symbol),
            'address'=> $token,
            'chain' => $chain,
        ];
    }

    /**
     * @Route("/vault/{hash}", name="vault_address", methods={"GET"})
     */
    public function detail(string $hash): Response
    {
        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(60 * 10);

        /** @var \App\Entity\CrossFarm|null $farmDb */
        $farmDb = $this->crossFarmRepository->findOneBy([
            'hash' => $hash,
        ]);

        if (!$farmDb) {
            throw new NotFoundHttpException('vault not found');
        }

        $others = array_map(
            fn(array $i) => $this->farmPools->enrichFarmData($i['json']),
            $farmDb->getToken() ? $this->crossFarmRepository->findFarmIdsByToken($farmDb->getToken(), $farmDb->getFarmId()) : []
        );

        $json = $farmDb->getJson();

        return $this->render('vault/detail.html.twig', [
            'farm' => $this->farmPools->enrichFarmData($farmDb->getJson()),
            'json' => $json,
            'others' => $others,
            'farm_db' => $farmDb,
            'token_card' => $farmDb->getToken() ? $this->getTokenCard($farmDb->getToken(), $farmDb->getChain()) : null,
            'chain_context' => ChainUtil::getChain($farmDb->getChain()),
        ], $response);
    }
}