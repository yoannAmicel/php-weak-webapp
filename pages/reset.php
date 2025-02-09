<!DOCTYPE html>

<?php
    include '../includes/header.php';
    require_once '../config/config.php';
    require_once '../functions/security.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si un token est passé dans l'URL
    $token = $_GET['token'] ?? null;

    if (!$token) {
        $_SESSION['error'] = 'Invalid or missing token.';
        header('Location: /?page=login');
        exit;
    }

    // Récupérer l'utilisateur associé au token
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = 'This reset link is invalid or has expired.';
            header('Location: /?page=login');
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        die('Server error: ' . $e->getMessage());
    }

    // Si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Vérifier les mots de passe
        if (empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'Please fill in both password fields.';
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match.';
        } elseif (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long.';
        } else {
            // Hacher le mot de passe et mettre à jour la base de données
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            try {
                $stmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
                $stmt->execute(['password' => $hashedPassword, 'id' => $user['id']]);

                $_SESSION['success'] = 'Your password has been reset successfully. You can now log in.';
                header('Location: /?page=login');
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                die('Server error: ' . $e->getMessage());
            }
        }
    }
?>


<head>
    <title>Reset Password</title>
</head>

    <!-- Message Flash -->
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>


    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg mt-20 mb-12">
            <h2 class="text-2xl font-bold mb-4">Reset Password</h2>
            <p class="text-sm text-gray-600 mb-4">Please enter your new password below.</p>

            <!-- Error Message -->
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input id="password" type="password" name="password" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input id="confirm_password" type="password" name="confirm_password" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset Password
                    </button>
                </div>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-6 text-center">
                <a href="/pages/login.php" class="text-indigo-600 hover:text-indigo-500 font-medium">Back to Login</a>
            </div>
        </div>
    </div>

<?php
    include '../includes/footer.php';
?>