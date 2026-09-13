<?php

declare(strict_types=1);

namespace App\User\Application\Command;

final readonly class GenerateBackupCodesCommand
{
    public function __construct(public string $userId) {}
}
