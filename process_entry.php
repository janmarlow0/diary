<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

require_once 'config.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    if (!isset($_POST['action']) || !isset($_POST['entry_id'])) {
        throw new Exception('Missing required parameters');
    }

    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];
    $entry_id = filter_var($_POST['entry_id'], FILTER_VALIDATE_INT);
    $password = $_POST['password'] ?? '';

    if ($entry_id === false) {
        throw new Exception('Invalid entry ID');
    }

    // Verify user password
    $stmt = $conn->prepare("SELECT password, salt FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !verifyPassword($password, $user['password'], $user['salt'])) {
        throw new Exception('Invalid password');
    }

    // Fetch the entry and verify ownership
    $stmt = $conn->prepare("SELECT encrypted_title, encrypted_content, iv FROM diary_entries WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $entry = $result->fetch_assoc();
    $stmt->close();

    if (!$entry) {
        throw new Exception('Entry not found or access denied');
    }

    $response = ['success' => true];

    switch ($action) {
        case 'decrypt':
            $decrypted_title = decryptData($entry['encrypted_title'], $entry['iv']);
            $decrypted_content = decryptData($entry['encrypted_content'], $entry['iv']);
            
            if ($decrypted_title === '' || $decrypted_content === '') {
                throw new Exception('Decryption failed');
            }
            
            $response['title'] = $decrypted_title;
            $response['content'] = $decrypted_content;
            break;

        case 'encrypt':
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }

            $new_key = bin2hex(random_bytes(32));
            $encrypted_title = encryptData($title, $new_key);
            $encrypted_content = encryptData($content, $new_key);

            $stmt = $conn->prepare("UPDATE diary_entries SET encrypted_title = ?, encrypted_content = ?, iv = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssii", $encrypted_title, $encrypted_content, $new_key, $entry_id, $user_id);

            if (!$stmt->execute()) {
                throw new Exception('Failed to encrypt entry');
            }
            $stmt->close();
            $response['message'] = 'Entry encrypted successfully';
            break;

        case 'edit':
            $new_title = $_POST['title'] ?? '';
            $new_content = $_POST['content'] ?? '';
            
            if (empty($new_title) || empty($new_content)) {
                throw new Exception('Title and content are required');
            }

            $new_key = bin2hex(random_bytes(32));
            $encrypted_title = encryptData($new_title, $new_key);
            $encrypted_content = encryptData($new_content, $new_key);

            $stmt = $conn->prepare("UPDATE diary_entries SET encrypted_title = ?, encrypted_content = ?, iv = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssii", $encrypted_title, $encrypted_content, $new_key, $entry_id, $user_id);

            if (!$stmt->execute()) {
                throw new Exception('Failed to update entry');
            }
            $stmt->close();
            $response['message'] = 'Entry updated successfully';
            break;

        case 'delete':
            $stmt = $conn->prepare("DELETE FROM diary_entries WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $entry_id, $user_id);

            if (!$stmt->execute()) {
                throw new Exception('Failed to delete entry');
            }
            $stmt->close();
            $response['message'] = 'Entry deleted successfully';
            break;

        default:
            throw new Exception('Invalid action');
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Diary Entry Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>