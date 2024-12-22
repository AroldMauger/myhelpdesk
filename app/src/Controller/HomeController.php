<?php

namespace App\Controller;

use App\Controller\WorkspaceController;
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
        $workspaceController = new WorkspaceController();
        $workspaces = $workspaceController->getWorkspaces();

        echo $this->render('index.html.twig', [
            'username' => $username,
            'workspaces' => $workspaces
        ]);
    }


    public function previousConversations() {

                if (isset($_SESSION['user_id'])) {
                    $username = $_SESSION['username'];
                    $userId = $_SESSION['user_id'];
                    $role = $_SESSION['role'] ?? null;

                    $stmt = $this->pdo->prepare('SELECT * FROM conversations WHERE user_id = ? ORDER BY created_at DESC');
                    $stmt->execute([$userId]);
                    $conversations = $stmt->fetchAll();

                } else {
                    header('Location: /login');
                    exit;
                }

                // Passer les données dans la vue
                echo $this->render('conversations/previous.html.twig', [
                    'username' => $username,
                    'conversations' => $conversations,
                    'role' => $role,
                ]);
            }

}