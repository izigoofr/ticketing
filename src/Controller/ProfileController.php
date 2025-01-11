<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\User;
use App\Repository\LinkRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ProfileController extends AbstractController
{
    private $userRepository;
    private $linkRepository;
    private $manager;
    public function __construct(UserRepository $userRepository,LinkRepository $linkRepository ,
    EntityManagerInterface $manager){
        $this->userRepository = $userRepository;
        $this->linkRepository = $linkRepository;
        $this->manager = $manager;
    }

    #[Route('/profile', name: 'profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig');
    }

    #[Route('/user-profile/{id}', name: 'user_profile')]
    public function profile($id): Response
    {
        $user = $this->userRepository->find($id);
        $links = $this->manager->getRepository(Link::class)->findBy(['user' => $id]);
        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'links' => $links
        ]);
    }

    #[Route('/profile/connections', name:'profile_connections')]
    public function connection(Security $security){
        /** @var User */
        $user = $security->getUser();
        $links = $this->linkRepository->findBy(['user' => $user->getId()]);
        return $this->render('profile/connections.html.twig', [
            'links' => $links
        ]);
    }


    #[Route('/profile/update', methods: 'POST', name:'update_profile')]
    public function update(Request $request, Security $security){
        /** @var User */
        $user = $security->getUser();
        //handle image upload
        $imagePath = $request->files->get('file');
        if ($imagePath) {
            $newImageName = uniqid() . '.' . $imagePath->guessExtension();
            try {
                $imagePath->move(
                    $this->getParameter('kernel.project_dir') . '/public/assets/img/avatars/',
                    $newImageName
                );
                //remove old image if existe
                if($user->getImagePath() != null){
                    unlink($this->getParameter('kernel.project_dir') . '/public/' . $user->getImagePath());
                }
                $user->setImagePath('assets/img/avatars/' . $newImageName);
            } catch (Exception $e) {
                dd($e->getMessage());
            }
        }
        $user->setAddress($request->get('address'))
             ->setCountry($request->get('country'))
             ->setDateOfBirth($request->get('dob'))
             ->setEmail($request->get('email'))
             ->setFirstName($request->get('firstName'))
             ->setLastName($request->get('lastName'))
             ->setPhoneNumber($request->get('phoneNumber'))
             ->setState($request->get('state'))
             ->setZipCode($request->get('zipCode'));
        $this->userRepository->save($user, true);
        return new Response('updated');
    }


    #[Route('/profile/connection/link', name: 'profile_connection_link', methods: 'POST')]
    public function link(Request $request, Security $security) : Response{
        $link = new Link();
        $link->setPlatform($request->get('platform'))
             ->setUrl($request->get('url'))
             ->setUser($security->getUser());
        $this->linkRepository->save($link, true);
        return new Response('linked');
    }

    #[Route('/profile/connection/unlink/{id}', name: 'profile_connection_unlink', methods: 'POST')]
    public function unlink($id) : Response{
        $link = $this->linkRepository->find($id);
        $this->linkRepository->remove($link, true);
        return new Response('unlinked');
    }

    #[Route('/profile/security', name: 'profile_security')]
    public function security(){
        return $this->render('profile/security.html.twig');
    }


    #[Route('/profile/security/update-password', name: 'profile_update_password', methods: 'POST')]
    public function updatePassword(Request $request, Security $security, UserPasswordHasherInterface $userPasswordHasherInterface){
        $oldPassword = $request->get('oldPassword');
        $newPassword = $request->get('password');
        /** @var User */
        $user = $security->getUser();
        if($userPasswordHasherInterface->isPasswordValid($user, $oldPassword)){
            $user->setPassword($userPasswordHasherInterface->hashPassword($user, $newPassword));
            $this->manager->flush();
            return new Response('updated');
        }else{
            return new Response('echec');
        }
    }

}
