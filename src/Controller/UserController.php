<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    private $manager;
    private $userRepository;
    private $passwordHasher;
    

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasherInterface){
        $this->userRepository= $userRepository;
        $this->manager = $manager;
        $this->passwordHasher = $userPasswordHasherInterface;
    }

    #[Route('/members', name: 'member_list')]
    public function index(Request $request): Response
    {
        if($request->query->has('query')){
            $queryBuilder = $this->manager->createQueryBuilder();
            $query = $queryBuilder->select('u')
                    ->from('App\Entity\User', 'u')
                    ->where($queryBuilder->expr()->like('u.firstName', ':firstName'))
                    ->orWhere($queryBuilder->expr()->like('u.lastName', ':lastName'))
                    ->setParameter('firstName', '%'.$request->query->get('query').'%')
                    ->setParameter('lastName', '%'.$request->query->get('query').'%')
                    ->getQuery();
            $userList = $query->getResult();
        }else{
            //get members list
            $userList = $this->userRepository->findAll();
        }
        return $this->render('user/index.html.twig', [
            'user_list' => $userList
        ]);
    }

    #[Route('/members/store', name: 'store_member', methods:'POST')]
    public function store(Request $request): Response{
        $user = new User();
        $user->setAddress($request->get('address'))
            ->setCountry($request->get('country'))
            ->setDateOfBirth($request->get('dob'))
            ->setEmail($request->get('email'))
            ->setFirstName($request->get('fisrtname'))
            ->setLastName($request->get('lastname'))
            ->setRoles(["ROLE_USER"])
            ->setPassword($this->passwordHasher->hashPassword($user, "user123"))
            ->setPhoneNumber($request->get('phonenumber'))
            ->setState($request->get('state'))
            ->setZipCode($request->get('zipcode'));
        
        $this->manager->persist($user);
        $this->manager->flush();
        return new Response('created');
    }

    #[Route('/members/{id}/get', name: 'get_member_data', methods:'GET')]
    public function getMember($id) : JsonResponse{
        $user = $this->userRepository->find($id);
        $user_data = [
            'id' => $user->getId(),
            'address' => $user->getAddress(),
            'dob' => $user->getDateOfBirth(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'phoneNumber' => $user->getPhoneNumber(),
            'state' => $user->getState(),
            'country' => $user->getCountry(),
            'zipCode' => $user->getZipCode()
        ];
        return new JsonResponse($user_data);
    }

    #[Route('/members/update', name: 'update_member', methods:'POST')]
    public function update(Request $request): Response{
        $user = $this->userRepository->find($request->get('id'));
        $user->setAddress($request->get('address'))
            ->setCountry($request->get('country'))
            ->setDateOfBirth($request->get('dob'))
            ->setEmail($request->get('email'))
            ->setFirstName($request->get('fisrtname'))
            ->setLastName($request->get('lastname'))
            ->setPassword($this->passwordHasher->hashPassword($user, 'this_is_password'))
            ->setPhoneNumber($request->get('phonenumber'))
            ->setState($request->get('state'))
            ->setZipCode($request->get('zipcode'));
        $this->manager->flush();
        return new Response('updated');
    }

    #[Route('/members/{id}/destroy', name: 'destroy_member', methods:'POST')]
    public function destroy($id){
        $user = $this->userRepository->find($id);
        $this->manager->remove($user);
        $this->manager->flush();
        return new Response('deleted');
    }

    #[Route('/members/{id}/protmote-to-manager', name: 'promote_to_manager', methods:'POST')]
    public function promoteToManager($id) : Response{  
        $user = $this->userRepository->find($id);
        $user->setRoles(['ROLE_MANAGER']);
        $this->manager->flush();
        return new Response('promoted');
    }


    #[Route('/members/{id}/demote-from-manager', name: 'demote_from_manager', methods:'POST')]
    public function demoteFromManager($id) : Response{  
        $user = $this->userRepository->find($id);
        $user->setRoles([]);
        $this->manager->flush();
        return new Response('demoted');
    }
}
