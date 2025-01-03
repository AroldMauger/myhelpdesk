<?php

namespace App\Controller;

use App\Repository\ConversationRepository;
use App\Service\AnythingLLMService;

class HomeController extends AbstractController
{
    public function landingPage():void {
        echo $this->render('landing-page.html.twig');
    }

    public function home(): void {

        if (! $this->sessionService->isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $service = new AnythingLLMService();
        $workspaces = $service->getWorkspaces();

        echo $this->render('index.html.twig', [
            'username' => $this->sessionService->getUserName(),
            'workspaces' => $workspaces,
        ]);
    }

    public function previousConversations():void {

                if (isset($_SESSION['user_id'])) {
                    $username = $_SESSION['username'];
                    $userId = $_SESSION['user_id'];
                    $role = $_SESSION['role'] ?? null;

                    $repository = new ConversationRepository();
                    $conversations = $repository->getPreviousConversations($userId);
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
