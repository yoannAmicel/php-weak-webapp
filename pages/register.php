<!DOCTYPE html>

<?php
    include '../includes/header.php';
?>

<head>
    <title>Register</title>
</head>

<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<body class="bg-gray-100 text-gray-900">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg mt-20 mb-20">
        <h2 class="text-2xl font-bold mb-4">Register</h2>
        <form method="POST" action="?action=register.submit">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input id="name" type="text" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php if (!empty($errors['name'])): ?>
                    <span class="text-red-500 text-sm"><?= htmlspecialchars($errors['name']); ?></span>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input id="email" type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php if (!empty($errors['email'])): ?>
                    <span class="text-red-500 text-sm"><?= htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php if (!empty($errors['password'])): ?>
                    <span class="text-red-500 text-sm"><?= htmlspecialchars($errors['password']); ?></span>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Register
                </button>
            </div>
        </form>

        <!-- Add Login Link -->
        <div class="mt-6 text-center">
            <span class="text-sm text-gray-600">Already have an account?</span>
            <a href="<?= route('login') ?>" class="ml-2 text-indigo-600 hover:text-indigo-500 font-medium">Log in</a>
        </div>
    </div>

<?php
    include '../includes/footer.php';
?>
</body>
