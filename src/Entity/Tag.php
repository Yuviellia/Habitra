<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "tags")]
class Tag {
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User")]
    #[ORM\JoinColumn(name: "iduser", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private $user;

    #[ORM\Column(type: "string", length: 255)]
    private $name;

    #[ORM\Column(type: "datetime")]
    private $createdAt;

    #[ORM\OneToMany(mappedBy: "tag", targetEntity: "App\Entity\Marked")]
    private $markedDates;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->markedDates = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): self {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string { return $this->name;  }
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getMarkedDates(): Collection {
        return $this->markedDates;
    }
}
