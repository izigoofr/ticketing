<?php

namespace App\Controller;

use App\Service\SmsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
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

    #[Route('/sendSms', name: 'send_sms', methods: ['POST', 'GET'])]
    public function sendSms(Request $request, TexterInterface $texter): Response
    {
        if ($request->isMethod('POST')) {
            $phoneNumber = $request->request->get('number');
            $messageText = $request->request->get('text');

            try {
                $sms = new SmsMessage(
                    $phoneNumber,
                    $messageText,
                    'MuSender'
                );

                $texter->send($sms);

                // Ajouter un message flash de succès
                $this->addFlash('success', "Le SMS a été envoyé avec succès au numéro : $phoneNumber");

                // Redirection vers la même page (ou une autre si besoin)
                return $this->redirectToRoute('send_sms');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
            }
        }

        return $this->render('sms/index.html.twig');
    }


}
