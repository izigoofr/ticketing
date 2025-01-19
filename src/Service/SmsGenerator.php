<?php
// src/Service/MessageGenerator.php
namespace App\Service;

use Twilio\Rest\Client;

class SmsGenerator
{

    public function SendSms(string $number, string $name, string $text)
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


    public function sendSmsToUser(string $phoneNumber, string $name, string $text): void
    {
        try {
            $this->SendSmsUser($phoneNumber, $name, $text); // Supposons que cette méthode envoie le SMS
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to send SMS: ' . $e->getMessage());
        }
    }

// Exemple d'implémentation fictive de la méthode SendSms
    private function SendSmsUser(string $phoneNumber, string $name, string $text): void
    {
        // Utiliser une API comme Twilio, Vonage, ou une autre
        // Exemple simplifié avec un message de simulation
        if (empty($phoneNumber) || empty($text)) {
            throw new \InvalidArgumentException('Invalid phone number or text.');
        }

        // Exemple : appel API fictive
        // $apiResponse = $this->smsProvider->send($phoneNumber, $text);

        // Simuler une réussite pour l'exemple
    }

}



