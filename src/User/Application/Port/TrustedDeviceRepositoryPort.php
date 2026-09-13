<?php

declare(strict_types=1);

namespace App\User\Application\Port;

use App\User\Domain\Entity\TrustedDevice;
use DateTimeImmutable;

interface TrustedDeviceRepositoryPort
{
    public function save(TrustedDevice $device): void;

    public function findByHash(string $tokenHash): ?TrustedDevice;

    /**
     * Deletes all devices where expiresAt is older than the provided date.
     * Returns the number of deleted records.
     */
    public function deleteExpired(DateTimeImmutable $now): int;
}
