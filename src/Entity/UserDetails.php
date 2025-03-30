<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_details")
 */
class UserDetails {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone;

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getSurname(): ?string { return $this->surname; }
    public function setSurname(string $surname): self {
        $this->surname = $surname;
        return $this;
    }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self {
        $this->phone = $phone;
        return $this;
    }
}
