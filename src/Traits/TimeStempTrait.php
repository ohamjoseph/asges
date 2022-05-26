<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TimeStempTrait
{


    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createAt;
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updateAt;



    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(?\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist() {
        $this->createAt = new \DateTime();
        $this->updateAt = new \DateTime();

    }

    #[ORM\PreUpdate]
    public function onPreUpdate(){
        $this->updateAt = new \DateTime();
    }
}