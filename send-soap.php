<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

function reply(int $status, bool $success, string $message): never {
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') reply(405, false, 'Method not allowed.');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['https://lordofthesabbath.church', 'https://www.lordofthesabbath.church'];
if ($origin !== '' && !in_array($origin, $allowedOrigins, true)) reply(403, false, 'This request was not accepted.');

$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw) > 50000) reply(413, false, 'The journal entry is too large.');
$data = json_decode($raw, true);
if (!is_array($data)) reply(400, false, 'Invalid form submission.');
if (!empty($data['website'])) reply(200, true, 'Your SOAP journal has been emailed successfully.');

$email = filter_var(trim((string)($data['email'] ?? '')), FILTER_VALIDATE_EMAIL);
if ($email === false || strlen((string)$email) > 254) reply(422, false, 'Please enter a valid email address.');

function cleanText(mixed $value, int $limit): string {
    $text = trim(strip_tags((string)$value));
    $text = preg_replace('/\r\n?|\n/u', "\n", $text) ?? '';
    return mb_substr($text, 0, $limit, 'UTF-8');
}

$reference = cleanText($data['reference'] ?? '', 100);
$scripture = cleanText($data['scripture'] ?? '', 2000);
$observation = cleanText($data['observation'] ?? '', 8000);
$application = cleanText($data['application'] ?? '', 8000);
$prayer = cleanText($data['prayer'] ?? '', 8000);
if ($observation === '' && $application === '' && $prayer === '') reply(422, false, 'Please write at least one SOAP journal entry.');

$subject = 'My SOAP of the Day — ' . ($reference !== '' ? $reference : date('j F Y'));
$body = "MY SOAP OF THE DAY\n\n";
$body .= "SCRIPTURE\n" . ($reference !== '' ? $reference . " KJV\n" : '') . $scripture . "\n\n";
$body .= "OBSERVATION\n" . ($observation !== '' ? $observation : '(No entry)') . "\n\n";
$body .= "APPLICATION\n" . ($application !== '' ? $application : '(No entry)') . "\n\n";
$body .= "PRAYER\n" . ($prayer !== '' ? $prayer : '(No entry)') . "\n\n";
$body .= "—\nSent privately from Lord of the Sabbath Church\nhttps://lordofthesabbath.church\n";

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$headers = [
    'From: Lord of the Sabbath Church <shalom@lordofthesabbath.church>',
    'Reply-To: shalom@lordofthesabbath.church',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit'
];

if (!mail((string)$email, $encodedSubject, $body, implode("\r\n", $headers))) {
    reply(500, false, 'The email service could not send your journal yet. Please try again shortly.');
}
reply(200, true, 'Your SOAP journal has been emailed successfully.');
