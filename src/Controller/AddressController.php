<?php

namespace App\Controller;

use App\Client\NodeClient;
use App\Pools\FarmPools;
use App\Symbol\IconResolver;
use App\Utils\RandomAddress;
use App\Utils\Web3Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends AbstractController
{
    /**
     * @Route("/0x{address}", name="app_farm_index")
     */
    public function index(string $address): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            return new Response($this->renderView('address/invalid.html.twig'), 404);
        }

        $var = [
            'address' => $address,
            'address_truncate' => substr($address, 0, 8) . '...' . substr($address, -8),
        ];

        $var['app_context'] = $var;

        return $this->render('address/index.html.twig', $var);
    }

    /**
     * @Route("/random", name="random_address")
     */
    public function random(RandomAddress $randomAddress): Response
    {
        $randomAddresses = $randomAddress->getRandomAddresses();

        return $this->forward('App\Controller\FarmController::index', [
            'address' => substr($randomAddresses[array_rand($randomAddresses)], 2),
        ]);
    }

    /**
     * @Route("/farms/0x{address}", name="farm_content")
     */
    public function ajax(string $address, NodeClient $nodeClient): Response
    {
        $address = '0x' . $address;

        if (!Web3Util::isAddress($address)) {
            return new Response($this->renderView('address/invalid.html.twig'), 404);
        }

        $address = strtolower($address);

        $addressFarms = $nodeClient->getAddressFarms($address);

        $response = new Response();

        $response->setPublic();
        $response->setMaxAge(9);

        return $this->render('address/content.html.twig', [
            'address' => $address,
            'address_truncate' => substr($address, 0, 8) . '...' . substr($address, -8),
            'platforms' => $addressFarms['farms'],
            'wallet' => $addressFarms['wallet'],
            'summary' => $addressFarms['summary'],
        ], $response);
    }

    /**
     * @Route("/farms/0x{address}/{farmId}", name="farm_detail")
     */
    public function detail(string $address, string $farmId, NodeClient $nodeClient, IconResolver $iconResolver, FarmPools $farmPools): Response
    {
        $farm = null;
        foreach ($farmPools->generateFarms() as $farm1) {
            $id = $farm1['id'] ?? 'foo';
            if (md5($id) === $farmId) {
                $farm = $farm1;
                break;
            }
        }

        if (!$farm) {
            throw new NotFoundHttpException();
        }

        $details = $nodeClient->getDetails($address, $farm['id']);

        if (isset($details['lpTokens'])) {
            foreach ($details['lpTokens'] as $key => $lpToken) {
                $details['lpTokens'][$key]['icon'] = $iconResolver->getIcon($lpToken['symbol']);
            }
        }

        return $this->render('address/details.html.twig', [
            'farm' => $farm,
            'details' => $details,
        ]);
    }
}
