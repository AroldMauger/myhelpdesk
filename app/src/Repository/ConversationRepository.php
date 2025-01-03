<?php

namespace App\Repository;


use App\Model\Conversation;

class ConversationRepository extends AbstractRepository
{
    public function getPreviousConversations(int $userId): array {

        $stmt = $this->pdo->prepare('SELECT * FROM conversations WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        $conversations = $stmt->fetchAll();

        return $conversations;
    }

    public function createConversation(int $userId, string $userMessage, string $workspaceSlug): int {

        $stmt = $this->pdo->prepare('INSERT INTO conversations (user_id, subject, category) VALUES (?, ?, ?)');

        $stmt->execute([$userId, $userMessage, $workspaceSlug]);
        $conversationId = $this->pdo->lastInsertId();
        return $conversationId;
    }

    public function endConversation(int $rating, int $conversationId): void {

        $stmt = $this->pdo->prepare('UPDATE conversations SET rating = ? WHERE id = ?');
        $stmt->execute([$rating, $conversationId]);
    }

    public function getConversation(int $conversationId): array {

        $stmt = $this->pdo->prepare('SELECT * FROM conversations WHERE id = ?');
        $stmt->execute([$conversationId]);
        $conversation = $stmt->fetch();

        return $conversation;
    }
}
