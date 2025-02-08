<!DOCTYPE html>

<?php
    include '../includes/header.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Récupérer les valeurs de la session si elles existent
    $name = isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : '';
    $email = isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : '';
?>


<head>
    <title>Contact</title>
</head>


    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">CONTACT</h1>

        <!-- Message Flash -->
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- Contact Information -->
        <div class="text-center mb-8">
            <p class="text-lg font-bold text-gray-800">Avenix</p>
            <p>12 rue de Paris - 75000 Paris (FRANCE)</p>
            <p>+33 (0)6 98 08 28 60</p>
            <a href="mailto:contact@avenix.com" class="text-indigo-600 hover:text-indigo-800">contact@avenix.com</a>
        </div>

        <?php
            // Générer un token CSRF si nécessaire
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
        ?>

        <!-- Contact Form -->
        <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
            <form action="?action=contact.submit" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700">Name*</label>
                    <input type="text" id="name" name="name" value="<?= $name ?>" required class="w-full mt-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700">Email*</label>
                    <input type="email" id="email" name="email" value="<?= $email ?>" required class="w-full mt-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="message" class="block text-sm font-bold text-gray-700">Your request*</label>
                    <textarea id="message" name="message" rows="4" required class="w-full mt-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700">Submit</button>
                </div>
            </form>
        </div>

    </div>

<?php
    include '../includes/footer.php';
?>
