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
<h3 style='font-family:Arial,sans-serif;color:#333;'>New Application Submission</h3>
<table cellpadding='10' cellspacing='0' 
       style='width:100%;max-width:700px;border-collapse:separate;border-spacing:0;border-radius:8px;overflow:hidden;font-family:Arial,sans-serif;background:#f9f9f9;color:#333;'>
  <tr style='background:#6b1d3f;color:#fff;font-weight:bold;'>
    <td style='padding:12px;'>Field</td>
    <td style='padding:12px;'>Value</td>
  </tr>
  <tr style='background:#fff;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Name</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$name</td>
  </tr>
  <tr style='background:#f7f7f7;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Contact Method</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$contactMethod</td>
  </tr>
  <tr style='background:#fff;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Contact Info</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$contactInfo</td>
  </tr>
  <tr style='background:#f7f7f7;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Current Life</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$currentLife</td>
  </tr>
  <tr style='background:#fff;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Commitment</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$commitment</td>
  </tr>
  <tr style='background:#f7f7f7;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Interested?</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$interested</td>
  </tr>
  <tr style='background:#fff;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Tried Before</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$triedBefore</td>
  </tr>
  <tr style='background:#f7f7f7;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Income Goal</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$incomeGoal</td>
  </tr>
  <tr style='background:#fff;'>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>Challenge</td>
    <td style='padding:12px;border-bottom:1px solid #ddd;'>$challenge</td>
  </tr>
  <tr style='background:#f7f7f7;'>
    <td style='padding:12px;'>Free Training?</td>
    <td style='padding:12px;'>$freeTraining</td>
  </tr>
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

    $mail->setFrom(getenv('GMAIL_USER'), 'The New Rich Application');
    $mail->addAddress(getenv('MAIL_TO'));
    $mail->addAddress(getenv('MAIL_TO_2'));   

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



