<?php

namespace App\Controller;

use App\Client\NodeClient;
use App\Pools\FarmPools;
use App\Repository\PlatformRepository;
use App\Symbol\IconResolver;
use App\Utils\InterestUtil;
use App\Utils\Web3Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
        return $this->render('pool/index.html.twig', [
            'farms_preload' => json_encode([
                'farms' => array_slice($farmPools->generateContent(), 0, 20),
                'platforms' => $this->platformRepository->getPlatforms(),
            ])
        ]);
    }
}