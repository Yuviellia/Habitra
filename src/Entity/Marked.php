<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="marked")
 */
class Marked {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Many Marked entries belong to One Tag.
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag")
     * @ORM\JoinColumn(name="idtag", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $tag;

    /**
     * @ORM\Column(type="date")
     */
    private $date;


    public function getId(): ?int { return $this->id; }

    public function getTag(): ?Tag { return $this->tag; }
    public function setTag(Tag $tag): self {
        $this->tag = $tag;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): self {
        $this->date = $date;
        return $this;
    }
}
