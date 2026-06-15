<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Diagnostica connessione SMTP ===\n\n";
echo "Host:  " . SMTP_HOST . "\n";
echo "Porta: " . SMTP_PORT . "\n\n";

// Test 1: risoluzione DNS
$ip = gethostbyname(SMTP_HOST);
if ($ip === SMTP_HOST) {
    echo "❌ DNS: impossibile risolvere " . SMTP_HOST . "\n";
} else {
    echo "✅ DNS: " . SMTP_HOST . " -> " . $ip . "\n";
}

// Test 2: connessione TCP sulla porta SMTP
echo "\nTentativo connessione TCP (timeout 5s)...\n";
$errno  = 0;
$errstr = '';
$sock = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 5);
if ($sock) {
    echo "✅ Porta " . SMTP_PORT . " raggiungibile\n";
    fclose($sock);
} else {
    echo "❌ Porta " . SMTP_PORT . " NON raggiungibile: [{$errno}] {$errstr}\n";
    echo "   → Il firewall del server probabilmente blocca le connessioni SMTP in uscita.\n";
}

// Test 3: porta alternativa
$altPort = (SMTP_PORT === 587) ? 465 : 587;
echo "\nTest porta alternativa {$altPort}...\n";
$sock2 = @fsockopen(SMTP_HOST, $altPort, $errno, $errstr, 5);
if ($sock2) {
    echo "✅ Porta {$altPort} raggiungibile\n";
    fclose($sock2);
} else {
    echo "❌ Porta {$altPort} NON raggiungibile: [{$errno}] {$errstr}\n";
}

// Test 4: funzione mail() nativa
echo "\nTest mail() nativa PHP...\n";
$sent = @mail('test@example.invalid', 'test', 'test');
echo $sent ? "✅ mail() accettata dal sistema\n" : "❌ mail() non disponibile\n";

echo "\nFine diagnostica.\n";
