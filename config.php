<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'secure_diary');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session if it hasn't been started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($encrypted . '::' . base64_encode($iv));
}

function decryptData($encryptedData, $key) {
    try {
        list($encrypted, $iv) = explode('::', base64_decode($encryptedData), 2);
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            0,
            base64_decode($iv)
        );
        return $decrypted !== false ? $decrypted : '';
    } catch (Exception $e) {
        error_log("Decryption error: " . $e->getMessage());
        return '';
    }
}

function generateSalt($length = 16) {
    return bin2hex(random_bytes($length));
}

function hashPassword($password, $salt) {
    return hash('sha256', $password . $salt);
}

function verifyPassword($password, $hashedPassword, $salt) {
    return hash('sha256', $password . $salt) === $hashedPassword;
}
?>

