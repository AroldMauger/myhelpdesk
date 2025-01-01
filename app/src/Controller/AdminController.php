<?php

namespace App\Controller;
use App\Controller\WorkspaceController;
use App\Repository\AdminRepository;

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

    public function allConversations()
    {
        if (isset($_SESSION['user_id'])) {

            $role = $_SESSION['role'];

            if ($role !== 'administrateur') {
                header('Location: /login');
                exit;
            }

            $repository = new AdminRepository();
            $conversations = $repository->allConversations();

            echo $this->render('admin/all-conversations.html.twig', [
                'conversations' => $conversations,
                'role' => $role
            ]);
        }
    }

    public function deleteConversation()
    {
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['role'];

            if ($role !== 'administrateur') {
                header('Location: /login');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $conversationId = $_POST['conversation_id'];

                $repository = new AdminRepository();
                $response = $repository->deleteConversation($conversationId);

                header('Location: /previous');
                exit;
            }
        } else {
            header('Location: /login');
            exit;
        }
    }


}