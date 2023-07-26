<?php

function debug($data, $log = true): void
{
    if ($log) {
        file_put_contents(__DIR__ . '/logs.txt', print_r($data, true), FILE_APPEND);
    } else {
        echo "<pre>" . print_r($data, 1) . "</pre>";
    }
}

function send_request(string $url): mixed
{
    return json_decode(file_get_contents(
        $url,
        false,
        stream_context_create(['http' => ['ignore_errors' => true]])
    ));
}

function check_chat_id(int $chat_id): bool
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE chat_id = ?");
    $stmt->execute([$chat_id]);
    return (bool)$stmt->fetchColumn();
}

function add_subscriber(int $chat_id, array $data): bool
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO subscribers (chat_id, name, email) VALUES (?, ?, ?)");
    return $stmt->execute([$chat_id, $data['name'], $data['email']]);
}

function remove_subscriber(int $chat_id): bool
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM subscribers WHERE chat_id = ?");
    return $stmt->execute([$chat_id]);
}

function get_products(int $start, int $per_page): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products LIMIT $start, $per_page");
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_start(int $page, int $per_page): int {
    return ($page - 1) * $per_page;
}