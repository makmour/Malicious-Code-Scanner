<?php
declare(strict_types=1);

namespace MCS\Notifiers;

use MCS\NotifierInterface;

final class WebhookNotifier implements NotifierInterface
{
    private string $url;
    public function __construct(string $url) { $this->url = $url; }

    public function notify(array $findings): void
    {
        if (empty($findings)) return;

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => json_encode(['findings' => $findings], JSON_UNESCAPED_SLASHES)
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]
        ]);

        @file_get_contents($this->url, false, $ctx);
    }
}
