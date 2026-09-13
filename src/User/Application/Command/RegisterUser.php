<?php

namespace App\User\Application\Command;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUser
{
    public function __construct(
        #[Assert\NotBlank(message: 'Username is required.')]
        #[Assert\Length(
            min: 3,
            max: 30,
            minMessage: 'Username must be at least {{ limit }} characters long.',
            maxMessage: 'Username cannot exceed {{ limit }} characters.'
        )]
        #[Assert\Regex(
            pattern: '/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*$/',
            message: 'Username can only contain letters, numbers, and underscores.'
        )]
        public string $username,

        #[Assert\NotBlank(message: 'Email is required.')]
        #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
        public string $email,

        #[Assert\NotBlank(message: 'Password is required.')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Your password must be at least {{ limit }} characters long.'
        )]
        #[SerializedName('password')]
        public string $plainPassword,

        // Not part of the request body; overwritten via withId() once the controller generates the aggregate id.
        public string $id = '',
    ) {}

    public function withId(string $id): self
    {
        return new self($this->username, $this->email, $this->plainPassword, $id);
    }
}
