<?php

namespace App\Controller;

use Twig\Environment;
use PDO;


abstract class AbstractController
{
    protected Environment $twig;
    protected PDO $pdo;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../template');
        $this->twig = new \Twig\Environment($loader);

        $this->pdo = require __DIR__ . './../config/bdd.php';

    }

    protected function render(string $view, array $params = []): string {
        return $this->twig->render($view, $params);
    }
}