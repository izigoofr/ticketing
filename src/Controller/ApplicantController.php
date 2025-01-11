<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Team;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/applicant')]
class ApplicantController extends AbstractController
{
    #[Route('/', name: 'applicant', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository, Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer les projets de l'utilisateur connecté
        $projects = $projectRepository->findBy(['user' => $user]);

        return $this->render('applicant/index.html.twig', [
            'projects' => $projects,
        ]);

    }

    #[Route('/new', name: 'app_applicant_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        MailerInterface $mailer
    ): Response {
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
                ->from(new Address('contact@app-dev.fr', 'Florajet'))
                ->to('f.stoessel@florajet.com', 'g.carnoy@florajet.com') // Liste d'adresses
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

            return $this->redirectToRoute('applicant', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('applicant/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }




    #[Route('/{id}', name: 'app_applicant_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('applicant/show.html.twig', [
            'project' => $project,
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
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('applicant', [], Response::HTTP_SEE_OTHER);
    }
}
