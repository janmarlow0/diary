<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'], $_POST['encrypted_title'], $_POST['encrypted_content'], $_POST['iv'])) {
        $id = $_POST['id'];
        $encrypted_title = $_POST['encrypted_title'];
        $encrypted_content = $_POST['encrypted_content'];
        $iv = $_POST['iv'];

        // Update the entry in the database
        $stmt = $conn->prepare("UPDATE diary_entries SET encrypted_title = ?, encrypted_content = ?, iv = ? WHERE id = ?");
        $stmt->bind_param("sssi", $encrypted_title, $encrypted_content, $iv, $id);
        $stmt->execute();
        $stmt->close();

        echo "Entry updated!";
    }
}
