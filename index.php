<?php

error_reporting(-1);
ini_set('display_errors', 0);
ini_set('log_errors', 'on');
ini_set('error_log', __DIR__ . '/errors.log');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
$phrases = require_once __DIR__ . '/phrases.php';
require_once __DIR__ . '/keyboards.php';
require_once __DIR__ . '/functions.php';

/**
 * @var array $phrases
 * @var array $inline_keyboard1
 * @var array $keyboard1
 * @var array $keyboard2
 */

$telegram = new \Telegram\Bot\Api(TOKEN);
$update = $telegram->getWebhookUpdate();

//$chat_id = $update['message']['chat']['id'] ?? 0;
$text = $update['message']['text'] ?? '';
$name = $update['message']['from']['first_name'] ?? 'Guest';

if (isset($update['message']['chat']['id'])) {
    $chat_id = $update['message']['chat']['id'];
} elseif (isset($update['user']['id'])) {
    $chat_id = (int)$update['user']['id'];
    $query_id = $update['query_id'] ?? '';
    $cart = $update['cart'] ?? [];
    $total_sum = $update['total_sum'] ?? 0;
    $total_sum = (int)$total_sum;
} elseif (isset($update['pre_checkout_query']['id'])) {
    $chat_id = $update['pre_checkout_query']['id'];
}

if ($text == '/start') {
    $keyboard = checkChatId($chat_id) ? $keyboard2 : $keyboard1;
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => sprintf($phrases['start'], $name),
        'parse_mode' => 'HTML',
        'reply_markup' => new \Telegram\Bot\Keyboard\Keyboard($keyboard),
    ]);
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $phrases['inline_keyboard'],
        'parse_mode' => 'HTML',
        'reply_markup' => new \Telegram\Bot\Keyboard\Keyboard($inline_keyboard1),
    ]);
} elseif ($text == $phrases['btn_unsubscribe']) {
    if (deleteSubscriber($chat_id)) {
        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $phrases['success_unsubscribe'],
            'parse_mode' => 'HTML',
            'reply_markup' => new \Telegram\Bot\Keyboard\Keyboard($keyboard1),
        ]);
    } else {
        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $phrases['error_unsubscribe'],
            'parse_mode' => 'HTML',
            'reply_markup' => new \Telegram\Bot\Keyboard\Keyboard($keyboard2),
        ]);
    }
} elseif (isset($update['message']['web_app_data'])) {
    $btn = $update['message']['web_app_data']['button_text'];
    $data = json_decode($update['message']['web_app_data']['data'], 1);

    if (!checkChatId($chat_id) && !empty($data['name']) && !empty($data['email'])) {
        if (addSubscriber($chat_id, $data)) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => $phrases['success_subscribe'],
                'parse_mode' => 'HTML',
                'reply_markup' => new \Telegram\Bot\Keyboard\Keyboard($keyboard2),
            ]);
        } else {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => $phrases['error_subscribe'],
                'parse_mode' => 'HTML',
                'reply_markup' => new \Telegram\Bot\Keyboard\Keyboard($keyboard1),
            ]);
        }
    } else {
        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $phrases['error'],
            'parse_mode' => 'HTML',
            'reply_markup' => new \Telegram\Bot\Keyboard\Keyboard($keyboard1),
        ]);
    }
} elseif (!empty($query_id) && !empty($cart) && !empty($total_sum)) {
    debug($cart);
    if (checkCart($cart, $total_sum)) {
        if (!$order_id = addOrder($chat_id, $update)) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "Error add order",
                'parse_mode' => 'HTML',
            ]);
            $res = ['res' => false, 'answer' => 'Cart Error in order'];
            echo json_encode($res);
            die;
        }

        $order_products = [];
        foreach ($cart as $item) {
            $order_products[] = [
                'label' => "{$item['title']} x {$item['qty']}",
                'amount' => $item['price'] * $item['qty'],
            ];
        }

        try {
            $telegram->sendInvoice([
                'chat_id' => $chat_id,
                'title' => "Order № {$order_id}",
                'description' => "Payment",
                'payload' => $order_id,
                'provider_token' => STRIPE_TOKEN,
                'currency' => 'USD',
                'prices' => $order_products
            ]);
            $res = ['res' => true];
            echo json_encode($res);
            die;
        } catch (\Telegram\Bot\Exceptions\TelegramSDKException $e) {
            $res = ['res' => false, 'answer' => $e->getMessage()];
            echo json_encode($res);
            die;
        }
    } else {
        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => "Cart Error",
            'parse_mode' => 'HTML',
        ]);
        $res = ['res' => false, 'answer' => 'Cart Error in check'];
    }

    echo json_encode($res);
    die;
} elseif (isset($update['pre_checkout_query'])) {
    $telegram->answerPreCheckoutQuery([
        'pre_checkout_query_id' => $chat_id,
        'ok' => true,
    ]);
} elseif (isset($update['message']['successful_payment'])) {
    $order_id = $update['message']['successful_payment']['invoice_payload'];
    $payment_id = $update['message']['successful_payment']['provider_payment_charge_id'];
    $sum = $update['message']['successful_payment']['total_amount'] / 100;
    $curr = $update['message']['successful_payment']['currency'];
    toggleOrderStatus($order_id, $payment_id);
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Payment for order #{$order_id} final sum: {$sum} {$curr}",
        'parse_mode' => 'HTML',
    ]);
} else {
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $phrases['error'],
        'parse_mode' => 'HTML',
    ]);
}
