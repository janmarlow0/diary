<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch diary entries for the current user
$stmt = $conn->prepare("SELECT id, encrypted_title, encrypted_content, iv, created_at FROM diary_entries WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "View My Entries";
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js"></script>
    <style>
        :root {
            --primary: #6366F1;
            --primary-dark: #4F46E5;
            --background: #F0F9FF;
            --nav-footer: #BFDBFE;
            --text-primary: #1E293B;
            --text-secondary: #475569;
            --card-bg: white;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: var(--nav-footer);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 2rem;
            flex: 1;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 3rem;
        }

        .welcome-section h1 {
            color: var(--text-primary);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .entries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .entry {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            transition: transform 0.2s ease;
        }

        .entry:hover {
            transform: translateY(-5px);
        }

        .entry h3 {
            color: var(--text-primary);
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .entry p {
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .entry-date {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .entry-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: #E2E8F0;
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: #CBD5E1;
        }

        .btn-info {
            background: #60A5FA;
            color: white;
        }

        .btn-info:hover {
            background: #3B82F6;
        }

        .btn-danger {
            background: #EF4444;
            color: white;
        }

        .btn-danger:hover {
            background: #DC2626;
        }

        .footer {
            background: var(--nav-footer);
            padding: 2rem;
            text-align: center;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-links a {
            margin: 0 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .social-links a {
            margin-left: 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .social-links a:hover {
            color: var(--primary);
        }

        .social-links i {
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 80px;
                padding: 1rem;
            }
            
            .entries-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .social-links {
                margin-top: 1rem;
            }

            .nav-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="index.php" class="logo">
                <i class="fas fa-book-open"></i>
                My Diary
            </a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="diary.php">New Entry</a>
                <a href="view_entries.php">View Entries</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Your Diary Entries</h1>
            <p>View and manage all your personal diary entries</p>
        </div>

        <div class="card">
            <h2>Your Entries</h2>
            <?php if (empty($entries)): ?>
                <p>You haven't added any entries yet.</p>
            <?php else: ?>
                <div class="entries-grid">
                    <?php foreach ($entries as $entry): ?>
                        <div class="entry" data-entry-id="<?php echo $entry['id']; ?>">
                            <h3 class="encrypted-title" data-encrypted="<?php echo htmlspecialchars($entry['encrypted_title']); ?>" data-key="<?php echo htmlspecialchars($entry['iv']); ?>">
                                [Encrypted Title]
                            </h3>
                            <p class="encrypted-content" data-encrypted="<?php echo htmlspecialchars($entry['encrypted_content']); ?>" data-key="<?php echo htmlspecialchars($entry['iv']); ?>">
                                [Encrypted Content]
                            </p>
                            <p class="entry-date">Created at: <?php echo htmlspecialchars($entry['created_at']); ?></p>
                            <div class="entry-actions">
                                <button class="decrypt-btn btn btn-primary">Decrypt</button>
                                <button class="encrypt-btn btn btn-secondary" style="display:none;">Encrypt</button>
                                <button class="edit-btn btn btn-info">Edit</button>
                                <button class="delete-btn btn btn-danger">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Support</a>
            </div>
            <div class="social-links">
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Decrypt button
        document.querySelectorAll('.decrypt-btn').forEach(button => {
            button.addEventListener('click', function() {
                const entry = button.closest('.entry');
                const encryptedTitle = entry.querySelector('.encrypted-title').getAttribute('data-encrypted');
                const encryptedContent = entry.querySelector('.encrypted-content').getAttribute('data-encrypted');
                const iv = entry.querySelector('.encrypted-title').getAttribute('data-key');

                // Show loading indicator
                button.textContent = 'Decrypting...';
                button.disabled = true;

                // Decrypt data
                const decryptedTitle = decryptData(encryptedTitle, iv);
                const decryptedContent = decryptData(encryptedContent, iv);

                // Update UI
                entry.querySelector('.encrypted-title').textContent = decryptedTitle;
                entry.querySelector('.encrypted-content').textContent = decryptedContent;

                // Hide decrypt button and show encrypt button
                button.style.display = 'none';
                entry.querySelector('.encrypt-btn').style.display = 'inline-block';
            });
        });

        // Encrypt button
        document.querySelectorAll('.encrypt-btn').forEach(button => {
            button.addEventListener('click', function() {
                const entry = button.closest('.entry');
                const originalTitle = entry.querySelector('.encrypted-title').textContent;
                const originalContent = entry.querySelector('.encrypted-content').textContent;
                const iv = entry.querySelector('.encrypted-title').getAttribute('data-key');

                // Show loading indicator
                button.textContent = 'Encrypting...';
                button.disabled = true;

                // Encrypt data
                const encryptedTitle = encryptData(originalTitle, iv);
                const encryptedContent = encryptData(originalContent, iv);

                // Revert text to "[Encrypted Title]" and "[Encrypted Content]"
                entry.querySelector('.encrypted-title').textContent = '[Encrypted Title]';
                entry.querySelector('.encrypted-content').textContent = '[Encrypted Content]';
                entry.querySelector('.encrypted-title').setAttribute('data-encrypted', encryptedTitle);
                entry.querySelector('.encrypted-content').setAttribute('data-encrypted', encryptedContent);

                // Hide encrypt button and show decrypt button
                button.style.display = 'none';
                entry.querySelector('.decrypt-btn').style.display = 'inline-block';
            });
        });

        // Edit button functionality
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const entry = button.closest('.entry');
                const entryId = entry.getAttribute('data-entry-id');
                const encryptedTitle = entry.querySelector('.encrypted-title').getAttribute('data-encrypted');
                const encryptedContent = entry.querySelector('.encrypted-content').getAttribute('data-encrypted');
                const iv = entry.querySelector('.encrypted-title').getAttribute('data-key');

                const decryptedTitle = decryptData(encryptedTitle, iv);
                const decryptedContent = decryptData(encryptedContent, iv);

                const newTitle = prompt("Edit Title", decryptedTitle);
                const newContent = prompt("Edit Content", decryptedContent);

                if (newTitle && newContent) {
                    const encryptedNewTitle = encryptData(newTitle, iv);
                    const encryptedNewContent = encryptData(newContent, iv);

                    // Send updated data to the server for saving
                    updateEntry(entryId, encryptedNewTitle, encryptedNewContent, iv);
                }
            });
        });

        // Delete button functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const entry = button.closest('.entry');
                const entryId = entry.getAttribute('data-entry-id');

                if (confirm("Are you sure you want to delete this entry?")) {
                    deleteEntry(entryId);
                }
            });
        });
    });

    // Decrypt data using CryptoJS
    function decryptData(encryptedData, iv) {
        const key = CryptoJS.enc.Base64.parse("your-secret-key"); // Replace with your secret key
        const ivWordArray = CryptoJS.enc.Hex.parse(iv);
        const decrypted = CryptoJS.AES.decrypt(encryptedData, key, { iv: ivWordArray });
        return decrypted.toString(CryptoJS.enc.Utf8);
    }

    // Encrypt data using CryptoJS
    function encryptData(data, iv) {
        const key = CryptoJS.enc.Base64.parse("your-secret-key"); // Replace with your secret key
        const ivWordArray = CryptoJS.enc.Hex.parse(iv);
        const encrypted = CryptoJS.AES.encrypt(data, key, { iv: ivWordArray });
        return encrypted.toString();
    }

    // Update entry in the database
    function updateEntry(entryId, encryptedTitle, encryptedContent, iv) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_entry.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert("Entry updated successfully!");
                location.reload();  // Reload to see the updated data
            } else {
                alert("Error updating entry.");
            }
        };
        xhr.send("id=" + entryId + "&encrypted_title=" + encodeURIComponent(encryptedTitle) + "&encrypted_content=" + encodeURIComponent(encryptedContent) + "&iv=" + iv);
    }

    // Delete entry from the database
    function deleteEntry(entryId) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_entry.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert("Entry deleted successfully!");
                location.reload();  // Reload to remove the entry from the page
            } else {
                alert("Error deleting entry.");
            }
        };
        xhr.send("id=" + entryId);
    }
</script>
</body>
</html>