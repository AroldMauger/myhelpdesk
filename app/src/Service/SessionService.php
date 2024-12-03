<?php
namespace App\Service;

class SessionService
{
    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function setMessage(string $type, string $message): void
    {
        $_SESSION[$type] = $message;
    }

    public function getMessage(string $type): ?string
    {
        return $_SESSION[$type] ?? null;
    }

    public function clearMessage(string $type): void
    {
        unset($_SESSION[$type]);
    }
}


//getUserId
//login
//logout
// avec sessions PHP
// sessionStart
// sessionStop