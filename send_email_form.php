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
            // Prepare payment data
            $payments = [
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
            ];
            $totalAmount = 537530;
            $levyAmount = 1854000;
            $levyPercentage = 10;
            $remittanceAmount = 18540000;
            $currentDate = date('l, j F Y');

            // Generate payment rows for the template
            $paymentRows = '';
            foreach ($payments as $index => $payment) {
                $paymentRows .= "<tr>";
                $paymentRows .= "<td>" . ($index + 1) . "</td>";
                $paymentRows .= "<td>" . number_format($payment['amount'], 2) . "</td>";
                $paymentRows .= "<td>" . $payment['date'] . "</td>";
                $paymentRows .= "<td>" . $payment['time'] . "</td>";
                $paymentRows .= "</tr>";
            }

            // Load HTML template
            $template = file_get_contents(__DIR__ . '/remittance_email_template.html');
            if ($template === false) {
                throw new Exception('Could not load email template');
            }

            // Replace placeholders with actual data
            $replacements = [
                '{RECIPIENT_NAME}' => htmlspecialchars($_POST['recipient_name']),
                '{PAYMENT_ROWS}' => $paymentRows,
                '{TOTAL_AMOUNT}' => number_format($totalAmount, 2),
                '{LEVY_AMOUNT}' => number_format($levyAmount, 2),
                '{LEVY_PERCENTAGE}' => $levyPercentage,
                '{REMITTANCE_AMOUNT}' => number_format($remittanceAmount, 2),
                '{SENDER_NAME}' => 'Mr. Michael Eganza',
                '{SENDER_TITLE}' => 'Director – Banking and Payment Services',
                '{CURRENT_DATE}' => $currentDate
            ];

            $emailContent = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $template
            );

            $to = filter_var($_POST['recipient_email'], FILTER_SANITIZE_EMAIL);
            $subject = 'CONFIRMATION OF REMITTANCE VERIFICATION FEE PAYMENT AND OUTSTANDING CUSTOMS LEVY';

            // Create PHPMailer instance
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->SMTPDebug = SMTP::DEBUG_OFF;
                $mail->isSMTP();
                $mail->Host       = 'mail.supportcbk.net';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'info@supportcbk.net';
                $mail->Password   = 'Mont@2001';            
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      
                $mail->Port       = 465;
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                // Recipients
                $mail->setFrom('info@supportcbk.net', 'Central Bank of Kenya');
                $mail->addAddress($to, htmlspecialchars($_POST['recipient_name']));
                $mail->addReplyTo('info@supportcbk.net', 'Central Bank of Kenya');

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $emailContent;
                $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailContent));

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
    <meta name="description" content="Send Remittance Confirmation Email">
    <title>Send Remittance Confirmation | Central Bank</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bank-primary': '#0056b3',
                        'bank-secondary': '#004494',
                        'bank-success': '#10b981',
                        'bank-error': '#ef4444',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 font-sans">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl">
            <!-- Header -->
            <div class="bg-gradient-to-r from-bank-primary to-bank-secondary rounded-t-xl p-6 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img src="https://www.centralbank.go.ke/wp-content/uploads/2016/09/NewLogoCBK.png" 
                             alt="Central Bank Logo" 
                             class="h-12 w-auto bg-white rounded-lg p-2">
                        <div>
                            <h1 class="text-2xl font-bold">Remittance Confirmation</h1>
                            <p class="text-blue-100 text-sm">Send remittance verification confirmations</p>
                        </div>
                    </div>
                    <nav>
                        <a href="send_email.php" 
                           class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm border border-white/20">
                            Settlement Notification
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Form Container -->
            <div class="p-8">
                <!-- Success/Error Messages -->
                <?php if ($message): ?>
                    <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-green-700 font-medium"><?php echo $message; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-red-700 font-medium"><?php echo $error; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="send_email_form.php" id="remittanceForm" class="space-y-8">
                    <!-- Recipient Information Section -->
                    <div class="space-y-6">
                        <div class="border-l-4 border-bank-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">Recipient Information</h3>
                            <p class="text-sm text-gray-600">Enter the recipient's details for remittance confirmation</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="recipient_name" class="block text-sm font-medium text-gray-700">
                                    Recipient Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="recipient_name" 
                                    name="recipient_name" 
                                    required
                                    value="<?php echo isset($_POST['recipient_name']) ? htmlspecialchars($_POST['recipient_name']) : ''; ?>"
                                    placeholder="Enter recipient's full name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bank-primary focus:border-bank-primary transition-colors duration-200 placeholder-gray-400"
                                >
                            </div>
                            
                            <div class="space-y-2">
                                <label for="recipient_email" class="block text-sm font-medium text-gray-700">
                                    Recipient Email <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="recipient_email" 
                                    name="recipient_email" 
                                    required
                                    value="<?php echo isset($_POST['recipient_email']) ? htmlspecialchars($_POST['recipient_email']) : ''; ?>"
                                    placeholder="Enter recipient's email address"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bank-primary focus:border-bank-primary transition-colors duration-200 placeholder-gray-400"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Email Preview Section -->
                    <div class="space-y-6">
                        <div class="border-l-4 border-orange-500 pl-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">Email Template Preview</h3>
                            <p class="text-sm text-gray-600">This email contains remittance verification details and payment confirmations</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-800">Email Subject:</h4>
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Automated</span>
                                </div>
                                <p class="text-sm text-gray-700 bg-white p-3 rounded border-l-4 border-blue-400">
                                    CONFIRMATION OF REMITTANCE VERIFICATION FEE PAYMENT AND OUTSTANDING CUSTOMS LEVY
                                </p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div class="space-y-2">
                                        <h5 class="font-medium text-gray-700">Payment Details:</h5>
                                        <ul class="text-gray-600 space-y-1">
                                            <li>• Payment 1: KES 437,530.00</li>
                                            <li>• Payment 2: KES 100,000.00</li>
                                            <li>• <strong>Total: KES 537,530.00</strong></li>
                                        </ul>
                                    </div>
                                    <div class="space-y-2">
                                        <h5 class="font-medium text-gray-700">Levy Information:</h5>
                                        <ul class="text-gray-600 space-y-1">
                                            <li>• Levy Amount: KES 1,854,000.00</li>
                                            <li>• Levy Rate: 10%</li>
                                            <li>• <strong>Remittance: KES 18,540,000.00</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit" class="w-full bg-gradient-to-r from-bank-primary to-bank-secondary hover:from-bank-secondary hover:to-bank-primary text-white font-semibold py-4 px-6 rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none" id="submitBtn">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send Confirmation Email
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-6">
                <svg class="animate-spin h-8 w-8 text-bank-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Sending Email...</h3>
            <p class="text-gray-600">Please wait while we process your request.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('remittanceForm');
            const submitBtn = document.getElementById('submitBtn');
            const loadingModal = document.getElementById('loadingModal');

            form.addEventListener('submit', function(e) {
                // Show loading modal
                loadingModal.classList.remove('hidden');
                submitBtn.disabled = true;
                document.body.style.overflow = 'hidden';
            });

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>