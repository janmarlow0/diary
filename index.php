<?php
require_once 'config.php';

$pageTitle = "Welcome to Secure Diary";
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
:root {
    --primary: #6366F1;
    --primary-dark: #4F46E5;
    --background: #F0F9FF;
    --nav-footer: #BFDBFE;
    --text-primary: #1E293B;
    --text-secondary: #475569;
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
}

.container {
    width: 100%;
    max-width: 560px;
    margin: 40px auto;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    flex: 1;
}

h1 {
    text-align: center;
    color: var(--text-primary);
    margin-bottom: 2rem;
    font-size: 2.25rem;
}

h2 {
    color: var(--text-primary);
    margin-top: 2rem;
    font-size: 1.5rem;
}

.features {
    margin: 2rem 0;
}

.features ul {
    list-style-type: none;
    padding: 0;
}

.features li {
    color: var(--text-secondary);
    margin: 1rem 0;
    padding-left: 1.5rem;
    position: relative;
}

.features li:before {
    content: "•";
    color: var(--primary);
    position: absolute;
    left: 0;
}

.cta {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    padding: 1rem 2rem;
    border-radius: 8px;
    font-size: 1.125rem;
    font-weight: 500;
    text-decoration: none;
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
    background: white;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-secondary:hover {
    background: var(--background);
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

.copyright {
    text-align: center;
    padding: 1rem;
    background: var(--nav-footer);
    color: var(--text-secondary);
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
}
</style>
</head>
<body>
<nav class="navbar">
    <div class="nav-content">
        <div class="logo">
            <i class="fas fa-book-open"></i>
            My Diary
        </div>
    </div>
</nav>

<div class="container">
    <h1>Welcome to Secure Diary</h1>
    <p style="color: var(--text-secondary); text-align: center;">Secure Diary is a platform where you can safely store your personal thoughts and memories.</p>
    <div class="features">
        <h2>Features:</h2>
        <ul>
            <li>End-to-end encryption for your entries</li>
            <li>Secure user authentication</li>
            <li>Easy-to-use interface</li>
        </ul>
    </div>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="cta">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-secondary">Register</a>
        </div>
    <?php else: ?>
        <div class="cta">
            <a href="diary.php" class="btn btn-primary">Go to My Diary</a>
        </div>
    <?php endif; ?>
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
