<?php

namespace App\Controller;

use App\Pools\FarmPools;
use App\Repository\CrossPlatformRepository;
use App\Utils\ChainGuesser;
use App\Utils\ChainUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PoolController extends AbstractController
{
    private ChainGuesser $chainGuesser;
    private CrossPlatformRepository $crossPlatformRepository;

    public function __construct(
        ChainGuesser $chainGuesser,
        CrossPlatformRepository $crossPlatformRepository
    ) {
        $this->chainGuesser = $chainGuesser;
        $this->crossPlatformRepository = $crossPlatformRepository;
    }

    /**
     * @Route("/farm-pools", name="pools", methods={"GET"})
     * @Route("/farm-pools/{chain}", name="pools_chain", methods={"GET"}, requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos"
     * })
     */
    public function index(FarmPools $farmPools, ?string $chain)
    {
        if (!$chain) {
            $chain = $this->chainGuesser->getChain();
        }

        if (!ChainUtil::getChainOrNull($chain)) {
            throw $this->createNotFoundException('Invalid chain');
        }

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $this->render('pool/index.html.twig', [
            'chain_context' => ChainUtil::getChain($chain),
            'farms_preload' => json_encode([
                'farms' => array_slice($farmPools->renderAllFarms($chain), 0, 20),
                'platforms' => $this->crossPlatformRepository->getPlatformsOnChain($chain),
            ])
        ], $response);
    }

    /**
     * @Route("/farms/{chain}.json", name="app_default_farms", methods={"GET"}, requirements={
     *  "chain"="bsc|polygon|fantom|kcc|harmony|celo|moonriver|cronos"
     * })
     */
    public function farms(FarmPools $farmPools, string $chain): JsonResponse
    {
        $response = new JsonResponse([
            'farms' => $farmPools->renderAllFarms($chain),
            'platforms' => $this->crossPlatformRepository->getPlatformsOnChain($chain),
        ]);

        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $response;
    }
}