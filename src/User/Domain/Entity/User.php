<?php

namespace App\User\Domain\Entity;

use App\Shared\Domain\Aggregate\AggregateRoot;
use App\Shared\Domain\Aggregate\AggregateRootTrait;
use App\User\Domain\Event\TwoFactorCodeRequested;
use App\User\Domain\Event\UserWasRegistered;
use App\User\Infrastructure\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Ecotone\Modelling\Attribute\Identifier;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, GoogleTwoFactorInterface, EmailTwoFactorInterface, AggregateRoot
{
    use AggregateRootTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Identifier] // Tells Ecotone this is the unique ID for routing messages
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 30, unique: true)]
    private string $username; // Add username property

    #[ORM\Column(type: 'string', length: 320, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 250)]
    private string $password;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isActive = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isGoogleAuthEnabled = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $googleAuthSecret = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isEmailAuthEnabled = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $emailAuthCode = null;

    #[ORM\Column(type: 'encrypt', nullable: true, options: ['customSchemaOptions' => ['columnDefinition' => 'TEXT']])]
    private ?array $backupCodes = null;

    // Private constructor: The only way to create a User is through a Command
    private function __construct(Uuid $id, string $username, string $email, string $hashedPassword)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $hashedPassword;
    }

    public static function register(
        string $id,
        string $username,
        string $email,
        string $hashedPassword
    ): self
    {
        $username = trim($username);

        if (strlen($username) < 3 || strlen($username) > 30) {
            throw new \InvalidArgumentException("Username must be between 3 and 30 characters.");
        }

        if (!preg_match('/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*$/', $username)) {
            throw new \InvalidArgumentException("Username can only contain letters, numbers, and underscores.");
        }

        // You can add domain validation here (e.g., email format checking)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                sprintf('The email "%s" is not a valid format.', $email)
            );
        }

        $token = bin2hex(random_bytes(32));

        $user = new self(
            Uuid::fromString($id),
            $username,
            $email,
            $hashedPassword
        );

        $user->isVerified = false;
        $user->verificationToken = $token;

        // 3. Record the domain event!
        // Ecotone will automatically extract this and publish it after $em->flush() succeeds
        $user->recordThat(new UserWasRegistered($id, $email, $token));

        return $user;
    }

    public function verifyEmail(string $token): void
    {
        if ($this->isVerified) {
            throw new \DomainException("Account is already verified.");
        }

        if ($this->verificationToken !== $token) {
            throw new \InvalidArgumentException("Invalid verification token.");
        }

        // Change the state and clear the token so it can't be reused
        $this->isVerified = true;
        $this->verificationToken = null;
        $this->isActive = true;
    }

    /**
     * Verifies the provided 6-digit email authentication code.
     * Uses hash_equals to prevent timing attacks.
     */
    public function verifyEmailAuthCode(string $code): bool
    {
        if (empty($this->emailAuthCode) || empty($code)) {
            return false;
        }

        // Constant-time string comparison to prevent side-channel timing attacks
        return hash_equals($this->emailAuthCode, $code);
    }

    /**
     * Clears the single-use email authentication code after successful verification.
     */
    public function clearEmailAuthCode(): void
    {
        $this->emailAuthCode = null;
    }

    public function enableEmailAuthenticator(): void
    {
        $this->isEmailAuthEnabled = true;
    }

    public function enableGoogleAuthenticator(string $secret): void
    {
        if (empty($secret)) {
            throw new \DomainException('Google Authenticator secret cannot be empty.');
        }

        $this->googleAuthSecret = $secret;
        $this->isGoogleAuthEnabled = true;
    }

    /**
     * Generates a 6-digit code, stores it, and fires a Domain Event.
     */
    public function generateAndSendEmailAuthCode(): void
    {
        // Generate random 6-digit code (e.g., 123456)
        $this->emailAuthCode = (string) random_int(100000, 999999);

        // Record event to notify infrastructure to send the email
        $this->recordThat(
            new TwoFactorCodeRequested($this->id, $this->email, $this->emailAuthCode)
        );
    }

    public function setBackupCodes(array $codes): void
    {
        $this->backupCodes = $codes;
    }

    public function isValidBackupCode(string $submittedCode): bool
    {
        if (!$this->backupCodes) {
            return false;
        }

        return in_array($submittedCode, $this->backupCodes, true);
    }

    public function consumeBackupCode(string $submittedCode): bool
    {
        if (!$this->backupCodes) {
            return false;
        }

        $normalizedInput = strtolower(trim($submittedCode));

        foreach ($this->backupCodes as $index => $storedCode) {
            if (strtolower(trim($storedCode)) === $normalizedInput) {
                // Remove the code so it can NEVER be used again
                unset($this->backupCodes[$index]);

                // Re-index the array to prevent JSON serialization issues
                $this->backupCodes = array_values($this->backupCodes);

                return true; // Code was valid and consumed
            }
        }

        return false; // Code was invalid
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // Guarantees every user has at least ROLE_USER

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    // --- Google Authenticator Interface Methods ---
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->isGoogleAuthEnabled;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthSecret;
    }

    // --- Email 2FA Interface Methods ---
    public function isEmailAuthEnabled(): bool
    {
        return $this->isEmailAuthEnabled;
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    public function getEmailAuthCode(): ?string
    {
        return $this->emailAuthCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailAuthCode = $authCode;
    }

    private function recordThat(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
