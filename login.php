<?php
// login.php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';


if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        if (login($username, $password)) {
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}

require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TodoList App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .glass-dark {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Title -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">TodoList</h1>
            <p class="text-white/80">Organize your tasks beautifully</p>
        </div>

        <!-- Login Form -->
        <div class="glass rounded-2xl p-8 shadow-xl">
            <h2 class="text-2xl font-semibold text-white mb-6 text-center">Welcome Back</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-100 px-4 py-3 rounded-lg mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-white/90 text-sm font-medium mb-2">Username or Email</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="w-full px-4 py-3 glass-dark rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30"
                        placeholder="Enter your username or email"
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        required
                    >
                </div>

                <div>
                    <label for="password" class="block text-white/90 text-sm font-medium mb-2">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-3 glass-dark rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-white/20 hover:bg-white/30 text-white font-semibold py-3 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-white/30"
                >
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-white/80">
                    Don't have an account? 
                    <a href="register.php" class="text-white hover:underline font-semibold">Sign up</a>
                </p>
            </div>
        </div>

    </div>
</body>
</html>