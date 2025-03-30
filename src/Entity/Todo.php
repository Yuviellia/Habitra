<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "todo")]
class Todo {
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User")]
    #[ORM\JoinColumn(name: "iduser", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private $user;

    #[ORM\Column(type: "text")]
    private $task;

    #[ORM\Column(type: "datetime")]
    private $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): self {
        $this->user = $user;
        return $this;
    }

    public function getTask(): ?string { return $this->task; }
    public function setTask(string $task): self {
        $this->task = $task;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self {
        $this->createdAt = $createdAt;
        return $this;
    }
}
