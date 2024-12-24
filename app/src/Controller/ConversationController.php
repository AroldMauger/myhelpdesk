<?php

namespace App\Controller;
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

use App\DTO\ChatBotMessageDTO;
use App\Model\ChatBotConstants;
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

            $chatbotResponse = $this->askChatbot($userMessage, $workspaceSlug);
            $stmt = $this->pdo->prepare('INSERT INTO conversations (user_id, subject, category) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $userMessage, $workspaceSlug]);

            $conversationId = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare('INSERT INTO messages (conversation_id, user_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$conversationId, ChatBotConstants::CHAT_BOT_ID, $chatbotResponse->textResponse]);  // 0 pour l'ID du chatbot

            header('Location: /conversation?id=' . $conversationId);
            exit;
        }
    }


    public function addMessage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionService = new SessionService();
            $sessionService->startSession();

            $conversationId = $_POST['conversation_id'] ?? $_GET['id'] ?? null;

            if (!$conversationId) {
                throw new \Exception('ID de conversation non spécifié.');
            }

            $userId = $_SESSION['user_id'];
            $message = $_POST['message'];

            if (isset($_SESSION['workspace_slug'])) {
                $workspaceSlug = $_SESSION['workspace_slug'];
            } else {
                $stmt = $this->pdo->prepare('SELECT category FROM conversations WHERE id = ?');
                $stmt->execute([$conversationId]);
                $result = $stmt->fetch();

                if ($result) {
                    $workspaceSlug = $result['category'];
                    $_SESSION['workspace_slug'] = $workspaceSlug;
                } else {
                    throw new \Exception('Conversation non trouvée.');
                }
            }

            $stmt = $this->pdo->prepare('INSERT INTO messages (conversation_id, user_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$conversationId, $userId, $message]);

            $chatbotResponse = $this->askChatbot($message, $workspaceSlug);

            $stmt = $this->pdo->prepare('INSERT INTO messages (conversation_id, user_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$conversationId, ChatBotConstants::CHAT_BOT_ID, $chatbotResponse->textResponse]);

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

            $stmt = $this->pdo->prepare('UPDATE conversations SET rating = ? WHERE id = ?');
            $stmt->execute([$rating, $conversationId]);

            header('Location: /conversation?id=' . $conversationId);
            exit;
        }
    }

    public function viewConversation()
    {
        $conversationId = $_GET['id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? null;

        if (!$conversationId) {
            header('Location: /home');
            exit;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM conversations WHERE id = ?');
        $stmt->execute([$conversationId]);
        $conversation = $stmt->fetch();

        $stmt = $this->pdo->prepare('
        SELECT m.*, u.username
        FROM messages m
        JOIN users u ON m.user_id = u.id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
    ');
        $stmt->execute([$conversationId]);
        $messages = $stmt->fetchAll();

        echo $this->render('conversations/conversation.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'user_id' => $userId,
            'chatBotId' => ChatBotConstants::CHAT_BOT_ID,
            'role' => $role,
        ]);
    }


    public function askChatbot($userMessage, $workspaceSlug)
    {
        $apiUrl = "http://172.27.144.1:3001/api/v1/workspace/{$workspaceSlug}/chat";
        $accessToken = getenv('JWT_SECRET');

        $postData = json_encode([
            'message' => $userMessage,
            'mode' => 'query'
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $accessToken,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response !== false) {
            return json_decode($response);
        }

        return null;
    }


}