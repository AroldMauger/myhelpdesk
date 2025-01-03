<?php

namespace App\Controller;
use App\Repository\AdminRepository;
use App\Service\AnythingLLMService;

class AdminController extends AbstractController
{
    public function home():void {

        if (isset($_SESSION['user_id'])) {
            $username = $_SESSION['username'];
        } else {
            header('Location: /login');
            exit;
        }

        $service = new AnythingLLMService();
        $workspaces = $service->getWorkspaces();

        echo $this->render('admin/dashboard.html.twig', [
            'username' => $username,
            'workspaces' => $workspaces,
        ]);
    }

    public function allConversations():void
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
                'role' => $role,
            ]);
        }
    }

    public function deleteConversation():void
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
                $repository->deleteConversation($conversationId);

                header('Location: /previous');
                exit;
            }
        } else {
            header('Location: /login');
            exit;
        }
    }

    public function displayUsers():void
    {
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['role'];
            $userId = $_SESSION['user_id'];

            if ($role !== 'administrateur') {
                header('Location: /login');
                exit;
            }

            $repository = new AdminRepository();
            $users = $repository->getUsers();

            echo $this->render('admin/users.html.twig', [
                'users' => $users,
                'role' => $role,
                'user_id' => $userId,
            ]);
        }
    }

    public function updateRole():void {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $userRole = $_POST['user_role'];

            $repository = new AdminRepository();
            $repository->updateRole($userId, $userRole);

           $this->displayUsers();

        }
    }

}
