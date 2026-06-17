<?php
declare(strict_types=1);

function get_real_ip(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP) ?: 'unknown';
    }
    return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?: 'unknown';
}

function write_log(string $event, ?int $userId = null, array $detail = []): void {
    try {
        db()->prepare(
            'INSERT INTO logs (user_id, event, detail, ip) VALUES (?, ?, ?, ?)'
        )->execute([
            $userId,
            $event,
            $detail ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null,
            get_real_ip(),
        ]);
    } catch (Throwable) {
        // Il log non deve mai bloccare l'applicazione
    }
}
