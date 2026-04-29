<?php

require_once __DIR__ . '/includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/includes/phpmailer/SMTP.php';
require_once __DIR__ . '/includes/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require __DIR__ . '/config/email.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->Port = (int) $config['port'];
    $mail->SMTPSecure = $config['secure'];
    $mail->CharSet = 'UTF-8';

    $mail->SMTPDebug = 2;

    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addReplyTo($config['reply_to'], $config['reply_name']);
    $mail->addAddress('adryano@csfdigital.com.br');

    $mail->isHTML(true);
    $mail->Subject = 'Teste SMTP';
    $mail->Body = '<p>Teste de envio SMTP.</p>';

    $mail->send();
    echo 'E-mail enviado com sucesso.';
} catch (Exception $e) {
    echo '<pre>Erro: ' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
}