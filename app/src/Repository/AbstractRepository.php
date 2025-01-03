<?php

namespace App\Repository;

use PDO;

abstract class AbstractRepository
{
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = require __DIR__ . './../config/bdd.php';
    }

}
