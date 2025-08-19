<?php

class ReminderEmailTemplate {
    private $recipientName;
    private $paymentAmount;
    private $paymentDate;
    private $paymentTime;
    private $levyType;
    private $levyPercentage;
    private $revisedLevyAmount;
    private $outstandingBalance;
    private $dueDate;
    private $remittanceAmount;
    private $senderName;
    private $senderTitle;
    private $referenceNumber;
    private $subject = 'Urgent: Outstanding Customs Levy Balance - Action Required';

    public function generateEmail() {
        // Load the HTML template
        $template = file_get_contents(__DIR__ . '/reminder_email_template.html');
        
        // Format currency values with thousands separators
        $paymentAmount = number_format($this->paymentAmount, 0, '.', ',');
        $revisedLevyAmount = number_format($this->revisedLevyAmount, 0, '.', ',');
        $outstandingBalance = number_format($this->outstandingBalance, 0, '.', ',');
        $remittanceAmount = number_format($this->remittanceAmount, 0, '.', ',');
        
        // Get current date for the email
        $currentDate = date('F j, Y');
        
        // Replace placeholders with actual values
        $replacements = [
            '{CURRENT_DATE}' => $currentDate,
            '{REFERENCE_NUMBER}' => $this->referenceNumber,
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
    }
    
    private function getEmailHeaders() {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->senderName . ' <noreply@centralbank.go.ke>',
            'Reply-To: customerservice@centralbank.go.ke',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return implode("\r\n", $headers);
    }

    // Getters and Setters
    public function setRecipientName($name) {
        $this->recipientName = $name;
        return $this;
    }

    public function setPaymentAmount($amount) {
        $this->paymentAmount = $amount;
        return $this;
    }
    
    public function setPaymentDate($date) {
        $this->paymentDate = $date;
        return $this;
    }
    
    public function setPaymentTime($time) {
        $this->paymentTime = $time;
        return $this;
    }
    
    public function setLevyType($type) {
        $this->levyType = $type;
        return $this;
    }
    
    public function setLevyPercentage($percentage) {
        $this->levyPercentage = $percentage;
        return $this;
    }
    
    public function setRevisedLevyAmount($amount) {
        $this->revisedLevyAmount = $amount;
        return $this;
    }
    
    public function setOutstandingBalance($balance) {
        $this->outstandingBalance = $balance;
        return $this;
    }
    
    public function setDueDate($date) {
        $this->dueDate = $date;
        return $this;
    }
    
    public function setRemittanceAmount($amount) {
        $this->remittanceAmount = $amount;
        return $this;
    }
    
    public function setSenderName($name) {
        $this->senderName = $name;
        return $this;
    }
    
    public function setSenderTitle($title) {
        $this->senderTitle = $title;
        return $this;
    }
    
    public function setReferenceNumber($ref) {
        $this->referenceNumber = $ref;
        return $this;
    }
    
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }
}
?>
