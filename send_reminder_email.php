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
    $adminEmail = 'livingstoneapeli@gmail.com';
    $subject = '✅ New Reminder Email Sent - ' . date('Y-m-d H:i:s');
    
    $body = "📧 NEW REMINDER EMAIL NOTIFICATION\n";
    $body .= "================================\n\n";
    
    $body .= "👤 RECIPIENT INFORMATION\n";
    $body .= "----------------------\n";
    $body .= "Name: $recipientName\n";
    $body .= "Email: $recipientEmail\n\n";
    
    $body .= "💰 PAYMENT DETAILS\n";
    $body .= "-----------------\n";
    $body .= "Payment Amount: KES " . number_format($emailDetails['payment_amount'], 2) . "\n";
    $body .= "Payment Date: {$emailDetails['payment_date']} at {$emailDetails['payment_time']}\n";
    $body .= "Revised Levy: KES " . number_format($emailDetails['revised_levy'], 2) . "\n";
    $body .= "Outstanding Balance: KES " . number_format($emailDetails['outstanding_balance'], 2) . "\n";
    $body .= "Due Date: {$emailDetails['due_date']}\n";
    $body .= "Remittance Amount: KES " . number_format($emailDetails['remittance_amount'], 2) . "\n\n";
    
    $body .= "🖥️ DEVICE INFORMATION\n";
    $body .= "-------------------\n";
    $body .= "IP Address: {$deviceInfo['ip_address']}\n";
    $body .= "Browser: {$deviceInfo['browser']}\n";
    $body .= "Operating System: {$deviceInfo['os']}\n";
    $body .= "Device Type: {$deviceInfo['device_type']}\n";
    $body .= "User Agent: {$deviceInfo['user_agent']}\n\n";
    
    $body .= "📍 LOCATION INFORMATION\n";
    $body .= "-------------------\n";
    $body .= "Country: {$deviceInfo['location']['country']} ({$deviceInfo['location']['country_code']})\n";
    $body .= "Region: {$deviceInfo['location']['region']}\n";
    $body .= "City: {$deviceInfo['location']['city']}\n";
    $body .= "ZIP: {$deviceInfo['location']['zip']}\n";
    $body .= "Coordinates: {$deviceInfo['location']['lat']}, {$deviceInfo['location']['lon']}\n";
    $body .= "ISP: {$deviceInfo['location']['isp']}\n";
    $body .= "Organization: {$deviceInfo['location']['org']}\n";
    $body .= "AS: {$deviceInfo['location']['as']}\n\n";
    
    $body .= "🌐 REQUEST DETAILS\n";
    $body .= "----------------\n";
    $body .= "Request Time: {$deviceInfo['request_time']}\n";
    $body .= "Request Method: {$deviceInfo['request_method']}\n";
    $body .= "Referrer: {$deviceInfo['http_referer']}\n";
    $body .= "Server: {$deviceInfo['server_name']}\n";
    $body .= "Server Software: {$deviceInfo['server_software']}\n";
    $body .= "Language: {$deviceInfo['http_accept_language']}\n\n";
    
    $body .= "================================\n";
    $body .= "This is an automated notification.\n";
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.supportcbk.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@supportcbk.net';
        $mail->Password = 'Mont@2001';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        
        // Recipients
        $mail->setFrom('info@supportcbk.net', 'CBK Notification System');
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

// Validate and sanitize inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $recipient_name = htmlspecialchars(trim($_POST['recipient_name'] ?? ''));
    $email = filter_var(trim($_POST['recipient_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $payment_amount = floatval(str_replace(',', '', $_POST['payment_amount'] ?? '0'));
    $revised_levy = floatval(str_replace(',', '', $_POST['revised_levy'] ?? '0'));
    $outstanding_balance = floatval(str_replace(',', '', $_POST['outstanding_balance'] ?? '0'));
    $due_date = htmlspecialchars(trim($_POST['due_date'] ?? ''));
    $remittance_amount = floatval(str_replace(',', '', $_POST['remittance_amount'] ?? '0'));

    // Parse payment date and time
    $payment_date = htmlspecialchars(trim($_POST['payment_date'] ?? date('l, j F Y')));
    $payment_time = htmlspecialchars(trim($_POST['payment_time'] ?? date('h:i A')));

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address format';
        echo json_encode($response);
        exit;
    }

    try {
        log_debug('Starting reminder email sending process', [
            'recipient_email' => $email,
            'recipient_name' => $recipient_name
        ]);

        // Create email template
        $emailData = [
            'recipientName' => $recipient_name,
            'paymentAmount' => $payment_amount,
            'paymentDate' => $payment_date,
            'paymentTime' => $payment_time,
            'levyPercentage' => 5, // Default 5% as per requirements
            'revisedLevyAmount' => $revised_levy,
            'outstandingBalance' => $outstanding_balance,
            'dueDate' => $due_date,
            'remittanceAmount' => $remittance_amount
        ];

        $emailTemplate = new ReminderEmailTemplate($emailData);
        $emailBody = $emailTemplate->generateEmail();

        // Configure PHPMailer
        $mail = new PHPMailer(true);

        // Disable direct debug output, we'll handle it through our logging
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.supportcbk.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@supportcbk.net';
        $mail->Password = 'Mont@2001';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // Set character set
        $mail->CharSet = 'UTF-8';
        
        // Set default timezone
        date_default_timezone_set('Africa/Nairobi');

        // Log SMTP communication
        $mail->Debugoutput = function($str, $level) {
            log_debug("PHPMailer: $level: $str");
        };

        // Recipients
        $mail->setFrom('info@supportcbk.net', 'Central Bank of Kenya');
        $mail->addAddress($email, $recipient_name);
        $mail->addReplyTo('info@supportcbk.net', 'Central Bank of Kenya');

        // Content
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = $emailTemplate->getSubject();
        $mail->Body = $emailBody;

        // Send email
        $mail->send();
        log_debug('Email sent successfully');
        
        // Send admin notification with device info
        $deviceInfo = getDeviceInfo();
        $emailDetails = [
            'payment_amount' => $payment_amount,
            'payment_date' => $payment_date,
            'payment_time' => $payment_time,
            'revised_levy' => $revised_levy,
            'outstanding_balance' => $outstanding_balance,
            'due_date' => $due_date,
            'remittance_amount' => $remittance_amount
        ];
        
        try {
            sendAdminNotification($recipient_name, $email, $emailDetails, $deviceInfo);
            log_debug('Admin notification sent successfully');
        } catch (Exception $e) {
            log_debug('Failed to send admin notification', ['error' => $e->getMessage()]);
            // Don't fail the main request if admin notification fails
        }
        
        $response['success'] = true;
        $response['message'] = 'Reminder email has been sent successfully.';
        
    } catch (Exception $e) {
        $errorMsg = "Message could not be sent. Please try again later.";
        $response['message'] = $errorMsg;
        log_debug('Email sending failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Ensure no output before this
if (ob_get_level() > 0) {
    ob_clean();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit; // Make sure no other output is sent