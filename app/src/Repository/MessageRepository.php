<?php

namespace App\Repository;

use App\Model\ChatBotConstants;

class MessageRepository extends AbstractRepository
{
    public function createMessage(int $conversationId, array $chatbotResponse):void {

        $stmt = $this->pdo->prepare('INSERT INTO messages (conversation_id, user_id, message) VALUES (?, ?, ?)');

        $stmt->execute([$conversationId, ChatBotConstants::CHAT_BOT_ID, $chatbotResponse['textResponse']]);
        return;
    }

    public function addUserMessage(int $conversationId, int $userId, string $message): string {
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

        return $workspaceSlug;
    }

    public function getConversationMessages(int $conversationId): array {

        $stmt = $this->pdo->prepare('
        SELECT m.*, u.username
        FROM messages m
        JOIN users u ON m.user_id = u.id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
    ');
        $stmt->execute([$conversationId]);
        $messages = $stmt->fetchAll();
        return $messages;
    }
}
