<?php
// src/Service/MessageGenerator.php
namespace App\Service;

use Twilio\Rest\Client;

class SmsGenerator
{

    public function SendSms(string $number, string $name, string $text): void
    {

        $accountSid = $_ENV['twilio_account_sid'];  //Identifiant du compte twilio
        $authToken = $_ENV['twilio_auth_token']; //Token d'authentification
        $fromNumber = $_ENV['twilio_from_number']; // Numéro de test d'envoie sms offert par twilio
        $toNumber = $number; // Le numéro de la personne qui reçoit le message
        $message = ''.$name.' vous a envoyé le message suivant:'.' '.$text.''; //Contruction du sms
        $client = new Client($accountSid, $authToken);
        $client->messages->create(
            $toNumber,
            [
                'from' => $fromNumber,
                'body' => $message,
            ]
        );
    }

    public function sendSmsToProjectUser(float|bool|int|string|null $projectId)
    {

        $phoneNumber = '+33668435344'; // Remplacer par le numéro de téléphone de l'utilisateur du projet
        $name = 'John Doe'; // Remplacer par le nom de l'utilisateur du projet
        $text = 'Bonjour, vous avez reçu un nouveau message sur le projet #'.$projectId.'.'; // Message à envoyer
        $this->SendSms($phoneNumber, $name, $text);

    }




}



