<?php

// src/Shared/Infrastructure/Dbal/Type/EncryptType.php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Dbal\Type;

use App\Shared\Infrastructure\Dbal\Driver\Platform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

final class EncryptType extends Type
{
    public const NAME = 'encrypt';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => $column['length'] ?? 300]);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        /** @var Platform $platform */
        if (null === $value) {
            return null;
        }

        try {
            // Encode arrays/objects to JSON before encrypting
            $value = json_encode($value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);

            // Delegate to our custom Platform trait
            return $platform->encryptValue($value);
        } catch (\Throwable $e) {
            throw SerializationFailed::new($value, self::NAME, $e->getMessage(), $e);
        }
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        /** @var Platform $platform */
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_resource($value)) {
                $value = stream_get_contents($value);
            }

            // Delegate to our custom Platform trait
            $value = $platform->decryptValue($value);

            // Decode the JSON back to a PHP array/string
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw ValueNotConvertible::new($value, 'mixed', $e->getMessage(), $e);
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
