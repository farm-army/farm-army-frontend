<?php declare(strict_types=1);

namespace App\Controller;

use App\Client\NodeClient;
use App\Pools\FarmPools;
use App\Utils\Web3Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api", name="api_docu", methods={"GET"})
     */
    public function api(): Response
    {
        return $this->render('api/api.html.twig');
    }

    /**
     * @Route("/api/v0/farms", name="api_farms", methods={"GET"})
     */
    public function index(FarmPools $farmPools): Response
    {
        $response = new JsonResponse($farmPools->generateApiFarms());

        $response->setPublic();
        $response->setMaxAge(60 * 30);

        return $response;
    }

    /**
     * @Route("/api/v0/prices", name="api_prices", methods={"GET"})
     */
    public function prices(NodeClient $nodeClient): Response
    {
        return new JsonResponse($nodeClient->getPrices());
    }

    /**
     * @Route("/api/v0/tokens", name="api_tokens", methods={"GET"})
     */
    public function tokens(NodeClient $nodeClient): Response
    {
        return new JsonResponse($nodeClient->getTokens());
    }

    /**
     * @Route("/api/v0/liquidity-tokens", name="api_liquidity_tokens", methods={"GET"})
     */
    public function liquidityTokens(NodeClient $nodeClient): Response
    {
        return new JsonResponse($nodeClient->getLiquidityTokens());
    }

    /**
     * @Route("/api/v0/farms/0x{address}", name="api_farm_addreses", methods={"GET"})
     */
    public function address(NodeClient $nodeClient, string $address): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            return new JsonResponse(['message' => 'invalid address'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'platforms' => $nodeClient->getAddressFarms($address)['farms'],
        ]);
    }

    /**
     * @Route("/api/v0/balances/0x{address}", name="api_farm_balances", methods={"GET"})
     */
    public function balances(NodeClient $nodeClient, string $address): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            return new JsonResponse(['message' => 'invalid address'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($nodeClient->getBalances($address));
    }
}