<?php

namespace App\Controller;

use App\Pools\FarmPools;
use App\Repository\FarmRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArrivalsController extends AbstractController
{
    private FarmRepository $farmRepository;
    private FarmPools $farmPools;
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        FarmPools $farmPools,
        FarmRepository $farmRepository,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->farmPools = $farmPools;
        $this->cacheItemPool = $cacheItemPool;
        $this->farmRepository = $farmRepository;
    }

    /**
     * @Route("/arrivals", name="arrivals", methods={"GET"})
     */
    public function index(): Response
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 15);

        return $this->render('arrivals/index.html.twig', [
            'timeline' => $this->getFarms(),
        ], $response);
    }

    private function getFarms(): array
    {
        $cache = $this->cacheItemPool->getItem('arrivals-farms-v3');

        if ($cache->isHit()) {
            return $cache->get();
        }

        $this->farmPools->triggerFetchUpdate();

        $result = [];

        foreach ($this->farmRepository->getNewFarmsTimeline() as $farm) {
            $date = $farm['createdAt']->format('Y-m-d');
            if (!isset($result[$date])) {
                $result[$date] = [
                    'date' => $date,
                    'items' => [],
                ];
            }

            $result[$date]['items'][] = $this->farmPools->renderFarms([$farm['json']], 'components/farms_frontpage.html.twig')[0]['content'];
        }

        $result = array_values($result);

        $this->cacheItemPool->save(
            $cache->set($result)->expiresAfter(60 * 30)
        );

        return $result;
    }
}
