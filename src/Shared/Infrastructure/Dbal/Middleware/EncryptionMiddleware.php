<?php

namespace App\Shared\Infrastructure\Dbal\Middleware;

use App\Shared\Infrastructure\Dbal\Driver\EncryptedDriver;
use App\Shared\Infrastructure\Security\EncryptionService;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('doctrine.middleware')]
readonly class EncryptionMiddleware implements Middleware
{
    public function __construct(
        private EncryptionService $encryptionService,

        // Tells Symfony to inject the kernel.secret parameter directly
        #[Autowire(param: 'kernel.secret')]
        private string $appSecret
    ) {}

    public function wrap(Driver $driver): Driver
    {
        $encryptedDriver = new EncryptedDriver($driver);
        $encryptedDriver->setEncryptionConfig($this->encryptionService, $this->appSecret);

        return $encryptedDriver;
    }
}
