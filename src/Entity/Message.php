<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'messages')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $created_timestamp = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $fk_userId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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
