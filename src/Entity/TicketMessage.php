<?php

namespace App\Entity;

use App\Repository\TicketMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketMessageRepository::class)]
#[ORM\Table(name: 'messages_tickets')]
class TicketMessage
{
    #[ORM\Id]
    #[ORM\Column(name: 'fk_ticketId', type: Types::BIGINT)]
    private ?int $fk_ticketId = null;

    #[ORM\Id]
    #[ORM\Column(name: 'fk_messageId',type: Types::BIGINT)]
    private ?int $fk_messageId = null;

    public function getFkTicketId(): ?int
    {
        return $this->fk_ticketId;
    }

    public function setFkTicketId(int $fk_ticketId): self
    {
        $this->fk_ticketId = $fk_ticketId;

        return $this;
    }

    public function getFkMessageId(): ?int
    {
        return $this->fk_messageId;
    }

    public function setFkMessageId(int $fk_messageId): self
    {
        $this->fk_messageId = $fk_messageId;

        return $this;
    }
}
