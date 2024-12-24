<?php

namespace App\Controller;

use App\Service\SessionService;
use Twig\Environment;
use PDO;

abstract class AbstractController
{
    protected Environment $twig;
    protected PDO $pdo;
    protected SessionService $sessionService;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../template');
        $this->twig = new \Twig\Environment($loader);
        $this->pdo = require __DIR__ . './../config/bdd.php';
        $this->sessionService = new SessionService();
        $this->sessionService->startSession();
    }

    protected function render(string $view, array $params = []): string
    {
        return $this->twig->render($view, $params);
    }

    protected function getSuccessMessage(): ?string
    {
        return $this->sessionService->getMessage('success_message');
    }

    protected function getErrorMessage(): ?string
    {
        return $this->sessionService->getMessage('error_message');
    }

    protected function clearMessages(): void
    {
        $this->sessionService->clearMessage('success_message');
        $this->sessionService->clearMessage('error_message');
    }
}
