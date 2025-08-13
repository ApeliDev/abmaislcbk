<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'EmailTemplate.php';

// Function to log errors to a file
function logError($message) {
    $logFile = __DIR__ . '/email_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = isset($backtrace[1]) ? $backtrace[1]['function'] . '()' : 'main';
    $logMessage = "[$timestamp] [$caller] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    if (empty($_POST['recipient_name']) || empty($_POST['recipient_email'])) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Create email content
            $emailTemplate = new EmailTemplate([
                'recipientName' => htmlspecialchars($_POST['recipient_name']),
                'payments' => [
                    [
                        'amount' => 437530,
                        'date' => 'Monday, 11 August 2025',
                        'time' => '4:00 PM'
                    ],
                    [
                        'amount' => 100000,
                        'date' => 'Tuesday, 12 August 2025',
                        'time' => '4:30 PM'
                    ]
                ],
                'totalAmount' => 537530,
                'levyAmount' => 1854000,
                'levyPercentage' => 10,
                'transactionValue' => 18540000,
                'remittanceAmount' => 18540000,
                'date' => date('l, j F Y')
            ]);

            $emailContent = $emailTemplate->generateEmail();
            $to = filter_var($_POST['recipient_email'], FILTER_SANITIZE_EMAIL);
            $subject = 'CONFIRMATION OF REMITTANCE VERIFICATION FEE PAYMENT AND OUTSTANDING CUSTOMS LEVY';

            // Create PHPMailer instance
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->SMTPDebug = SMTP::DEBUG_OFF;                      // Enable verbose debug output
                $mail->isSMTP();                                          // Send using SMTP
                $mail->Host       = 'mail.supportcbk.net';                     // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                 // Enable SMTP authentication
                $mail->Username   = 'info@supportcbk.net';               // SMTP username
                $mail->Password   = 'Mont@2001';            
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      
                $mail->Port       = 465;                                  // TCP port to connect to
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                // Recipients
                $mail->setFrom('no-reply@centralbank.go.ke', 'Central Bank of Kenya');
                $mail->addAddress($to, htmlspecialchars($_POST['recipient_name']));
                $mail->addReplyTo('no-reply@centralbank.go.ke', 'No Reply');

                // Content
                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body = $emailContent;

                $mail->send();
                $message = 'Email sent successfully to ' . htmlspecialchars($_POST['recipient_email']);
                logEmailAttempt($to, $subject, true);
            } catch (Exception $e) {
                $errorMsg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $error = 'Failed to send email. Please try again.';
                logError("❌ ERROR: " . $errorMsg);
                logEmailAttempt($to, $subject, false, $errorMsg);
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Remittance Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-title {
            color: #0d6efd;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .btn-send {
            background-color: #0d6efd;
            border: none;
            padding: 10px 25px;
            font-weight: 500;
        }
        .btn-send:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="form-title">Send Remittance Confirmation</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="send_email_form.php">
            <div class="mb-3">
                <label for="recipient_name" class="form-label">Recipient Name</label>
                <input type="text" class="form-control" id="recipient_name" name="recipient_name" required 
                       value="<?php echo isset($_POST['recipient_name']) ? htmlspecialchars($_POST['recipient_name']) : ''; ?>">
            </div>
            <div class="mb-4">
                <label for="recipient_email" class="form-label">Recipient Email</label>
                <input type="email" class="form-control" id="recipient_email" name="recipient_email" required
                       value="<?php echo isset($_POST['recipient_email']) ? htmlspecialchars($_POST['recipient_email']) : ''; ?>">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-send">Send Confirmation Email</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
