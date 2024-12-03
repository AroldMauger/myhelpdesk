<?php

namespace App\Controller;

use App\Service\SessionService;

class HomeController extends AbstractController
{
    public function home() {

        if (isset($_SESSION['user_id'])) {
            $username = $_SESSION['username'];
        } else {
            header('Location: /login');
            exit;
        }

        echo $this->render('index.html.twig', [
            'username' => $username,
        ]);
    }

}