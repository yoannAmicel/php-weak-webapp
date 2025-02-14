<!DOCTYPE html>

<?php
    include_once '../includes/header.php';
    include_once '../includes/contact_helper.php';
?>


<head>
    <title>Contact</title>

    <!-- Chargement de l'API Google reCAPTCHA v3 avec la clé publique récupérée depuis Vault -->
    <script src="https://www.google.com/recaptcha/api.js?render=<?php print(getVaultSecret("apps/data/avenix/captcha", "public_key") ?? '')?>"></script>
    <?php if (!empty(getVaultSecret("apps/data/avenix/captcha", "public_key"))): ?>
        <!-- S.Contact.6 - Encapsulation de Recaptcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars(getVaultSecret("apps/data/avenix/captcha", "public_key")) ?>"></script>
        <script>
            grecaptcha.ready(function() {
                grecaptcha.execute("<?= htmlspecialchars(getVaultSecret("apps/data/avenix/captcha", "public_key")) ?>", { action: "contact_form" }).then(function(token) {
                    document.getElementById('recaptcha-token').value = token;
                });
            });
        </script>
    <?php else: ?>
        <script>console.error("reCAPTCHA key is missing.");</script>
    <?php endif; ?>
</head>


    <div class="container mx-auto px-4 py-8">
        <!-- Titre principal de la page de contact -->
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">CONTACT</h1>


        <!-- Informations de contact de l'entreprise -->
        <div class="text-center mb-8">
            <p class="text-lg font-bold text-gray-800">Avenix</p>
            <p>12 rue de Paris - 75000 Paris (FRANCE)</p>
            <p>+33 (0)6 98 08 28 60</p>
            <a href="mailto:contact@avenix.com" class="text-indigo-600 hover:text-indigo-800">contact@avenix.com</a>
        </div>

        <!-- Formulaire de contact -->
        <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
            <form action="?action=contact.submit" method="POST" class="space-y-4">
                <!-- Protection CSRF : Inclusion du token pour vérifier l'authenticité de la requête -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- Google reCAPTCHA : Champ caché pour stocker la réponse -->
                <input type="hidden" name="g-recaptcha-response" id="recaptcha-token">




                <!----------------------------------------------------------------------------------->
                <!-- Affichage des messages de succès et d'erreur ----------------------------------->
                <?php if (!empty($_SESSION['success_message'])): ?>
                    <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded mb-4 w-full">
                        <?= htmlspecialchars($_SESSION['success_message']) ?>
                    </div>
                    <?php unset($_SESSION['success_message']); // Suppression du message après affichage ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['error_message'])): ?>
                    <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4 w-full">
                        <?= htmlspecialchars($_SESSION['error_message']) ?>
                    </div>
                    <?php unset($_SESSION['error_message']); // Suppression du message après affichage ?>
                <?php endif; ?>
                <!----------------------------------------------------------------------------------->




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

                <!-- Bouton d'envoi du formulaire -->
                <div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700">
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>


<?php
    include '../includes/footer.php';
?>
