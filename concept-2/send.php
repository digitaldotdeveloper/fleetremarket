<?php
/**
 * Fleet Remarketing — form mailer
 * Receives the website forms (JSON or classic POST) and emails them to to_email.
 * Settings live in config.php next to this file.
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$cfg = require __DIR__ . '/config.php';
if (is_file(__DIR__ . '/config.local.php')) $cfg = array_replace_recursive($cfg, require __DIR__ . '/config.local.php');   // optional local overrides

function out(bool $ok, string $msg, int $code = 200): never {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Method not allowed', 405);

// ---- read input (JSON from the site, or a normal form post)
$d = [];
if (str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $d = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
} else {
    $d = $_POST;
}
$f = fn(string $k, int $max = 300) => mb_substr(trim((string)($d[$k] ?? '')), 0, $max);

// ---- spam gates
if ($f('_honey') !== '') out(true, 'ok');                       // bot filled the hidden field: pretend success
$rateFile = sys_get_temp_dir() . '/fr-form-' . md5($_SERVER['REMOTE_ADDR'] ?? '') . '.txt';
$hits = is_file($rateFile) ? array_filter(array_map('intval', file($rateFile)), fn($t) => $t > time() - 3600) : [];
if (count($hits) >= (int)$cfg['max_per_hour']) out(false, 'Too many requests, please try again later.', 429);
$hits[] = time();
@file_put_contents($rateFile, implode("\n", $hits));

// ---- validate
$name    = $f('name', 120);
$email   = $f('email', 200);
$phone   = $f('phone', 60);
$dealer  = $f('dealer', 160);
$topic   = $f('topic', 120);
$message = $f('message', 4000);
$page    = $f('page', 300);
$subject = $f('_subject', 150) ?: $cfg['subject_default'];

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) out(false, 'Please enter your name and a valid email.', 422);
foreach ([$name, $email, $phone, $dealer, $topic, $subject] as $v) {
    if (preg_match('/[\r\n]/', $v)) out(false, 'Invalid input.', 422);   // header-injection guard
}

// ---- build the email (plain text + simple HTML table)
$rows = array_filter([
    'Name'    => $name,
    'Dealer'  => $dealer,
    'Email'   => $email,
    'Phone'   => $phone,
    'Topic'   => $topic,
    'Message' => $message,
    'Page'    => $page,
    'IP'      => $_SERVER['REMOTE_ADDR'] ?? '',
    'Sent'    => date('Y-m-d H:i:s T'),
], fn($v) => $v !== '');

$text = '';
$html = '<table cellpadding="8" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px">';
foreach ($rows as $k => $v) {
    $text .= str_pad($k . ':', 10) . $v . "\n";
    $html .= '<tr><td style="border:1px solid #ddd;background:#f5f5f5;font-weight:bold;vertical-align:top">' . htmlspecialchars($k)
           . '</td><td style="border:1px solid #ddd;white-space:pre-wrap">' . nl2br(htmlspecialchars($v)) . '</td></tr>';
}
$html .= '</table>';

$sent = ($cfg['transport'] === 'smtp')
    ? smtp_send($cfg, $subject, $text, $html, $email, $name)
    : php_mail_send($cfg, $subject, $text, $html, $email, $name);

$sent ? out(true, 'Message sent.') : out(false, 'Could not send the message right now.', 500);

// =====================================================================
function mime_body(string $text, string $html, string $boundary): string {
    return "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n$text\r\n"
         . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n$html\r\n--$boundary--\r\n";
}
function enc(string $s): string { return '=?UTF-8?B?' . base64_encode($s) . '?='; }

/** Transport A: the hosting's own mail() (cPanel/Exim). From must be an address on the site's domain. */
function php_mail_send(array $cfg, string $subject, string $text, string $html, string $replyTo, string $replyName): bool {
    $b = 'b' . bin2hex(random_bytes(12));
    $headers = "From: " . enc($cfg['from_name']) . " <{$cfg['from_email']}>\r\n"
             . "Reply-To: " . enc($replyName) . " <$replyTo>\r\n"
             . "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=\"$b\"\r\nX-Mailer: FleetRemarketing-Form";
    $ok = @mail($cfg['to_email'], enc($subject), mime_body($text, $html, $b), $headers, '-f' . $cfg['from_email']);
    if (!$ok) error_log('mail() failed: ' . (error_get_last()['message'] ?? 'unknown'));
    return $ok;
}

/** Transport B: authenticated SMTP with STARTTLS (e.g. smtp.gmail.com:587 + Gmail app password). No libraries needed. */
function smtp_send(array $cfg, string $subject, string $text, string $html, string $replyTo, string $replyName): bool {
    $s = $cfg['smtp'];
    $fp = @stream_socket_client("tcp://{$s['host']}:{$s['port']}", $errno, $errstr, 15);
    if (!$fp) { error_log("SMTP connect failed: $errstr"); return false; }
    stream_set_timeout($fp, 15);
    $read = function () use ($fp): string {
        $out = '';
        while (($line = fgets($fp, 515)) !== false) { $out .= $line; if (isset($line[3]) && $line[3] === ' ') break; }
        return $out;
    };
    $cmd = function (string $c, string $expect) use ($fp, $read): bool {
        fwrite($fp, $c . "\r\n");
        $r = $read();
        if (!str_starts_with($r, $expect)) { error_log("SMTP error after [" . substr($c, 0, 12) . "]: " . trim($r)); return false; }
        return true;
    };
    $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $read();
    if (!$cmd("EHLO $host", '250')) return false;
    if (!$cmd('STARTTLS', '220')) return false;
    if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { error_log('SMTP TLS failed'); return false; }
    if (!$cmd("EHLO $host", '250')) return false;
    if (!$cmd('AUTH LOGIN', '334')) return false;
    if (!$cmd(base64_encode($s['user']), '334')) return false;
    if (!$cmd(base64_encode($s['pass']), '235')) return false;
    if (!$cmd("MAIL FROM:<{$cfg['from_email']}>", '250')) return false;
    if (!$cmd("RCPT TO:<{$cfg['to_email']}>", '250')) return false;
    if (!$cmd('DATA', '354')) return false;
    $b = 'b' . bin2hex(random_bytes(12));
    $data = "From: " . enc($cfg['from_name']) . " <{$cfg['from_email']}>\r\n"
          . "To: <{$cfg['to_email']}>\r\n"
          . "Reply-To: " . enc($replyName) . " <$replyTo>\r\n"
          . "Subject: " . enc($subject) . "\r\n"
          . "Date: " . date(DATE_RFC2822) . "\r\n"
          . "Message-ID: <" . bin2hex(random_bytes(8)) . "@$host>\r\n"
          . "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=\"$b\"\r\n\r\n"
          . mime_body($text, $html, $b);
    $data = preg_replace('/^\./m', '..', $data);
    fwrite($fp, $data . "\r\n.\r\n");
    $ok = str_starts_with($read(), '250');
    fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}
