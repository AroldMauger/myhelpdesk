<?php

namespace App\Controller;
use App\Controller\WorkspaceController;

class AdminController extends AbstractController
{
    public function home() {


        if (isset($_SESSION['user_id'])) {
            $username = $_SESSION['username'];
        } else {
            header('Location: /login');
            exit;
        }
        $workspaceController = new WorkspaceController();
        $workspaces = $workspaceController->getWorkspaces();

        echo $this->render('admin/dashboard.html.twig', [
            'username' => $username,
            'workspaces' => $workspaces
        ]);
    }

}