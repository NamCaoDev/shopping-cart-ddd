<?php

namespace App\Shared\Presentation\Response;

readonly class ApiMessageResponse
{
    public function __construct(public string $message) {}
}
