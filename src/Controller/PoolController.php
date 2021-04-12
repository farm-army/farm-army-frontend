<?php

namespace App\Controller;

use App\Pools\FarmPools;
use App\Repository\PlatformRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                'farms' => array_slice($farmPools->generateContent(), 0, 20),
                'platforms' => $this->platformRepository->getPlatforms(),
            ])
        ], $response);
    }
}