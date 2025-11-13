<?php
// mail.php - Các hàm gửi email
require_once __DIR__ . '/../config.php';

/**
 * Gửi email dạng HTML đơn giản.
 *
 * @param string $to
 * @param string $subject
 * @param string $body
 * @param array $options
 * @return bool
 */
function sendEmail($to, $subject, $body, array $options = []) {
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $fromAddress = $options['from_address'] ?? MAIL_FROM_ADDRESS;
    $fromName = $options['from_name'] ?? MAIL_FROM_NAME;
    $returnPath = $options['return_path'] ?? MAIL_RETURN_PATH;
    $isHtml = $options['is_html'] ?? true;

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [];
    $headers[] = 'From: ' . sprintf('"%s" <%s>', addslashes($fromName), $fromAddress);
    $headers[] = 'Reply-To: ' . $fromAddress;
    $headers[] = 'Return-Path: ' . $returnPath;
    $headers[] = 'MIME-Version: 1.0';

    if ($isHtml) {
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
    } else {
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    }

    // Ensure proper line endings for mail headers
    $headersString = implode("\r\n", $headers);

    // Use mail function; suppress warnings to avoid leaking config paths
    $result = @mail($to, $encodedSubject, $body, $headersString);

    if (!$result) {
        error_log("Failed to send email to {$to} with subject {$subject}");
    }

    return $result;
}
?>

