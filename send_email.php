<?php
<<<<<<< Updated upstream
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'email_errors.log');
=======
error_reporting(E_ALL);
ini_set('display_errors', 1);
>>>>>>> Stashed changes

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

<<<<<<< Updated upstream
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
=======
// Function to log errors to a file
function logError($message) {
    $logFile = __DIR__ . '/email_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = isset($backtrace[1]) ? $backtrace[1]['function'] . '()' : 'main';
    $logMessage = "[$timestamp] [$caller] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    // Also output to screen for debugging
    echo "DEBUG: $message<br>\n";
}

// Function to log email attempts
function logEmailAttempt($email, $subject, $success, $error = '') {
    $logFile = __DIR__ . '/email_attempts.log';
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    $logMessage = "[$timestamp] $status - To: $email | Subject: $subject";
    if (!$success) {
        $logMessage .= " | Error: $error";
    }
    $logMessage .= PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

$response = ['success' => false, 'message' => ''];
>>>>>>> Stashed changes

logError('=== Starting email sending process ===');
logError('Request method: ' . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< Updated upstream
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
=======
    try {
        // Debug: Show all received POST data
        logError('Raw POST data received:');
        foreach ($_POST as $key => $value) {
            $displayValue = ($key === 'recipient_email') ? 
                substr($value, 0, 3) . '***@' . substr(strrchr($value, '@'), 1) : $value;
            logError("  $key = '$displayValue'");
        }
        
        if (empty($_POST)) {
            throw new Exception('No POST data received. Check if form is submitting correctly.');
        }
        $required_fields = [
            'recipient_name' => 'Recipient name',
            'recipient_email' => 'Recipient email',
            'paid_amount' => 'Paid amount',
            'paid_amount_numeric' => 'Paid amount (numeric)',
            'outstanding_amount' => 'Outstanding amount',
            'due_date' => 'Due date'
        ];
>>>>>>> Stashed changes

        $missing_fields = [];
        foreach ($required_fields as $field => $label) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                $missing_fields[] = $label . " (field: $field)";
                logError("Missing field: $field");
            }
        }

        if (!empty($missing_fields)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing_fields));
        }

        // Sanitize inputs
        $recipient_name = trim($_POST['recipient_name']);
        $email = trim($_POST['recipient_email']);
        $paid_amount = trim($_POST['paid_amount']);
        $paid_amount_numeric = trim($_POST['paid_amount_numeric']);
        $outstanding_amount = trim($_POST['outstanding_amount']);
        $due_date = trim($_POST['due_date']);

        logError("Processed data:");
        logError("  Name: '$recipient_name'");
        logError("  Email: '" . substr($email, 0, 3) . "***@" . substr(strrchr($email, '@'), 1) . "'");
        logError("  Paid Amount: '$paid_amount'");
        logError("  Outstanding: '$outstanding_amount'");
        logError("  Due Date: '$due_date'");

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address format: ' . $email);
        }

        // Validate date
        $dateTimestamp = strtotime($due_date);
        if (!$dateTimestamp) {
            throw new Exception('Invalid due date format: ' . $due_date);
        }

        // Load email template
        $template_path = __DIR__ . '/email_template.html';
        logError("Looking for template at: $template_path");
        
        if (!file_exists($template_path)) {
            throw new Exception('Email template file not found at: ' . $template_path);
        }
        
        $template = file_get_contents($template_path);
        if ($template === false) {
            throw new Exception('Failed to read email template');
        }
        
        logError("Template loaded successfully (" . strlen($template) . " characters)");

        // Process template
        $replacements = [
            '{RECIPIENT_NAME}' => htmlspecialchars($recipient_name, ENT_QUOTES, 'UTF-8'),
            '{PAID_AMOUNT}' => htmlspecialchars($paid_amount, ENT_QUOTES, 'UTF-8'),
            '{PAID_AMOUNT_NUMERIC}' => htmlspecialchars($paid_amount_numeric, ENT_QUOTES, 'UTF-8'),
            '{OUTSTANDING_AMOUNT}' => htmlspecialchars($outstanding_amount, ENT_QUOTES, 'UTF-8'),
            '{DUE_DATE}' => date('j F Y', $dateTimestamp),
            '{SENDER_NAME}' => 'Mr. Michael Eganza',
            '{SENDER_TITLE}' => 'Director – Banking and Payment Services'
        ];
<<<<<<< Updated upstream

        $template = str_replace(array_keys($replacements), array_values($replacements), $template);
=======
        
        logError("Template replacements:");
        foreach ($replacements as $key => $value) {
            $displayValue = (strpos($key, 'RECIPIENT') !== false && strpos($value, '@') !== false) ? 
                substr($value, 0, 3) . '***' : $value;
            logError("  $key => '$displayValue'");
        }
        
        $emailBody = str_replace(array_keys($replacements), array_values($replacements), $template);
        logError("Template processed successfully (" . strlen($emailBody) . " characters after replacement)");
>>>>>>> Stashed changes

        // Setup PHPMailer - use working settings from test
        $mail = new PHPMailer(true);
        
        // Use minimal debug for production
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Change to SMTP::DEBUG_SERVER if you need more details
        $mail->Debugoutput = function($str, $level) {
            logError("SMTP: " . trim($str));
        };

<<<<<<< Updated upstream
        // Enable verbose debug output
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Server settings
=======
        // Server settings (use the working configuration)
>>>>>>> Stashed changes
        $mail->isSMTP();
        $mail->Host = 'mail.supportcbk.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@supportcbk.net';
        $mail->Password = 'Mont@2001';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
<<<<<<< Updated upstream
        
        // Set character set
        $mail->CharSet = 'UTF-8';
        
        // Set default timezone
        date_default_timezone_set('Africa/Nairobi');

        // Additional debugging info
        $mail->Debugoutput = function($str, $level) {
            log_debug("PHPMailer: $level: $str");
        };

=======
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 30;
        
        // SSL options
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // Headers
        $mail->XMailer = ' ';
        
>>>>>>> Stashed changes
        // Recipients
        $mail->setFrom('info@supportcbk.net', 'Central Bank of Kenya');
        $mail->addAddress($email, $recipient_name);

        // Content
        $emailSubject = 'RE: MANDATORY SETTLEMENT OF OUTSTANDING BALANCE - ' . $recipient_name;
        $mail->isHTML(true);
<<<<<<< Updated upstream
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
=======
        $mail->Subject = $emailSubject;
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));

        logError("Email configured, attempting to send...");
        logError("Subject: $emailSubject");
        
        // Send email
        if ($mail->send()) {
            $response['success'] = true;
            $response['message'] = 'Settlement notification email has been sent successfully';
            logEmailAttempt($email, $emailSubject, true);
            logError("✅ EMAIL SENT SUCCESSFULLY!");
        } else {
            throw new Exception('Email sending failed: ' . $mail->ErrorInfo);
        }
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        $response['message'] = "Failed to send email: " . $errorMsg;
        logError("❌ ERROR: " . $errorMsg);
        
        if (isset($email) && isset($emailSubject)) {
            logEmailAttempt($email, $emailSubject, false, $errorMsg);
        }
>>>>>>> Stashed changes
    }
} else {
    $response['message'] = 'Invalid request method. Expected POST, got: ' . $_SERVER['REQUEST_METHOD'];
    logError('Invalid request method received');
}

logError('=== Email process completed ===');
logError('Response: ' . json_encode($response));

// Output JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>