<?php

namespace App\Controller;

class LoginController extends AbstractController
{
    public function displayLogin(): void
    {
        echo $this->render('authentication/login.html.twig', [
            'success_message' => $this->getSuccessMessage(),
            'error_message' => $this->getErrorMessage(),
        ]);

        $this->clearMessages();
    }

    public function displaySignup(): void
    {
        echo $this->render('authentication/signup.html.twig', [
            'success_message' => $this->getSuccessMessage(),
            'error_message' => $this->getErrorMessage(),
        ]);

        $this->clearMessages();
    }

    public function signup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $this->sessionService->setMessage('error_message', 'Email utilisateur déjà existant');
                header('Location: /signup');
                exit;
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'utilisateur';

                $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, $email, $password_hash, $role]);

                $this->sessionService->setMessage('success_message', 'Inscription réussie! Connectez-vous en cliquant ici : <a href="/login" class="connection-link">CONNEXION</a>');
                header('Location: /signup');
                exit;
            }
        }
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                if($user['role'] === 'utilisateur') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    header('Location: /home');
                    exit;
                } elseif ($user['role'] === 'administrateur') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    header('Location: /admin');
                    exit;
                }
            } else {
                $this->sessionService->setMessage('error_message', 'Email utilisateur ou mot de passe incorrect.');
                header('Location: /login');
                exit;
            }
        }
    }
}
