<?php

function debug($data, $log = true): void
{
    if ($log) {
        file_put_contents(__DIR__ . '/logs.txt', print_r($data, true), FILE_APPEND);
    } else {
        echo "<pre>" . print_r($data, 1) . "</pre>";
    }
}


function checkChatId(int $chat_id): bool
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE chat_id = ?");
    $stmt->execute([$chat_id]);
    return (bool)$stmt->fetchColumn();
}

function addSubscriber(int $chat_id, array $data): bool
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO subscribers (chat_id, name, email) VALUES (?, ?, ?)");
    return $stmt->execute([$chat_id, $data['name'], $data['email']]);
}

function deleteSubscriber(int $chat_id): bool
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM subscribers WHERE chat_id = ?");
    return $stmt->execute([$chat_id]);
}

function getProducts(int $start, int $per_page): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products LIMIT $start, $per_page");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getStart(int $page, int $per_page): int
{
    return ($page - 1) * $per_page;
}

function checkCart(array $cart, int $total_sum): bool
{
    global $pdo;
    $ids = array_keys($cart);
    $in_placeholders = rtrim(str_repeat('?,', count($ids)), ',');

    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($in_placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    debug($in_placeholders);
    debug($products);
    if (count($products) != count($ids)) {
        return false;
    }

    $sum = 0;
    foreach ($products as $product) {
        $sum += $product['price'] * $cart[$product['id']]['qty'];
    }

    return $sum == $total_sum;
}

function addOrder(int $chatId, \Telegram\Bot\Objects\Update $update): bool|int
{
    global $pdo;
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (chat_id, query_id, total_sum) VALUES (?, ?, ?)");
        $stmt->execute([$chatId, $update['query_id'], $update['total_sum']]);
        $order_id = $pdo->lastInsertId();
        $sqlPart = '';
        $binds = [];
        foreach ($update['cart'] as $item) {
            $sqlPart .= "(?,?,?,?,?),";

            $binds = array_merge($binds, [$order_id, $item['id'], $item['title'], $item['price'], $item['qty']]);
        }
        $sqlPart = rtrim($sqlPart, ',');
        $stmt = $pdo->prepare("INSERT INTO order_products (order_id, product_id, title, price, qty) VALUES $sqlPart");
        $result = $stmt->execute($binds) ? $order_id : false;
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        debug($e->getMessage());
    }
    return $result;
}

function toggleOrderStatus(int $orderId, string $paymentId): bool
{
    global $pdo;
    $statement = $pdo->prepare("UPDATE orders SET status = 1, payment_id = ? WHERE id = ?");
    return $statement->execute([$paymentId, $orderId]);
}