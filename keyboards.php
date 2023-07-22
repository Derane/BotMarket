<?php
/**
 * @var array $phrases
 */

$keyboard = [
    'keyboard' => [
        [
            ['text' => $phrases['btn_subscribe'], 'web_app' => ['url' => WEBAPP]]
        ]
    ],
    'resize_keyboard' => true,
    'input_field_placeholder' => $phrases['select_btn']
];
$keyboard1 = [
    'inline_keyboard' => [
        [
            ['text' => $phrases['inline_btn'], 'web_app' => ['url' => WEBAPP1]]
        ]
    ]
];