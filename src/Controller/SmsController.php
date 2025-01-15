<?php

namespace App\Controller;


use App\Repository\UserRepository;
use App\Service\SmsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SmsController extends AbstractController
{
    #[Route('/sms', name: 'app_sms')]
    public function index(): Response
    {


        return $this->render('sms/index.html.twig',['smsSent'=>false]);
    }
    #[Route('/sendSms', name: 'send_sms', methods: ['GET'])]
    public function sendSms(Request $request, SmsGenerator $smsGenerator): Response
    {
        $projectId = $request->query->get('id'); // Récupère l'id du projet depuis la requête

        if (!$projectId) {
            throw $this->createNotFoundException('No project ID provided.');
        }

        // Logique d'envoi du SMS ici
        $smsGenerator->sendSmsToProjectUser($projectId);

        // Retourne une vue ou un JSON
        return $this->render('sms/index.html.twig', [
            'smsSent' => true,
            'projectId' => $projectId,
        ]);
    }

}
