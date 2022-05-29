<?php

namespace App\Entity;

use App\Repository\MailRepository;
use App\Traits\TimeStempTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailRepository::class)]
class Mail
{

    use TimeStempTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $subject;

    #[ORM\Column(type: 'text', nullable: true)]
    private $message;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'mails')]
    private $user;

    #[ORM\ManyToOne(targetEntity: Association::class, inversedBy: 'mails')]
    private $association;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

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
}
