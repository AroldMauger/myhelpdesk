<?php

if (!function_exists('getConnection')) {
    function getConnection() {
        $dbname = getenv('MYSQL_DATABASE') ?: 'myhelpdesk';
        $host = getenv('MYSQL_HOST') ?: 'database';
        $user = getenv('MYSQL_USER') ?: 'user';
        $password = getenv('MYSQL_PASSWORD') ?: 'password';

        return new PDO("mysql:dbname=$dbname;host=$host", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}

return getConnection();