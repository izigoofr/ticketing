<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TeamController extends AbstractController
{
    private $teamRepository;
    private $manager;

    public function __construct(TeamRepository $teamRepository, EntityManagerInterface $entityManagerInterface){
        $this->teamRepository = $teamRepository;
        $this->manager = $entityManagerInterface;
    }

    #[Route('/teams', name: 'team_list')]
    public function index(Request $request): Response
    {
        if($request->query->get('query')){
            $queryBuilder = $this->manager->createQueryBuilder();
            $query = $queryBuilder->select('t')
                                  ->from('App\Entity\Team', 't')
                                  ->where($queryBuilder->expr()->like('t.name', ':name'))
                                  ->setParameter('name', '%'. $request->query->get('query') .'%')
                                  ->getQuery();
            $teamList = $query->getResult();
        }else{
            $teamList = $this->teamRepository->findAll();
        }
        $userList = $this->manager->getRepository(User::class)->findBy(['team' => null]);
        return $this->render('team/index.html.twig', [
            'team_list' => $teamList,
            'user_list' => $userList
        ]);
    }

    #[Route('/team/store', name: 'store_team', methods: 'POST')]
    public function store(Request $request){
        $team = new Team();
        $team->setName($request->get('name'));
        $this->manager->persist($team);
        $this->manager->flush();
        return new Response('created');
    }

    #[Route('/team/get/{id}', name: 'get_team_data', methods: 'GET')]
    public function getTeamData($id) : JsonResponse{
        $team = $this->teamRepository->find($id);
        $team_data = [
            'id' => $team->getId(),
            'name' => $team->getName(),
            'members' => []
        ];
        foreach($team->getUsers() as $user){
            $team_data['members'][] = [
                'id' => $user->getId(),
                'full_name' => $user->getFirstName() . ' ' . $user->getLastName()
            ];
        }

        return new JsonResponse($team_data);
    }


    #[Route('/team/{id}/add-member/{user_id}', name: 'add_member', methods: 'POST')]
    public function addMember($id, $user_id) : JsonResponse{
        $user = $this->manager->getRepository(User::class)->find($user_id);
        $team = $this->teamRepository->find($id);
        $user->setTeam($team);
        $this->manager->flush();
        $user_data = [
            'id' => $user->getId(),
            'full_name' => $user->getFirstName() . ' ' . $user->getLastName()
        ];
        return new JsonResponse($user_data);
    }

    #[Route('/team/remove-member/{user_id}', name: 'remove_member', methods: 'POST')]
    public function removeMember($user_id) : Response{
        $user = $this->manager->getRepository(User::class)->find($user_id);
        $user->setTeam(null);
        $this->manager->flush();
        return new Response('deleted');
    }

    #[Route('/teams/{id}/update', name: 'update_team', methods: 'POST')]
    public function update(Request $request, $id) : Response{
        $team = $this->teamRepository->find($id);
        $team->setName($request->get('name'));
        $this->manager->flush();
        return new Response('updated');
    }


    #[Route('/team/{id}/destroy', name: 'destroy_team', methods: 'POST')]
    public function destroy($id): Response{
        $team = $this->teamRepository->find($id);
        $this->manager->remove($team);
        $this->manager->flush();
        return new Response('deleted');
    }
}
