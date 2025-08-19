<?php

class EmailTemplate {
    private $recipientName;
    private $payments = [];
    private $totalAmount;
    private $levyAmount;
    private $levyPercentage;
    private $transactionValue;
    private $remittanceAmount;
    private $senderName;
    private $senderTitle;
    private $subject;
    private $date;

    public function __construct($data = []) {
        $this->recipientName = $data['recipientName'] ?? '';
        $this->payments = $data['payments'] ?? [];
        $this->totalAmount = $data['totalAmount'] ?? 0;
        $this->levyAmount = $data['levyAmount'] ?? 0;
        $this->levyPercentage = $data['levyPercentage'] ?? 10; // Default 10%
        $this->transactionValue = $data['transactionValue'] ?? 0;
        $this->remittanceAmount = $data['remittanceAmount'] ?? 0;
        $this->senderName = $data['senderName'] ?? 'Mr. Michael Eganza';
        $this->senderTitle = $data['senderTitle'] ?? 'Director – Banking and Payment Services';
        $this->subject = $data['subject'] ?? 'CONFIRMATION OF REMITTANCE VERIFICATION FEE PAYMENT AND OUTSTANDING CUSTOMS LEVY';
        $this->date = $data['date'] ?? date('l, j F Y');
    }

    public function generateEmail() {
        $email = "RE: {$this->subject}\n\n";
        $email .= "Dear {$this->recipientName},\n\n";
        $email .= "This is to formally confirm receipt of the remittance verification fee payments as follows:\n\n";

        // Add payment details
        foreach ($this->payments as $index => $payment) {
            $email .= ($index + 1) . ". KES " . number_format($payment['amount'], 0, '.', ',') . 
                     " received on " . $payment['date'] . " at " . $payment['time'] . ".\n\n";
        }

        $email .= "These payments complete the total verification fee of KES " . 
                 number_format($this->totalAmount, 0, '.', ',') . " as stipulated in our previous correspondence.\n\n";

        $email .= "In accordance with statutory clearance requirements, a Customs/Export–Import Levy " .
                 "amounting to KES " . number_format($this->levyAmount, 0, '.', ',') . 
                 " (representing {$this->levyPercentage}% of the declared gold export transaction value) " .
                 "is now payable to cover mandatory gold export clearance, customs duty, and import assessment fees.\n\n";

        $email .= "Kindly arrange to settle this levy through your appointed agent within one (1) to seven (7) working days from the date of this notice. " .
                 "Processing and release of the revised remittance of KES " . 
                 number_format($this->remittanceAmount, 0, '.', ',') . 
                 " will proceed only upon confirmation of this payment.\n\n";

        $email .= "We thank you for your prompt attention and cooperation.\n\n";
        $email .= "Yours faithfully,\n";
        $email .= "{$this->senderName}\n";
        $email .= "{$this->senderTitle}\n";
        $email .= "Central Bank of Kenya";

        return $email;
    }

    // Getters and Setters for all properties
    public function setRecipientName($name) {
        $this->recipientName = $name;
        return $this;
    }

    public function addPayment($amount, $date, $time) {
        $this->payments[] = [
            'amount' => $amount,
            'date' => $date,
            'time' => $time
        ];
        return $this;
    }

    public function setTotalAmount($amount) {
        $this->totalAmount = $amount;
        return $this;
    }

    public function setLevyAmount($amount) {
        $this->levyAmount = $amount;
        return $this;
    }

    public function setLevyPercentage($percentage) {
        $this->levyPercentage = $percentage;
        return $this;
    }

    public function setTransactionValue($value) {
        $this->transactionValue = $value;
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

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function setDate($date) {
        $this->date = $date;
        return $this;
    }
}
