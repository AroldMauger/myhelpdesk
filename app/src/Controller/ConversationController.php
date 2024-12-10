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
            $subject = $_POST['message'];

            $chatbotResponse = $this->askChatbot($subject);

            $stmt = $this->pdo->prepare('INSERT INTO conversations (user_id, subject) VALUES (?, ?)');
            $stmt->execute([$userId, $subject]);

            $conversationId = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare('INSERT INTO messages (conversation_id, user_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$conversationId, ChatBotConstants::CHAT_BOT_ID, $chatbotResponse->content]);  // 0 pour l'ID du chatbot

            header('Location: /conversation?id=' . $conversationId);
            exit;
        }
    }


    public function addMessage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionService = new SessionService();
            $sessionService->startSession();

            $conversationId = $_POST['conversation_id'];
            $userId = $_SESSION['user_id'];
            $message = $_POST['message'];

            $stmt = $this->pdo->prepare('INSERT INTO messages (conversation_id, user_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$conversationId, $userId, $message]);

            $chatbotResponse = $this->askChatbot($message);

            $stmt = $this->pdo->prepare('INSERT INTO messages (conversation_id, user_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$conversationId, ChatBotConstants::CHAT_BOT_ID, $chatbotResponse->content]);

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

            header('Location: /home');
            exit;
        }
    }

    public function viewConversation()
    {
        $conversationId = $_GET['id'] ?? null;

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

        echo $this->render('conversation.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'chatBotId' => ChatBotConstants::CHAT_BOT_ID
        ]);
    }


    public function askChatbot(string $userMessage): ChatBotMessageDTO
    {
        $apiUrl = 'http://192.168.32.1:11434/api/generate';
        $model = 'mistral';

        $data = [
            'model' => $model,
            'prompt' => "[INST] $userMessage [/INST]",
            'raw' => true,
            'stream' => false,
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            return new ChatBotMessageDTO('Désolé, une erreur est survenue.', false);
        }

        $responseData = json_decode($response, true);
        $content = $responseData['response'] ?? 'Réponse non valide';

        return new ChatBotMessageDTO($content, true);
    }

}