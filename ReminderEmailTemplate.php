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
            $template = file_get_contents(__DIR__ . '/reminder_email_template.html');
            if ($template === false) {
                throw new Exception('Unable to load email template');
            }

            // Format values for display
            $paymentAmount = $this->formatNumber($this->paymentAmount);
            $revisedLevyAmount = $this->formatNumber($this->revisedLevyAmount);
            $outstandingBalance = $this->formatNumber($this->outstandingBalance);
            $remittanceAmount = $this->formatNumber($this->remittanceAmount);
            
            // Format the payment date and time from the stored values
            $paymentDateTime = '';
            if (!empty($this->paymentDate) && !empty($this->paymentTime)) {
                $paymentDateTime = date('l, j F Y \a\t h:i A', strtotime($this->paymentDate . ' ' . $this->paymentTime));
            }
            
            // Format the due date
            $dueDate = !empty($this->dueDate) ? date('l, j F Y', strtotime($this->dueDate)) : '';
            
            // Replace placeholders with actual values
            $replacements = [
                '{RECIPIENT_NAME}' => $this->recipientName,
                '{PAYMENT_AMOUNT}' => $paymentAmount,
                '{PAYMENT_DATETIME}' => $paymentDateTime,
                '{LEVY_TYPE}' => $this->levyType,
                '{LEVY_PERCENTAGE}' => $this->levyPercentage,
                '{LEVY_AMOUNT}' => $revisedLevyAmount,
                '{OUTSTANDING_BALANCE}' => $outstandingBalance,
                '{DUE_DATE}' => $dueDate,
                '{REMITTANCE_AMOUNT}' => $remittanceAmount,
                '{SENDER_NAME}' => $this->senderName,
                '{SENDER_TITLE}' => $this->senderTitle,
                '{CURRENT_DATE}' => date('F j, Y'),
                '{REFERENCE_NUMBER}' => $this->referenceNumber
            ];
            
            // Apply all replacements
            $emailContent = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $template
            );
            
            return [
                'subject' => $this->subject,
                'body' => $emailContent
            ];
            
        } catch (Exception $e) {
            error_log('Error generating email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function formatNumber($number, $decimals = 2) {
        if ($number === null || $number === '') {
            return '0.00';
        }
        return number_format((float)$number, $decimals);
    }
    
    private function formatCurrency($amount, $currency = 'KSh') {
        if ($amount === null || $amount === '') {
            return $currency . ' 0.00';
        }
        return $currency . ' ' . number_format((float)$amount, 2);
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

    public function getSubject() {
        return $this->subject;
    }
}
?>
