<?php
require '../../vendor/autoload.php';

use Application\Mail;
use Application\Page;

try {
    $dsn = "pgsql:host=" . getenv('DB_PROD_HOST') . ";dbname=" . getenv('DB_PROD_NAME');
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$mail = new Mail($pdo);
$page = new Page();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page->list($mail->getAllMail());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['subject'], $data['body'])) {
        $page->badRequest();
        exit;
    }

    $id = $mail->createMail(trim($data['subject']), trim($data['body']));
    http_response_code(201);
    $page->item(["id" => $id]);
    exit;
}

$page->badRequest();
