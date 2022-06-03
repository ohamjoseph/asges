<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait StatusActiviteTrait
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $status;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    #[ORM\PrePersist]
    public function PreStatus()
    {
        if (is_null($this->status)) {
            $this->status = 'ACTIVE';
        }
    }
}
