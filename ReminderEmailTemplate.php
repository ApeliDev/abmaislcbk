<?php

class ReminderEmailTemplate {
    private $recipientName = '';
    private $paymentAmount = 0;
    private $paymentDate = '';
    private $paymentTime = '';
    private $levyType = 'Customs/Export-Import Levy';
    private $levyPercentage = 5;
    private $revisedLevyAmount = 0;
    private $outstandingBalance = 0;
    private $dueDate = '';
    private $remittanceAmount = 0;
    private $senderName = 'Central Bank of Kenya';
    private $senderTitle = 'Banking and Payment Services';
    private $referenceNumber = '';
    private $subject = 'Urgent: Outstanding Customs Levy Balance - Action Required';

    public function generateEmail() {
        try {
            // Load the HTML template
            $templatePath = __DIR__ . '/reminder_email_template.html';
            if (!file_exists($templatePath)) {
                throw new Exception("Email template not found at: " . $templatePath);
            }
            
            $template = file_get_contents($templatePath);
            if ($template === false) {
                throw new Exception("Failed to read email template");
            }
            
            // Format currency values with thousands separators, handle null/empty values
            $paymentAmount = $this->formatCurrency($this->paymentAmount);
            $revisedLevyAmount = $this->formatCurrency($this->revisedLevyAmount);
            $outstandingBalance = $this->formatCurrency($this->outstandingBalance);
            $remittanceAmount = $this->formatCurrency($this->remittanceAmount);
            
            // Get current date for the email
            $currentDate = date('F j, Y');
            
            // Set default reference if not provided
            $referenceNumber = !empty($this->referenceNumber) ? $this->referenceNumber : 'REF-' . time();
            
            // Replace placeholders with actual values
            $replacements = [
                '{CURRENT_DATE}' => $currentDate,
                '{REFERENCE_NUMBER}' => $referenceNumber,
                '{RECIPIENT_NAME}' => $this->recipientName,
                '{PAYMENT_AMOUNT}' => $paymentAmount,
                '{PAYMENT_DATE}' => $this->paymentDate,
                '{PAYMENT_TIME}' => $this->paymentTime,
                '{LEVY_TYPE}' => $this->levyType,
                '{LEVY_PERCENTAGE}' => $this->levyPercentage,
                '{LEVY_AMOUNT}' => $revisedLevyAmount,
                '{OUTSTANDING_BALANCE}' => $outstandingBalance,
                '{DUE_DATE}' => $this->dueDate,
                '{REMITTANCE_AMOUNT}' => $remittanceAmount,
                '{SENDER_NAME}' => $this->senderName,
                '{SENDER_TITLE}' => $this->senderTitle
            ];
            
            // Apply all replacements
            $emailContent = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $template
            );
            
            return [
                'subject' => $this->subject,
                'body' => $emailContent,
                'headers' => $this->getEmailHeaders()
            ];
            
        } catch (Exception $e) {
            error_log('Error generating email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function formatCurrency($amount) {
        if ($amount === null || $amount === '') {
            return '0';
        }
        return number_format((float)$amount, 0, '.', ',');
    }
    
    private function getEmailHeaders() {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->senderName . ' <noreply@centralbank.go.ke>',
            'Reply-To: customerservice@centralbank.go.ke',
            'X-Mailer: PHP/' . phpversion(),
            'X-Priority: 1 (Highest)',
            'X-MSMail-Priority: High',
            'Importance: High'
        ];
        
        return implode("\r\n", $headers);
    }

    // Getters and Setters with chaining
    public function setRecipientName($name) {
        $this->recipientName = (string)$name;
        return $this;
    }

    public function setPaymentAmount($amount) {
        $this->paymentAmount = $amount !== null ? (float)$amount : 0;
        return $this;
    }
    
    public function setPaymentDate($date) {
        $this->paymentDate = (string)$date;
        return $this;
    }
    
    public function setPaymentTime($time) {
        $this->paymentTime = (string)$time;
        return $this;
    }
    
    public function setLevyType($type) {
        $this->levyType = (string)$type;
        return $this;
    }
    
    public function setLevyPercentage($percentage) {
        $this->levyPercentage = $percentage !== null ? (float)$percentage : 5;
        return $this;
    }
    
    public function setRevisedLevyAmount($amount) {
        $this->revisedLevyAmount = $amount !== null ? (float)$amount : 0;
        return $this;
    }
    
    public function setOutstandingBalance($balance) {
        $this->outstandingBalance = $balance !== null ? (float)$balance : 0;
        return $this;
    }
    
    public function setDueDate($date) {
        $this->dueDate = (string)$date;
        return $this;
    }
    
    public function setRemittanceAmount($amount) {
        $this->remittanceAmount = $amount !== null ? (float)$amount : 0;
        return $this;
    }
    
    public function setSenderName($name) {
        $this->senderName = (string)$name;
        return $this;
    }
    
    public function setSenderTitle($title) {
        $this->senderTitle = (string)$title;
        return $this;
    }
    
    public function setReferenceNumber($ref) {
        $this->referenceNumber = (string)$ref;
        return $this;
    }
    
    public function setSubject($subject) {
        $this->subject = (string)$subject;
        return $this;
    }

    public function getReferenceNumber() {
        return $this->referenceNumber;
    }
}
?>
