<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    function clean($value) {
        return htmlspecialchars(trim($value));
    }

    // ========== PET INFO ==========
    $pet_name = clean($_POST["pet_name"] ?? "Unknown Pet");

    // ========== Applicant Info ==========
    $first_name  = clean($_POST["first_name"] ?? "");
    $last_name   = clean($_POST["last_name"] ?? "");
    $email       = clean($_POST["email"] ?? "");
    $phone       = clean($_POST["phone"] ?? "");
    $address     = clean($_POST["address"] ?? "");
    $birthdate   = clean($_POST["birthdate"] ?? "");
    $occupation  = clean($_POST["occupation"] ?? "");
    $company     = clean($_POST["company"] ?? "");
    $social      = clean($_POST["social"] ?? "");

    // ========== Experience ==========
    $building   = clean($_POST["building"] ?? "");
    $rent       = clean($_POST["rent"] ?? "");
    $live_with  = isset($_POST["livewith"]) ? implode(", ", $_POST["livewith"]) : "";
    $experience = clean($_POST["experience"] ?? "");

    // ========== Interview ==========
    $interview_date = clean($_POST["interview_date"] ?? "");
    $hour   = clean($_POST["time_hour"] ?? "");
    $minute = clean($_POST["time_minute"] ?? "");
    $ampm   = clean($_POST["time_ampm"] ?? "");
    $interview_time = "$hour:$minute $ampm";
    $visit = clean($_POST["visit"] ?? "");

    // ========== Agreement ==========
    $agreement = isset($_POST["agreement"]) ? "Agreed" : "Did NOT Agree";

    // ========== File Count ==========
    $homePhotosCount = !empty($_FILES["home_photos"]["name"][0]) 
        ? count($_FILES["home_photos"]["name"]) 
        : 0;

    $hasValidID = !empty($_FILES["valid_id"]["name"]) ? "YES" : "NO";

    // ========== Start Mail ==========
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'trixiekabiling07@gmail.com';
        $mail->Password   = 'qgmp etiz hxjj qwxf';   // APP PASSWORD
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // ✅ FIXED SENDER (REQUIRED BY GMAIL)
        $mail->setFrom('trixiekabiling07@gmail.com', 'FurFinds Online Pet Adoption Center');
        $mail->addAddress('trixiekabiling07@gmail.com');

        // ✅ REPLY TO USER
        $mail->addReplyTo($email, "$first_name $last_name");

        // ========== Attach Valid ID ==========
        if (!empty($_FILES["valid_id"]["tmp_name"])) {
            $mail->addAttachment(
                $_FILES["valid_id"]["tmp_name"],
                $_FILES["valid_id"]["name"]
            );
        }

        // ========== Attach Home Photos ==========
        if (!empty($_FILES["home_photos"]["tmp_name"])) {
            foreach ($_FILES["home_photos"]["tmp_name"] as $i => $tmp) {
                if (!empty($tmp)) {
                    $mail->addAttachment(
                        $tmp,
                        $_FILES["home_photos"]["name"][$i]
                    );
                }
            }
        }

        // ========== EMAIL TEMPLATE ==========
        $mail->isHTML(true);
        $mail->Subject = "New Adoption Application for $pet_name - $first_name $last_name";

        // ✅ CORRECT PATH BASED ON YOUR FOLDER STRUCTURE
        $templatePath = __DIR__ . "/email-templates/adoption-template.html";
        $template = file_get_contents($templatePath);

        if (!$template) {
            echo "failed";
            exit;
        }

        $replacements = [
            "{{PET_NAME}}" => $pet_name,
            "{{APPLICANT_NAME}}" => "$first_name $last_name",
            "{{FIRST_NAME}}" => $first_name,
            "{{LAST_NAME}}" => $last_name,
            "{{EMAIL}}" => $email,
            "{{PHONE}}" => $phone,
            "{{ADDRESS}}" => $address,
            "{{BIRTHDATE}}" => $birthdate,
            "{{OCCUPATION}}" => $occupation,
            "{{COMPANY}}" => $company,
            "{{SOCIAL}}" => $social,

            "{{BUILDING}}" => ucfirst($building),
            "{{RENT}}" => ucfirst($rent),
            "{{LIVE_WITH}}" => $live_with,
            "{{HOME_PHOTOS_COUNT}}" => $homePhotosCount,

            "{{EXPERIENCE}}" => nl2br($experience),

            "{{INTERVIEW_DATE}}" => $interview_date,
            "{{INTERVIEW_TIME}}" => $interview_time,
            "{{VISIT}}" => ucfirst($visit),

            "{{AGREEMENT}}" => $agreement,
            "{{VALID_ID}}" => $hasValidID,

            "{{SUBMISSION_DATE}}" => date("F d, Y"),
            "{{SUBMISSION_TIME}}" => date("h:i A"),
            "{{CURRENT_YEAR}}" => date("Y")
        ];

        $mail->Body = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        // ✅ SEND EMAIL
        $mail->send();
        echo "success";

    } catch (Exception $e) {
        echo "failed";
    }
}
?>
