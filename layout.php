<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - My Diary</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="app-container">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-content">
                    <a href="/" class="navbar-brand">
                        <i class="fas fa-book-lock"></i>
                        <span>My Diary</span>
                    </a>
                    <div class="navbar-menu">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="diary.php">My Diary</a>
                            <a href="logout.php">Logout</a>
                        <?php else: ?>
                            <a href="login.php">Login</a>
                            <a href="register.php">Register</a>
                        <?php endif; ?>
                        <button id="themeToggle" class="btn btn-icon">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        <main class="content">
            <?php echo $content; ?>
        </main>
        <footer class="footer">
            <div class="footer-content">
                <p>&copy; <?php echo date('Y'); ?> My Diary. All rights reserved.</p>
                
            </div>
        </footer>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script src="script.js"></script>
</body>
</html>

