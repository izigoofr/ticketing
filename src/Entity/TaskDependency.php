<?php

namespace App\Entity;

use App\Repository\TaskDependencyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskDependencyRepository::class)]
class TaskDependency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'dependencies')]
    private ?Task $task = null;

    #[ORM\ManyToOne]
    private ?Task $dependent_task = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getDependentTask(): ?Task
    {
        return $this->dependent_task;
    }

    public function setDependentTask(?Task $dependent_task): self
    {
        $this->dependent_task = $dependent_task;

        return $this;
    }
}
