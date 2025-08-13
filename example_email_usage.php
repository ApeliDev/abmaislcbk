<?php
require_once 'EmailTemplate.php';

// Example 1: Using the constructor with all data
$emailData = [
    'recipientName' => 'Ms. Gatu',
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
    'transactionValue' => 18540000, // 10% of this is the levyAmount
    'remittanceAmount' => 18540000,
    'date' => date('l, j F Y') // Current date in the same format
];

$email = new EmailTemplate($emailData);
$emailContent = $email->generateEmail();

// Output the email
header('Content-Type: text/plain');
echo $emailContent;

// Example 2: Using the fluent interface
/*
$email = (new EmailTemplate())
    ->setRecipientName('Ms. Gatu')
    ->addPayment(437530, 'Monday, 11 August 2025', '4:00 PM')
    ->addPayment(100000, 'Tuesday, 12 August 2025', '4:30 PM')
    ->setTotalAmount(537530)
    ->setLevyAmount(1854000)
    ->setLevyPercentage(10)
    ->setTransactionValue(18540000)
    ->setRemittanceAmount(18540000)
    ->setDate(date('l, j F Y'));

$emailContent = $email->generateEmail();
*/

// To send the email using PHP's mail() function:
/*
$to = 'recipient@example.com';
$subject = 'CONFIRMATION OF REMITTANCE VERIFICATION FEE PAYMENT AND OUTSTANDING CUSTOMS LEVY';
$headers = 'From: sender@centralbank.go.ke' . "\r\n" .
           'Reply-To: no-reply@centralbank.go.ke' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $emailContent, $headers);
*/
?>
