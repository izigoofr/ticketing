<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class ClientController extends AbstractController
{

    private $clientRepository;
    private $manager;
    public function __construct(EntityManagerInterface $manger, ClientRepository $clientRepository){
        $this->clientRepository = $clientRepository;
        $this->manager = $manger;
    }

    #[Route('/clients', name: 'client_list')]
    public function index(): Response
    {
        $clientList = $this->clientRepository->findAll();
        return $this->render('client/index.html.twig', [
            'client_list' => $clientList
        ]);
    }

    #[Route('/clients/store', name: 'store_client', methods: 'POST')]
    public function store(Request $request): JsonResponse{
        $client = new Client();
        $client->setFirstName($request->get('firstName'))
               ->setLastName($request->get('lastName'))
               ->setContactInfo($request->get('contactInfo'));
        $this->manager->persist($client);
        $this->manager->flush();
        $client_data = [
            'id' => $client->getId(),
            'fisrt_name' => $client->getFirstName(),
            'last_name' => $client->getLastName(),
            'contact_info' => $client->getContactInfo()
        ];
        return new JsonResponse($client_data);
    }

    #[Route('/clients/get/{id}', name: 'get_client', methods: 'get')]
    public function getClient($id) : JsonResponse{
        $client = $this->clientRepository->find($id);
        $client_data = [
            'id' => $client->getId(),
            'first_name' => $client->getFirstName(),
            'last_name' => $client->getLastName(),
            'contact_info' => $client->getContactInfo()
        ];
        return new JsonResponse($client_data);
    }

    #[Route('/clients/update/{id}', name: 'update_client', methods: 'POST')]
    public function update(Request $request, $id) : Response{
        $client = $this->clientRepository->find($id);
        $client->setFirstName($request->get('firstName'))
               ->setLastName($request->get('lastName'))
               ->setContactInfo($request->get('contactInfo'));
        $this->manager->flush();
        return new Response('updated');
    }


    #[Route('/clients/destroy/{id}', name: 'destroy_client', methods: 'POST')]
    public function destroy($id) : Response{
        $client = $this->clientRepository->find($id);
        $this->manager->remove($client);
        $this->manager->flush();
        return new Response('deleted');
    }
}
