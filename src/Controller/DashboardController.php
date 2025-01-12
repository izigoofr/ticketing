<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use App\Service\DatabaseInternetService;

class DashboardController extends AbstractController
{
    private $projectRepository;
    private $messageRepository;
    private $taskRepository;
    private $userRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        MessageRepository $messageRepository,
        TaskRepository $taskRepository,
        UserRepository $userRepository,
    ) {
        $this->projectRepository = $projectRepository;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->taskRepository = $taskRepository;
    }
    #[Route('/dashboard', name: 'dashboard')]
    public function index(Security $security, DatabaseInternetService $databaseInternetService): Response
    {
    /** @var User */
    $user = $security->getUser();

    $projectLits = $this->projectRepository->findAll();
    $memberList = $this->userRepository->findAll();
    $messages = $this->messageRepository->findBy(['recipent' => $user->getId()]);
    $users = $this->userRepository->findAll(); // Tous les utilisateurs

    // Liste des noms de famille à exclure
    $excludedLastNames = ['Virginie', 'Melanie', 'root', 'Franck', 'Oceane']; // Exemple de noms exclus




    $tasksByUser = [];

    foreach ($users as $u) {
        // Exclusion des utilisateurs
        if (in_array($u->getFirstName(), $excludedLastNames, true)) {
            continue;
        }

        $tasksByUser[$u->getFirstName()] = [
            'Not Started Yet' => 0,
            'In Progress' => 0,
            'Finished' => 0,
        ];

        foreach ($u->getTasks() as $task) {
            if (isset($tasksByUser[$u->getFirstName()][$task->getStatus()])) {
                $tasksByUser[$u->getFirstName()][$task->getStatus()]++;
            }
        }
    }





    $projects = $this->projectRepository->findAll(); // Tous les projets
    $projectTaskData = [];

    foreach ($projects as $project) {
        $taskCount = 0;
        $totalDays = 0;
        $userNames = [];
        $statusCounts = [
            'Not Started Yet' => 0,
            'In Progress' => 0,
            'Finished' => 0,
        ];

        $tasks = $project->getTasks(); // Récupérer les tâches associées au projet

        foreach ($tasks as $task) {
            $taskCount++;

            // Ajouter les jours pour chaque tâche
            $totalDays += $task->getDays(); // Champ `days` dans la table `task`

            // Compter les statuts
            if (isset($statusCounts[$task->getStatus()])) {
                $statusCounts[$task->getStatus()]++;
            }

            // Récupérer les utilisateurs assignés
            if ($task->getUser()) {
                $user = $task->getUser();
                $userNames[] = $user->getFirstName() . ' ' . $user->getLastName();
            }
        }


        $projectTaskData[] = [
            'projectName' => $project->getTitle(), // Nom du projet
            'taskCount' => $taskCount, // Nombre de tâches
            'totalDays' => $totalDays, // Nombre total de jours
            'userNames' => array_unique($userNames), // Liste unique des utilisateurs affectés
            'statusCounts' => $statusCounts, // Nombre de tâches par statut
        ];
    }







    // Préparation des données supplémentaires
    $currentDate = Carbon::now();
    $last7days = array();
    $last7days[] = $currentDate->toDateString();
    for ($i = 1; $i < 7; $i++) {
        $date = $currentDate->subDay()->toDateString();
        $last7days[] = $date;
    }

    $taskList = $this->taskRepository->findAll();
    $last7days = array_reverse($last7days);

    $tab = array();
    foreach ($memberList as $index => $member) {
        $tab[$index]['object'] = $member;
        $count = 0;

        foreach ($member->getTasks() as $task) {
            if ($task->getStatus() === 'Finished') {
                $count++;
            }
        }

        $tab[$index]['nmbrOfTask'] = $count;
    }

    usort($tab, function ($a, $b) {
        return $b['nmbrOfTask'] - $a['nmbrOfTask'];
    });

    $tab = array_slice($tab, 0, 3);


  //  $totalCommands = $databaseInternetService->countTodayCommands();


    return $this->render('dashboard/index.html.twig', [
        'project_list' => $projectLits,
        'task_list' => $taskList,
        'member_list' => $memberList,
        'messages' => $messages,
        'last7days' => $last7days,
        'top_3_member' => $tab,
        'tasks_by_user' => $tasksByUser,
        'project_task_data' => $projectTaskData,
       // 'total_commands_today' => $totalCommands,
        'user' => $user
    ]);
}

}
