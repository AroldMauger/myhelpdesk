<?php

namespace App\Repository;

use App\Model\User;
use App\Repository\AbstractRepository;

class UserRepository extends AbstractRepository {

    public function fetchUserByEmail(string $email):?User {

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, User::class);

        $user = $stmt->fetch();
        return $user ?: null;

    }

    public function insertUser(string $username, string $email, string $password_hash, string $role): void {

        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $password_hash, $role]);

        return;
    }
}