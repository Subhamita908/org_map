<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include config.php for database connection
require 'config.php'; // Assumes this provides a $pdo variable or similar
require 'phpmailer/PHPMailer.php';
require 'phpmailer/Exception.php';
require 'phpmailer/SMTP.php';

// Function to send email using PHPMailer (unchanged from your original code)
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'subhamitadeb4@gmail.com'; // Consider moving to config.php
        $mail->Password = 'xmhc webs qrao fnbm'; // Consider moving to config.php
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('subhamitadeb4@gmail.com', 'Ilogitron HR');
        $mail->addAddress($to);

        // Dynamic Subject and Body
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error ({$to}): " . $mail->ErrorInfo);
        return false;
    }
}

// Function to process emails from the database
function processEmailQueue($pdo) { // Pass PDO connection from config.php
    if (!$pdo) {
        return "No database connection available.";
    }

    try {
        // Fetch pending emails from the email_queue table
        $stmt = $pdo->query("SELECT id, recipient_email, subject, body FROM email_queue WHERE status = 'pending'");
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($emails)) {
            return "No pending emails in the queue.";
        }

        foreach ($emails as $email) {
            $id = $email['id'];
            $to = $email['recipient_email'];
            $subject = $email['subject'];
            $body = $email['body'];

            // Send the email
            if (sendEmail($to, $subject, $body)) {
                // Update status to 'sent'
                $stmt = $pdo->prepare("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                echo "Email sent to $to\n";
            } else {
                // Update status to 'failed'
                $stmt = $pdo->prepare("UPDATE email_queue SET status = 'failed' WHERE id = ?");
                $stmt->execute([$id]);
                echo "Failed to send email to $to\n";
            }
        }
        return "Email queue processed.";
    } catch (PDOException $e) {
        error_log("PDO Query Error: " . $e->getMessage());
        return "Error processing email queue: " . $e->getMessage();
    }
}

// Example usage
// Assuming config.php defines $pdo
if (!isset($pdo)) {
    die("Error: PDO connection not found in config.php.");
}
echo processEmailQueue($pdo);
?>