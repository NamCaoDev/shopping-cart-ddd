<?php

// src/Shared/Infrastructure/Dbal/Driver/EncryptedDriver.php
namespace App\Shared\Infrastructure\Dbal\Driver;

use App\Shared\Infrastructure\Security\EncryptionService;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\ServerVersionProvider;

class EncryptedDriver extends AbstractDriverMiddleware
{
    private EncryptionService $encryptionService;
    private string $appSecret;

    public function setEncryptionConfig(EncryptionService $encryptionService, string $appSecret): void
    {
        $this->encryptionService = $encryptionService;
        $this->appSecret = $appSecret;
    }

    public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
    {
        $platform = new Platform();
        $platform->setEncryptionConfig($this->encryptionService, $this->appSecret);

        return $platform;
    }
}
