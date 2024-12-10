<?php

namespace App\DTO;

class ChatBotMessageDTO
{
    public function __construct(public readonly string $content, public readonly bool $isSuccess)
    {

    }
}