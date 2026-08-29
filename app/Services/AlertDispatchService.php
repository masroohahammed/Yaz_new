<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Email\Email;

/**
 * Email + optional WhatsApp webhook alerts alongside in-app notifications.
 */
class AlertDispatchService
{
    public function __construct(
        private BaseConnection $db,
        private array $settings = []
    ) {
        if ($settings === []) {
            foreach ($this->db->table('system_settings')->get()->getResultArray() as $row) {
                $this->settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }

    public function notifyUser(int $userId, string $title, string $message, string $type = 'general', ?int $refId = null): void
    {
        try {
            $this->db->table('notifications')->insert([
                'user_id'      => $userId,
                'title'        => $title,
                'message'      => $message,
                'type'         => $type,
                'reference_id' => $refId,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'notification insert: ' . $e->getMessage());
        }

        $user = $this->db->table('users')->select('email, phone, name')->where('id', $userId)->get()->getRowArray();
        if ($user) {
            $this->sendEmail((string) ($user['email'] ?? ''), $title, $message);
            $this->sendWhatsApp((string) ($user['phone'] ?? ''), $title . ': ' . $message);
        }
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        if (($this->settings['alert_email_enabled'] ?? '1') !== '1' || $to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        try {
            $email = service('email');
            $from = $this->settings['company_email'] ?? 'noreply@localhost';
            $email->setFrom($from, $this->settings['company_name'] ?? 'FM ERP');
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage(nl2br(esc($body)));
            $email->setMailType('html');

            return $email->send(false);
        } catch (\Throwable $e) {
            log_message('warning', 'Email alert skipped: ' . $e->getMessage());

            return false;
        }
    }

    public function sendWhatsApp(string $phone, string $text): bool
    {
        if (($this->settings['alert_whatsapp_enabled'] ?? '0') !== '1') {
            return false;
        }
        $url = trim((string) ($this->settings['alert_whatsapp_webhook'] ?? ''));
        if ($url === '') {
            return false;
        }
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => json_encode([
                    'phone'   => $phone,
                    'message' => $text,
                ]),
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $code >= 200 && $code < 300;
        } catch (\Throwable $e) {
            log_message('warning', 'WhatsApp webhook: ' . $e->getMessage());

            return false;
        }
    }
}
