<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Project $project = null;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: File::class)]
    private Collection $files;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskComment::class)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Report::class)]
    private Collection $reports;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskDependency::class)]
    private Collection $dependencies;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $start_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\Column(nullable: true)]
    private ?int $days = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $developerMail = null;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->dependencies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setTask($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getTask() === $this) {
                $file->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(TaskComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTask($this);
        }

        return $this;
    }

    public function removeComment(TaskComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTask() === $this) {
                $comment->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setTask($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getTask() === $this) {
                $report->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskDependency>
     */
    public function getDependencies(): Collection
    {
        return $this->dependencies;
    }

    public function addDependency(TaskDependency $dependency): self
    {
        if (!$this->dependencies->contains($dependency)) {
            $this->dependencies->add($dependency);
            $dependency->setTask($this);
        }

        return $this;
    }

    public function removeDependency(TaskDependency $dependency): self
    {
        if ($this->dependencies->removeElement($dependency)) {
            // set the owning side to null (unless already changed)
            if ($dependency->getTask() === $this) {
                $dependency->setTask(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function setDays(?int $days): static
    {
        $this->days = $days;

        return $this;
    }

    public function getGitlab(): ?string
    {
        return $this->gitlab;
    }

    public function setGitlab(?string $gitlab): static
    {
        $this->gitlab = $gitlab;

        return $this;
    }

    public function getDeveloperMail(): ?string
    {
        return $this->developerMail;
    }

    public function setDeveloperMail(?string $developerMail): static
    {
        $this->developerMail = $developerMail;

        return $this;
    }
}
