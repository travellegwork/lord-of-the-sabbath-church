<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

function respond(int $code, bool $success, string $message): never {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, false, 'Method not allowed.');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['https://lordofthesabbath.church', 'https://www.lordofthesabbath.church'];
if ($origin !== '' && !in_array($origin, $allowed, true)) respond(403, false, 'This request was not accepted.');
$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw) > 150000) respond(413, false, 'The saved information is too large to email.');
$data = json_decode($raw, true);
if (!is_array($data)) respond(400, false, 'Invalid submission.');
if (!empty($data['website'])) respond(200, true, 'Email sent successfully.');
$email = filter_var(trim((string)($data['email'] ?? '')), FILTER_VALIDATE_EMAIL);
if ($email === false || strlen((string)$email) > 254) respond(422, false, 'Please enter a valid email address.');
$kind = trim(strip_tags((string)($data['kind'] ?? 'Saved Plan')));
$kind = mb_substr($kind, 0, 80, 'UTF-8');
$body = trim(strip_tags((string)($data['body'] ?? '')));
$body = mb_substr($body, 0, 100000, 'UTF-8');
if ($body === '') respond(422, false, 'There is no saved information to email.');
$subject = 'My ' . $kind . ' — Lord of the Sabbath Church';
$encoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$headers = [
    'From: Lord of the Sabbath Church <shalom@lordofthesabbath.church>',
    'Reply-To: shalom@lordofthesabbath.church',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit'
];
$message = $body . "\n\n—\nSent privately from Lord of the Sabbath Church\nhttps://lordofthesabbath.church\n";
if (!mail((string)$email, $encoded, $message, implode("\r\n", $headers))) respond(500, false, 'The email service could not send your saved information yet.');
respond(200, true, 'Email sent successfully.');
