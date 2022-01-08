<?php

namespace App\Controller;

use App\Pools\FarmPools;
use App\Repository\CrossFarmRepository;
use App\Utils\ChainUtil;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArrivalsController extends AbstractController
{
    private FarmPools $farmPools;
    private CacheItemPoolInterface $cacheItemPool;
    private CrossFarmRepository $crossFarmRepository;

    public function __construct(
        FarmPools $farmPools,
        CacheItemPoolInterface $cacheItemPool,
        CrossFarmRepository $crossFarmRepository
    ) {
        $this->farmPools = $farmPools;
        $this->cacheItemPool = $cacheItemPool;
        $this->crossFarmRepository = $crossFarmRepository;
    }

    /**
     * @Route("/arrivals", name="arrivals", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 15);
        $response->setEtag(md5($request->getUri()));

        $chains = [];
        if ($request->query->has('chain')) {
            if (!$chain = ChainUtil::getChainOrNull($request->query->get('chain'))) {
                throw $this->createNotFoundException('Invalid chain');
            }

            $chains[] = $chain['id'];
        }

        return $this->render('arrivals/index.html.twig', [
            'timeline' => $this->getFarms($chains),
            'chains' => $chains,
        ], $response);
    }

    private function getFarms(array $chains): array
    {
        $sortedChains = $chains;
        asort($sortedChains);

        $cache = $this->cacheItemPool->getItem('arrivals-farms-v4-' . md5(json_encode($sortedChains)));

        if ($cache->isHit()) {
            return $cache->get();
        }

        $result = [];

        foreach ($this->crossFarmRepository->getNewFarmsTimeline($sortedChains) as $farm) {
            $date = $farm['createdAt']->format('Y-m-d');
            if (!isset($result[$date])) {
                $result[$date] = [
                    'date' => $date,
                    'items' => [],
                ];
            }

            $result[$date]['items'][] = $this->farmPools->renderFarms(
                [$farm['json']],
                'components/farms_frontpage.html.twig',
                ['cross_chain' => true]
            )[0]['content'];
        }

        $result = array_values($result);

        $this->cacheItemPool->save(
            $cache->set($result)->expiresAfter(60 * 30)
        );

        return $result;
    }
}
