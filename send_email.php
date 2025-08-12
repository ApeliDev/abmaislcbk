<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Validate and sanitize inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = filter_var($_POST['recipient_name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['recipient_email'], FILTER_SANITIZE_EMAIL);


    $paid_amount = filter_var($_POST['paid_amount'], FILTER_SANITIZE_STRING);
    $paid_amount_numeric = filter_var($_POST['paid_amount_numeric'], FILTER_SANITIZE_STRING);
    $outstanding_amount = filter_var($_POST['outstanding_amount'], FILTER_SANITIZE_STRING);
    $due_date = filter_var($_POST['due_date'], FILTER_SANITIZE_STRING);

    // Validate email
     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address format';
        echo json_encode($response);
        exit;
    }

    try {
        // Load email template from file
        $template = file_get_contents('email_template.html');

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

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.supportcbk.net'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'info@supportcbk.net'; 
        $mail->Password = 'Mont@2001'; 
        $mail->SMTPSecure = 'ssl'; 
        $mail->Port = 465; 

        // Recipients
        $mail->setFrom('info@supportcbk.net', 'Central Bank of Kenya');
        $mail->addAddress($email, $recipient_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'RE: MANDATORY SETTLEMENT OF OUTSTANDING BALANCE - ' . $recipient_name;
        $mail->Body = $template;
        // Plain text version removed as requested

        $mail->send();
        $response['success'] = true;
        $response['message'] = 'Settlement notification email has been sent successfully';
    } catch (Exception $e) {
        $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
?>