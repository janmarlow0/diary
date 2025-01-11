<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$entry_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($entry_id === 0) {
    header("Location: diary.php");
    exit();
}

$stmt = $conn->prepare("SELECT encrypted_title, encrypted_content, iv FROM diary_entries WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $entry_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: diary.php");
    exit();
}

$entry = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    $encryption_key = bin2hex(random_bytes(16));
    $encrypted_title = encryptData($title, $encryption_key);
    $encrypted_content = encryptData($content, $encryption_key);
    
    // Save the encrypted data and IV
    $stmt = $conn->prepare("UPDATE diary_entries SET encrypted_title = ?, encrypted_content = ?, iv = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $encrypted_title, $encrypted_content, $encryption_key, $entry_id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: diary.php");
        exit();
    } else {
        $error = "Failed to update entry. Please try again.";
    }
    
    $stmt->close();
}

$pageTitle = "Edit Entry";
ob_start();
?>

<div class="container">
    <h1>Edit Entry</h1>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form action="" method="post" id="editForm">
        <input type="text" name="title" id="title" placeholder="Entry Title" required>
        <textarea name="content" id="content" placeholder="Your thoughts..." required rows="5"></textarea>
        <button type="submit">Update Entry</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to decrypt data using AES-GCM with Web Crypto API
    const decryptData = async (encryptedData, ivHex, encryptionKeyHex) => {
        const iv = hexStringToBuffer(ivHex);
        const key = await crypto.subtle.importKey(
            "raw", 
            hexStringToBuffer(encryptionKeyHex),
            { name: "AES-GCM" },
            false,
            ["decrypt"]
        );

        const decryptedData = await crypto.subtle.decrypt(
            { name: "AES-GCM", iv: iv },
            key,
            hexStringToBuffer(encryptedData)
        );

        const decoder = new TextDecoder();
        return decoder.decode(decryptedData);
    };

    // Helper function to convert hex strings to ArrayBuffers
    const hexStringToBuffer = (hexString) => {
        const buffer = new ArrayBuffer(hexString.length / 2);
        const view = new Uint8Array(buffer);
        for (let i = 0; i < hexString.length; i += 2) {
            view[i / 2] = parseInt(hexString.substr(i, 2), 16);
        }
        return buffer;
    };

    const title = document.getElementById('title');
    const content = document.getElementById('content');

    // Decrypt title and content from the encrypted values and display them
    const decryptTitle = async () => {
        const decryptedTitle = await decryptData('<?php echo $entry['encrypted_title']; ?>', '<?php echo $entry['iv']; ?>', '<?php echo bin2hex(random_bytes(16)); ?>');
        title.value = decryptedTitle;
    };

    const decryptContent = async () => {
        const decryptedContent = await decryptData('<?php echo $entry['encrypted_content']; ?>', '<?php echo $entry['iv']; ?>', '<?php echo bin2hex(random_bytes(16)); ?>');
        content.value = decryptedContent;
    };

    decryptTitle();
    decryptContent();
});
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>