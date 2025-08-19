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
    private $subject;

    public function __construct($data = []) {
        $this->recipientName = $data['recipientName'] ?? '';
        $this->paymentAmount = $data['paymentAmount'] ?? 0;
        $this->paymentDate = $data['paymentDate'] ?? date('l, j F Y');
        $this->paymentTime = $data['paymentTime'] ?? date('h:i A');
        $this->levyType = $data['levyType'] ?? 'Customs/Export–Import Levy';
        $this->levyPercentage = $data['levyPercentage'] ?? 5; // Default 5%
        $this->revisedLevyAmount = $data['revisedLevyAmount'] ?? 0;
        $this->outstandingBalance = $data['outstandingBalance'] ?? 0;
        $this->dueDate = $data['dueDate'] ?? date('l, j F Y', strtotime('+2 days'));
        $this->remittanceAmount = $data['remittanceAmount'] ?? 0;
        $this->senderName = $data['senderName'] ?? 'Mr. Michael Eganza';
        $this->senderTitle = $data['senderTitle'] ?? 'Director – Banking and Payment Services';
        $this->subject = $data['subject'] ?? 'Urgent Reminder: Outstanding Customs Levy Balance – Action Required';
    }

    public function generateEmail() {
        $email = "Subject: {$this->subject}\n\n";
        $email .= "Dear {$this->recipientName},\n\n";
        $email .= "I hope you are well.\n\n";
        $email .= "This is to confirm receipt of KES " . number_format($this->paymentAmount, 0, '.', ',') . ", " . 
                 "paid on {$this->paymentDate} at {$this->paymentTime}, " . 
                 "toward the {$this->levyType}.\n\n";
        
        $email .= "Please be advised that the duty has been officially revised to {$this->levyPercentage}% and will be " .
                 "withheld and deducted at the time of final transaction settlement. " .
                 "The revised levy stands at KES " . number_format($this->revisedLevyAmount, 0, '.', ',') . ", " .
                 "leaving a balance of KES " . number_format($this->outstandingBalance, 0, '.', ',') . ".\n\n";
        
        $email .= "Kindly ensure the outstanding amount is settled within two (2) days from today, inclusive – by {$this->dueDate}.\n\n";
        
        $email .= "Once the full payment is confirmed, the remittance of KES " . 
                 number_format($this->remittanceAmount, 0, '.', ',') . 
                 " will be processed and reflected in your account promptly thereafter.\n\n";
        
        $email .= "Yours faithfully,\n";
        $email .= "{$this->senderName}\n";
        $email .= "{$this->senderTitle}\n";
        $email .= "Central Bank of Kenya";

        return $email;
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

    public function setPaymentDateTime($date, $time) {
        $this->paymentDate = $date;
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

    public function setOutstandingBalance($amount) {
        $this->outstandingBalance = $amount;
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

    public function setSenderInfo($name, $title) {
        $this->senderName = $name;
        $this->senderTitle = $title;
        return $this;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }
}
?>
