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
        $number=$request->request->get('number');

        $name=$request->request->get('name');

        $text=$request->request->get('text');

        $number_test=$_ENV['twilio_to_number'];// Numéro vérifier par twilio. Un seul numéro autorisé pour la version de test.

        //Appel du service
        $smsGenerator->sendSms($number_test ,$name,$text);

        return $this->render('sms/index.html.twig', ['smsSent'=>true]);
    }





}
