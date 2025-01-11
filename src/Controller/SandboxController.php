<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Sandbox;
use App\Form\CommentType;
use App\Form\SandboxType;
use App\Repository\SandboxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/sandbox')]
class SandboxController extends AbstractController
{
    #[Route('/', name: 'app_sandbox_index', methods: ['GET'])]
    public function index(SandboxRepository $sandboxRepository): Response
    {
        return $this->render('sandbox/index.html.twig', [
            'sandboxes' => $sandboxRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sandbox_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $sandbox = new Sandbox();
        $form = $this->createForm(SandboxType::class, $sandbox);
        $form->handleRequest($request);
        $user = $security->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $sandbox->setUsers($user);
            $sandbox->setCreatedAt(new \DateTimeImmutable('now'));
            $entityManager->persist($sandbox);
            $entityManager->flush();

            return $this->redirectToRoute('app_sandbox_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sandbox/new.html.twig', [
            'sandbox' => $sandbox,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sandbox_show', methods: ['GET', 'POST'])]
    public function show(Sandbox $sandbox, FormFactoryInterface $formFactory, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $formFactory->create(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_sandbox_show', ['id' => $sandbox->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associez le Sandbox au commentaire
            $comment->setSandbox($sandbox);
            $entityManager->persist($comment);
            $entityManager->flush();

            // Redirigez après soumission
            return $this->redirectToRoute('app_sandbox_show', ['id' => $sandbox->getId()]);
        }

        return $this->render('sandbox/show.html.twig', [
            'sandbox' => $sandbox,
            'commentForm' => $form->createView(),
        ]);
    }


    #[Route('/{id}/edit', name: 'app_sandbox_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sandbox $sandbox, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SandboxType::class, $sandbox);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sandbox_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sandbox/edit.html.twig', [
            'sandbox' => $sandbox,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sandbox_delete', methods: ['POST'])]
    public function delete(Request $request, Sandbox $sandbox, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sandbox->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sandbox);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sandbox_index', [], Response::HTTP_SEE_OTHER);
    }
}
