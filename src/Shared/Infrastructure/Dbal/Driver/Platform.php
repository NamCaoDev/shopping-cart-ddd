<?php

namespace App\Shared\Infrastructure\Dbal\Driver;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform; // OR MySQLPlatform

class Platform extends PostgreSQLPlatform
{
    // This trait brings in the encryptValue and decryptValue methods!
    use EncryptionPlatformTrait;
}
