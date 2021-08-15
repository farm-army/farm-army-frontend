<?php

namespace App\Controller;

use App\Pools\FarmPools;
use App\Repository\FarmRepository;
use App\Repository\PlatformRepository;
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
    private PlatformRepository $platformRepository;
    private FarmRepository $farmRepository;
    private FarmPools $farmPools;
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        PlatformRepository $platformRepository,
        FarmRepository $farmRepository,
        FarmPools $farmPools,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->platformRepository = $platformRepository;
        $this->farmRepository = $farmRepository;
        $this->farmPools = $farmPools;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @Route("/theme", name="theme_toggle", methods={"POST"})
     */
    public function theme(Request $request): Response
    {
        $response = new Response();

        $theme = $request->get('theme', 'light');

        $response->headers->setCookie(new Cookie('theme', $theme, date_create()->modify('+180 days')));

        return $response;
    }

    /**
     * @Route("/", name="frontpage", methods={"GET"})
     */
    public function index(Request $request)
    {
        $platforms = $this->platformRepository->getPlatforms();

        $parameters = [
            'platforms' => $platforms,
            'providers' => $this->platformRepository->getPlatforms(),
        ];

        if ($chainAddress = $request->cookies->get('chain_address')) {
            $parameters['chain_address'] = $chainAddress;
        }

        $parameters = array_merge($parameters, $this->getFrontpageFarms());

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $this->render('frontpage/frontpage.html.twig', $parameters, $response);
    }

    private function getFrontpageFarms(): array
    {
        $cache = $this->cacheItemPool->getItem('frontpage-farms-v4');

        if ($cache->isHit()) {
            return $cache->get();
        }

        $this->farmPools->triggerFetchUpdate();

        $news = array_map(static fn(array $f) => $f['json'], $this->farmRepository->getNewFarm());
        $tvls = array_map(static fn(array $f) => $f['json'], $this->farmRepository->getTvl());

        $result = [
            'new' => $this->farmPools->renderFarms($news, 'components/farms_frontpage.html.twig'),
            'tvl' => $this->farmPools->renderFarms($tvls, 'components/farms_frontpage.html.twig'),
        ];

        $this->cacheItemPool->save(
            $cache->set($result)->expiresAfter(60 * 30)
        );

        return $result;
    }

    /**
     * @Route("/", methods={"POST"})
     */
    public function post(Request $request) {
        if (($address = $request->request->get('chain_address')) && Web3Util::isAddress($address)) {
            $response = new RedirectResponse($this->generateUrl('app_farm_index', ['address' => substr($address, 2)]));

            $response->headers->setCookie(new Cookie('chain_address', $address, date_create()->modify('+ 180 days'), '/', null, null, false));

            return $response;
        }

        $platforms = $this->platformRepository->getPlatforms();

        $parameters = [
            'invalid' => true,
            'platforms' => $platforms,
            'chain_address' => $address ?? '',
            'providers' => $platforms,
        ];

        $parameters = array_merge($parameters, $this->getFrontpageFarms());

        return $this->render('frontpage/frontpage.html.twig', $parameters);
    }

    /**
     * @Route("/sitemap.xml", name="sitemap", methods={"GET"})
     */
    public function sitemap(): Response
    {
        $content = $this->renderView('seo/sitemap.xml.twig', [
            'farms' => $this->farmRepository->getFarmHashes(),
            'tokens' => $this->farmRepository->getFarmTokens(),
        ]);

        $response = new Response($content, 200, [
            'Content-type' => ' text/xml; charset=utf-8'
        ]);

        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $response;
    }
}