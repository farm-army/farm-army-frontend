<?php

namespace App\Controller;

use App\Client\NodeClient;
use App\Pools\FarmPools;
use App\Repository\FarmRepository;
use App\Repository\PlatformRepository;
use App\Symbol\IconResolver;
use App\Utils\ChainGuesser;
use App\Utils\ChainUtil;
use App\Utils\RandomAddress;
use App\Utils\Web3Util;
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
    /**
     * @Route("/0x{address}", name="app_farm_index")
     */
    public function index(string $address, PlatformRepository $platformRepository, UrlGeneratorInterface $urlGenerator, ChainUtil $chainUtil, ChainGuesser $chainGuesser): Response
    {
        $addressNoPrefix = $address;
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            return new Response($this->renderView('address/invalid.html.twig'), 404);
        }

        $var = [
            'explorer' => $chainUtil->getChainExplorerUrl($chainGuesser->getChain()),
            'address' => $address,
            'address_truncate' => substr($address, 0, 8) . '...' . substr($address, -8),
            'platform_chunks' => array_map(
                fn($chunk) => $urlGenerator->generate('farm_json_platform_chunks', ['address' => $addressNoPrefix, 'chunk' => $chunk]),
                array_keys($platformRepository->getPlatformChunks())
            ),
            'wallet_url' => $urlGenerator->generate('farm_json_wallet', ['address' => $addressNoPrefix]),
        ];

        $var['app_context'] = $var;


        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(9);

        return $this->render('address/index.html.twig', $var, $response);
    }

    /**
     * @Route("/0x{address}/transactions", name="app_farm_transactions", methods={"GET"})
     */
    public function transactions(string $address, NodeClient $nodeClient, ChainUtil $chainUtil, ChainGuesser $chainGuesser): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            throw new BadRequestHttpException('invalid address');
        }

        $response = new Response();
        $response->headers->set('X-Robots-Tag', 'noindex');

        $response->setPublic();
        $response->setMaxAge(9);

        $transactions = $nodeClient->getTransactions($address, $chainGuesser->getChain());

        return $this->render('address/transactions.html.twig', [
            'explorer' => $chainUtil->getChainExplorerUrl($chainGuesser->getChain()),
            'address' => $address,
            'transactions' => $transactions,
        ], $response);
    }
    /**
     * @Route("/random", name="random_address")
     */
    public function random(RandomAddress $randomAddress): Response
    {
        $randomAddresses = $randomAddress->getRandomAddresses();

        return $this->forward('App\Controller\AddressController::index', [
            'address' => substr($randomAddresses[array_rand($randomAddresses)], 2),
        ]);
    }

    /**
     * @Route("/farms/0x{address}", name="farm_content")
     */
    public function ajax(string $address, NodeClient $nodeClient): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            return new Response($this->renderView('address/invalid.html.twig'), 404);
        }

        $address = strtolower($address);

        $addressFarms = $nodeClient->getAddressFarms($address);

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
        ], $response);
    }

    /**
     * @Route("/farms/0x{address}/platform/{chunk}.json", name="farm_json_platform_chunks", methods={"GET"}, requirements={
     *      "chunk"="\d{1,2}",
     *      "_format"="json",
     * })
     */
    public function jsonAjax(string $address, string $chunk, NodeClient $nodeClient, Environment $twig, PlatformRepository $platformRepository): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            throw new BadRequestHttpException('Invalid address');
        }

        $chunks = $platformRepository->getPlatformChunks();
        if (!isset($chunks[$chunk])) {
            throw new BadRequestHttpException('Invalid chunk');
        }

        $platforms = $nodeClient->getAddressFarmsForPlatforms(strtolower($address), $chunks[$chunk]);

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
     * @Route("/farms/0x{address}/wallet.json", name="farm_json_wallet", methods={"GET"}, requirements={
     *      "_format"="json",
     * })
     */
    public function jsonWallet(string $address, NodeClient $nodeClient, Environment $twig, IconResolver $iconResolver): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            throw new BadRequestHttpException('Invalid address');
        }

        $walletRaw = $nodeClient->getWallet(strtolower($address));
        $tokens = array_map(static function (array $x) use ($iconResolver) {
            $x['icon'] = $iconResolver->getTokenIconForSymbolAddress([[
                'address' => $x['token'],
                'symbol' => $x['symbol'],
            ]]);

            return $x;
        }, $walletRaw['tokens'] ?? []);

        $liquidityPools = array_map(static function (array $x) use ($iconResolver) {
            $parts = array_map(
                fn(string $part) => ['symbol' => $part],
                explode('-', $x['symbol'])
            );

            $x['icon'] = $iconResolver->getTokenIconForSymbolAddress($parts);

            return $x;
        }, $walletRaw['liquidityPools'] ?? []);

        $wallet = [...$tokens, ...$liquidityPools];

        usort($wallet, static function ($a, $b) {
            return ($b['usd'] ?? 0) <=> ($a['usd'] ?? 0);
        });

        $html = $twig->render('address/wallet/modal.html.twig', [
            'address' => $address,
            'address_truncate' => substr($address, 0, 8) . '...' . substr($address, -8),
            'wallet' => $wallet
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
    public function detail(string $address, string $farmId, NodeClient $nodeClient, IconResolver $iconResolver, FarmPools $farmPools, FarmRepository $farmRepository): Response
    {
        $farm = $farmRepository->findFarmIdByHash($farmId);
        if (!$farm) {
            throw new NotFoundHttpException();
        }

        $details = $nodeClient->getDetails($address, $farm->getFarmId());

        if (isset($details['lpTokens'])) {
            foreach ($details['lpTokens'] as $key => $lpToken) {
                $details['lpTokens'][$key]['icon'] = $iconResolver->getIcon($lpToken['symbol']);
            }
        }

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(9);

        return $this->render('address/details.html.twig', [
            'farm' => $farmPools->enrichFarmData($farm->getJson()),
            'details' => $details,
        ], $response);
    }

    /**
     * @Route("/farms/0x{address}/{farmId}/action", name="farm_action")
     */
    public function actions(string $address, string $farmId, NodeClient $nodeClient, FarmRepository $farmRepository, NodeClient $client): Response
    {
        $farm = $farmRepository->findFarmIdByHash($farmId);
        if (!$farm) {
            throw new NotFoundHttpException();
        }

        $details = $nodeClient->getDetails($address, $farm->getFarmId());

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(9);

        $chain = ChainUtil::getChain($details['farm']['farm']['chain']);

        $actions = array_map(function(array $item) {
            $title = $item['method'];

            if ($item['type'] === 'claim_all') {
                $title = 'Claim All';
            } else if ($item['type'] === 'claim') {
                $title = 'Claim';
            } else if ($item['type'] === 'claim_fake') {
                $title = 'Claim';
            }

            $substr = isset($item['inputs']) && count($item['inputs']) > 0
                ? substr(json_encode($item['inputs']), 1, -1)
                : null;

            return [
                'contract' => $item['contract'],
                'title' => $title,
                'signature' => $item['method'] . '(' . $substr . ')',
                'web3' => $item,
            ];
        }, $details['farm']['farm']['actions'] ??  []);

        $prices = $client->getPrices();

        return $this->render('address/actions.html.twig', [
            'address' => $address,
            'details' => $details,
            'chain' => $chain,
            'gas_price' => $prices[$chain['token']] ?? null,
            'actions' => $actions,
        ], $response);
    }
}
