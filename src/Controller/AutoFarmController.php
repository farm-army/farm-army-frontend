<?php declare(strict_types=1);

namespace App\Controller;

use App\Client\NodeClient;
use App\Form\AutoFarmType;
use App\Pools\FarmPools;
use App\Utils\Web3Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AutoFarmController extends AbstractController
{
    private NodeClient $nodeClient;
    private FarmPools $farmPools;

    public function __construct(NodeClient $nodeClient, FarmPools $farmPools)
    {
        $this->nodeClient = $nodeClient;
        $this->farmPools = $farmPools;
    }

    /**
     * @Route("/masterchef/0x{masterChef}/0x{address}", name="auto_farm_masterchef_address", methods={"GET"}, requirements={"masterChef": "^[0-9a-fA-F]{40}$","address": "^[0-9a-fA-F]{40}$"})
     * @Route("/masterchef/0x{masterChef}", name="auto_farm_masterchef", methods={"GET"}, requirements={"address": "^[0-9a-fA-F]{40}$"})
     */
    public function masterChef(string $masterChef, ?string $address): Response
    {
        $masterChef = '0x' . $masterChef;
        $address = $address ? '0x' . $address : null;

        if (!Web3Util::isAddress($masterChef) || ($address && !Web3Util::isAddress($address))) {
            throw $this->createNotFoundException('address invalid');
        }

        $form = $this->createForm(AutoFarmType::class, [
            'masterchef' => $masterChef,
            'address' => $address,
        ], [
            'action' => $this->generateUrl('auto_farm'),
        ]);

        $response = $this->nodeClient->getAutofarm($masterChef, $address);

        $farms = $this->farmPools->renderFarms($response['masterChefMetadata']['pools'] ?? [], 'autofarm/farms/farms.html.twig');

        return $this->render('autofarm/index.html.twig', [
            'master_chef_farms' => $farms,
            'address_masterchef' => $masterChef,
            'pools' => $this->nodeClient->formatFarms($response['pools'] ?? []),
            'address' => $address,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/masterchef", name="auto_farm", methods={"GET", "POST"})
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(AutoFarmType::class, null, [
            'action' => $this->generateUrl('auto_farm'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $masterchef = $form->get('masterchef')->getData();
            $address = $form->get('address')->getData();

            if ($masterchef && $address) {
                return $this->redirectToRoute('auto_farm_masterchef_address', [
                    'address' => substr($address, 2),
                    'masterChef' => substr($masterchef, 2),
                ]);
            }

            return $this->redirectToRoute('auto_farm_masterchef', [
                'masterChef' => substr($masterchef, 2),
            ]);
        }

        return $this->render('autofarm/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}