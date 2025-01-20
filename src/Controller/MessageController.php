<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class MessageController extends AbstractController
{
    private $messageRepository;
    private $userRepository;
    private $manager;
    public function __construct(MessageRepository $messageRepository, EntityManagerInterface $manager,
    UserRepository $userRepository){
        $this->manager = $manager;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/inbox/{recipent_id}', name: 'inbox')]
    public function index(Security $security, $recipent_id = null): Response
    {
        /** @var User */
        $user = $security->getUser();
        $memberList = $this->userRepository->createQueryBuilder('m')
                                           ->where('m.id != :currentUser')
                                           ->setParameter('currentUser', $user->getId())
                                           ->getQuery()
                                           ->getResult();
        if($recipent_id == null){
            $recipent = reset($memberList);
        }else{
            $recipent = $this->userRepository->find($recipent_id);
        }
        $messages = $this->messageRepository->getByUsers($user->getId(), $recipent->getId());
        foreach($messages as $message){
            if($message->getRecipent()->getId() == $user->getId()){
                $message->setStatus(1);
                $this->manager->flush();
            }
        }
        return $this->render('message/inbox.html.twig', [
            'member_list' => $memberList,
            'inbox' => $messages,
            'recipent' => $recipent
        ]);
    }

    #[Route('/inbox/{recipent_id}/send', name: 'send_message', methods: 'POST')]
    public function store(Request $request,Security $security ,$recipent_id) : JsonResponse{
        /**@var User */
        $sender = $security->getUser();
        $recipent = $this->userRepository->find($recipent_id);
        $message = new Message();
        $message->setObject('message')
                ->setContent($request->get('content'))
                ->setSender($sender)
                ->setStatus(0)
                ->setRecipent($recipent)
                ->setCreatedAt(new \DateTimeImmutable);
        $this->messageRepository->save($message, true);
        $serializedMessage = [
            'content' => $message->getContent(),
            'full_name' => $message->getSender()->getFirstName() . ' ' . $message->getSender()->getLastName() . '<span class="text-info"> (you)</span>',
            'image' => $message->getSender()->getImagePath() != null ? $message->getSender()->getImagePath() : 'assets/img/avatars/no-avatar.png',
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
        return new JsonResponse($serializedMessage);
    }
}
