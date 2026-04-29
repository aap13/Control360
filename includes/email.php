<?php

function chamados_email_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $file = __DIR__ . '/../config/email.php';
    if (!is_file($file)) {
        $config = ['enabled' => false];
        return $config;
    }

    $config = require $file;
    if (!is_array($config)) {
        $config = ['enabled' => false];
    }

    if (!isset($config['enabled'])) {
        $config['enabled'] = true;
    }

    return $config;
}

function chamados_email_enabled(): bool
{
    $config = chamados_email_config();
    return !empty($config['enabled'])
        && !empty($config['host'])
        && !empty($config['port'])
        && !empty($config['username'])
        && !empty($config['password'])
        && !empty($config['from_email']);
}

function chamados_email_log(string $message): void
{
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $line = '[' . date('Y-m-d H:i:s') . '] [email] ' . $message . PHP_EOL;
    @file_put_contents($dir . '/app.log', $line, FILE_APPEND);
}

function chamados_email_base_url(): string
{
    $scheme = 'https';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $scheme = strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'http' ? 'http' : 'https';
    } elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
        $scheme = strtolower((string) $_SERVER['REQUEST_SCHEME']) === 'http' ? 'http' : 'https';
    } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    }

    $host = !empty($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : 'csfdigital.com.br';
    return $scheme . '://' . $host;
}

function chamados_email_public_link(array $chamado): string
{
    $base = rtrim(chamados_email_base_url(), '/');
    return $base . '/hesk/acompanhar.php?protocolo=' . rawurlencode((string) ($chamado['protocolo'] ?? '')) . '&email=' . rawurlencode((string) ($chamado['email_solicitante'] ?? ''));
}

function chamados_email_internal_link(array $chamado): string
{
    $base = rtrim(chamados_email_base_url(), '/');
    $adminDir = is_file(__DIR__ . '/../config.php') ? basename(dirname(__DIR__)) : 'control';
    return $base . '/' . $adminDir . '/chamados_visualizar.php?id=' . (int) $chamado['id'];
}

function chamados_email_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function chamados_email_html_layout(string $title, string $intro, array $rows, string $buttonLabel = '', string $buttonUrl = ''): string
{
    $rowsHtml = '';
    $isAlt = false;

    foreach ($rows as $label => $value) {
        if ($value === null || $value === '') {
            continue;
        }

        $bg = $isAlt ? '#f8fafc' : '#ffffff';
        $rowsHtml .= ''
            . '<tr>'
            . '<td style="padding:14px 16px; border-bottom:1px solid #e5e7eb; background:' . $bg . '; color:#6b7280; font-size:14px; font-weight:700; width:180px;">' . chamados_email_escape($label) . '</td>'
            . '<td style="padding:14px 16px; border-bottom:1px solid #e5e7eb; background:' . $bg . '; color:#111827; font-size:14px; font-weight:600;">' . chamados_email_escape($value) . '</td>'
            . '</tr>';

        $isAlt = !$isAlt;
    }

    $buttonHtml = '';
    if ($buttonLabel !== '' && $buttonUrl !== '') {
        $buttonHtml = ''
            . '<tr>'
            . '<td align="center" style="padding:30px 24px 8px 24px;">'
            . '<table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center">'
            . '<tr>'
            . '<td align="center" bgcolor="#A31D1E" style="border-radius:8px;">'
            . '<a href="' . chamados_email_escape($buttonUrl) . '" target="_blank" style="display:inline-block; min-width:220px; padding:14px 28px; font-family:Arial,Helvetica,sans-serif; font-size:15px; line-height:18px; font-weight:700; color:#ffffff; text-decoration:none; background:#A31D1E; border-radius:8px; text-align:center;">' . chamados_email_escape($buttonLabel) . '</a>'
            . '</td>'
            . '</tr>'
            . '</table>'
            . '</td>'
            . '</tr>';
    }

    return '<!DOCTYPE html>'
        . '<html lang="pt-BR">'
        . '<head>'
        . '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0" />'
        . '<title>' . chamados_email_escape($title) . '</title>'
        . '</head>'
        . '<body style="margin:0; padding:0; background-color:#eef2f7;">'
        . '<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="background-color:#eef2f7; margin:0; padding:0;">'
        . '<tr>'
        . '<td align="center" style="padding:32px 16px;">'
        . '<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="680" style="width:680px; max-width:680px; background-color:#ffffff; border:1px solid #dbe2ea;">'
        . '<tr>'
        . '<td style="background-color:#0f172a; padding:0;">'
        . '<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%">'
        . '<tr><td style="height:5px; font-size:0; line-height:0; background-color:#A31D1E;">&nbsp;</td></tr>'
        . '<tr>'
        . '<td style="padding:22px 24px 20px 24px;">'
        . '<div style="font-family:Arial,Helvetica,sans-serif; font-size:28px; line-height:32px; font-weight:700; color:#ffffff;">SERVICE DESK - CSF</div>'
        . '</td>'
        . '</tr>'
        . '</table>'
        . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td style="padding:28px 24px 10px 24px; font-family:Arial,Helvetica,sans-serif;">'
        . '<div style="font-size:30px; line-height:36px; font-weight:700; color:#111827;">' . chamados_email_escape($title) . '</div>'
        . '<div style="padding-top:12px; font-size:16px; line-height:24px; color:#4b5563;">' . nl2br(chamados_email_escape($intro)) . '</div>'
        . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td style="padding:8px 24px 0 24px;">'
        . '<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="border:1px solid #e5e7eb; background-color:#ffffff;">'
        . $rowsHtml
        . '</table>'
        . '</td>'
        . '</tr>'
        . $buttonHtml
        . '<tr>'
        . '<td style="padding:26px 24px 28px 24px; font-family:Arial,Helvetica,sans-serif; font-size:12px; line-height:18px; color:#6b7280;">Esta é uma notificação automática do Service Desk CSF.</td>'
        . '</tr>'
        . '</table>'
        . '</td>'
        . '</tr>'
        . '</table>'
        . '</body>'
        . '</html>';
}

function chamados_email_send(string $to, string $subject, string $html, array $options = array()): bool
{
    $config = chamados_email_config();
    if (!chamados_email_enabled()) {
        chamados_email_log('Envio ignorado: configuração SMTP ausente.');
        return false;
    }

    $to = trim($to);
    if ($to === '' || strpos($to, '@') === false) {
        chamados_email_log('Destinatário inválido: ' . $to);
        return false;
    }

    $host = (string) $config['host'];
    $port = (int) $config['port'];
    $secure = isset($config['secure']) ? strtolower((string) $config['secure']) : '';
    $username = (string) $config['username'];
    $password = (string) $config['password'];
    $fromEmail = (string) $config['from_email'];
    $fromName = isset($config['from_name']) ? (string) $config['from_name'] : $fromEmail;
    $replyTo = !empty($options['reply_to']) ? (string) $options['reply_to'] : (!empty($config['reply_to']) ? (string) $config['reply_to'] : $fromEmail);
    $replyName = !empty($options['reply_name']) ? (string) $options['reply_name'] : (!empty($config['reply_name']) ? (string) $config['reply_name'] : $fromName);

    $transport = ($secure === 'ssl') ? 'ssl://' : '';
    $timeout = !empty($config['connect_timeout']) ? (int) $config['connect_timeout'] : 20;
    $remote = $transport . $host . ':' . $port;
    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        chamados_email_log('Falha ao conectar SMTP: ' . $errstr . ' (' . $errno . ')');
        return false;
    }

    $readTimeout = !empty($config['socket_timeout']) ? (int) $config['socket_timeout'] : 30;
    stream_set_timeout($socket, $readTimeout);

    $expect = function ($codes) use ($socket) {
        $codes = (array) $codes;
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }
        if ($response === '') {
            return [false, 'Sem resposta do servidor SMTP'];
        }
        $status = (int) substr($response, 0, 3);
        if (!in_array($status, $codes, true)) {
            return [false, trim($response)];
        }
        return [true, $response];
    };

    $send = function ($command, $codes) use ($socket, $expect) {
        fwrite($socket, $command . "\r\n");
        return $expect($codes);
    };

    list($ok, $response) = $expect([220]);
    if (!$ok) {
        fclose($socket);
        chamados_email_log('SMTP banner inválido: ' . $response);
        return false;
    }

    $helloHost = !empty($_SERVER['HTTP_HOST']) ? preg_replace('/:\d+$/', '', (string) $_SERVER['HTTP_HOST']) : 'localhost';
    list($ok, $response) = $send('EHLO ' . $helloHost, [250]);
    if (!$ok) {
        fclose($socket);
        chamados_email_log('Falha no EHLO: ' . $response);
        return false;
    }

    if ($secure === 'tls') {
        list($ok, $response) = $send('STARTTLS', [220]);
        if (!$ok || !stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            chamados_email_log('Falha ao iniciar TLS: ' . $response);
            return false;
        }
        list($ok, $response) = $send('EHLO ' . $helloHost, [250]);
        if (!$ok) {
            fclose($socket);
            chamados_email_log('Falha no EHLO após TLS: ' . $response);
            return false;
        }
    }

    list($ok, $response) = $send('AUTH LOGIN', [334]);
    if (!$ok) {
        fclose($socket);
        chamados_email_log('Falha AUTH LOGIN: ' . $response);
        return false;
    }
    list($ok, $response) = $send(base64_encode($username), [334]);
    if (!$ok) {
        fclose($socket);
        chamados_email_log('Falha usuário SMTP: ' . $response);
        return false;
    }
    list($ok, $response) = $send(base64_encode($password), [235]);
    if (!$ok) {
        fclose($socket);
        chamados_email_log('Falha senha SMTP: ' . $response);
        return false;
    }

    list($ok, $response) = $send('MAIL FROM:<' . $fromEmail . '>', [250]);
    if (!$ok) {
        fclose($socket);
        chamados_email_log('MAIL FROM recusado: ' . $response);
        return false;
    }
    list($ok, $response) = $send('RCPT TO:<' . $to . '>', [250, 251]);
    if (!$ok) {
        fclose($socket);
        chamados_email_log('RCPT TO recusado: ' . $response);
        return false;
    }
    list($ok, $response) = $send('DATA', [354]);
    if (!$ok) {
        fclose($socket);
        chamados_email_log('DATA recusado: ' . $response);
        return false;
    }

    $boundary = 'b' . md5(uniqid('', true));
    $headers = array(
        'Date: ' . date('r'),
        'To: <' . $to . '>',
        'From: ' . chamados_email_mime_address($fromEmail, $fromName),
        'Reply-To: ' . chamados_email_mime_address($replyTo, $replyName),
        'Subject: ' . chamados_email_header($subject),
        'Message-ID: <' . md5(uniqid('', true)) . '@' . preg_replace('/[^A-Za-z0-9\.-]/', '', $helloHost) . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    );
    $plain = strip_tags(str_replace(array('<br>', '<br/>', '<br />', '</p>'), array("\n", "\n", "\n", "\n\n"), $html));
    $plain = html_entity_decode($plain, ENT_QUOTES, 'UTF-8');

    $body = implode("\r\n", $headers) . "\r\n\r\n"
        . '--' . $boundary . "\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . chamados_email_escape_dots($plain) . "\r\n"
        . '--' . $boundary . "\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . chamados_email_escape_dots($html) . "\r\n"
        . '--' . $boundary . "--\r\n.";

    fwrite($socket, $body . "\r\n");
    list($ok, $response) = $expect([250]);
    $send('QUIT', [221]);
    fclose($socket);

    if (!$ok) {
        chamados_email_log('Falha ao finalizar DATA: ' . $response);
        return false;
    }

    return true;
}

function chamados_email_escape_dots(string $content): string
{
    $content = str_replace("\r\n", "\n", $content);
    $content = str_replace("\r", "\n", $content);
    $lines = explode("\n", $content);
    foreach ($lines as $index => $line) {
        if (isset($line[0]) && $line[0] === '.') {
            $lines[$index] = '.' . $line;
        }
    }
    return implode("\r\n", $lines);
}

function chamados_email_header(string $text): string
{
    return '=?UTF-8?B?' . base64_encode($text) . '?=';
}

function chamados_email_mime_address(string $email, string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return '<' . $email . '>';
    }
    return chamados_email_header($name) . ' <' . $email . '>';
}


function chamados_prioridade_label(string $prioridade): string
{
    $labels = function_exists('chamados_prioridade_labels') ? chamados_prioridade_labels() : array();
    $prioridade = strtolower(trim($prioridade));
    return isset($labels[$prioridade]) ? $labels[$prioridade] : ucfirst($prioridade !== '' ? $prioridade : 'Media');
}

function chamados_notificar_abertura(array $chamado): void
{
    $publicLink = chamados_email_public_link($chamado);
    $internalLink = chamados_email_internal_link($chamado);

    $htmlSolicitante = chamados_email_html_layout(
        'Seu chamado foi registrado com sucesso',
        'Recebemos seu chamado e nossa equipe já foi avisada. Guarde o protocolo abaixo para acompanhamento.',
        array(
            'Protocolo' => $chamado['protocolo'],
            'Solicitante' => $chamado['nome_solicitante'],
            'E-mail' => $chamado['email_solicitante'],
            'Categoria' => isset($chamado['categoria_nome']) ? $chamado['categoria_nome'] : '',
            'Assunto' => $chamado['assunto'],
            'Prioridade' => chamados_prioridade_label($chamado['prioridade']),
            'Status' => chamados_status_labels()[$chamado['status']],
        ),
        'Acompanhar chamado',
        $publicLink
    );
    chamados_email_send((string) $chamado['email_solicitante'], 'Chamado aberto - ' . $chamado['protocolo'], $htmlSolicitante);

    $config = chamados_email_config();
    if (!empty($config['ti_email'])) {
        $htmlTi = chamados_email_html_layout(
            'Novo chamado aberto',
            'Um novo chamado foi aberto e precisa de atendimento da equipe de TI.',
            array(
                'Protocolo' => $chamado['protocolo'],
                'Solicitante' => $chamado['nome_solicitante'],
                'E-mail' => $chamado['email_solicitante'],
                'Categoria' => isset($chamado['categoria_nome']) ? $chamado['categoria_nome'] : '',
                'Assunto' => $chamado['assunto'],
                'Prioridade' => chamados_prioridade_label($chamado['prioridade']),
                'Status' => chamados_status_labels()[$chamado['status']],
            ),
            'Abrir chamado no painel',
            $internalLink
        );
        chamados_email_send((string) $config['ti_email'], 'Novo chamado aberto - ' . $chamado['protocolo'], $htmlTi);
    }
}

function chamados_notificar_resposta(array $chamado, string $mensagem): void
{
    $html = chamados_email_html_layout(
        'Seu chamado recebeu uma resposta',
        'Nossa equipe registrou uma nova atualização no seu chamado.',
        array(
            'Protocolo' => $chamado['protocolo'],
            'Categoria' => isset($chamado['categoria_nome']) ? $chamado['categoria_nome'] : '',
            'Assunto' => $chamado['assunto'],
            'Status' => isset(chamados_status_labels()[$chamado['status']]) ? chamados_status_labels()[$chamado['status']] : $chamado['status'],
            'Resposta' => trim(preg_replace('/\s+/', ' ', $mensagem)),
        ),
        'Acompanhar chamado',
        chamados_email_public_link($chamado)
    );
    chamados_email_send((string) $chamado['email_solicitante'], 'Nova resposta - ' . $chamado['protocolo'], $html);
}

function chamados_notificar_resolucao(array $chamado): void
{
    $html = chamados_email_html_layout(
        'Chamado resolvido',
        'Seu chamado foi marcado como resolvido pela equipe de TI.',
        array(
            'Protocolo' => $chamado['protocolo'],
            'Categoria' => isset($chamado['categoria_nome']) ? $chamado['categoria_nome'] : '',
            'Assunto' => $chamado['assunto'],
            'Status' => isset(chamados_status_labels()[$chamado['status']]) ? chamados_status_labels()[$chamado['status']] : $chamado['status'],
        ),
        'Acompanhar chamado',
        chamados_email_public_link($chamado)
    );
    chamados_email_send((string) $chamado['email_solicitante'], 'Chamado resolvido - ' . $chamado['protocolo'], $html);
}
