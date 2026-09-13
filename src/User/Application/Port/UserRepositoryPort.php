<?php

// src/User/Application/Port/UserRepositoryPort.php
namespace App\User\Application\Port;

use App\User\Domain\Entity\User;

interface UserRepositoryPort
{
    public function isEmailTaken(string $email): bool;

    public function isUsernameTaken(string $username): bool;

    public function save(User $user): void;

    public function findById(string $id): ?User;
}
