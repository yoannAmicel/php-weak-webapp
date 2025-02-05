<!DOCTYPE html>

<?php
    include '../includes/header.php';
?>

<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<head>
    <title>Forgot Password</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg mt-20 mb-12">
        <h2 class="text-2xl font-bold mb-4">Forgot Password</h2>
        <p class="text-sm text-gray-600 mb-4">Enter your email address below, and we will send you a link to reset your password.</p>
        <form method="POST" action="?action=password_reset.submit">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input id="email" type="email" name="email" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php if (!empty($errors['email'])): ?>
                    <span class="text-red-500 text-sm"><?= htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Send Reset Link
                </button>
            </div>
        </form>

        <!-- Add Back to Login Link -->
        <div class="mt-6 text-center">
            <a href="<?= route('login') ?>" class="text-indigo-600 hover:text-indigo-500 font-medium">Back to Login</a>
        </div>
    </div>

<?php
    include '../includes/footer.php';
?>
</body>
