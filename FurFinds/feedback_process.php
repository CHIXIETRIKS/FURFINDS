<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer/src/Exception.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize input
    $feedback = isset($_POST["fb-message"]) ? htmlspecialchars(trim($_POST["fb-message"])) : '';
    $consent1 = isset($_POST["consent1"]);
    $consent2 = isset($_POST["consent2"]);
    
    // Validate
    if (empty($feedback) || !$consent1 || !$consent2) {
        header("Location: index.html?status=error");
        exit();
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "shanecpybara@gmail.com";
        $mail->Password = "lygr ajdh eywe aktx";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom("shanecpybara@gmail.com", "FurFinds Feedback System");
        $mail->addAddress("shanecpybara@gmail.com", "FurFinds Team");
        $mail->addReplyTo("noreply@furfinds.com", "No Reply");

        // Load and prepare template
        $emailTemplate = file_get_contents(__DIR__ . '/email-templates/feedback-email.html');

        $emailBody = str_replace(
            ['{{FEEDBACK_MESSAGE}}', '{{SUBMISSION_DATE}}', '{{SUBMISSION_TIME}}', '{{CURRENT_YEAR}}'],
            [nl2br($feedback), date('F j, Y'), date('g:i A'), date('Y')],
            $emailTemplate
        );

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "New Feedback Submission - FurFinds";
        $mail->Body = $emailBody;
        
        // Send
        $mail->send();

        header("Location: index.html?status=success#feedback-section");
        exit();

    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        header("Location: index.html?status=error#feedback-section");
        exit();
    }
}

// Direct access fallback
header("Location: index.html");
exit();
?>