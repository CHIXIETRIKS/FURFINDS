<?php
// Set JSON header FIRST before any output
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer/src/Exception.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";

// Function to return JSON and exit
function jsonResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    jsonResponse(false, 'Invalid request method.');
}

// Sanitize input
$name = isset($_POST["name"]) ? htmlspecialchars(trim($_POST["name"])) : '';
$email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : '';
$phone = isset($_POST["phone"]) ? htmlspecialchars(trim($_POST["phone"])) : 'Not provided';
$message = isset($_POST["message"]) ? htmlspecialchars(trim($_POST["message"])) : '';

// Validate
if (empty($name) || empty($email) || empty($message)) {
    jsonResponse(false, 'Please fill in all required fields.');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.');
}

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "mikeandreisoria89@gmail.com";
    $mail->Password = "lduynxtikjbbkbig";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom("mikeandreisoria89@gmail.com", "FurFinds Contact System");
    $mail->addAddress("mikeandreisoria89@gmail.com", "FurFinds Team");
    $mail->addReplyTo("noreply@furfinds.com", "No Reply");

    // Load and prepare template
    $templatePath = __DIR__ . '/email-templates/message-template.html';
    
    if (!file_exists($templatePath)) {
        error_log("Template file not found: {$templatePath}");
        jsonResponse(false, 'Email template not found.');
    }
    
    $emailTemplate = file_get_contents($templatePath);
    
    if ($emailTemplate === false) {
        error_log("Failed to read template file: {$templatePath}");
        jsonResponse(false, 'Failed to load email template.');
    }

    $emailBody = str_replace(
        ['{{SENDER_NAME}}', '{{SENDER_EMAIL}}', '{{SENDER_PHONE}}', '{{MESSAGE_CONTENT}}', '{{SUBMISSION_DATE}}', '{{SUBMISSION_TIME}}', '{{CURRENT_YEAR}}'],
        [$name, $email, $phone, nl2br($message), date('F j, Y'), date('g:i A'), date('Y')],
        $emailTemplate
    );

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "New Contact Message - FurFinds";
    $mail->Body = $emailBody;
    
    // Send
    $mail->send();
    
    jsonResponse(true, 'Message sent successfully!');

} catch (Exception $e) {
    error_log("PHPMailer Error: {$mail->ErrorInfo}");
    jsonResponse(false, 'Failed to send message. Please try again.');
}
?>