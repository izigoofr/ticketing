<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CustomService{
    
    private $manager;
    
    public function __construct(EntityManagerInterface $manager){
        $this->manager = $manager;
    }

    public function deleteUnusedTags() : Response{
        $unusedTags = $this->manager->getRepository(Tag::class)->findBy(['project' => null]);
        foreach($unusedTags as $tag){
            $this->manager->remove($tag);
        }
        $this->manager->flush();
        return new Response('deleted');
    }
}