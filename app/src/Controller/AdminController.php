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

    public function allConversations()
    {
        if (isset($_SESSION['user_id'])) {

            $role = $_SESSION['role'];

            if ($role !== 'administrateur') {
                // Redirige si l'utilisateur n'est pas administrateur
                header('Location: /login');
                exit;
            }

            $stmt = $this->pdo->prepare('
        SELECT 
            conversations.id, 
            conversations.category, 
            conversations.subject, 
            conversations.created_at, 
            conversations.rating, 
            users.username 
        FROM conversations 
        JOIN users ON conversations.user_id = users.id 
        ORDER BY conversations.created_at DESC
    ');

            $stmt->execute();
            $conversations = $stmt->fetchAll();

            // Passer les données dans la vue
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

                $stmt = $this->pdo->prepare('DELETE FROM conversations WHERE id = :id');
                $stmt->bindParam(':id', $conversationId, \PDO::PARAM_INT);
                $stmt->execute();

                header('Location: /previous');
                exit;
            }
        } else {
            header('Location: /login');
            exit;
        }
    }


}