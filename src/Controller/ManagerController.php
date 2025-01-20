<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\ProjectComment;
use App\Entity\Report;
use App\Entity\Task;
use App\Entity\TaskComment;
use App\Entity\TaskDependency;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\ReportRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class ManagerController extends AbstractController
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

    #[Route('/manager', name: 'manager')]
    public function index(Security $security): Response
    {
        /** @var User */
        $manager = $security->getUser();
        $projectList = $this->projectRepository->findBy(['user' => $manager->getId()]);
        return $this->render('manager/index.html.twig', [
            'project_list' => $projectList
        ]);
    }

    #[Route('/manager/project/{id}', name:'project_more')]
    public function more($id){
        $project = $this->projectRepository->find($id);
        return $this->render('manager/more.html.twig', [
            'project' => $project
        ]);
    }

    #[Route('/manager/{id}/update-project-status', methods: 'POST' ,name: 'update_project_status')]
    public function updateProjectStatus(Request $request, $id, MailerInterface $mailer) : Response{
        $project = $this->projectRepository->find($id);
        if($request->get('status') == 'start'){
            $project->setStatus('In Progress');
            $project->setStartDate((new \DateTimeImmutable())->format('Y-m-d'));
        }else{
            $project->setStatus($request->get('status'));
        }
        $this->manager->flush();
        $email = (new Email())
            ->from(new Address('contact@app-prod.fr', 'Florajet ticketing'))
            ->to($project->getMailApplicant())
            ->subject('Request Status')
            ->html('The status of your request <strong>' . $project->getTitle() . '</strong> has been changed to ' . $project->getStatus() . '.');
        $mailer->send($email);
        try {
            $mailer->send($email);
            $this->addFlash('success', 'Le statut a été créé et une notification a été envoyée.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le statut a été créé, mais une erreur est survenue lors de l\'envoi de l\'email.');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('danger', 'Le statut a été créé, mais une erreur est survenue lors de l\'envoi de l\'email.');
        }

        return new Response('updated');
    }

    #[Route('/project/{id}/new-comment', methods: 'POST' ,name: 'new_project_comment')] // franck :)
    public function addComment(Request $request, $id, Security $security, MailerInterface $mailer, TaskRepository $taskRepository) : JsonResponse{
        $project = $this->projectRepository->find($id);
        $comment = new ProjectComment();
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 403);
        }

        $tasks = $taskRepository->findBy(['project' => $project]);
        $taskData = [];
        foreach ($tasks as $task) {
            $taskData[] = $task->getDeveloperMail();
        }
        $comment->setContent($request->get('content'))
                ->setProject($project)
                 ->setDeveloperEmail($security->getUser()->getEmail())
                ->setUser($user)
                ->setCreatedAt(new \DateTimeImmutable());
        $this->manager->persist($comment);
        $this->manager->flush();
        if($comment->getUser()->getImagePath() == null){
            $imagePath = 'assets/img/avatars/no-avatar.png';
        }else{
            $imagePath = $comment->getUser()->getImagePath();
        }
        $data = [
            'fullName' => $comment->getUser()->getFirstName() . ' ' . $comment->getUser()->getLastName(),
            'imagePath' => $imagePath,
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:m:s')
        ];
        $email = (new Email())
            ->from(new Address('contact@app-prod.fr', 'Florajet ticketing')) // Adresse de l'expéditeur
            ->to($project->getMailApplicant()) // Adresse du destinataire
            ->cc(...$taskData)
            ->subject('Nouveau commentaire ajouté au projet')
            ->text(sprintf(
                "Bonjour,\n\n%s a ajouté un nouveau commentaire au projet '%s':\n\n%s\n\nCordialement,\nL'équipe.",
                $user->getFirstName() . ' ' . $user->getLastName(),
                $project->getTitle(),
                $comment->getContent()
            ));
        $mailer->send($email);
        return new JsonResponse($data);
    }

    #[Route('/project/{id}/new-comment/task', methods: 'POST' ,name: 'new_task_comment')]
    public function addTaskComment(Request $request, $id, Security $security) : JsonResponse{
        $task = $this->manager->getRepository(Task::class)->find($id);
        $comment = new TaskComment();
        $comment->setContent($request->get('content'))
                ->setTask($task)
                ->setUser($security->getUser())
                ->setDeveloperMail($task->getUser()->getEmail())
                ->setCreatedAt(new \DateTimeImmutable());
        $this->manager->persist($comment);
        $this->manager->flush();
        if($comment->getUser()->getImagePath() == null){
            $imagePath = 'assets/img/avatars/no-avatar.png';
        }else{
            $imagePath = $comment->getUser()->getImagePath();
        }
        $data = [
            'fullName' => $comment->getUser()->getFirstName() . ' ' . $comment->getUser()->getLastName(),
            'imagePath' => $imagePath,
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:m:s')
        ];
        return new JsonResponse($data);
    }

    #[Route('/project/{id}/new-file/task', methods: 'POST' ,name: 'new_task_file')]
    public function addFile(Request $request, $id) : Response{
        $task = $this->manager->getRepository(Task::class)->find($id);
        $request_file  = $request->files->get('file');
        $newFileName = uniqid() . '.' . $request_file->getClientOriginalExtension();
        $directoryPath = $this->getParameter('kernel.project_dir') . '\public\assets\files';
        $file = new File();
        $file->setName($request_file->getClientOriginalName())
             ->setSize($request_file->getSize())
             ->setType($request_file->getClientOriginalExtension())
             ->setPath('assets/files/'.$newFileName)
             ->setTask($task);
        $request_file->move(
            $directoryPath, $newFileName
        );
        $this->manager->persist($file);
        $this->manager->flush();
        return new Response('added');
    }

    #[Route('/project/{id}/delete-task', methods: 'POST' ,name: 'delete_task')]
    public function deleteTask($id) : Response{
        $task = $this->manager->getRepository(Task::class)->find($id);
        $dependent_task = $this->manager->getRepository(TaskDependency::class)->findBy(['dependent_task' => $task->getId()]);
        $dependency_task = $this->manager->getRepository(TaskDependency::class)->findBy(['task' => $id]);
        foreach($dependency_task as $row){
            $this->manager->remove($row);
            $this->manager->flush();
        }
        foreach($dependent_task as $row){
            $this->manager->remove($row);
            $this->manager->flush();
        }
        $comments = $this->manager->getRepository(TaskComment::class)->findBy(['task' => $task->getId()]);
        foreach($comments as $comment){
            $this->manager->remove($comment);
            $this->manager->flush();
        }
        $files = $this->manager->getRepository(File::class)->findBy(['task' => $task->getId()]);
        foreach($files as $file){
            $this->manager->remove($file);
            $this->manager->flush();
        }
        $reports = $this->manager->getRepository(Report::class)->findBy(['task' => $task->getId()]);
        foreach($reports as $report){
            $this->manager->remove($report);
            $this->manager->flush();
        }
        $this->manager->remove($task);
        $this->manager->flush();
        return new Response('deleted');
    }

    #[Route('/manager/project/{id}/new-dependency/task', methods: 'POST' ,name: 'new_depedency')]
    public function addDependency(Request $request, $id) : Response{
        $task = $this->manager->getRepository(Task::class)->find($id);
        //test if existe
        if($this->manager->getRepository(TaskDependency::class)->findBy(['task' => $id, 'dependent_task' => $request->get('dependent_task_id')])){
            return new Response('exist');
        }
        $dependent_task = $this->manager->getRepository(Task::class)->find($request->get('dependent_task_id'));
        $task_dependency = new TaskDependency();
        $task_dependency->setTask($task)
                        ->setType('to_do')
                        ->setDependentTask($dependent_task);
        $this->manager->persist($task_dependency);
        $this->manager->flush();
        return new Response('added');
    }

    #[Route('/manager/project/{id}/remove-dependency/task', methods: 'POST' ,name: 'remove_depedency')]
    public function removeDependency(Request $request, $id) : Response{

        $dependency = $this->manager->getRepository(TaskDependency::class)->find($id);
        $this->manager->remove($dependency);
        $this->manager->flush();
        return new Response('removed');
    }

    #[Route('/project/{id}/getFile', methods: 'GET' ,name: 'get_file')]
    public function getFile($id) : BinaryFileResponse{
        $file = $this->manager->getRepository(File::class)->find($id);
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' .  $file->getPath();
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,  $file->getName());
        return $response;
    }

    #[Route('/manager/project/{id}/create-task', methods: 'POST' ,name: 'create_task')]
    public function createTask(Request $request, $id, MailerInterface $mailer) : Response{
        $project = $this->projectRepository->find($id);

        $task = new Task();
        $task->setTitle($request->get('title'))
            ->setDescription($request->get('description'))
            ->setDays($request->get('days'))
            ->setGitlab($request->get('gitlab'))
            ->setStartDate($request->get('start_date'))
            ->setStatus('Not Started Yet')
            ->setDeveloperMail($this->manager->getRepository(User::class)->find($request->get('user_id'))->getEmail())
            ->setProject($project)
            ->setUser($this->manager->getRepository(User::class)->find($request->get('user_id')));
        $this->manager->persist($task);
        $this->manager->flush();
        $email = (new Email())
            ->from(new Address('contact@app-prod.fr', 'Florajet ticketing'))
            ->to($task->getDeveloperMail())
            ->subject('New Task')
            ->html('You have a new task to do <strong>' . $task->getTitle() . '</strong>.');
        $mailer->send($email);
        return new Response('created');
    }


    #[Route('/project/{id}/task/{task_id}', methods: 'GET' ,name: 'get_task')]
    public function getTask($id, $task_id) : Response{
        $project = $this->projectRepository->find($id);
        $task = $this->manager->getRepository(Task::class)->find($task_id);
        return $this->render('manager/task-details.html.twig', [
            'project' => $project,
            'task' => $task
        ]);
    }

    #[Route('/manager/project/{id}/send-report', methods: 'POST' ,name: 'send_project_report')]
    public function sendProjectReport(Request $request, $id, Security $security) : Response{
        $project = $this->projectRepository->find($id);
        $report = new Report();
        $report->setProject($project)
               ->setUser($security->getUser())
               ->setCreatedAt(new \DateTimeImmutable())
               ->setDescription($request->get('description'));
        $this->manager->persist($report);
        $this->manager->flush();
        return new Response('sended');
    }
}
