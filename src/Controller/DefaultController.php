<?php

namespace App\Controller;

use App\Pools\FarmPools;
use App\Repository\CrossFarmRepository;
use App\Repository\CrossPlatformRepository;
use App\Utils\ChainGuesser;
use App\Utils\ChainUtil;
use App\Utils\Web3Util;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private FarmPools $farmPools;
    private CacheItemPoolInterface $cacheItemPool;
    private ChainGuesser $chainGuesser;
    private CrossPlatformRepository $crossPlatformRepository;
    private CrossFarmRepository $crossFarmRepository;

    public function __construct(
        FarmPools $farmPools,
        CacheItemPoolInterface $cacheItemPool,
        CrossPlatformRepository $crossPlatformRepository,
        ChainGuesser $chainGuesser,
        CrossFarmRepository $crossFarmRepository
    ) {
        $this->farmPools = $farmPools;
        $this->cacheItemPool = $cacheItemPool;
        $this->chainGuesser = $chainGuesser;
        $this->crossPlatformRepository = $crossPlatformRepository;
        $this->crossFarmRepository = $crossFarmRepository;
    }

    /**
     * @Route("/{chain}", name="frontpage_chain", methods={"GET"}, requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam"
     * })
     * @Route("/", name="frontpage", methods={"GET"})
     */
    public function index(Request $request, ?string $chain)
    {
        $chainId = $this->getChainOrThrowNotFound($chain);

        $parameters = [
            'platforms' => $this->crossPlatformRepository->getPlatformsOnChain($chainId),
            'chain_context' => ChainUtil::getChain($chainId),
        ];

        if ($chainAddress = $request->cookies->get('chain_address_' . $chainId)) {
            $parameters['chain_address'] = $chainAddress;
        } elseif ($chainAddress = $request->cookies->get('chain_address')) {
            $parameters['chain_address'] = $chainAddress;
        }

        $parameters = array_merge($parameters, $this->getFrontpageFarms($chainId));

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $this->render('frontpage/frontpage.html.twig', $parameters, $response);
    }

    private function getFrontpageFarms(string $chain): array
    {
        $cache = $this->cacheItemPool->getItem('frontpage-farms-v4-' . $chain);

        if ($cache->isHit()) {
            return $cache->get();
        }

        $news = array_map(static fn(array $f) => $f['json'], $this->crossFarmRepository->getNewFarm($chain));
        $tvls = array_map(static fn(array $f) => $f['json'], $this->crossFarmRepository->getTvl($chain));
        $crossNew = array_map(static fn(array $f) => $f['json'], $this->crossFarmRepository->getAllNewFarm(30));

        $result = [
            'new' => $this->farmPools->renderFarms($news, 'components/farms_frontpage.html.twig'),
            'tvl' => $this->farmPools->renderFarms($tvls, 'components/farms_frontpage.html.twig'),
            'cross_new' => $this->farmPools->renderFarms($crossNew, 'components/farms_frontpage.html.twig', ['cross_chain' => true]),
        ];

        $this->cacheItemPool->save(
            $cache->set($result)->expiresAfter(60 * 30)
        );

        return $result;
    }

    /**
     * @Route("/{chainId}", methods={"POST"}, name="frontpage_post_chain", requirements={
     *  "chainId"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos|moonbeam"
     * })
     * @Route("/", methods={"POST"}, name="frontpage_post")
     */
    public function post(Request $request, ?string $chainId) {
        $chain = $this->getChainOrThrowNotFound($chainId);

        if (($address = $request->request->get('chain_address')) && Web3Util::isAddress($address)) {
            $arguments = ['address' => substr($address, 2)];

            if ($chainId !== null) {
                $arguments['chain'] = $chainId;
                $route = 'chain_app_farm_index';
            } else {
                $route = 'app_farm_index';
            }

            $response = new RedirectResponse($this->generateUrl($route, $arguments));

            if (!$request->cookies->has('chain_address')) {
                $response->headers->setCookie(new Cookie('chain_address', $address, date_create()->modify('+ 180 days'), '/', null, null, false));
            }

            $response->headers->setCookie(new Cookie('chain_address_' . $chain, $address, date_create()->modify('+ 180 days'), '/', null, null, false));

            return $response;
        }

        $parameters = [
            'invalid' => true,
            'platforms' => $this->crossPlatformRepository->getPlatformsOnChain($chain),
            'chain_address' => $address ?? '',
            'chain_context' => ChainUtil::getChain($chain),
        ];

        $parameters = array_merge($parameters, $this->getFrontpageFarms($chain));

        return $this->render('frontpage/frontpage.html.twig', $parameters);
    }

    /**
     * @Route("/sitemap.xml", name="sitemap", methods={"GET"})
     */
    public function sitemap(): Response
    {
        $content = $this->renderView('seo/sitemap.xml.twig', [
            'farms' => $this->crossFarmRepository->getFarmHashes(),
            'tokens' => $this->crossFarmRepository->getFarmTokens(),
        ]);

        $response = new Response($content, 200, [
            'Content-type' => ' text/xml; charset=utf-8'
        ]);

        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $response;
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