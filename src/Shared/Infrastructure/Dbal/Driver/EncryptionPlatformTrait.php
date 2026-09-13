<?php

namespace App\Shared\Infrastructure\Dbal\Driver;

use App\Shared\Infrastructure\Security\EncryptionService;
use Random\RandomException;

trait EncryptionPlatformTrait
{
    private EncryptionService $encryptionService;
    private string $encryptionKey;

    public function setEncryptionConfig(EncryptionService $encryptionService, string $appSecret): void
    {
        $this->encryptionService = $encryptionService;

        // Ensure the key is exactly 32 bytes for SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES
        $this->encryptionKey = hash('sha256', $appSecret, true);
    }

    /**
     * @throws RandomException
     * @throws \SodiumException
     */
    public function encryptValue(string $value): string
    {
        // 'dbal_column' acts as Additional Authenticated Data (AAD) to prevent ciphertext tampering
        return $this->encryptionService->encrypt($value, 'dbal_column', $this->encryptionKey);
    }

    /**
     * @throws \SodiumException
     */
    public function decryptValue(string $value): string
    {
        return $this->encryptionService->decrypt($value, 'dbal_column', $this->encryptionKey);
    }
}
