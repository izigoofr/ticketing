<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    // onDelete: 'CASCADE' permet de supprimer les commentaires liés à un sandbox supprimé
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Sandbox $sandbox = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSandbox(): ?Sandbox
    {
        return $this->sandbox;
    }

    public function setSandbox(?Sandbox $sandbox): static
    {
        $this->sandbox = $sandbox;

        return $this;
    }
}
