<?php

namespace App\Controller;

use ApiPlatform\Api\UrlGeneratorInterface;
use App\Entity\Comment;
use App\Entity\Sandbox;
use App\Form\CommentType;
use App\Form\SandboxType;
use App\Repository\SandboxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
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
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security,  MailerInterface $mailer): Response
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

            // Récupération des utilisateurs taggés
            $taggedUsers = $sandbox->getTaggedUsers();
            $taggedUserDetails = [];

            foreach ($taggedUsers as $taggedUser) {
                $taggedUserDetails[] = sprintf(
                    '%s %s (%s)',
                    $taggedUser->getFirstName(),
                    $taggedUser->getLastName(),
                    $taggedUser->getEmail()
                );
            }

            // Formatage des utilisateurs taggés pour le mail
            $taggedUsersText = implode("\n- ", $taggedUserDetails); // Texte brut
            $taggedUsersHtml = '<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $taggedUserDetails)) . '</li></ul>'; // HTML

            // Création de l'email
            $email = (new Email())
                ->from(new Address('contact@app-prod.fr', 'Florajet ticketing'))
                ->to($user->getEmail()) // Email de l'utilisateur qui crée la sandbox
                ->bcc(...array_map(fn($user) => $user->getEmail(), $taggedUsers->toArray())) // Utilisateurs taggés en BCC
                ->subject('Création d\'une sandbox')
                ->text(sprintf(
                    "Une nouvelle sandbox a été créée :\n\n- Utilisateur : %s\n- Titre : %s\n- Date de création : %s\n\nUtilisateurs taggés :\n- %s",
                    $user->getEmail(),
                    $sandbox->getTitle(),
                    $sandbox->getCreatedAt()->format('d/m/Y H:i'),
                    $taggedUsersText
                ))
                ->html(sprintf(
                    '<p>Une nouvelle sandbox a été créée :</p>
                             <ul>
                                 <li><strong>Utilisateur :</strong> %s</li>
                                 <li><strong>Titre :</strong> %s</li>
                                 <li><strong>Date de création :</strong> %s</li>
                             </ul>
                             <p><strong>Utilisateurs taggés :</strong></p>
                             %s',
                    htmlspecialchars($user->getEmail(), ENT_QUOTES),
                    htmlspecialchars($sandbox->getTitle(), ENT_QUOTES),
                    htmlspecialchars($sandbox->getCreatedAt()->format('d/m/Y H:i'), ENT_QUOTES),
                    $taggedUsersHtml
                ));

            try {
                $mailer->send($email);
                $this->addFlash('success', sprintf(
                    'Le projet a été créé et une notification a été envoyée aux utilisateurs taggés : %s.',
                    implode(', ', array_map(fn($user) => $user->getFirstName(), $taggedUsers->toArray()))
                ));
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Le projet a été créé, mais une erreur est survenue lors de l\'envoi de l\'email.');
            }

            return $this->redirectToRoute('app_sandbox_index', [], Response::HTTP_SEE_OTHER);
        }





        return $this->render('sandbox/new.html.twig', [
            'sandbox' => $sandbox,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sandbox_show', methods: ['GET', 'POST'])]
    public function show(Sandbox $sandbox, FormFactoryInterface $formFactory, Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $comment = new Comment();
        $form = $formFactory->create(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_sandbox_show', ['id' => $sandbox->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour commenter.');
            }
            $comment->setUsers($user);
            $comment->setSandbox($sandbox);
            $comment->setCreateAt(new \DateTimeImmutable('now'));
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

    #[Route('/file/upload', name: 'file_upload', defaults: ['_format' => 'json'], methods: ['POST'])]
    public function fileUpload(
        Request $request,
        ParameterBagInterface $parameterBag,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $fichier = $request->files->get('upload');

        if (!$fichier) {
            return new JsonResponse(['error' => ['message' => 'No file uploaded']], Response::HTTP_BAD_REQUEST);
        }

        $extension = strtolower($fichier->getClientOriginalExtension());
        $newFileName = md5(uniqid()) . '.' . $extension;

        $path = $parameterBag->get('kernel.project_dir') . '/public/uploads/sandbox';

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        try {
            $fichier->move($path, $newFileName);

            $link = $request->getSchemeAndHttpHost() . '/uploads/sandbox/' . $newFileName;

            return new JsonResponse(['url' => $link]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => ['message' => 'File upload failed: ' . $e->getMessage()]], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
