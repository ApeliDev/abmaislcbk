

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Settlement Notification System">
    <title>Settlement Notification System | Central Bank</title>
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
                            <h1 class="text-2xl font-bold">Settlement Notification</h1>
                            <p class="text-blue-100 text-sm">Send mandatory settlement notices to customers</p>
                        </div>
                    </div>
                    <nav class="flex flex-col sm:flex-row gap-2">
                        <a href="send_email_form.php" 
                           class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm border border-white/20 text-center">
                            Send Remittance Confirmation
                        </a>

                        <a href="reminder_form.html" 
                           class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm border border-white/20 text-center">
                            Send Reminder Email
                        </a> 
                    </nav>
                </div>
            </div>

            <!-- Form Container -->
            <div class="p-8">
                <form id="settlementForm" class="space-y-8">
                    <!-- Recipient Information Section -->
                    <div class="space-y-6">
                        <div class="border-l-4 border-bank-primary pl-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">Recipient Information</h3>
                            <p class="text-sm text-gray-600">Enter the recipient's details</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="recipient_name" class="block text-sm font-medium text-gray-700">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="recipient_name" 
                                    name="recipient_name" 
                                    required
                                    placeholder="Enter recipient's full name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bank-primary focus:border-bank-primary transition-colors duration-200 placeholder-gray-400"
                                >
                            </div>
                            
                            <div class="space-y-2">
                                <label for="recipient_email" class="block text-sm font-medium text-gray-700">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="recipient_email" 
                                    name="recipient_email" 
                                    required
                                    placeholder="Enter recipient's email address"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bank-primary focus:border-bank-primary transition-colors duration-200 placeholder-gray-400"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information Section -->
                    <div class="space-y-6">
                        <div class="border-l-4 border-green-500 pl-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">Payment Information</h3>
                            <p class="text-sm text-gray-600">Enter payment and settlement details</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="paid_amount" class="block text-sm font-medium text-gray-700">
                                    Paid Amount (in words) <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="paid_amount" 
                                    name="paid_amount" 
                                    required
                                    placeholder="e.g. Five Thousand Only"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bank-primary focus:border-bank-primary transition-colors duration-200 placeholder-gray-400"
                                >
                            </div>
                            
                            <div class="space-y-2">
                                <label for="paid_amount_numeric" class="block text-sm font-medium text-gray-700">
                                    Paid Amount (numeric) <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="paid_amount_numeric" 
                                    name="paid_amount_numeric" 
                                    required
                                    placeholder="e.g. 5,000.00"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bank-primary focus:border-bank-primary transition-colors duration-200 placeholder-gray-400"
                                >
                            </div>
                            
                            <div class="space-y-2">
                                <label for="outstanding_amount" class="block text-sm font-medium text-gray-700">
                                    Outstanding Amount <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="outstanding_amount" 
                                    name="outstanding_amount" 
                                    required
                                    placeholder="Enter outstanding amount"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bank-primary focus:border-bank-primary transition-colors duration-200 placeholder-gray-400"
                                >
                            </div>
                            
                            <div class="space-y-2">
                                <label for="due_date" class="block text-sm font-medium text-gray-700">
                                    Due Date <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="due_date" 
                                    name="due_date" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bank-primary focus:border-bank-primary transition-colors duration-200"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit" class="w-full bg-gradient-to-r from-bank-primary to-bank-secondary hover:from-bank-secondary hover:to-bank-primary text-white font-semibold py-4 px-6 rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none" id="submitBtn">
                            <span id="btnText">Send Settlement Notice</span>
                            <svg id="btnSpinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Alert Box -->
                    <div id="alertBox" class="hidden rounded-lg p-4 border-l-4" role="alert">
                        <div class="flex items-center">
                            <svg id="alertIcon" class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span id="alertText" class="font-medium"></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full text-center transform scale-95 transition-transform duration-300" id="modalContent">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Success!</h3>
            <p id="successMessage" class="text-gray-600 mb-6 leading-relaxed"></p>
            <button onclick="closeModal()" class="w-full bg-gradient-to-r from-bank-primary to-bank-secondary hover:from-bank-secondary hover:to-bank-primary text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02]">
                OK
            </button>
        </div>
    </div>

    <script>
        // Modal functions
        function showModal(message) {
            const modal = document.getElementById('successModal');
            const modalContent = document.getElementById('modalContent');
            const successMessage = document.getElementById('successMessage');
            
            successMessage.textContent = message;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Animate modal
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
        }

        function closeModal() {
            const modal = document.getElementById('successModal');
            const modalContent = document.getElementById('modalContent');
            
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }

        // Close modal when clicking outside content
        window.onclick = function(event) {
            const modal = document.getElementById('successModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('settlementForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const alertBox = document.getElementById('alertBox');
            const alertText = document.getElementById('alertText');
            
            // Set default date to today + 7 days
            const dueDate = document.getElementById('due_date');
            const today = new Date();
            const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
            dueDate.valueAsDate = nextWeek;
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Validate form
                if (!form.checkValidity()) {
                    showAlert('Please fill in all required fields correctly', 'error');
                    return;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                btnText.textContent = 'Sending...';
                btnSpinner.classList.remove('hidden');
                
                try {
                    // Prepare form data
                    const formData = new FormData(form);
                    
                    // Send data to server
                    const response = await fetch('send_email.php', { 
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        showModal('Settlement notice sent successfully!');
                        form.reset();
                        // Reset date to default after form submission
                        dueDate.valueAsDate = new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000);
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
                    btnSpinner.classList.add('hidden');
                }
            });
            
            function showAlert(message, type) {
                if (type === 'success') {
                    showModal(message);
                } else {
                    alertText.textContent = message;
                    alertBox.className = 'block rounded-lg p-4 border-l-4 bg-red-50 border-red-400 text-red-700';
                    alertBox.classList.remove('hidden');
                    
                    // Hide alert after 5 seconds
                    setTimeout(() => {
                        alertBox.classList.add('hidden');
                    }, 5000);
                }
            }

            // Make showAlert available globally for the form submission
            window.showAlert = showAlert;
        });
    </script>
</body>
</html>