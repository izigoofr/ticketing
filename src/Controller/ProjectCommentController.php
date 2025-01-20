<?php

namespace App\Controller;

use App\Entity\ProjectComment;
use App\Form\ProjectCommentType;
use App\Repository\ProjectCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project/comment')]
final class ProjectCommentController extends AbstractController
{
    #[Route(name: 'app_project_comment_index', methods: ['GET'])]
    public function index(ProjectCommentRepository $projectCommentRepository): Response
    {
        return $this->render('project_comment/index.html.twig', [
            'project_comments' => $projectCommentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_project_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projectComment = new ProjectComment();
        $form = $this->createForm(ProjectCommentType::class, $projectComment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projectComment);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_comment/new.html.twig', [
            'project_comment' => $projectComment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_comment_show', methods: ['GET'])]
    public function show(ProjectComment $projectComment): Response
    {
        return $this->render('project_comment/show.html.twig', [
            'project_comment' => $projectComment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProjectComment $projectComment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectCommentType::class, $projectComment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_project_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_comment/edit.html.twig', [
            'project_comment' => $projectComment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_comment_delete', methods: ['POST'])]
    public function delete(Request $request, ProjectComment $projectComment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projectComment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($projectComment);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_project_comment_index', [], Response::HTTP_SEE_OTHER);
    }
}
