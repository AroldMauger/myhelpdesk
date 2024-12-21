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
        $apiUrl = 'http://192.168.32.1:3000/api/chat/completions';
        $model = 'mistral:latest'; // Modèle que vous utilisez
        $accessToken = getenv('MISTRAL_API_KEY'); // Récupère la variable d'environnement pour le token

        // Structure de la requête pour l'API OpenWebUI
        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $userMessage, // Le message de l'utilisateur
                ]
            ]
        ];

        // Initialisation de la requête cURL
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken, // Ajoute le token Bearer pour l'authentification
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Exécution de la requête
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Vérification du code de statut HTTP
        if ($httpCode !== 200 || $response === false) {
            return new ChatBotMessageDTO('Désolé, une erreur est survenue.', false); // Si la requête échoue
        }

        // Traitement de la réponse de l'API
        $responseData = json_decode($response, true);
        $content = $responseData['choices'][0]['message']['content'] ?? 'Réponse non valide'; // Extraire la réponse de l'API

        // Retourner la réponse sous forme d'un DTO
        return new ChatBotMessageDTO($content, true);
    }

    public function uploadFile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            $apiUrl = 'http://localhost:3000/api/v1/files/'; // Endpoint d'upload du fichier
            $filePath = $_FILES['file']['tmp_name'];
            $fileName = $_FILES['file']['name'];

            // Envoi du fichier à l'API
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . getenv('MISTRAL_API_KEY'), // Utilisation de la clé API
                'Accept: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'file' => new \CURLFile($filePath, mime_content_type($filePath), $fileName),
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || $response === false) {
                $_SESSION['error_message'] = 'Erreur lors de l’upload du fichier.';
                header('Location: /conversation?id=' . ($_GET['id'] ?? ''));
                exit;
            }

            // Traitement de la réponse pour récupérer l'ID du fichier
            $responseData = json_decode($response, true);
            $fileId = $responseData['id']; // ID du fichier téléchargé

            $_SESSION['success_message'] = 'Fichier ajouté avec succès. ID : ' . $fileId;

            // Vous pouvez maintenant utiliser cet ID de fichier dans une autre fonction pour poser des questions
            $_SESSION['file_id'] = $fileId;

            header('Location: /conversation?id=' . ($_GET['id'] ?? ''));
            exit;
        }
    }


    private function extractTextFromPdf($filePath)
    {
        // Si vous utilisez une bibliothèque PHP pour extraire le texte d'un PDF
        // Par exemple, vous pouvez utiliser une bibliothèque comme `setasign/fpdi`
        // Voici un exemple simplifié (vous devrez l'adapter pour votre propre bibliothèque)

        // Assurez-vous d'avoir installé une bibliothèque pour lire des PDF (ex : pdftotext)
        // Le code ici est juste un exemple
        $content = '';
        if (file_exists($filePath)) {
            $content = shell_exec("pdftotext " . escapeshellarg($filePath) . " -");
        }
        return $content;
    }

}