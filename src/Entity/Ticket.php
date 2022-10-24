<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'tickets')]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $closed = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $created_timestamp = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $fk_userId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isClosed(): ?bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function getCreatedTimestamp(): ?string
    {
        return $this->created_timestamp;
    }

    public function setCreatedTimestamp(string $created_timestamp): self
    {
        $this->created_timestamp = $created_timestamp;

        return $this;
    }

    public function getFkUserId(): ?string
    {
        return $this->fk_userId;
    }

    public function setFkUserId(string $fk_userId): self
    {
        $this->fk_userId = $fk_userId;

        return $this;
    }
}
