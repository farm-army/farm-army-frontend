<?php

namespace App\Controller;

use App\Client\NodeClient;
use App\Pools\FarmPools;
use App\Repository\CrossFarmRepository;
use App\Repository\CrossPlatformRepository;
use App\Repository\NftRepository;
use App\Symbol\IconResolver;
use App\Utils\ChainGuesser;
use App\Utils\ChainUtil;
use App\Utils\RandomAddress;
use App\Utils\Web3Util;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class AddressController extends AbstractController
{
    private ChainGuesser $chainGuesser;
    private CrossPlatformRepository $crossPlatformRepository;
    private CrossFarmRepository $crossFarmRepository;

    public function __construct(
        ChainGuesser $chainGuesser,
        CrossPlatformRepository $crossPlatformRepository,
        CrossFarmRepository $crossFarmRepository
    ) {
        $this->chainGuesser = $chainGuesser;
        $this->crossPlatformRepository = $crossPlatformRepository;
        $this->crossFarmRepository = $crossFarmRepository;
    }

    /**
     * @Route("/{chain}/0x{address}", name="chain_app_farm_index", requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam"
     * })
     * @Route("/0x{address}", name="app_farm_index")
     */
    public function index(string $address, ?string $chain, UrlGeneratorInterface $urlGenerator, ChainUtil $chainUtil): Response
    {
        $addressNoPrefix = $address;
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            $chain = $this->getChainOrThrowNotFound($chain);

            return new Response($this->renderView('address/invalid.html.twig', [
                'chain_context' => ChainUtil::getChain($chain),
            ]), 404);
        }

        $chain = $this->getChainOrThrowNotFound($chain);

        $var = [
            'explorer' => $chainUtil->getChainExplorerUrl($chain),
            'address' => $address,
            'address_truncate' => substr($address, 0, 8) . '...' . substr($address, -8),
            'platform_chunks' => array_map(
                fn($chunk) => $urlGenerator->generate('farm_json_platform_chain_chunks', ['address' => $addressNoPrefix, 'chunk' => $chunk, 'chain' => $chain]),
                array_keys($this->crossPlatformRepository->getPlatformChunksOnChain($chain))
            ),
            'wallet_url' => $urlGenerator->generate('farm_json_chain_wallet', ['address' => $addressNoPrefix, 'chain' => $chain]),
            'transaction_url' => $urlGenerator->generate('app_farm_transactions_chain', ['address' => $addressNoPrefix, 'chain' => $chain]),
            'nft_url' => $urlGenerator->generate('app_farm_nfts_chain', ['address' => $addressNoPrefix, 'chain' => $chain]),
            'chain_context' => ChainUtil::getChain($chain),
        ];

        $var['app_context'] = $var;

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(9);

        return $this->render('address/index.html.twig', $var, $response);
    }

    /**
     * @Route("/{chain}/0x{address}/transactions", name="app_farm_transactions_chain", methods={"GET"}, requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam"
     * })
     * @Route("/0x{address}/transactions", name="app_farm_transactions", methods={"GET"})
     */
    public function transactions(string $address, ?string $chain, NodeClient $nodeClient, ChainUtil $chainUtil): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            throw new BadRequestHttpException('invalid address');
        }

        $chain = $this->getChainOrThrowNotFound($chain);

        $response = new Response();
        $response->headers->set('X-Robots-Tag', 'noindex');

        $response->setPublic();
        $response->setMaxAge(9);

        $transactions = $nodeClient->getTransactions($address, $chain);

        return $this->render('address/transactions.html.twig', [
            'explorer' => $chainUtil->getChainExplorerUrl($chain),
            'address' => $address,
            'transactions' => $transactions,
            'chain_context' => ChainUtil::getChain($chain),
        ], $response);
    }

    /**
     * @Route("/0x{address}/nfts", name="app_farm_nfts", methods={"GET"})
     * @Route("/{chain}/0x{address}/nfts", name="app_farm_nfts_chain", methods={"GET"}, requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam"
     * })
     */
    public function nfts(string $address, ?string $chain, NodeClient $nodeClient, ChainUtil $chainUtil, NftRepository $nftRepository): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            throw new BadRequestHttpException('invalid address');
        }

        $chain = $this->getChainOrThrowNotFound($chain);

        $response = new Response();
        $response->headers->set('X-Robots-Tag', 'noindex');

        $response->setPublic();
        $response->setMaxAge(9);

        $collections = $nodeClient->getNfts($address, $chain);

        foreach($collections as $key => $collection) {
            if (!$infos = $nftRepository->getCollectionInfo(strtolower($collection['address']))) {
                continue;
            }

            $collections[$key] = array_merge($collections[$key], $infos);
        }

        return $this->render('address/nft.html.twig', [
            'explorer' => $chainUtil->getChainExplorerUrl($chain),
            'address' => $address,
            'nfts' => $collections,
            'chain_context' => ChainUtil::getChain($chain),
        ], $response);
    }

    /**
     * @Route("/{chain}/random", name="random_address_chain", requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam"
     * })
     * @Route("/random", name="random_address")
     */
    public function random(RandomAddress $randomAddress, ?string $chain): Response
    {
        $chain = $this->getChainOrThrowNotFound($chain);

        $randomAddresses = $randomAddress->getRandomAddresses($chain);

        return $this->forward('App\Controller\AddressController::index', [
            'address' => substr($randomAddresses[array_rand($randomAddresses)], 2),
            'chain' => $chain,
        ]);
    }

    /**
     * @Route("/farms/{chain}/0x{address}", name="farm_content_chain", requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam"
     * })
     * @Route("/farms/0x{address}", name="farm_content")
     */
    public function ajax(string $address, ?string $chain, NodeClient $nodeClient): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            $chain = $this->getChainOrThrowNotFound($chain);

            return new Response($this->renderView('address/invalid.html.twig', [
                'chain_context' => ChainUtil::getChain($chain),
            ]), 404);
        }

        $chain = $this->getChainOrThrowNotFound($chain);

        $address = strtolower($address);

        $addressFarms = $nodeClient->getAddressFarms($chain, $address);

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(9);
        $response->headers->set('X-Robots-Tag', 'noindex');

        return $this->render('address/content.html.twig', [
            'address' => $address,
            'address_truncate' => substr($address, 0, 8) . '...' . substr($address, -8),
            'platforms' => $addressFarms['farms'],
            'wallet' => $addressFarms['wallet'],
            'summary' => $addressFarms['summary'],
            'chain_context' => ChainUtil::getChain($chain),
        ], $response);
    }

    /**
     * @Route("/farms/{chain}/0x{address}/platform/{chunk}.json", name="farm_json_platform_chain_chunks", methods={"GET"}, requirements={
     *      "chunk"="\d{1,2}",
     *      "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam",
     *      "_format"="json",
     * })
     * @Route("/farms/0x{address}/platform/{chunk}.json", name="farm_json_platform_chunks", methods={"GET"}, requirements={
     *      "chunk"="\d{1,2}",
     *      "_format"="json",
     * })
     */
    public function jsonAjax(string $address, ?string $chain, string $chunk, NodeClient $nodeClient, Environment $twig, CrossPlatformRepository $crossPlatformRepository): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            throw new BadRequestHttpException('Invalid address');
        }

        $chain = $this->getChainOrThrowNotFound($chain);

        $chunks = $crossPlatformRepository->getPlatformChunksOnChain($chain);
        if (!isset($chunks[$chunk])) {
            throw new BadRequestHttpException('Invalid chunk');
        }

        $platforms = $nodeClient->getAddressFarmsForPlatforms($chain, strtolower($address), $chunks[$chunk]);

        foreach($platforms as $key => $platform) {
            $platforms[$key]['html'] = $twig->render('address/platform/platform.html.twig', [
                'address' => $address,
                'platform' => $platform
            ]);
        }

        $response = new JsonResponse($platforms);
        $response->setPublic();
        $response->setMaxAge(9);
        $response->headers->set('X-Robots-Tag', 'noindex');

        return $response;
    }

    /**
     * @Route("/farms/{chain}/0x{address}/wallet.json", name="farm_json_chain_wallet", methods={"GET"}, requirements={
     *      "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam",
     *      "_format"="json",
     * })
     * @Route("/farms/0x{address}/wallet.json", name="farm_json_wallet", methods={"GET"}, requirements={
     *      "_format"="json",
     * })
     */
    public function jsonWallet(string $address, ?string $chain, NodeClient $nodeClient, Environment $twig, IconResolver $iconResolver, ChainGuesser $chainGuesser): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            throw new BadRequestHttpException('Invalid address');
        }

        $chain = ChainUtil::getChain($this->getChainOrThrowNotFound($chain));

        $walletRaw = $nodeClient->getWallet($chain['id'], strtolower($address));
        $tokens = array_map(static function (array $x) use ($iconResolver, $chain) {
            $x['icon'] = $iconResolver->getTokenIconForSymbolAddress($chain['id'], [[
                'address' => $x['token'],
                'symbol' => $x['symbol'],
            ]]);

            return $x;
        }, $walletRaw['tokens'] ?? []);

        $liquidityPools = array_map(static function (array $x) use ($iconResolver, $chain) {
            $parts = array_map(
                fn(string $part) => ['symbol' => $part],
                explode('-', $x['symbol'])
            );

            $x['icon'] = $iconResolver->getTokenIconForSymbolAddress($chain['id'], $parts);

            return $x;
        }, $walletRaw['liquidityPools'] ?? []);

        $wallet = [...$tokens, ...$liquidityPools];

        usort($wallet, static function ($a, $b) {
            return ($b['usd'] ?? 0) <=> ($a['usd'] ?? 0);
        });

        $html = $twig->render('address/wallet/modal.html.twig', [
            'address' => $address,
            'address_truncate' => substr($address, 0, 8) . '...' . substr($address, -8),
            'wallet' => $wallet,
            'chain_context' => $chain,
        ]);

        $response = new JsonResponse([
            'tokens' => $tokens,
            'liquidityPools' => $liquidityPools,
            'html' => $html,
        ]);

        $response->setPublic();
        $response->setMaxAge(9);
        $response->headers->set('X-Robots-Tag', 'noindex');

        return $response;
    }

    /**
     * @Route("/farms/0x{address}/{farmId}", name="farm_detail")
     */
    public function detail(string $address, string $farmId, NodeClient $nodeClient, IconResolver $iconResolver, FarmPools $farmPools): Response
    {
        $farm = $this->crossFarmRepository->findFarmIdByHash($farmId);
        if (!$farm) {
            throw new NotFoundHttpException();
        }

        $chain = $farm->getChain();

        $details = $nodeClient->getDetails($chain, $address, $farm->getFarmId());

        foreach ($details['lpTokens'] ?? [] as $key => $lpToken) {
            $details['lpTokens'][$key]['icon'] = $iconResolver->getIcon($lpToken['symbol'], $chain);
        }

        foreach ($details['yield']['lpTokens'] ?? [] as $key => $lpToken) {
            $details['yield']['lpTokens'][$key]['icon'] = $iconResolver->getIcon($lpToken['symbol'], $chain);
        }

        foreach ($details['farm']['rewards'] ?? [] as $key => $reward) {
            $details['farm']['rewards'][$key]['icon'] = $iconResolver->getIcon($reward['symbol'], $chain);
        }

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(9);

        return $this->render('address/details.html.twig', [
            'farm' => $farmPools->enrichFarmData($farm->getJson()),
            'details' => $details,
            'chain_context' => ChainUtil::getChain($farm->getChain()),
        ], $response);
    }

    /**
     * @Route("/farms/0x{address}/{farmId}/action", name="farm_action")
     */
    public function actions(string $address, string $farmId, NodeClient $nodeClient, NodeClient $client): Response
    {
        $farm = $this->crossFarmRepository->findFarmIdByHash($farmId);
        if (!$farm) {
            throw new NotFoundHttpException();
        }

        $details = $nodeClient->getDetails($farm->getChain(), $address, $farm->getFarmId());

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(9);

        $chain = ChainUtil::getChain($details['farm']['farm']['chain']);

        $actions = array_map(static function(array $item) {
            $title = $item['method'];

            if ($item['type'] === 'claim_all') {
                $title = 'Claim All';
            } else if ($item['type'] === 'claim') {
                $title = 'Claim';
            } else if ($item['type'] === 'claim_fake') {
                $title = 'Claim';
            }

            $substr = '';
            if (isset($item['inputs']) && count($item['inputs']) > 0) {
                $items = array_map(static function($item) {
                    if (is_string($item) && str_starts_with($item, '0x')) {
                        return substr($item, 0, 3) . '...' . substr($item, -3);
                    }

                    return $item;
                }, $item['inputs']);

                $string = json_encode($items);
                $substr = substr($string, 1, -1);
            }

            $arr = [
                'contract' => $item['contract'],
                'title' => $title,
                'signature' => $item['method'] . '(' . $substr . ')',
                'web3' => $item,
                'arguments' => $item['arguments'] ?? [],
            ];

            if (isset($item['arguments']) && count($item['arguments']) > 0) {
                $arr['named_signature'] = sprintf("%s(%s)", $item['method'], implode(', ', $item['arguments']));
            }

            return $arr;
        }, $details['farm']['farm']['actions'] ??  []);

        $prices = $client->getPrices($chain['id']);

        return $this->render('address/actions.html.twig', [
            'address' => $address,
            'details' => $details,
            'chain_context' => $chain,
            'gas_price' => $prices[$chain['token']] ?? null,
            'actions' => $actions,
        ], $response);
    }

    private function getChainOrThrowNotFound(?string $chain): string
    {
        if (!$chain) {
            $chain = $this->chainGuesser->getChain();
        }

        try {
            ChainUtil::getChain($chain);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('invalid chain');
        }

        return $chain;
    }
}
