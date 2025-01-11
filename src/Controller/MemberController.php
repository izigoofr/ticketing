<?php

namespace App\Controller;

use App\Entity\Report;
use App\Entity\Task;
use App\Entity\TaskDependency;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MemberController extends AbstractController
{

    private $projectRepository;
    private $reportRepository;
    private $manager;

    public function __construct(ProjectRepository $projectRepository, EntityManagerInterface $manager,
    ReportRepository $reportRepository){
        $this->projectRepository = $projectRepository;
        $this->reportRepository = $reportRepository;
        $this->manager = $manager;
    }
    #[Route('/member', name: 'member')]
    public function index(Security $security): Response
    {
        /** @var User */
        $member = $security->getUser();
        if($member->getTeam()){
            $projectList = $this->projectRepository->findBy(['team' => $member->getTeam()->getId()]);
        }else{
            $projectList = null;
        }
        return $this->render('member/index.html.twig', [
            'project_list' => $projectList
        ]);
    }

    #[Route('/member/project/{id}', name: 'member_project')]
    public function getTasks($id) : Response{
        $project = $this->projectRepository->find($id);
        return $this->render('member/tasks.html.twig', [
            'project' => $project
        ]);
    }

    #[Route('member/project/{id}/task/{task_id}', methods: 'GET' ,name: 'get_task_2')]
    public function getTask($id, $task_id) : Response{
        $project = $this->projectRepository->find($id);
        $task = $this->manager->getRepository(Task::class)->find($task_id);
        return $this->render('member/task-details.html.twig', [
            'project' => $project, 
            'task' => $task
        ]);
    }

    #[Route('member/project/{id}/task/{task_id}/start', methods: 'POST' ,name: 'start_task')]
    public function startTask($id, $task_id) : Response{
        $task = $this->manager->getRepository(Task::class)->find($task_id);
        $task_dependency = $this->manager->getRepository(TaskDependency::class)->findBy(['task' => $task_id]);
        foreach($task_dependency as $dependency){
            $check_task = $dependency->getDependentTask();
            if($check_task->getStatus() != 'Finished'){
                return new Response('cannot start');
            }
        }
        $task->setStatus('In Progress');
        $task->setStartDate(new \DateTimeImmutable());
        $this->manager->flush();
        return new Response('started');
    }

    #[Route('member/project/{id}/task/{task_id}/update', methods: 'POST' ,name: 'update_task')]
    public function updateTask(Request $request, $id, $task_id) : Response{
        $task = $this->manager->getRepository(Task::class)->find($task_id);
        $task->setStatus($request->get('status'));
        if($request->get('status') == 'Finished'){
            $task->setEndDate(new \DateTimeImmutable());
        }
        $this->manager->flush();
        return new Response('updated');
    }

    #[Route('/member/project/{id}/send-report', methods: 'POST' ,name: 'send_task_report')]
    public function sendProjectReport(Request $request, $id, Security $security) : Response{
        $task = $this->manager->getRepository(Task::class)->find($id);
        $report = new Report();
        $report->setTask($task)
               ->setUser($security->getUser())
               ->setCreatedAt(new \DateTimeImmutable())
               ->setDescription($request->get('description'));
        $this->manager->persist($report);
        $this->manager->flush();
        return new Response('sended');
    }


    #[Route('/member/project/{id}/comments', methods: 'GET' ,name: 'project_comments')]
    public function getComment($id) : JsonResponse{
        $project = $this->projectRepository->find($id);
        $comments = $project->getComments();
        $serializedComments = array();
        foreach ($comments as $comment) {
            $fullName = $comment->getUser()->getFirstName() . ' ' . $comment->getUser()->getLastName();
            $imagePath = $comment->getUser()->getImagePath();
            
            $serializedComments[] = [
                'id' => $comment->getId(),
                'fullName' => $fullName,
                'imagePath' => ($imagePath != null ? $imagePath : 'assets/img/avatars/no-avatar.png'),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                // Add other properties you want to include in the response
            ];
        }
        
        return new JsonResponse($serializedComments);
    }


}
