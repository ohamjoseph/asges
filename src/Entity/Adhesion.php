<?php

namespace App\Entity;

use App\Repository\AdhesionRepository;
use App\Traits\TimeStempTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdhesionRepository::class)]
#[ORM\UniqueConstraint(name: 'user_association', fields: ["user","association"])]
#[ORM\HasLifecycleCallbacks]
class Adhesion
{

    use TimeStempTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $role;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'adhesions')]
    private $user;

    #[ORM\ManyToOne(targetEntity: Association::class, inversedBy: 'adhesions')]
    private $association;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

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

    public function getAssociation(): ?Association
    {
        return $this->association;
    }

    public function setAssociation(?Association $association): self
    {
        $this->association = $association;

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->getCreateAt();
    }

}
