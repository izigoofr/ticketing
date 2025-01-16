<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectComment;
use App\Entity\Team;
use App\Form\ProjectType;
use App\Repository\ProjectCommentRepository;
use App\Repository\ProjectRepository;
use App\Repository\ReportRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

#[Route('/applicant')]
class ApplicantController extends AbstractController
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
    #[Route('/', name: 'applicant', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findBy(['applicant' => $this->getUser()->getFirstName()]);

        return $this->render('applicant/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'app_applicant_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        Security               $security,
        MailerInterface        $mailer
    ): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer cette action.');
        }

        $project = new Project();
        $project->setUser($user);

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team = $entityManager->getRepository(Team::class)->find(1);
            if (!$team) {
                throw $this->createNotFoundException('L\'équipe par défaut avec l\'ID 1 n\'existe pas.');
            }

            $project->setTeam($team);
            $project->setStatus('Not Started Yet');
            // recuperer le email du user connecter
            $project->setMailApplicant($user->getEmail());
            $project->setPriority(1);

            // Récupérer le prénom de l'utilisateur
            $project->setApplicant($user->getFirstName());

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('attachment')->getData();


            if ($uploadedFile) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    $uploadedFile->move($uploadsDirectory, $newFilename);
                    $project->setAttachment($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier.' . $e->getMessage());
                }
            }
            $entityManager->persist($project);
            $entityManager->flush();
            // Envoi d'email
            $email = (new Email())
                ->from(new Address('contact@app-prod.fr', 'Florajet ticketing'))
                ->to('f.stoessel@florajet.com') // Liste d'adresses
                ->subject('Création d\'un nouveau projet')
                ->text('Un nouveau projet a été créé dans votre application.')
                ->html('<p>Un nouveau projet a été créé.</p>');

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Le projet a été créé et une notification a été envoyée.');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Le projet a été créé, mais une erreur est survenue lors de l\'envoi de l\'email.');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger', 'Le projet a été créé, mais une erreur est survenue lors de l\'envoi de l\'email.');
            }

            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('project_list');
            }
            elseif ($this->isGranted('ROLE_MANAGER')) {
                return $this->redirectToRoute('manager');
            }
            elseif ($this->isGranted('ROLE_APPLICANT')) {
                return $this->redirectToRoute('applicant');
            }

        }
        return $this->renderForm('applicant/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_applicant_show', methods: ['GET'])]
    public function show(Project $project, ProjectCommentRepository $projectCommentRepository, TaskRepository $taskRepository): Response
    {


        return $this->render('applicant/show.html.twig', [
            'project' => $project,
            'projectComments' => $projectCommentRepository->findBy(['project' => $project]),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_applicant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('applicant', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('applicant/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_applicant_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }
        return $this->redirectToRoute('applicant', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/comment', name: 'new_comment', methods: ['POST'])]
    public function addComment(
        Project $project,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        UrlGeneratorInterface $urlGenerator,
        MailerInterface $mailer,
        TaskRepository $taskRepository
    ): RedirectResponse {

        // Récupération des tâches associées au projet
        $tasks = $taskRepository->findBy(['project' => $project]);
        $taskData = [];
        foreach ($tasks as $task) {
            $taskData[] = $task->getDeveloperMail(); // Supposons que cette méthode retourne l'email du développeur
        }

        // Récupérer l'utilisateur associé au projet
        $projectOwner = $project->getUser(); // Assurez-vous que la relation User -> Project existe dans votre entité
        if (!$projectOwner) {
            $request->getSession()->getFlashBag()->add('error', 'Aucun utilisateur associé à ce projet.');
            return new RedirectResponse($urlGenerator->generate('applicant'));
        }
        $projectOwnerEmail = $projectOwner->getEmail(); // Récupérer l'email de l'utilisateur

        // Récupération des données du formulaire
        $content = $request->request->get('content');
        if (empty($content)) {
            $request->getSession()->getFlashBag()->add('error', 'Le contenu ne peut pas être vide.');
            return new RedirectResponse($urlGenerator->generate('app_applicant_show', [
                'id' => $project->getId(),
            ]));
        }

        // Création du commentaire
        $comment = new ProjectComment();
        $comment->setContent($content);
        $comment->setProject($project);
        $comment->setUser($security->getUser());
        $comment->setDeveloperEmail($security->getUser()->getEmail());
        $comment->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($comment);
        $entityManager->flush();

        // Envoi de l'email à tous les destinataires
        $email = (new Email())
            ->from(new Address('contact@app-prod.fr', 'Florajet ticketing'))
            ->to($projectOwnerEmail) // Email du propriétaire du projet
            ->cc(...$taskData) // Ajouter les adresses des développeurs en CC
            ->subject('Nouveau commentaire sur le projet')
            ->text('Un nouveau commentaire a été ajouté sur le projet.')
            ->html('<p>Un nouveau commentaire a été ajouté sur le projet.</p>');

        $mailer->send($email);

        $request->getSession()->getFlashBag()->add('success', 'Commentaire ajouté avec succès.');

        return new RedirectResponse($urlGenerator->generate('applicant'));
    }


}
