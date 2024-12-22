<?php

namespace App\Controller;

class AdminController extends AbstractController
{
    public function home() {

        if (isset($_SESSION['user_id'])) {
            $username = $_SESSION['username'];
        } else {
            header('Location: /login');
            exit;
        }

        echo $this->render('admin/dashboard.html.twig', [
            'username' => $username,
        ]);
    }
}