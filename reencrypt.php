<?php
require_once 'config.php';

try {
    // Get all entries
    $stmt = $conn->prepare("SELECT id, encrypted_title, encrypted_content, iv FROM diary_entries");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Generate new key
        $new_key = bin2hex(random_bytes(32));
        
        // Try to decrypt with old method
        try {
            $title = decryptData($row['encrypted_title'], $row['iv']);
            $content = decryptData($row['encrypted_content'], $row['iv']);
            
            if ($title !== false && $content !== false) {
                // Re-encrypt with new method
                $new_encrypted_title = encryptData($title, $new_key);
                $new_encrypted_content = encryptData($content, $new_key);
                
                // Update the entry
                $update_stmt = $conn->prepare("UPDATE diary_entries SET encrypted_title = ?, encrypted_content = ?, iv = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $new_encrypted_title, $new_encrypted_content, $new_key, $row['id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                echo "Successfully re-encrypted entry {$row['id']}\n";
            }
        } catch (Exception $e) {
            echo "Failed to process entry {$row['id']}: {$e->getMessage()}\n";
        }
    }
    
    echo "Re-encryption complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>