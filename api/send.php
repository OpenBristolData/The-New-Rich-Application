<?php
// api/send.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// Require PHPMailer manually
require __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/vendor/PHPMailer/src/SMTP.php';
require __DIR__ . '/vendor/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get form data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

function safe($v) { return htmlspecialchars(trim((string)($v ?? ''))); }

$name = safe($data['name'] ?? '');

$contactMethod = safe($data['contactMethod'] ?? '');
$contactInfo = safe($data['contactInfo'] ?? '');
$currentLife = nl2br(safe($data['currentLife'] ?? ''));
$commitment = safe($data['commitment'] ?? '');
$interested = safe($data['interested'] ?? '');
$triedBefore = safe($data['triedBefore'] ?? '');
$incomeGoal = safe($data['incomeGoal'] ?? '');
$challenge = nl2br(safe($data['challenge'] ?? ''));
$freeTraining = safe($data['freeTraining'] ?? '');

$html = "
  <h3>New Application Submission</h3>
  <table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;max-width:700px;font-family:Arial'>
    <tr><td><b>Name</b></td><td>$name</td></tr>
    <tr><td><b>Contact Method</b></td><td>$contactMethod</td></tr>
    <tr><td><b>Contact Info</b></td><td>$contactInfo</td></tr>
    <tr><td><b>Current Life</b></td><td>$currentLife</td></tr>
    <tr><td><b>Commitment</b></td><td>$commitment</td></tr>
    <tr><td><b>Interested?</b></td><td>$interested</td></tr>
    <tr><td><b>Tried Before</b></td><td>$triedBefore</td></tr>
    <tr><td><b>Income Goal</b></td><td>$incomeGoal</td></tr>
    <tr><td><b>Challenge</b></td><td>$challenge</td></tr>
    <tr><td><b>Free Training?</b></td><td>$freeTraining</td></tr>
  </table>
";

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('GMAIL_USER');
    $mail->Password = getenv('GMAIL_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(getenv('GMAIL_USER'), 'Website Form');
    $mail->addAddress(getenv('MAIL_TO'));

    if (filter_var($contactInfo, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($contactInfo, $name ?: $contactInfo);
    }

    $mail->isHTML(true);
    $mail->Subject = "New Application from $name";
    $mail->Body = $html;
    $mail->AltBody = strip_tags($html);

    $mail->send();
    echo json_encode(['ok' => true, 'message' => 'Email sent']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $mail->ErrorInfo]);
}

