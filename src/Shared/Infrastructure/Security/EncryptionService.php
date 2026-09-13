<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use InvalidArgumentException;
use Random\RandomException;
use UnexpectedValueException;

final readonly class EncryptionService
{
    /**
     * @throws RandomException
     * @throws \SodiumException
     */
    public function encrypt(
        #[\SensitiveParameter] string $message,
        string                        $additionalData,
        #[\SensitiveParameter] string $key,
    ): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $encrypted = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($message, $additionalData, $nonce, $key);

        return sprintf('%s.%s', base64_encode($nonce), base64_encode($encrypted));
    }

    /**
     * @throws \SodiumException
     */
    public function decrypt(
        string                        $encryptedMessage,
        string                        $additionalData,
        #[\SensitiveParameter] string $key,
    ): string
    {
        if (!str_contains($encryptedMessage, '.')) {
            throw new InvalidArgumentException('Invalid encrypted message format');
        }

        [$nonce, $encrypted] = explode('.', $encryptedMessage, 2);

        $message = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            base64_decode($encrypted),
            $additionalData,
            base64_decode($nonce),
            $key,
        );

        if (false === $message) {
            throw new UnexpectedValueException('Can not decrypt message');
        }

        return $message;
    }
}
