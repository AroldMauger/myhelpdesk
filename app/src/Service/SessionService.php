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

    public function isAuthenticated(): bool
    {
        return isset($_SESSION);
    }

    public function getUserName(): ?string
    {
        if(isset($_SESSION['username'])) {
            return $_SESSION['username'];
        }

        return null;
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();

            session_unset();
            session_destroy();

            header('Location: /');
            exit;
        }
    }
}
