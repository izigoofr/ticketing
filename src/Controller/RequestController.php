<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RequestController extends AbstractController
{
    #[Route('/request', name: 'request')]
    public function index(): Response
    {
        return $this->render('request/index.html.twig', [
            'controller_name' => 'RequestController',
        ]);
    }
}
