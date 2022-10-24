<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'reviews')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $data = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $created_timestamp = null;

    #[ORM\Column(name: 'fk_userId')]
    private ?int $fk_userId = null;

    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

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

    public function getFkUserId(): ?int
    {
        return $this->fk_userId;
    }

    public function setFkUserId(int $fk_userId): self
    {
        $this->fk_userId = $fk_userId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
