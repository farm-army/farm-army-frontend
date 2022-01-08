<?php declare(strict_types=1);

namespace App\Controller;

use App\Client\NodeClient;
use App\Pools\FarmPools;
use App\Utils\ChainGuesser;
use App\Utils\ChainUtil;
use App\Utils\Web3Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private ChainGuesser $chainGuesser;

    public function __construct(ChainGuesser $chainGuesser)
    {
        $this->chainGuesser = $chainGuesser;
    }

    /**
     * @Route("/api", name="api_docu", methods={"GET"})
     */
    public function api(): Response
    {
        $chainIds = array_map(
            fn(array $chain) => $chain['id'],
            ChainUtil::getChains()
        );

        return $this->render('api/api.html.twig', [
            'chain_ids' => $chainIds,
        ]);
    }

    /**
     * @Route("/api/v0/farms", name="api_farms", methods={"GET"})
     */
    public function index(Request $request, FarmPools $farmPools): Response
    {
        $chain = $this->getChainOrThrowNotFound($request);

        $response = new JsonResponse($farmPools->generateApiFarms($chain));

        $response->setPublic();
        $response->setMaxAge(60 * 30);
        $response->setEtag(md5($request->getUri()));

        return $response;
    }

    /**
     * @Route("/api/v0/prices", name="api_prices", methods={"GET"})
     */
    public function prices(Request $request, NodeClient $nodeClient): Response
    {
        $chain = $this->getChainOrThrowNotFound($request);

        return new JsonResponse($nodeClient->getPrices($chain));
    }

    /**
     * @Route("/api/v0/tokens", name="api_tokens", methods={"GET"})
     */
    public function tokens(Request $request, NodeClient $nodeClient): Response
    {
        $chain = $this->getChainOrThrowNotFound($request);

        return new JsonResponse($nodeClient->getTokens($chain));
    }

    /**
     * @Route("/api/v0/liquidity-tokens", name="api_liquidity_tokens", methods={"GET"})
     */
    public function liquidityTokens(Request $request, NodeClient $nodeClient): Response
    {
        $chain = $this->getChainOrThrowNotFound($request);

        return new JsonResponse($nodeClient->getLiquidityTokens($chain));
    }

    /**
     * @Route("/api/v0/farms/0x{address}", name="api_farm_addreses", methods={"GET"})
     */
    public function address(Request $request, NodeClient $nodeClient, ?string $chain, string $address): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            return new JsonResponse(['message' => 'invalid address'], Response::HTTP_BAD_REQUEST);
        }

        $chain = $this->getChainOrThrowNotFound($request);

        return new JsonResponse([
            'platforms' => $nodeClient->getAddressFarms($chain, $address)['farms'],
        ]);
    }

    /**
     * @Route("/api/v0/balances/0x{address}", name="api_farm_balances", methods={"GET"})
     */
    public function balances(Request $request, NodeClient $nodeClient, string $address): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            return new JsonResponse(['message' => 'invalid address'], Response::HTTP_BAD_REQUEST);
        }

        $chain = $this->getChainOrThrowNotFound($request);

        return new JsonResponse($nodeClient->getBalances($chain, $address));
    }

    private function getChainOrThrowNotFound(Request $request): string
    {
        $chain = $request->query->has('chain')
            ? $request->query->get('chain')
            : $this->chainGuesser->getChain();

        try {
            ChainUtil::getChain($chain);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('invalid chain');
        }

        return $chain;
    }
}