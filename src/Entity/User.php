<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(name: "first_name", nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(name: "last_name", nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(nullable: true)]
    private ?string $address = null;

    #[ORM\Column(name: "date_of_birth", nullable: true)]
    private ?string $dateOfBirth = null;

    #[ORM\Column(name: "phone_number", nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(nullable: true)]
    private ?string $country = null;

    #[ORM\Column(nullable: true)]
    private ?string $state = null;

    #[ORM\Column(name: "zip_code", nullable: true)]
    private ?string $zipCode = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Link::class)]
    private Collection $links;


    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Report::class)]
    private Collection $reports;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Project::class)]
    private Collection $projects;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Role $role = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Team $team = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ProjectComment::class)]
    private Collection $projectComments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TaskComment::class)]
    private Collection $TaskComments;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class)]
    private Collection $messages;

    #[ORM\OneToMany(mappedBy: 'recipent', targetEntity: Message::class)]
    private Collection $inbox;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_path = null;

    #[ORM\Column]
    private ?bool $isMenu = null;

    #[ORM\OneToMany(mappedBy: 'users', targetEntity: Sandbox::class)]
    private Collection $sandboxes;

    #[ORM\ManyToMany(targetEntity: Sandbox::class, mappedBy: 'taggedUsers')]
    private Collection $taggedSandboxes;

    #[ORM\OneToMany(mappedBy: 'users', targetEntity: Comment::class)]
    private Collection $comments;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->projectComments = new ArrayCollection();
        $this->TaskComments = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->inbox = new ArrayCollection();
        $this->sandboxes = new ArrayCollection();
        $this->taggedSandboxes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?string $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return Collection<int, Link>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): self
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setUser($this);
        }

        return $this;
    }

    public function removeLink(Link $link): self
    {
        if ($this->links->removeElement($link)) {
            // set the owning side to null (unless already changed)
            if ($link->getUser() === $this) {
                $link->setUser(null);
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
            $report->setUser($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getUser() === $this) {
                $report->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getUser() === $this) {
                $project->setUser(null);
            }
        }

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Collection<int, ProjectComment>
     */
    public function getProjectComments(): Collection
    {
        return $this->projectComments;
    }

    public function addProjectComment(ProjectComment $projectComment): self
    {
        if (!$this->projectComments->contains($projectComment)) {
            $this->projectComments->add($projectComment);
            $projectComment->setUser($this);
        }

        return $this;
    }

    public function removeProjectComment(ProjectComment $projectComment): self
    {
        if ($this->projectComments->removeElement($projectComment)) {
            // set the owning side to null (unless already changed)
            if ($projectComment->getUser() === $this) {
                $projectComment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskComment>
     */
    public function getTaskComments(): Collection
    {
        return $this->TaskComments;
    }

    public function addTaskComment(TaskComment $taskComment): self
    {
        if (!$this->TaskComments->contains($taskComment)) {
            $this->TaskComments->add($taskComment);
            $taskComment->setUser($this);
        }

        return $this;
    }

    public function removeTaskComment(TaskComment $taskComment): self
    {
        if ($this->TaskComments->removeElement($taskComment)) {
            // set the owning side to null (unless already changed)
            if ($taskComment->getUser() === $this) {
                $taskComment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getInbox(): Collection
    {
        return $this->inbox;
    }

    public function addInbox(Message $inbox): self
    {
        if (!$this->inbox->contains($inbox)) {
            $this->inbox->add($inbox);
            $inbox->setRecipent($this);
        }

        return $this;
    }

    public function removeInbox(Message $inbox): self
    {
        if ($this->inbox->removeElement($inbox)) {
            // set the owning side to null (unless already changed)
            if ($inbox->getRecipent() === $this) {
                $inbox->setRecipent(null);
            }
        }

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->image_path;
    }

    public function setImagePath(?string $image_path): self
    {
        $this->image_path = $image_path;

        return $this;
    }

    public function isIsMenu(): ?bool
    {
        return $this->isMenu;
    }

    public function setIsMenu(bool $isMenu): static
    {
        $this->isMenu = $isMenu;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return Collection<int, Sandbox>
     */
    public function getSandboxes(): Collection
    {
        return $this->sandboxes;
    }

    public function addSandbox(Sandbox $sandbox): static
    {
        if (!$this->sandboxes->contains($sandbox)) {
            $this->sandboxes->add($sandbox);
            $sandbox->setUsers($this);
        }

        return $this;
    }

    public function removeSandbox(Sandbox $sandbox): static
    {
        if ($this->sandboxes->removeElement($sandbox)) {
            // set the owning side to null (unless already changed)
            if ($sandbox->getUsers() === $this) {
                $sandbox->setUsers(null);
            }
        }

        return $this;
    }

    public function getTaggedSandboxes(): Collection
    {
        return $this->taggedSandboxes;
    }

    public function addTaggedSandbox(Sandbox $sandbox): static
    {
        if (!$this->taggedSandboxes->contains($sandbox)) {
            $this->taggedSandboxes->add($sandbox);
            $sandbox->addTaggedUser($this);
        }

        return $this;
    }

    public function removeTaggedSandbox(Sandbox $sandbox): static
    {
        if ($this->taggedSandboxes->removeElement($sandbox)) {
            $sandbox->removeTaggedUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUsers($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUsers() === $this) {
                $comment->setUsers(null);
            }
        }

        return $this;
    }
}
