<?php

const TOKEN = '6395924254:AAEEzoOFp1qilwVkuMMtRgXiElMIILysDz0';

const WEBAPP1 = 'https://weather3252.fun/bots/2/web-apps/page.php';
const WEBAPP2 = 'https://weather3252.fun/bots/2/web-apps/page1.php';

const STRIPE_TOKEN = '284685063:TEST:ZDAzY2M2N2UwZmJj';

$db = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '12345678',
    'db' => 'my_db',
];

$dsn = "mysql:host={$db['host']};dbname={$db['db']};charset=utf8";
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $db['user'], $db['pass'], $opt);
