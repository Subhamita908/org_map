<?php
require 'config.php'; // Loads $pdo
require 'email_helper.php';

date_default_timezone_set('Asia/Kolkata');
$now = new DateTime();

$hr_email = 'subhamitadeb4@gmail.com';

$output = "";
$emails_sent = 0;
$errors = [];

// Error handling function
function handleError($message) {
    global $errors;
    $errors[] = $message;
    error_log($message);
}

// Check DB connection
if (!isset($pdo)) {
    handleError("Database connection failed: PDO connection not defined");
    die("Database connection failed.");
}

// Test if 'employees' table is accessible
try {
    $stmt = $pdo->query("SELECT 1 FROM employees LIMIT 1");
    $output .= "<p>✓ Database connection successful. Employees table accessible.</p>";
} catch (PDOException $e) {
    handleError("Error: employees table not found or accessible - " . $e->getMessage());
}

// Date formats
$seven_days_later = (clone $now)->modify('+7 days')->format('m-d');
$today = $now->format('m-d');
$current_time = $now->format('H:i');

// Reusable message templates
$birthday_greetings = [
    "Wishing you a joyous birthday filled with happiness and success.",
    "May this special day bring you joy, laughter, and cherished memories.",
    "Wishing you continued success and a wonderful birthday celebration.",
    "On your birthday, we thank you for your dedication and commitment to Ilogitron Technologies.",
    "Have a fantastic birthday and a prosperous year ahead!"
];

$anniversary_messages = [
    "Congratulations on achieving this wonderful milestone with us.",
    "Thank you for your dedication and years of service at Ilogitron Technologies.",
    "Your hard work and loyalty have been instrumental to our growth and success.",
    "Celebrating your journey with us — your contributions are truly valued.",
    "Wishing you continued success and many more years of collaboration with Ilogitron."
];

// Function to send HTML table reminder
function sendReminder($pdo, $email, $sql, $date, $type, $table_headers) {
    global $emails_sent, $output;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date]);
        $results = $stmt->fetchAll();

        $list = "";
        $count = 0;
        foreach ($results as $row) {
            $list .= "<tr><td>" . htmlspecialchars($row['employee_id']) . "</td><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row[($type === 'birthday') ? 'date_of_birth' : 'date_of_join']) . "</td></tr>";
            $count++;
        }

        if ($count > 0) {
            $subject = "Reminder: Upcoming " . ucfirst($type) . "s in 7 Days";
            $body = "<p>Dear HR,</p><p>The following employees have upcoming {$type}s in 7 days:</p>
            <table border='1' cellspacing='0' cellpadding='8'>
            <thead><tr>{$table_headers}</tr></thead>
            <tbody>{$list}</tbody>
            </table><br>Regards,<br>Ilogitron Technologies";

            if (sendEmail($email, $subject, $body)) {
                $emails_sent++;
                $output .= "<p>✓ {$type} reminder email sent to HR for {$count} employees.</p>";
            } else {
                handleError("Failed to send {$type} reminder email to HR.");
            }
        } else {
            $output .= "<p>• No {$type}s in 7 days.</p>";
        }
    } catch (PDOException $e) {
        handleError("Failed to prepare or execute {$type} reminder query: " . $e->getMessage());
    }
}

// === Birthday and Anniversary Reminders in 7 Days ===
sendReminder($pdo, $hr_email,
    "SELECT employee_id, name, date_of_birth FROM employees WHERE DATE_FORMAT(date_of_birth, '%m-%d') = ?",
    $seven_days_later, 'birthday', "<th>Employee ID</th><th>Name</th><th>Date of Birth</th>");

sendReminder($pdo, $hr_email,
    "SELECT employee_id, name, date_of_join FROM employees WHERE DATE_FORMAT(date_of_join, '%m-%d') = ?",
    $seven_days_later, 'anniversary', "<th>Employee ID</th><th>Name</th><th>Joining Date</th>");

// === Today 12AM-00:15: HR & Employee Alerts ==
$time_check_passed = ($current_time >= '00:00' && $current_time <= '00:15');
$output .= "<p>Current time: {$current_time} (Time check: " . ($time_check_passed ? "PASSED" : "FAILED") . ")</p>";

if ($time_check_passed) {
    // HR Birthday & Anniversary Alert
    $types = [
        'birthday' => "SELECT name FROM employees WHERE DATE_FORMAT(date_of_birth, '%m-%d') = ?",
        'anniversary' => "SELECT name, date_of_join FROM employees WHERE DATE_FORMAT(date_of_join, '%m-%d') = ?"
    ];

    foreach ($types as $type => $sql) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$today]);
            $results = $stmt->fetchAll();
            $count = 0;

            foreach ($results as $row) {
                $name = htmlspecialchars($row['name']);
                $years = ($type === 'anniversary') ? (new DateTime($row['date_of_join']))->diff($now)->y : "";
                $subject = ($type === 'birthday') ? "Birthday Alert – {$name}" : "Work Anniversary – {$name}";
                $body = ($type === 'birthday') ? "Today is {$name}'s Birthday" : "Today is {$name}'s {$years} year work anniversary.";

                if (sendEmail($hr_email, $subject, $body)) {
                    $emails_sent++;
                    $count++;
                } else {
                    handleError("Failed to send {$type} alert to HR for {$name}");
                }
            }

            $output .= $count > 0
                ? "<p>✓ {$type} alerts sent to HR for {$count} employees.</p>"
                : "<p>• No {$type}s today for HR alerts.</p>";
        } catch (PDOException $e) {
            handleError("Failed to prepare or execute {$type} alert query: " . $e->getMessage());
        }
    }

    // Employee Birthday & Anniversary Wishes
    $types = [
        'birthday' => [
            "SELECT name, email FROM employees WHERE DATE_FORMAT(date_of_birth, '%m-%d') = ?",
            $birthday_greetings
        ],
        'anniversary' => [
            "SELECT name, email, date_of_join FROM employees WHERE DATE_FORMAT(date_of_join, '%m-%d') = ?",
            $anniversary_messages
        ]
    ];

    foreach ($types as $type => [$sql, $messages]) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$today]);
            $results = $stmt->fetchAll();
            $count = 0;

            foreach ($results as $row) {
                $name = htmlspecialchars($row['name']);
                $email = $row['email'];
                $years = ($type === 'anniversary') ? (new DateTime($row['date_of_join']))->diff($now)->y : "";
                $msg = $messages[array_rand($messages)];

                $subject = ($type === 'birthday') ? "Happy Birthday, {$name}!" : "Congratulations on Your Work Anniversary, {$name}!";
                $body = "<p>Dear {$name},</p><p>{$msg}</p>" .
                    ($type === 'anniversary' ? "<p>Today marks your {$years} year(s) with Ilogitron Technologies.</p>" : "") .
                    "<p>Warm regards,<br>Human Resources<br>Ilogitron Technologies</p>";

                if (sendEmail($email, $subject, $body)) {
                    $emails_sent++;
                    $count++;
                } else {
                    handleError("Failed to send {$type} email to {$name} ({$email})");
                }
            }

            $output .= $count > 0
                ? "<p>✓ {$type} emails sent to {$count} employees.</p>"
                : "<p>• No employee {$type}s today.</p>";
        } catch (PDOException $e) {
            handleError("Failed to prepare or execute {$type} employee query: " . $e->getMessage());
        }
    }
} else {
    $output .= "<p>• Daily email triggers not executed (outside 00:00–00:15).</p>";
}

// === Final Summary Email to HR ===
$summary_subject = "Daily HR Notification Summary – " . $now->format('Y-m-d');

if ($emails_sent === 0) {
    $summary_body = "<p>Dear HR,</p><p>Kindly note that there are no employee birthdays or work anniversaries scheduled for this month.</p><p>Regards,<br>Ilogitron Technologies System Bot</p>";
} else {
    $summary_body = "<p>Dear HR,</p><p>Here is the summary of today's automated notification system:</p>
    <ul><li>Total Emails Sent: <strong>{$emails_sent}</strong></li>
    <li>Status: " . (empty($errors) ? "✓ All tasks completed successfully." : "⚠ Some errors occurred.") . "</li></ul>";

    if (!empty($errors)) {
        $summary_body .= "<p><strong>Errors:</strong></p><ul>";
        foreach ($errors as $e) {
            $summary_body .= "<li>" . htmlspecialchars($e) . "</li>";
        }
        $summary_body .= "</ul>";
    }
    $summary_body .= "<p>Regards,<br>Ilogitron Technologies System Bot</p>";
}

if (sendEmail($hr_email, $summary_subject, $summary_body)) {
    $output .= "<p>✓ Summary email sent to HR.</p>";
    $emails_sent++;
} else {
    handleError("Failed to send summary email to HR");
}

?>

<!-- OUTPUT HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Birthday/Anniversary System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #000 100%);
            min-height: 100vh;
            padding: 20px;
            color: #fff;
        }
        
        .main-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(220, 20, 60, 0.3);
            border: 2px solid #dc143c;
        }
        
        .header {
            background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.1; }
        }
        
        .header h1 {
            color: #fff;
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .header .subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 1.1em;
            font-weight: 300;
            position: relative;
            z-index: 2;
        }
        
        .content {
            padding: 40px;
            background: #fff;
            color: #1a1a1a;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dc143c;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(220, 20, 60, 0.2);
        }
        
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #dc143c, #ff1744, #dc143c);
        }
        
        .summary-card h3 {
            color: #dc143c;
            font-size: 1.3em;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .summary-value {
            font-size: 2.5em;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        
        .summary-label {
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status-section {
            margin-bottom: 30px;
        }
        
        .status-card {
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 6px solid;
            position: relative;
            overflow: hidden;
        }
        
        .status-success {
            background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
            border-left-color: #dc143c;
            color: #1a5d1a;
        }
        
        .status-error {
            background: linear-gradient(135deg, #fff8f8 0%, #ffe8e8 100%);
            border-left-color: #dc143c;
            color: #8b0000;
        }
        
        .status-info {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
            border-left-color: #dc143c;
            color: #1a1a5d;
        }
        
        .status-card h3 {
            color: #dc143c;
            font-size: 1.4em;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #fff;
        }
        
        .icon-success { background: #28a745; }
        .icon-error { background: #dc143c; }
        .icon-info { background: #17a2b8; }
        
        .details-section {
            background: #1a1a1a;
            color: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            border: 2px solid #333;
        }
        
        .details-section h3 {
            color: #dc143c;
            font-size: 1.4em;
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .details-section p {
            line-height: 1.6;
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            background: rgba(255,255,255,0.05);
        }
        
        .details-section p:last-child {
            margin-bottom: 0;
        }
        
        .error-list {
            background: rgba(220, 20, 60, 0.1);
            border: 1px solid #dc143c;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }
        
        .error-list ul {
            list-style: none;
            margin: 0;
        }
        
        .error-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(220, 20, 60, 0.2);
            color: #ff6b6b;
        }
        
        .error-list li:last-child {
            border-bottom: none;
        }
        
        .error-list li::before {
            content: '⚠';
            margin-right: 8px;
            color: #dc143c;
        }
        
        .footer {
            background: #1a1a1a;
            color: #ccc;
            text-align: center;
            padding: 20px;
            font-size: 0.9em;
            border-top: 2px solid #333;
        }
        
        .footer strong {
            color: #dc143c;
        }
        
        /* Enhanced Responsive Design */
        @media (max-width: 1200px) {
            .main-container {
                margin: 0 15px;
            }
            
            .summary-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .main-container {
                margin: 0;
                border-radius: 10px;
            }
            
            .header {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 1.8em;
                margin-bottom: 8px;
            }
            
            .header .subtitle {
                font-size: 1em;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                margin-bottom: 25px;
            }
            
            .summary-card {
                padding: 20px 15px;
            }
            
            .summary-value {
                font-size: 2em;
            }
            
            .status-card {
                padding: 20px 15px;
                margin-bottom: 15px;
            }
            
            .status-card h3 {
                font-size: 1.2em;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .details-section {
                padding: 20px 15px;
                margin-top: 25px;
            }
            
            .details-section h3 {
                font-size: 1.2em;
                margin-bottom: 15px;
            }
            
            .footer {
                padding: 15px;
                font-size: 0.85em;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .header h1 {
                font-size: 1.5em;
                line-height: 1.2;
            }
            
            .header .subtitle {
                font-size: 0.9em;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .summary-card {
                padding: 15px 12px;
            }
            
            .summary-card h3 {
                font-size: 1.1em;
                margin-bottom: 12px;
            }
            
            .summary-value {
                font-size: 1.8em;
                margin-bottom: 8px;
            }
            
            .summary-label {
                font-size: 0.8em;
            }
            
            .status-card {
                padding: 15px 12px;
            }
            
            .status-card h3 {
                font-size: 1.1em;
            }
            
            .details-section {
                padding: 15px 12px;
            }
            
            .details-section p {
                font-size: 0.9em;
                padding: 6px 10px;
            }
            
            .error-list {
                padding: 15px;
            }
            
            .footer {
                padding: 12px;
                font-size: 0.8em;
                line-height: 1.4;
            }
            
            .footer p {
                word-break: break-word;
            }
        }
        
        @media (max-width: 320px) {
            .header h1 {
                font-size: 1.3em;
            }
            
            .summary-value {
                font-size: 1.5em;
            }
            
            .content {
                padding: 15px 10px;
            }
            
            .summary-card,
            .status-card,
            .details-section {
                padding: 12px 10px;
            }
        }
        
        @media (max-width: 768px) and (orientation: landscape) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2em;
            }
        }
        
        @media (max-width: 480px) and (orientation: landscape) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
        }
        
        .loading-bar {
            height: 3px;
            background: linear-gradient(90deg, #dc143c, #ff1744, #dc143c);
            animation: loading 2s ease-in-out infinite;
        }
        
        @keyframes loading {
            0%, 100% { transform: scaleX(1); }
            50% { transform: scaleX(1.1); }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1>🎉 HR Automation System</h1>
            <div class="subtitle">Employee Birthday & Anniversary Management</div>
        </div>
        
        <div class="loading-bar"></div>
        
        <div class="content">
            <div class="summary-grid">
                <div class="summary-card">
                    <h3>📧 Total Emails Sent</h3>
                    <div class="summary-value"><?= $emails_sent ?></div>
                    <div class="summary-label">Notifications Delivered</div>
                </div>
                
                <div class="summary-card">
                    <h3>📊 System Status</h3>
                    <div class="summary-value"><?= empty($errors) ? '✅' : '⚠️' ?></div>
                    <div class="summary-label"><?= empty($errors) ? 'All Systems Operational' : 'Issues Detected' ?></div>
                </div>
            </div>
            
            <div class="status-section">
                <div class="status-card <?= empty($errors) ? 'status-success' : 'status-error' ?>">
                    <h3>
                        <span class="status-icon <?= empty($errors) ? 'icon-success' : 'icon-error' ?>">
                            <?= empty($errors) ? '✓' : '!' ?>
                        </span>
                        Execution Summary
                    </h3>
                    <p><strong>Total Emails Sent:</strong> <?= $emails_sent ?></p>
                    <p><strong>Status:</strong> <?= empty($errors) ? '✅ Completed Successfully' : '⚠️ Completed with Errors' ?></p>
                    <p><strong>Execution Time:</strong> <?= date('Y-m-d H:i:s') ?> (IST)</p>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="status-section">
                    <div class="status-card status-error">
                        <h3>
                            <span class="status-icon icon-error">!</span>
                            Errors Encountered
                        </h3>
                        <div class="error-list">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="details-section">
                <h3>🔍 Execution Details</h3>
                <?= $output ?>
            </div>
        </div>
        
        <div class="footer">
            <p>
                <strong>Ilogitron Technologies</strong> | 
                Automated HR Notification System | 
                Last Updated: <?= date('Y-m-d H:i:s') ?> (IST)
            </p>
        </div>
    </div>
</body>
</html>