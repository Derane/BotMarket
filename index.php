<?php

error_reporting(E_ALL);

ini_set('log_errors', 1);

ini_set('error_log', 'errors_log');

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
debug($update);

$chat_id = $update['message']['chat']['id'] ?? 0;
$text = $update['message']['text'] ?? '';
$name = $update['message']['from']['first_name'] ?? 'Guest';

if (!$chat_id) {
    die;
}

if ($text == '/start') {
    $keyboard = check_chat_id($chat_id) ? $keyboard2 : $keyboard1;
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
} elseif (isset($update['message']['web_app_data'])) {
    $btn = $update['message']['web_app_data']['button_text'];
    $data = json_decode($update['message']['web_app_data']['data'], 1);

    if (!check_chat_id($chat_id) && !empty($data['name']) && !empty($data['email'])) {
        if (add_subscriber($chat_id, $data)) {
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
} else {
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $phrases['error'],
        'parse_mode' => 'HTML',
    ]);
}
