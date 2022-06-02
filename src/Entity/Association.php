<?php

namespace App\Entity;

use App\Repository\AssociationRepository;
use App\Traits\StatusTrait;
use App\Traits\TimeStempTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssociationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Association
{
    use TimeStempTrait;
    use StatusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private $nom;

    #[ORM\Column(type: 'string', length: 255)]
    private $noRecipice;

    #[ORM\Column(type: 'string', length: 255)]
    private $objectif;

    #[ORM\Column(type: 'text', nullable: true)]
    private $Description;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $nbrAdherant;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $adresse;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $tel;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mail;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $siege;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: Adhesion::class)]
    private $adhesions;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: Mail::class)]
    private $mails;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: Activite::class)]
    private $activites;

    public function __construct()
    {
        $this->adhesions = new ArrayCollection();
        $this->mails = new ArrayCollection();
        $this->activites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNoRecipice(): ?string
    {
        return $this->noRecipice;
    }

    public function setNoRecipice(string $noRecipice): self
    {
        $this->noRecipice = $noRecipice;

        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): self
    {
        $this->objectif = $objectif;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getNbrAdherant(): ?int
    {
        return $this->nbrAdherant;
    }

    public function setNbrAdherant(?int $nbrAdherant): self
    {
        $this->nbrAdherant = $nbrAdherant;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getSiege(): ?string
    {
        return $this->siege;
    }

    public function setSiege(?string $siege): self
    {
        $this->siege = $siege;

        return $this;
    }


    /**
     * @return Collection<int, Adhesion>
     */
    public function getAdhesions(): Collection
    {
        return $this->adhesions;
    }

    public function addAdhesion(Adhesion $adhesion): self
    {
        if (!$this->adhesions->contains($adhesion)) {
            $this->adhesions[] = $adhesion;
            $adhesion->setAssociation($this);
        }

        return $this;
    }

    public function removeAdhesion(Adhesion $adhesion): self
    {
        if ($this->adhesions->removeElement($adhesion)) {
            // set the owning side to null (unless already changed)
            if ($adhesion->getAssociation() === $this) {
                $adhesion->setAssociation(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getNom();
    }

    /**
     * @return Collection<int, Mail>
     */
    public function getMails(): Collection
    {
        return $this->mails;
    }

    public function addMail(Mail $mail): self
    {
        if (!$this->mails->contains($mail)) {
            $this->mails[] = $mail;
            $mail->setAssociation($this);
        }

        return $this;
    }

    public function removeMail(Mail $mail): self
    {
        if ($this->mails->removeElement($mail)) {
            // set the owning side to null (unless already changed)
            if ($mail->getAssociation() === $this) {
                $mail->setAssociation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Activite>
     */
    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activite $activite): self
    {
        if (!$this->activites->contains($activite)) {
            $this->activites[] = $activite;
            $activite->setAssociation($this);
        }

        return $this;
    }

    public function removeActivite(Activite $activite): self
    {
        if ($this->activites->removeElement($activite)) {
            // set the owning side to null (unless already changed)
            if ($activite->getAssociation() === $this) {
                $activite->setAssociation(null);
            }
        }

        return $this;
    }

}
