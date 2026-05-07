<?php

class Mailer {
    private string $to;
    private string $toName = '';
    private string $subject = '';
    private string $body = '';
    private string $altBody = '';
    private array $cc = [];
    private array $bcc = [];
    private array $attachments = [];
    private array $headers = [];

    public function to(string $email, string $name = ''): self {
        $this->to     = $email;
        $this->toName = $name;
        return $this;
    }

    public function cc(string $email): self { $this->cc[] = $email; return $this; }
    public function bcc(string $email): self { $this->bcc[] = $email; return $this; }

    public function subject(string $subject): self {
        $this->subject = $subject;
        return $this;
    }

    public function html(string $html): self {
        $this->body = $html;
        return $this;
    }

    public function text(string $text): self {
        $this->altBody = $text;
        return $this;
    }

    public function template(string $name, array $data = []): self {
        $templateFile = VIEWS_PATH . "/emails/{$name}.php";
        if (!file_exists($templateFile)) {
            throw new RuntimeException("Email template [$name] not found");
        }
        extract($data);
        ob_start();
        include $templateFile;
        $this->body = ob_get_clean();
        return $this;
    }

    public function attach(string $filePath, string $filename = ''): self {
        $this->attachments[] = ['path' => $filePath, 'name' => $filename ?: basename($filePath)];
        return $this;
    }

    public function send(): bool {
        $cfg = config('mail', []);
        $driver = $cfg['driver'] ?? 'mail';

        try {
            switch ($driver) {
                case 'smtp': return $this->sendSmtp($cfg);
                case 'log':  return $this->logEmail();
                default:     return $this->sendNative($cfg);
            }
        } catch (Throwable $e) {
            error_log("Mailer error: " . $e->getMessage());
            $this->logEmailError($e->getMessage());
            return false;
        }
    }

    private function sendSmtp(array $cfg): bool {
        // PHPMailer integration (if available via composer)
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'] ?? 'tls';
            $mail->Port       = (int)($cfg['port'] ?? 587);
            $mail->setFrom($cfg['from_address'] ?? 'noreply@example.com', $cfg['from_name'] ?? 'App');
            $mail->addAddress($this->to, $this->toName);
            foreach ($this->cc  as $cc)  $mail->addCC($cc);
            foreach ($this->bcc as $bcc) $mail->addBCC($bcc);
            foreach ($this->attachments as $a) $mail->addAttachment($a['path'], $a['name']);
            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body    = $this->body;
            $mail->AltBody = $this->altBody ?: strip_tags($this->body);
            $result = $mail->send();
            $this->logEmailResult($result ? 'sent' : 'failed');
            return $result;
        }
        return $this->sendNative($cfg);
    }

    private function sendNative(array $cfg): bool {
        $from = $cfg['from_address'] ?? 'noreply@example.com';
        $fromName = $cfg['from_name'] ?? 'App';
        $headers  = "From: {$fromName} <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $result = mail($this->to, $this->subject, $this->body, $headers);
        $this->logEmailResult($result ? 'sent' : 'failed');
        return $result;
    }

    private function logEmail(): bool {
        $logFile = STORAGE_PATH . '/logs/emails.log';
        $entry   = "[" . date('Y-m-d H:i:s') . "] To: {$this->to} | Subject: {$this->subject}\n";
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        $this->logEmailResult('sent');
        return true;
    }

    private function logEmailResult(string $status): void {
        try {
            Database::insert('email_logs', [
                'to_email' => $this->to,
                'subject'  => $this->subject,
                'template' => '',
                'status'   => $status,
                'sent_at'  => $status === 'sent' ? date('Y-m-d H:i:s') : null,
            ]);
        } catch (Throwable) {}
    }

    private function logEmailError(string $message): void {
        try {
            Database::insert('email_logs', [
                'to_email' => $this->to,
                'subject'  => $this->subject,
                'status'   => 'failed',
                'error'    => substr($message, 0, 500),
            ]);
        } catch (Throwable) {}
    }

    // Static factory
    public static function make(): self {
        return new self();
    }
}
