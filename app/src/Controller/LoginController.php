<?php

namespace App\Controller;
use App\Service\SessionService;

class LoginController extends AbstractController
{
    public function displayLogin(): void {
        echo $this->render('login.html.twig');
    }

    public function displaySignup(): void {
        $sessionService = new SessionService();
        $sessionService->startSession();

        echo $this->render('signup.html.twig', [
            'success_message' => $_SESSION['success_message'] ?? null,
            'error_message' => $_SESSION['error_message'] ?? null,
        ]);

        unset($_SESSION['success_message']);
        unset($_SESSION['error_message']);
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionService = new SessionService();
            $sessionService->startSession();

            $email = $_POST['email'];
            $password = $_POST['password'];

            // Vérifiez si l'email existe déjà
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                // Message d'erreur si l'email est déjà pris
                $_SESSION['error_message'] = 'Email utilisateur déjà existant';
                header('Location: /signup');
                exit;
            } else {
                // Hash du mot de passe et ajout de l'utilisateur
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'utilisateur';

                $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
                $stmt->execute([$email, $password_hash, $role]);

                // Message de succès après inscription
                $_SESSION['success_message'] = 'Inscription réussie! Connectez-vous en cliquant ici : <a href="/login" class="connection-link">CONNEXION</a>';
                header('Location: /signup');
                exit;
            }
        }
    }
}
