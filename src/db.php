<?php
session_start();
date_default_timezone_set('Asia/Novosibirsk');

require_once 'includes/config.php';

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    die("Ошибка подключения к БД. Сервер временно недоступен.");
}

require_once 'includes/functions.php';
require_once 'models/Event.php';
require_once 'models/Institute.php';
require_once 'models/Category.php';
require_once 'models/Participant.php';
require_once 'models/Faculty.php';

$eventModel = new Event($pdo);
$instituteModel = new Institute($pdo);
$categoryModel = new Category($pdo);
$participantModel = new Participant($pdo);
$facultyModel = new Faculty($pdo);
