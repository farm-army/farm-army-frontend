<?php

namespace App\Controller;

use App\Pools\FarmPools;
use App\Repository\PlatformRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PoolController extends AbstractController
{
    private $platformRepository;

    public function __construct(PlatformRepository $platformRepository)
    {
        $this->platformRepository = $platformRepository;
    }

    /**
     * @Route("/farm-pools", name="pools", methods={"GET"})
     */
    public function index(FarmPools $farmPools)
    {
        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $this->render('pool/index.html.twig', [
            'farms_preload' => json_encode([
                'farms' => array_slice($farmPools->renderAllFarms(), 0, 20),
                'platforms' => $this->platformRepository->getPlatforms(),
            ])
        ], $response);
    }


    /**
     * @Route("/farms.json", name="app_default_farms", methods={"GET"})
     */
    public function farms(FarmPools $farmPools): JsonResponse
    {
        $response = new JsonResponse([
            'farms' => $farmPools->renderAllFarms(),
            'platforms' => $this->platformRepository->getPlatforms(),
        ]);

        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $response;
    }
}