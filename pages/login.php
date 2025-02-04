<!DOCTYPE html>
<html lang="en">

<?php
    include '../includes/header.php';
?>

<head>
    <title>Login</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="max-w-md mx-auto bg-white p-12 rounded-lg shadow-lg mt-20 mb-20">
        <h2 class="text-2xl font-bold mb-4">Login</h2>

        <?php if (!empty($_SESSION['status'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                <?= htmlspecialchars($_SESSION['status']); ?>
                <?php unset($_SESSION['status']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/actions/login.submit.php">
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

            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                </div>
                <a class="text-sm text-indigo-600 hover:text-indigo-500" href="/pages/password_reset.php">
                    Forgot your password?
                </a>
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Log in
                </button>
            </div>
        </form>

        <!-- Add Create Account Button -->
        <div class="mt-6 text-center">
            <span class="text-sm text-gray-600">Don't have an account?</span>
            <a href="<?= route('register') ?>"
                class="ml-2 text-indigo-600 hover:text-indigo-500 font-medium">Create Account</a>
        </div>
    </div>

<?php
    include '../includes/footer.php';
?>
</body>

</html>
