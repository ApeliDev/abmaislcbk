<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'email_errors.log');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'debug' => []];

// Log function for debugging
function log_debug($message, $data = null) {
    $log = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    if ($data !== null) {
        $log .= 'Data: ' . print_r($data, true) . PHP_EOL;
    }
    error_log($log, 3, 'email_debug.log');
}

// Validate and sanitize inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $recipient_name = htmlspecialchars(trim($_POST['recipient_name'] ?? ''));
    $email = filter_var(trim($_POST['recipient_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $paid_amount = htmlspecialchars(trim($_POST['paid_amount'] ?? ''));
    $paid_amount_numeric = htmlspecialchars(trim($_POST['paid_amount_numeric'] ?? ''));
    $outstanding_amount = htmlspecialchars(trim($_POST['outstanding_amount'] ?? ''));
    $due_date = htmlspecialchars(trim($_POST['due_date'] ?? ''));

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address format';
        echo json_encode($response);
        exit;
    }

    try {
        log_debug('Starting email sending process', [
            'recipient_email' => $email,
            'recipient_name' => $recipient_name
        ]);

        // Load email template from file
        $template_path = __DIR__ . '/email_template.html';
        if (!file_exists($template_path)) {
            throw new Exception("Email template not found at: " . $template_path);
        }
        $template = file_get_contents($template_path);
        if ($template === false) {
            throw new Exception("Failed to read email template");
        }

        // Replace placeholders with actual values
        $replacements = [
            '{RECIPIENT_NAME}' => htmlspecialchars($recipient_name),
            '{PAID_AMOUNT}' => htmlspecialchars($paid_amount),
            '{PAID_AMOUNT_NUMERIC}' => htmlspecialchars($paid_amount_numeric),
            '{OUTSTANDING_AMOUNT}' => htmlspecialchars($outstanding_amount),
            '{DUE_DATE}' => date('j F Y', strtotime($due_date)),
            '{SENDER_NAME}' => 'Mr. Michael Eganza',
            '{SENDER_TITLE}' => 'Director – Banking and Payment Services'
        ];

        $template = str_replace(array_keys($replacements), array_values($replacements), $template);

        // Configure PHPMailer
        $mail = new PHPMailer(true);

        // Enable verbose debug output
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.supportcbk.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@supportcbk.net';
        $mail->Password = 'Mont@2001';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Set character set
        $mail->CharSet = 'UTF-8';
        
        // Set default timezone
        date_default_timezone_set('Africa/Nairobi');

        // Additional debugging info
        $mail->Debugoutput = function($str, $level) {
            log_debug("PHPMailer: $level: $str");
        };

        // Recipients
        $mail->setFrom('info@supportcbk.net', 'Central Bank of Kenya');
        $mail->addAddress($email, $recipient_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'MANDATORY SETTLEMENT OF OUTSTANDING BALANCE - ' . $recipient_name;
        $mail->Body = $template;
        
        // Add plain text version
        $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $template));
        $mail->AltBody = $plainText;

        // Send the email
        $mail->send();
        $response['success'] = true;
        $response['message'] = 'Settlement notification email has been sent successfully';
        log_debug('Email sent successfully');
    } catch (Exception $e) {
        $error_message = "Message could not be sent. " . $e->getMessage();
        if (isset($mail) && !empty($mail->ErrorInfo)) {
            $error_message .= " Mailer Error: " . $mail->ErrorInfo;
        }
        $response['message'] = $error_message;
        log_debug('Email sending failed', [
            'error' => $e->getMessage(),
            'mailer_error' => $mail->ErrorInfo ?? 'No mailer error info',
            'trace' => $e->getTraceAsString()
        ]);
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
?>