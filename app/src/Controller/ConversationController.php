<?php

namespace App\Controller;

use App\Model\ChatBotConstants;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\AnythingLLMService;
use App\Service\SessionService;
class ConversationController extends AbstractController
{
    public function startConversation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionService = new SessionService();
            $sessionService->startSession();

            $userId = $_SESSION['user_id'];
            $workspaceSlug = $_POST['workspace_slug'];
            $userMessage = $_POST['message'];

            // Stocker le workspaceSlug dans la session
            $_SESSION['workspace_slug'] = $workspaceSlug;

            $service = new AnythingLLMService();

            $chatbotResponse = $service->askChatbot($userMessage, $workspaceSlug);
            $chatbotResponse['textResponse'] = preg_replace('/\s*\[.*?\]\s*/', '', $chatbotResponse['textResponse']);

            $conversationRepository = new ConversationRepository();
            $conversationId = $conversationRepository->createConversation($userId, $userMessage, $workspaceSlug);

            $messageRepository = new MessageRepository();
            $messageRepository->addUserMessage($conversationId, $userId, $userMessage);

            $messageRepository->createMessage($conversationId, $chatbotResponse);

            header('Location: /conversation?id=' . $conversationId);
            exit;
        }
    }

    public function addMessage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionService = new SessionService();
            $sessionService->startSession();

            $conversationId = $_POST['conversation_id'] ?? $_GET['id'] ?? null;

            if (! $conversationId) {
                throw new \Exception('ID de conversation non spécifié.');
            }

            $userId = $_SESSION['user_id'];
            $message = $_POST['message'];

            $messageRepository = new MessageRepository();
            $workspaceSlug = $messageRepository->addUserMessage($conversationId, $userId, $message);

            $service = new AnythingLLMService();
            $chatbotResponse = $service->askChatbot($message, $workspaceSlug);
            $chatbotResponse['textResponse'] = preg_replace('/\s*\[.*?\]\s*/', '', $chatbotResponse['textResponse']);

            $messageRepository->createMessage($conversationId, $chatbotResponse);

            header('Location: /conversation?id=' . $conversationId);
            exit;
        }
    }

    public function endConversation()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionService = new SessionService();
            $sessionService->startSession();

            $conversationId = $_POST['conversation_id'];
            $rating = $_POST['rating'] ?? null;

            if ($rating === null) {
                $_SESSION['error_message'] = 'Veuillez sélectionner une note avant de soumettre.';
                header('Location: /conversation?id=' . $conversationId);
                exit;
            }

            $repository = new ConversationRepository();
            $repository->endConversation($rating, $conversationId);

            header('Location: /conversation?id=' . $conversationId);
            exit;
        }
    }

    public function viewConversation()
    {
        $conversationId = $_GET['id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        if (! $conversationId) {
            header('Location: /home');
            exit;
        }

        $conversationRepository = new ConversationRepository();
        $conversation = $conversationRepository->getConversation($conversationId);

        $messageRepository = new MessageRepository();
        $messages = $messageRepository->getConversationMessages($conversationId);

        echo $this->render('conversations/conversation.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'user_id' => $userId,
            'chatBotId' => ChatBotConstants::CHAT_BOT_ID,
            'role' => $role,
        ]);
    }
}
