<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class TicketingController extends AbstractController
{

    #[Route('/ticketing', name: 'ticketing')]
    public function index(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from(new Address('contact@app-dev.fr', 'Florajet'))
            ->to('franck.stoessel@gmail.com')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun againjjjjjj!')
            ->html('<p>See Twig integration for better HTML integratiokjojojon!</p>');

        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $mailer = new Mailer($transport);
        $mailer->send($email);
        return $this->render('ticketing/index.html.twig', [
            'controller_name' => 'TicketingController',
        ]);
    }
}
