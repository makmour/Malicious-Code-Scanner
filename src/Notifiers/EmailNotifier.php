<?php
declare(strict_types=1);

namespace MCS\Notifiers;

use MCS\NotifierInterface;
use PHPMailer\PHPMailer\PHPMailer;

final class EmailNotifier implements NotifierInterface
{
    /** @var array{to:string,smtp_host?:?string,smtp_user?:?string,smtp_pass?:?string,smtp_port?:int} */
    private array $cfg;

    /**
     * @param array{to:string,smtp_host?:?string,smtp_user?:?string,smtp_pass?:?string,smtp_port?:int} $cfg
     */
    public function __construct(array $cfg) { $this->cfg = $cfg; }

    public function notify(array $findings): void
    {
        if (empty($findings)) return;

        $mail = new PHPMailer(true);
        if (!empty($this->cfg['smtp_host'])) {
            $mail->isSMTP();
            $mail->Host = (string)$this->cfg['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = (string)($this->cfg['smtp_user'] ?? '');
            $mail->Password = (string)($this->cfg['smtp_pass'] ?? '');
            $mail->Port     = (int)($this->cfg['smtp_port'] ?? 587);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom($this->cfg['smtp_user'] ?? 'scanner@localhost', 'Malicious Code Scanner');
        $mail->addAddress($this->cfg['to']);
        $mail->Subject = '[MCS] Suspicious files detected';
        $mail->Body = json_encode($findings, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        $mail->AltBody = 'Suspicious files detected. See JSON body.';
        $mail->send();
    }
}
