<?php

namespace App\Api;

use ApiPlatform\Api\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/ckeditor', name: 'ckeditor')]
class FileUploadController
{

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private UrlGeneratorInterface $urlGenerator
    )
    {}

    #[Route('/file/upload', name: 'file_upload', defaults: ['_format=json'], methods: ['POST'])]
    public function fileUpload(Request $request): JsonResponse
    {
            $fichier = $request->files->get('upload');
            $ext = explode('.', strtolower($fichier->getClientOriginalName()));
            $extension = end($ext);
            $newFileName = md5(uniqid()) . '.' . $extension;
            $path = $this->parameterBag->get('kernel.project_dir') . 'sandbox/';
            if(!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $fichier->move($path, $newFileName);
            $link = $this->urlGenerator->generate('app_sandbox_index', [], UrlGeneratorInterface::ABS_URL) . 'uploads/sandbox/' . $newFileName;
            $request = ['url' => $link];
            return new JsonResponse($request);
    }
}
