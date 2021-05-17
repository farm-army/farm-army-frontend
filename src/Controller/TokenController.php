<?php

namespace App\Controller;

use App\Client\NodeClient;
use App\Entity\Farm;
use App\Pools\FarmPools;
use App\Repository\FarmRepository;
use App\Repository\PlatformRepository;
use App\Symbol\IconResolver;
use App\Utils\Web3Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TokenController extends AbstractController
{
    private FarmRepository $farmRepository;
    private FarmPools $farmPools;
    private PlatformRepository $platformRepository;
    private NodeClient $nodeClient;
    private IconResolver $iconResolver;

    public function __construct(
        FarmRepository $farmRepository,
        FarmPools $farmPools,
        PlatformRepository $platformRepository,
        NodeClient $nodeClient,
        IconResolver $iconResolver
    ) {
        $this->farmRepository = $farmRepository;
        $this->farmPools = $farmPools;
        $this->platformRepository = $platformRepository;
        $this->nodeClient = $nodeClient;
        $this->iconResolver = $iconResolver;
    }

    /**
     * @Route("/token/{token}", name="token_address", methods={"GET"})
     */
    public function token(string $token)
    {
        $token = strtolower($token);

        if (!Web3Util::isAddress($token)) {
            throw new NotFoundHttpException('invalid token address');
        }

        $vaultIds = $this->farmRepository->findFarmIdsByToken($token);

        if (count($vaultIds) === 0) {
            throw new NotFoundHttpException('token not found');
        }

        $others = [];
        $generateFarms = $this->farmPools->generateFarms();

        foreach ($generateFarms as $farmX) {
            if (in_array($farmX['id'], $vaultIds, true)) {
                $others[] = $farmX;
            }
        }

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(60 * 10);

        $info = $this->nodeClient->getTokenInfo($token);

        $samePairs = [];
        foreach ($info['equalLiquidityPools'] ?? [] as $pool) {
            $item = [
                'info' => $this->getTokenCardInfo($pool),
                'vaults' => [],
            ];

            $vaultIds = $this->farmRepository->findFarmIdsByToken(strtolower($pool['address']));

            foreach ($generateFarms as $farmX) {
                if (in_array($farmX['id'], $vaultIds, true)) {
                    $item['vaults'][] = $farmX;
                }
            }

            $samePairs[] = $item;
        }

        $parameters = [
            'token' => $token,
            'vaults' => $others,
            'token_card' => $this->getTokenCard($token),
            'same_pairs' => $samePairs,
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

    private function getTokenCardInfo(array $info): array
    {
        $symbol = implode('-', array_map(fn($item) => $item['symbol'], $info['tokens']));

        $icon = $this->iconResolver->getIcon($symbol);

        return [
            'icon' => $icon,
            'price' => $info['price'] ?? null,
            'symbol' => strtoupper($symbol),
            'address'=> $info['address'],
        ];
    }

    private function getTokenCard(string $token): array
    {
        $info = $this->nodeClient->getTokenInfo($token);

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
        ];
    }

    /**
     * @Route("/vault/{hash}", name="vault_address", methods={"GET"})
     */
    public function detail(string $hash)
    {
        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(60 * 10);

        /** @var Farm|null $farmDb */
        $farmDb = $this->farmRepository->findOneBy([
            'hash' => $hash,
        ]);

        if (!$farmDb) {
            throw new NotFoundHttpException('vault not found');
        }

        $farms = $this->farmPools->generateFarms();
        $otherIds = $farmDb->getToken() ? $this->farmRepository->findFarmIdsByToken($farmDb->getToken()) : [];

        $farm = null;
        $others = [];
        foreach($farms as $farmX) {
            if ($farmX['id'] === $farmDb->getFarmId()) {
                $farm = $farmX;
            } else if(in_array($farmX['id'], $otherIds, true)) {
                $others[] = $farmX;
            }
        }

        if (!$farm) {
            throw new NotFoundHttpException('vault not found');
        }

        $json = $farmDb->getJson();

        return $this->render('vault/detail.html.twig', [
            'farm' => $farm,
            'json' => $json,
            'others' => $others,
            'farm_db' => $farmDb,
            'token_card' => $farmDb->getToken() ? $this->getTokenCard($farmDb->getToken()) : null,
        ], $response);
    }
}