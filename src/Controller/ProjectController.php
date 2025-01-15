<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectComment;
use App\Entity\Report;
use App\Entity\Tag;
use App\Entity\Task;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use App\Repository\SandboxRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\CustomService;
use App\Service\SmsGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    private $projectRepository;
    private $teamRepository;
    private $clientRepository;
    private $userRepository;
    private $manager;
    private $sandboxRepository;
    public function __construct(ProjectRepository $projectRepository, EntityManagerInterface $manager,
    TeamRepository $teamRepository, ClientRepository $clientRepository, UserRepository $userRepository, SandboxRepository $sandboxRepository){
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
        $this->sandboxRepository = $sandboxRepository;
    }

    #[Route('/projects', name: 'project_list')]
    public function index(Request $request): Response
    {
        if($request->query->get('query')){
            $queryBuilder = $this->manager->createQueryBuilder();
            $query = $queryBuilder->select('p')
                                  ->from('App\Entity\Project', 'p')
                                  ->where($queryBuilder->expr()->like('p.title', ':title'))
                                  ->setParameter('title', '%'.$request->query->get('query').'%')
                                  ->getQuery();
            $project_list = $query->getResult();
        }else{
            $project_list = $this->projectRepository->findAll();
        }
        return $this->render('project/index.html.twig', [
            'project_list' => $project_list,
            'smsSent' => true,
        ]);
    }

    #[Route('/projects/create', name: 'create_project')]
    public function create(){
        $teams = $this->teamRepository->findAll();
        $users = $this->userRepository->findAll();
        $clients = $this->clientRepository->findAll();
        $sandbox = $this->sandboxRepository->findAll();
        return $this->render('project/create.html.twig', [
            'teams' => $teams,
            'clients' => $clients,
            'managers' => $users ,
            'sandboxes' => $sandbox
        ]);
    }

    #[Route('/project/edit/{id}', name: 'edit_project', methods:'GET')]
    public function edit($id){
        $project = $this->projectRepository->find($id);
        $tags = $this->manager->getRepository(Tag::class)->findBy(['project' => $id]);
        foreach($tags as $tag){
            $project->addTag($tag);
        }
        $teams = $this->teamRepository->findAll();
        $users = $this->userRepository->findAll();
        $clients = $this->clientRepository->findAll();
        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'teams' => $teams,
            'clients' => $clients,
            'managers' => $users
        ]);

    }

    #[Route('/projects/{id}/show', name: 'show_project', methods: 'GET')]
    public function show($id) : Response{
        $project = $this->projectRepository->find($id);
        $projectComments = $this->manager->getRepository(ProjectComment::class)->findBy(['project' => $id]);
        $projectReport = $this->manager->getRepository(Report::class)->findBy(['project' => $id]);
        $tasks = $this->manager->getRepository(Task::class)->findBy(['project' => $id]);
        return $this->render('project/show.html.twig', [
            'project' => $project,
            'comments' => $projectComments,
            'reports' => $projectReport,
            'tasks' => $tasks
        ]);
    }

    #[Route('/projects/store', name: 'store_project', methods: 'POST')]
    public function store(Request $request): Response
    {
        $client = $this->clientRepository->find($request->get('client_id'));
        $manager = $this->userRepository->find($request->get('manager_id'));
        $team = $this->teamRepository->find($request->get('team_id'));
        $sandboxId = $request->get('sandboxes');
        $sandbox = $sandboxId ? $this->sandboxRepository->find($sandboxId) : null;

        if (!$client || !$manager || !$team) {
            return new Response('Invalid client, manager, or team', Response::HTTP_BAD_REQUEST);
        }
        $tags_array = [];
        $tags_json = $request->get('tags');
        if ($tags_json) {
            $tags_array = json_decode($tags_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new Response('Invalid tags JSON', Response::HTTP_BAD_REQUEST);
            }
            $tags_array = array_map(fn($tag) => $tag['value'] ?? null, $tags_array);
            $tags_array = array_filter($tags_array);
        }

        $project = new Project();
        $project->setTitle($request->get('title'))
            ->setContent($request->get('content'))
            ->setDeadLine($request->get('deadline'))
            ->setPriority($request->get('priority'))
            ->setApplicant($request->get('applicant'))
            ->setType($request->get('type'))
            ->setSandboxes($sandbox) // Sandbox peut être null ici
            ->setStatus('Not Started Yet')
            ->setTeam($team)
            ->setUser($manager)
            ->setClient($client);
        $this->manager->persist($project);
        foreach ($tags_array as $value) {
            $_tag = new Tag();
            $_tag->setName($value);
            $_tag->setProject($project);
            $this->manager->persist($_tag);
        }
        $this->manager->flush();
        return new Response('created');
    }


    #[Route('/projects/update/{id}', name: 'update_project', methods: 'POST')]
    public function update(Request $request, $id, CustomService $customService) : Response{
        //get project
        $project = $this->projectRepository->find($id);
        //get client
        $client = $this->clientRepository->find($request->get('client_id'));
        //get manager
        $manager = $this->userRepository->find($request->get('manager_id'));
        //get team
        $team = $this->teamRepository->find($request->get('team_id'));
        //convert tags to array
        $tags_string = $request->get('tags');
        $tags_string = str_replace(['{', '}', '[', ']', ':', 'value', '"'], '', $tags_string);
        $tags_array = explode(',', $tags_string);
        $tags_array = array_map('trim', $tags_array);

        //delete old tags from tag table
        $tags = $this->manager->getRepository(Tag::class)->findBy(['project' => $id]);
        foreach($tags as $tag){
            $tag->setProject(null);
        }
        $this->manager->flush();
        $customService->deleteUnusedTags();
        //store new tags
        foreach($tags_array as $key => $value){
            $_tag = new Tag();
            $_tag->setName($value);
            $_tag->setProject($project);
            $this->manager->persist($_tag);
        }
        //update project data
        $project->setTitle($request->get('title'))
                ->setDeadLine($request->get('deadline'))
                ->setTeam($team)
                ->setUser($manager)
                ->setClient($client);
        $this->manager->flush();
        return new Response('updated');
    }

    #[Route('/projects/destroy/{id}', name: 'destroy_project', methods: 'POST')]
    public function destroy($id) : Response{
        //deleting tags associeted with the project
        $tags = $this->manager->getRepository(Tag::class)->findBy(['project' => $id]);
        foreach($tags as $tag){
            $this->manager->remove($tag);
        }
        $project = $this->projectRepository->find($id);
        $tasks = $project->getTasks();
        foreach($tasks as $task){
            $task_comment = $task->getComments();
            $task_files = $task->getFiles();
            $task_dependency = $task->getDependencies();
            $task_reports = $task->getReports();
            foreach($task_comment as $row){
                $this->manager->remove($row);
            }
            foreach($task_files as $row){
                $this->manager->remove($row);
            }
            foreach($task_dependency as $row){
                $this->manager->remove($row);
            }
            foreach($task_reports as $row){
                $this->manager->remove($row);
            }
            $this->manager->remove($task);
            $this->manager->flush();
        }
        $project_report = $project->getReports();
        foreach($project_report as $row){
            $this->manager->remove($row);
        }
        $project_comment = $project->getComments();
        foreach($project_comment as $row){
            $this->manager->remove($row);
        }
        $this->manager->flush();
        $this->manager->remove($project);
        $this->manager->flush();
        return new Response('deleted');
    }

    #[Route('/sendSmsManger', name: 'send_sms_manger', methods: ['GET'])]
    public function sendSms(Request $request, SmsGenerator $smsGenerator): Response
    {
        $projectId = $request->query->get('id'); // Récupère l'id du projet depuis la requête

        if (!$projectId) {
            throw $this->createNotFoundException('No project ID provided.');
        }

        // Logique d'envoi du SMS ici
        $smsGenerator->sendSmsToProjectUser($projectId);

        // Retourne une vue ou un JSON
        return $this->render('project/index.html.twig', [


        ]);
    }



}
