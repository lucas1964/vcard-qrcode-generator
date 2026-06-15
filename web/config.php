<?php
// ---------------------------------------------------------------------------
// Configurazione — compilare prima del deploy
// ---------------------------------------------------------------------------

// Database MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_database');
define('DB_USER', 'utente_db');
define('DB_PASS', 'password_db');
define('DB_CHARSET', 'utf8mb4');

// SMTP
define('SMTP_HOST',       'smtp.example.com');
define('SMTP_PORT',       587);               // 587 TLS oppure 465 SSL
define('SMTP_ENCRYPTION', 'tls');             // 'tls' oppure 'ssl'
define('SMTP_USER',       'noreply@example.com');
define('SMTP_PASS',       'password_smtp');
define('SMTP_FROM_NAME',  'QR Code Generator');

// Admin (email dell'account amministratore)
define('ADMIN_EMAIL', 'admin@example.com');

// OTP: durata in minuti
define('OTP_EXPIRE_MINUTES', 10);
