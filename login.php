<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, salt FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (verifyPassword($password, $user['password'], $user['salt'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: diary.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
    $stmt->close();
}

$pageTitle = "Login";
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

.nav-links a {
    margin-left: 2rem;
    color: var(--text-primary);
    text-decoration: none;
}

.container {
    width: 100%;
    max-width: 560px;
    margin: 40px auto;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 1.8);
    flex: 1;
}

.form-content {
    max-width: 400px;
    margin: 0 auto;
}

h1 {
    text-align: center;
    color: var(--text-primary);
    margin-bottom: 2rem;
    font-size: 2.25rem;
}

.input-group {
    position: relative;
    margin-bottom: 1.75rem;
    width: 100%;
}

input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #E2E8F0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--text-secondary);
}

button {
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

button:hover {
    background: var(--primary-dark);
}

.error {
    color: #DC2626;
    text-align: center;
    padding: 1rem;
    margin-bottom: 1.5rem;
    background: #FEE2E2;
    border-radius: 8px;
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
}
</style>
</head>
<body>
<nav class="navbar">
    <div class="nav-content">
        <div class="logo">
        <i class="fas fa-book-open"></i>
        My Diary</div>
    </div>
</nav>
<br>
<br>
<br>
<div class="container">
    <div class="form-content">
        <h1>Login</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="post">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="toggle-password fas fa-eye" onclick="togglePassword(this)"></i>
            </div>
            <button type="submit">Login</button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-secondary);">
            Don't have an account? <a href="register.php" style="color: var(--primary);">Register here</a>
        </p>
    </div>
</div>
<br>
<br>
<br>
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
function togglePassword(icon) {
    const input = icon.previousElementSibling;
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>