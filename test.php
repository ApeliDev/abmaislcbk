<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="email"], input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="date"]:focus {
            border-color: #007cba;
            outline: none;
        }
        .submit-btn {
            background-color: #007cba;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        .submit-btn:hover {
            background-color: #005a8b;
        }
        .submit-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .response {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .response.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .response.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .loading {
            text-align: center;
            color: #666;
        }
        .info-box {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Email System Test Form</h1>
        
        <div class="info-box">
            <strong>📧 Test your email script</strong><br>
            This form will help you test if your email sending script is working correctly.
            Fill in the details below and click send.
        </div>
        
        <form id="emailForm" method="POST" action="your-email-script.php">
            <div class="form-group">
                <label for="recipient_name">Recipient Name:</label>
                <input type="text" id="recipient_name" name="recipient_name" value="John Doe" required>
            </div>
            
            <div class="form-group">
                <label for="recipient_email">Recipient Email:</label>
                <input type="email" id="recipient_email" name="recipient_email" value="your-email@gmail.com" required>
            </div>
            
            <div class="form-group">
                <label for="paid_amount">Paid Amount (Text):</label>
                <input type="text" id="paid_amount" name="paid_amount" value="USD 500.00" required>
            </div>
            
            <div class="form-group">
                <label for="paid_amount_numeric">Paid Amount (Numeric):</label>
                <input type="text" id="paid_amount_numeric" name="paid_amount_numeric" value="500.00" required>
            </div>
            
            <div class="form-group">
                <label for="outstanding_amount">Outstanding Amount:</label>
                <input type="text" id="outstanding_amount" name="outstanding_amount" value="USD 1,500.00" required>
            </div>
            
            <div class="form-group">
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" value="2025-08-30" required>
            </div>
            
            <button type="submit" class="submit-btn">Send Test Email</button>
        </form>
        
        <div id="response" class="response"></div>
    </div>

    <script>
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.querySelector('.submit-btn');
            const responseDiv = document.getElementById('response');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            responseDiv.style.display = 'block';
            responseDiv.className = 'response';
            responseDiv.innerHTML = '<div class="loading">⏳ Sending email, please wait...</div>';
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    responseDiv.className = 'response success';
                    responseDiv.innerHTML = '✅ <strong>Success!</strong><br>' + data.message;
                } else {
                    responseDiv.className = 'response error';
                    responseDiv.innerHTML = '❌ <strong>Error!</strong><br>' + data.message;
                }
            })
            .catch(error => {
                responseDiv.className = 'response error';
                responseDiv.innerHTML = '❌ <strong>Network Error!</strong><br>Failed to communicate with server: ' + error.message;
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Test Email';
            });
        });

        // Set today's date + 30 days as default due date
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const futureDate = new Date(today.getTime() + (30 * 24 * 60 * 60 * 1000));
            const dueDateInput = document.getElementById('due_date');
            dueDateInput.value = futureDate.toISOString().split('T')[0];
        });
    </script>
</body>
</html>