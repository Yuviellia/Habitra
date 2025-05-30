<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\UserDetails")]
    #[ORM\JoinColumn(name: "iddetails", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private $userDetails;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private $email;

    #[ORM\Column(type: "string", length: 255)]
    private $password;

    #[ORM\Column(type: "string", length: 20)]
    private $role;

    #[ORM\Column(type: "boolean")]
    private $enabled = true;

    #[ORM\Column(type: "datetime")]
    private $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }
    public function getRoles(): array { return [$this->role]; } //
    public function setRoles(string $role): self {
        $this->role = $role;
        return $this;
    }

    public function eraseCredentials(): void { }
    public function getUserIdentifier(): string { return $this->email; }

    public function getId(): ?int { return $this->id; }

    public function getUserDetails(): ?UserDetails { return $this->userDetails; }
    public function setUserDetails(UserDetails $userDetails): self {
        $this->userDetails = $userDetails;
        return $this;
    }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): self {
        $this->password = $password;
        return $this;
    }

    public function getEnabled(): ?bool { return $this->enabled; }
    public function setEnabled(bool $enabled): self {
        $this->enabled = $enabled;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self {
        $this->createdAt = $createdAt;
        return $this;
    }
}
