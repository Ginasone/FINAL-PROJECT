<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/FINAL PROJECT/vendor/autoload.php'; // Composer autoload for PHPMailer

function sendAdminEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'your_smtp_host';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@example.com';
        $mail->Password   = 'your_email_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('admin@yoursite.com', 'K-Pop Content Admin');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

function notifyUserContentDeleted($userEmail, $contentTitle) {
    $subject = "Content Deleted";
    $body = "
    <p>Dear User,</p>
    <p>Your content titled '<strong>" . htmlspecialchars($contentTitle) . "</strong>' has been deleted by an administrator.</p>
    <p>If you believe this was done in error, please contact support.</p>
    <br>
    <p>Best regards,<br>K-Pop Content Admin Team</p>
    ";

    return sendAdminEmail($userEmail, $subject, $body);
}

function notifyUserAccountDeleted($userEmail) {
    $subject = "Account Deleted";
    $body = "
    <p>Dear User,</p>
    <p>Your account has been deleted by an administrator.</p>
    <p>If you believe this was done in error, please contact support.</p>
    <br>
    <p>Best regards,<br>K-Pop Content Admin Team</p>
    ";

    return sendAdminEmail($userEmail, $subject, $body);
}