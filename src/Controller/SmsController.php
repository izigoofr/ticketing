<?php

namespace App\Controller;

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
        $smsGenerator->sendSmsToProjectUser($projectId);
        return $this->render('sms/index.html.twig', [
            'smsSent' => true,
            'projectId' => $projectId,
        ]);
    }

    // envoyer des sms librement
    #[Route('/sendSmsToUser', name: 'send_sms_to_user', methods: ['POST'])]
    public function sendSmsToUser(Request $request, SmsGenerator $smsGenerator): Response
    {
        $phoneNumber = $request->request->get('phoneNumber'); // Utiliser POST pour plus de sécurité
        $name = $request->request->get('name');
        $text = $request->request->get('text');

        if (!$phoneNumber || !$name || !$text) {
            throw $this->createNotFoundException('Missing parameters.');
        }

        try {
            $smsGenerator->sendSmsToUser($phoneNumber, $name, $text);
            $smsSent = true;
        } catch (\Exception $e) {
            // Gérer l'erreur et afficher un message à l'utilisateur
            $smsSent = false;
            $errorMessage = $e->getMessage();
        }

        return $this->render('sms/index.html.twig', [
            'smsSent' => $smsSent,
            'phoneNumber' => $phoneNumber,
            'name' => $name,
            'text' => $text,
            'errorMessage' => $errorMessage ?? null,
        ]);
    }


}
