<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle adding a new diary entry
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['title']) && isset($_POST['content'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        
        // Generate a random encryption key
        $encryption_key = bin2hex(random_bytes(32));
        
        // Encrypt the title and content
        $encrypted_title = encryptData($title, $encryption_key);
        $encrypted_content = encryptData($content, $encryption_key);
        
        $stmt = $conn->prepare("INSERT INTO diary_entries (user_id, encrypted_title, encrypted_content, iv) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $encrypted_title, $encrypted_content, $encryption_key);
        
        if ($stmt->execute()) {
            $success = "Diary entry added successfully.";
        } else {
            $error = "Failed to add diary entry. Please try again.";
        }
        
        $stmt->close();
    }
}

// Fetch diary entries for the current user
$stmt = $conn->prepare("SELECT id, encrypted_title, encrypted_content, iv, created_at FROM diary_entries WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "My Diary";
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .card h2 {
            color: var(--text-primary);
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
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

        form input,
        form textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            margin-bottom: 1rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        form input:focus,
        form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        form button {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.125rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        form button:hover {
            background: var(--primary-dark);
        }

        .success {
            color: #059669;
            background: #D1FAE5;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .error {
            color: #DC2626;
            background: #FEE2E2;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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
        margin: 20px;
        padding: 2rem;
    }
    
    .footer-content {
        flex-direction: column;
        gap: 1rem;
    }
    
    .social-links {
        margin-top: 1rem;
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
                <a href="view_entries.php">View My Entries</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>Create and manage your personal diary entries</p>
        </div>

        <div class="card">
            <h2>Add New Entry</h2>
            <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form action="" method="post" id="diaryForm">
                <input type="text" name="title" placeholder="Entry Title" required>
                <textarea name="content" placeholder="Your thoughts..." required rows="5"></textarea>
                <button type="submit">Save Entry</button>
            </form>
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
</body>
</html>