<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Settlement Notification System">
    <title>Settlement Notification System | Central Bank</title>
    
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #004494;
            --success-color: #28a745;
            --error-color: #dc3545;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        
        .header img {
            height: 60px;
            margin-bottom: 1rem;
        }
        
        h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
        
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        }
        
        .btn {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }
        
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-top: 1rem;
            text-align: center;
            display: none;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="https://www.centralbank.go.ke/wp-content/uploads/2016/09/NewLogoCBK.png" alt="Central Bank Logo">
            <h1>Settlement Notification</h1>
            <p class="subtitle">Send mandatory settlement notices to customers</p>
        </div>
        
        <form id="settlementForm">
            <div class="form-group">
                <label for="recipient_name">Recipient Full Name</label>
                <input 
                    type="text" 
                    id="recipient_name" 
                    name="recipient_name" 
                    required
                    placeholder="Enter recipient's full name"
                >
            </div>
            
            <div class="form-group">
                <label for="recipient_email">Recipient Email</label>
                <input 
                    type="email" 
                    id="recipient_email" 
                    name="recipient_email" 
                    required
                    placeholder="Enter recipient's email address"
                >
            </div>
            
            <button type="submit" class="btn" id="submitBtn">
                <span id="btnText">Send Settlement Notice</span>
                <span id="btnSpinner" class="spinner" style="display: none;"></span>
            </button>
            
            <div id="alertBox" class="alert" role="alert"></div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('settlementForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const alertBox = document.getElementById('alertBox');
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Validate form
                if (!form.checkValidity()) {
                    showAlert('Please fill in all required fields correctly', 'error');
                    return;
                }
                
                // Prepare data
                const formData = new FormData(form);
                
                // Show loading state
                submitBtn.disabled = true;
                btnText.textContent = 'Sending...';
                btnSpinner.style.display = 'inline-block';
                
                try {
                    // Send request
                    const response = await fetch('send_email.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showAlert('Settlement notice sent successfully', 'success');
                        form.reset();
                    } else {
                        showAlert(result.message || 'Failed to send settlement notice', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('An error occurred while sending the notice', 'error');
                } finally {
                    // Reset button state
                    submitBtn.disabled = false;
                    btnText.textContent = 'Send Settlement Notice';
                    btnSpinner.style.display = 'none';
                }
            });
            
            function showAlert(message, type) {
                alertBox.textContent = message;
                alertBox.className = `alert alert-${type}`;
                alertBox.style.display = 'block';
                
                // Hide alert after 5 seconds
                setTimeout(() => {
                    alertBox.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>