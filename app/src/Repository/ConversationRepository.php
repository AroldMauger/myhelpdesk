<?php

namespace App\Repository;

use App\Controller\AbstractController;

class ConversationRepository extends AbstractController
{
    public function getPreviousConversations($userId) {

        $stmt = $this->pdo->prepare('SELECT * FROM conversations WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        $conversations = $stmt->fetchAll();

        return $conversations;
    }

    public function createConversation($userId, $userMessage, $workspaceSlug) {

        $stmt = $this->pdo->prepare('INSERT INTO conversations (user_id, subject, category) VALUES (?, ?, ?)');

        $stmt->execute([$userId, $userMessage, $workspaceSlug]);
        $conversationId = $this->pdo->lastInsertId();
        return $conversationId;
    }

    public function endConversation($rating, $conversationId) {

        $stmt = $this->pdo->prepare('UPDATE conversations SET rating = ? WHERE id = ?');
        $stmt->execute([$rating, $conversationId]);

    }

    public function getConversation($conversationId) {

        $stmt = $this->pdo->prepare('SELECT * FROM conversations WHERE id = ?');
        $stmt->execute([$conversationId]);
        $conversation = $stmt->fetch();

        return $conversation;
    }
}