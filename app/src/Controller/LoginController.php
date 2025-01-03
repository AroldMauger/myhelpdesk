<?php

namespace App\Controller;


use App\Repository\UserRepository;

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
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $repository = new UserRepository();
        $user = $repository->fetchUserByEmail($email);

        if ($user) {
            $this->sessionService->setMessage('error_message', 'Email utilisateur déjà existant');
            header('Location: /signup');
            exit;
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'utilisateur';

            $repository->insertUser($username, $email, $password_hash, $role);

            $this->sessionService->setMessage('success_message', 'Inscription réussie! Connectez-vous en cliquant ici : <a href="/login" class="connection-link">CONNEXION</a>');
            header('Location: /signup');
            exit;
        }
    }

    public function login(): void
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $repository = new UserRepository();
        $user = $repository->fetchUserByEmail($email);
            if ($user && password_verify($password, $user->getPasswordHash())) {
                if($user->getRole() === 'utilisateur') {
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['username'] = $user->getUsername();
                    $_SESSION['role'] = $user->getRole();

                    header('Location: /home');
                } elseif ($user->getRole() === 'administrateur') {
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['username'] = $user->getUsername();
                    $_SESSION['role'] = $user->getRole();

                    header('Location: /admin');
                }
            } else {
                $this->sessionService->setMessage('error_message', 'Email utilisateur ou mot de passe incorrect.');
                header('Location: /login');
                exit;
            }
        }

}
