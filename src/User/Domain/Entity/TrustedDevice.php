<?php

namespace App\User\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class TrustedDevice
{
    #[ORM\Id, ORM\Column(type: 'string')]
    private Uuid $id;

    #[ORM\Column(type: 'string')]
    private Uuid $userId;

    #[ORM\Column(type: 'string')]
    private string $tokenHash; // Store the SHA-256 hash here

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    public function __construct(Uuid $id, Uuid $userId, string $tokenHash, \DateTimeImmutable $expiresAt)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = $expiresAt;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }
}
