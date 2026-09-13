<?php

namespace App\Shared\Presentation\Response;

readonly class ResourceCreatedResponse
{
    public function __construct(
        public string $id,
        public string $message = 'Resource created successfully.'
    ) {}
}
