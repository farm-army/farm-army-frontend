<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KitchensinkController extends AbstractController
{
    /**
     * @Route(
     *     "/kitchensink",
     *     name="kitchensink",
     *     condition="'dev' === '%kernel.environment%'",
     *     defaults={"_firewall": true, "_token_check": false}
     * )
     */
    public function index(): Response
    {
        return $this->render('kitchensink/index.html.twig');
    }
}