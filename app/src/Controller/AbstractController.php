<?php

namespace App\Controller;

use Twig\Environment;

abstract class AbstractController
{
    private readonly Environment $twig;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../template');
        $this->twig = new \Twig\Environment($loader);
    }

    protected function render(string $view, array $params = []): string {
        return $this->twig->render($view, $params);
    }
}