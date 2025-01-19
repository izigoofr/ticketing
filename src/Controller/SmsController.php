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
    #[Route('/sendSmsToUser', name: 'send_sms_to_user', methods: ['GET'])]
    public function sendSmsToUser(Request $request, SmsGenerator $smsGenerator): Response
    {
        $phoneNumber = $request->query->get('phoneNumber'); // Récupère le numéro de téléphone depuis la requête
        $name = $request->query->get('name'); // Récupère le nom depuis la requête
        $text = $request->query->get('text'); // Récupère le message depuis la requête
        if (!$phoneNumber || !$name || !$text) {
            throw $this->createNotFoundException('Missing parameters.');
        }
        $smsGenerator->sendSmsToUser($phoneNumber, $name, $text);
        return $this->render('sms/index.html.twig', [
            'smsSent' => true,
            'phoneNumber' => $phoneNumber,
            'name' => $name,
            'text' => $text,
        ]);
    }

}
