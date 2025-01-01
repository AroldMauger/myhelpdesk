<?php

namespace App\Repository;

use App\Controller\AbstractController;

class AdminRepository extends AbstractController
{
    public function allConversations() {

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

        return $conversations;
    }

    public function deleteConversation($conversationId) {

        $stmt = $this->pdo->prepare('DELETE FROM conversations WHERE id = :id');
        $stmt->bindParam(':id', $conversationId, \PDO::PARAM_INT);
        $stmt->execute();

    }
}