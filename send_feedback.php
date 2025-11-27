<?php
// إيميلك الوحيد - هيوصلك عليه كل الرسائل
$admin_email = "info@infocuppaegy.com";

// إسم موقعك
$site_name = "InfoCuppa Egypt";

// تحقق أن الطريقة POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html?status=error&message=Invalid request method');
    exit;
}

// خد البيانات من الفورم
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// 1. تحقق من البيانات المطلوبة
if (empty($name) || empty($email) || empty($message)) {
    header('Location: index.html?status=error&message=Please fill all required fields');
    exit;
}

// 2. تحقق من صحة الإيميل
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.html?status=error&message=Please enter a valid email address');
    exit;
}

// 3. سجل البيانات في ملف (عشان تتأكد إنها واصلة)
$log_entry = [
    'time' => date('Y-m-d H:i:s'),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'message' => $message,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
];

// سجل في ملف text عادي
file_put_contents('feedback_log.txt', 
    "=== NEW FEEDBACK ===\n" .
    "Time: " . $log_entry['time'] . "\n" .
    "Name: " . $log_entry['name'] . "\n" .
    "Email: " . $log_entry['email'] . "\n" .
    "Phone: " . $log_entry['phone'] . "\n" .
    "Message: " . $log_entry['message'] . "\n" .
    "IP: " . $log_entry['ip'] . "\n" .
    "===================\n\n", 
    FILE_APPEND
);

// 4. أرسل الإيميل للإيميل الوحيد
$email_sent = sendFeedbackEmail($admin_email, $name, $email, $phone, $message);

if ($email_sent) {
    // سجل نجاح الإرسال
    file_put_contents('email_success_log.txt', 
        "✅ Sent to: $admin_email at " . date('Y-m-d H:i:s') . "\n", 
        FILE_APPEND
    );
    
    header('Location: index.html?status=success');
} else {
    // سجل فشل الإرسال
    file_put_contents('email_error_log.txt', 
        "❌ Failed to send to: $admin_email at " . date('Y-m-d H:i:s') . "\n", 
        FILE_APPEND
    );
    
    header('Location: index.html?status=error&message=Failed to send email. Please try again later.');
}
exit;

// دالة إرسال الإيميل
function sendFeedbackEmail($to, $name, $customer_email, $phone, $message) {
    global $site_name;
    
    $subject = "📝 New Customer Feedback - " . $site_name;
    
    // محتوى الإيميل - نسخة HTML جميلة
    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                background: #f4f4f4; 
                padding: 20px; 
            }
            .container { 
                background: white; 
                padding: 30px; 
                border-radius: 10px; 
                max-width: 600px; 
                margin: 0 auto; 
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #667eea, #764ba2); 
                color: white; 
                padding: 25px; 
                border-radius: 10px; 
                text-align: center; 
                margin-bottom: 20px;
            }
            .info { 
                margin: 20px 0; 
                padding: 20px; 
                background: #f8f9fa; 
                border-radius: 8px; 
                border-left: 4px solid #667eea;
            }
            .field { 
                margin: 12px 0; 
                padding: 8px 0;
            }
            .field-label { 
                font-weight: bold; 
                color: #333; 
                display: inline-block;
                width: 120px;
            }
            .message-box { 
                background: #e9ecef; 
                padding: 20px; 
                border-radius: 8px; 
                margin: 15px 0; 
                border: 1px solid #dee2e6;
                line-height: 1.6;
            }
            .footer { 
                text-align: center; 
                margin-top: 30px; 
                color: #666; 
                font-size: 14px; 
                padding-top: 20px;
                border-top: 1px solid #dee2e6;
            }
            .highlight {
                background: #fff3cd;
                padding: 10px;
                border-radius: 5px;
                border-left: 4px solid #ffc107;
                margin: 10px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; font-size: 28px;'>📝 New Customer Feedback</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>$site_name</p>
            </div>
            
            <div class='highlight'>
                <strong>🚀 Action Required:</strong> New customer feedback received and requires your attention.
            </div>
            
            <div class='info'>
                <h3 style='color: #333; margin-bottom: 15px;'>👤 Customer Information</h3>
                <div class='field'>
                    <span class='field-label'>Name:</span> $name
                </div>
                <div class='field'>
                    <span class='field-label'>Email:</span> 
                    <a href='mailto:$customer_email' style='color: #667eea; text-decoration: none;'>
                        $customer_email
                    </a>
                </div>
                <div class='field'>
                    <span class='field-label'>Phone:</span> 
                    " . ($phone ? "<a href='tel:$phone' style='color: #667eea; text-decoration: none;'>$phone</a>" : 'Not provided') . "
                </div>
            </div>
            
            <div class='info'>
                <h3 style='color: #333; margin-bottom: 15px;'>💬 Customer Message</h3>
                <div class='message-box'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
            </div>
            
            <div class='info'>
                <h3 style='color: #333; margin-bottom: 15px;'>🔍 Technical Details</h3>
                <div class='field'>
                    <span class='field-label'>IP Address:</span> " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "
                </div>
                <div class='field'>
                    <span class='field-label'>Submission Time:</span> " . date('F j, Y \a\t g:i A') . "
                </div>
            </div>
            
            <div class='footer'>
                <p>This email was automatically generated by $site_name Feedback System</p>
                <p style='font-size: 12px; color: #999;'>
                    💡 <strong>Quick Actions:</strong> 
                    <a href='mailto:$customer_email?subject=Re: Your Feedback' style='color: #667eea;'>Reply to Customer</a> • 
                    " . date('Y') . " $site_name
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // هيدرات الإيميل
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $site_name <noreply@infocuppaegy.com>\r\n";
    $headers .= "Reply-To: $customer_email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "X-Priority: 1 (Highest)\r\n";
    $headers .= "Importance: High\r\n";
    
    // حاول أرسل الإيميل
    return mail($to, $subject, $email_body, $headers);
}
?>