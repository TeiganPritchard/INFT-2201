<?php
require '../../../vendor/autoload.php';

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


$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($uri, '/'));
$id = end($parts);

if (!is_numeric($id)) {
    $page->badRequest();
    exit;
}

$id = (int)$id;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $result = $mail->getMail($id);
        if (!$result) {
            $page->notFound();
            exit;
        }
        $page->item($result);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['subject'], $data['body'])) {
            $page->badRequest();
            exit;
        }
        $rows = $mail->updateMail($id, trim($data['subject']), trim($data['body']));
        if ($rows === 0) {
            $page->notFound();
            exit;
        }
        $page->item(["updated" => true]);
        break;

    case 'DELETE':
        $rows = $mail->deleteMail($id);
        if ($rows === 0) {
            $page->notFound();
            exit;
        }
        $page->item(["deleted" => true]);
        break;

    default:
        $page->badRequest();
        break;
}
