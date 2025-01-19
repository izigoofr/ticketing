<?php

namespace App\Controller;

use App\Service\SmsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Attribute\Route;

class SmsController extends AbstractController
{
    //La vue du formulaire d'envoie du sms
    #[Route('/sms', name: 'app_sms_index')]
    public function index(): Response
    {
        return $this->render('sms/index.html.twig',['smsSent'=>false]);
    }

    //Gestion de l'envoie du sms
    #[Route('/sendSms', name: 'send_sms', methods: ['POST', 'GET'])]
    public function sendSms(Request $request, TexterInterface $texter): Response
    {
        $smsSent = false;

        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $phoneNumber = $request->request->get('number');
            $senderName = $request->request->get('name');
            $messageText = $request->request->get('text');

            try {
                // Créer et envoyer le message SMS
                $sms = new SmsMessage(
                    $phoneNumber,
                    $messageText,
                    $senderName
                );

                $texter->send($sms);
                $smsSent = true;
            } catch (\Exception $e) {
                // Gérer les erreurs ici
                $this->addFlash('error', 'Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
            }
        }

        return $this->render('sms/index.html.twig', ['smsSent' => $smsSent]);
    }







}
