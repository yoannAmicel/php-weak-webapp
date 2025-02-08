<!DOCTYPE html>

<head>
    <title>My Account</title>
</head>

<?php
    include '../includes/header.php';
    require_once '../config/config.php';
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($pdo)) {
        die('Erreur : connexion à la base de données non définie.');
    }

    // Vérifie si l'utilisateur est connecté
    if (!isset($_SESSION['user']['id'])) {
        header('Location: /?page=login');
        exit;
    }

    $userId = $_SESSION['user']['id'];

    // Récupération des informations utilisateur depuis la base de données
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            die('Erreur : utilisateur introuvable.');
        }
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des données utilisateur : ' . $e->getMessage());
    }

    // Traitement des actions utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Gestion de la suppression de la photo de profil
            if (isset($_POST['delete_profile_picture'])) {
                $currentProfilePicture = $user['profile_picture'];

                // Supprime l'ancienne photo de profil si elle n'est pas par défaut
                if (!empty($currentProfilePicture) && $currentProfilePicture !== '/img/users/everyone.png') {
                    $oldFile = __DIR__ . '/../public' . $currentProfilePicture;
                    if (file_exists($oldFile)) {
                        unlink($oldFile); // Supprime le fichier
                    }
                }

                // Met à jour la base de données avec l'image par défaut
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :id");
                $stmt->execute([
                    ':profile_picture' => '/img/users/everyone.png',
                    ':id' => $userId
                ]);

                $_SESSION['flash_message'] = 'Photo de profil supprimée avec succès.';
                header('Location: /?page=myaccount');
                exit;
            }

            // Récupération des champs du formulaire
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $currentProfilePicture = $user['profile_picture'];
            $profilePicturePath = $currentProfilePicture;

            // Gestion de l'upload de la photo de profil
            if (!empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $targetDir = __DIR__ . '/../public/img/users/';
                $fileTmp = $_FILES['profile_picture']['tmp_name'];
                $fileName = uniqid() . "_" . basename($_FILES['profile_picture']['name']);
                $targetFile = $targetDir . $fileName;

                // Vérification du type MIME
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($fileTmp);

                if (!in_array($fileType, $allowedTypes)) {
                    $_SESSION['error_message'] = 'Seuls les fichiers JPG, PNG et GIF sont autorisés.';
                    header('Location: /?page=myaccount');
                    exit;
                } elseif (move_uploaded_file($fileTmp, $targetFile)) {
                    // Supprimer l'ancienne photo de profil si elle existe et n'est pas par défaut
                    if (!empty($currentProfilePicture) && $currentProfilePicture !== '/img/users/everyone.png') {
                        $oldFile = __DIR__ . '/../public' . $currentProfilePicture;
                        if (file_exists($oldFile)) {
                            unlink($oldFile); // Supprime le fichier
                        }
                    }

                    // Met à jour le chemin de la nouvelle photo de profil
                    $profilePicturePath = '/img/users/' . $fileName;
                } else {
                    $_SESSION['error_message'] = 'Erreur lors de l’upload du fichier.';
                    header('Location: /?page=myaccount');
                    exit;
                }
            }

            // Mise à jour des informations générales
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, profile_picture = :profile_picture WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':profile_picture' => $profilePicturePath,
                ':id' => $userId
            ]);

            // Gestion du mot de passe
            if (!empty($newPassword) && $newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute([
                    ':password' => $hashedPassword,
                    ':id' => $userId
                ]);
                $_SESSION['flash_message'] = 'Informations et mot de passe mis à jour avec succès.';
            } elseif (!empty($newPassword) || !empty($confirmPassword)) {
                $_SESSION['error_message'] = 'Les mots de passe ne correspondent pas.';
            } else {
                $_SESSION['flash_message'] = 'Informations mises à jour avec succès.';
            }

            // Redirection pour éviter le renvoi du formulaire
            header('Location: /?page=myaccount');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            header('Location: /?page=myaccount');
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: /?page=myaccount');
            exit;
        }
    }
?>


<div class="w-full max-w-2xl mx-auto bg-white p-12 rounded-lg shadow-lg mt-16 mb-16">
    <h2 class="text-2xl font-bold mb-4">My Account</h2>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded mb-4">
            <?= htmlspecialchars($_SESSION['flash_message']); ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <!-- Error Messages -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4">
            <?= htmlspecialchars($_SESSION['error_message']); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <!-- Section "General" -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-600 border-b border-gray-300 pb-2">General</h3>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-600">Name</label>
                <input id="name" type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-600">Email Address</label>
                <input id="email" type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="profile_picture" class="block text-sm font-medium text-gray-600">Profile Picture</label>
                <!-- Champ pour uploader une nouvelle photo de profil -->
                <input id="profile_picture" type="file" name="profile_picture"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                <!-- Affichage de la photo actuelle -->
                <div class="mt-4 flex justify-center">
                    <img src="<?= htmlspecialchars($user['profile_picture']); ?>" alt="Current Profile Picture"
                        class="h-60 w-60 rounded-full object-cover shadow-md">
                </div>

                <!-- Bouton pour supprimer la photo de profil dans le formulaire principal -->
                <?php if ($user['profile_picture'] !== '/img/users/everyone.png'): ?>
                    <div class="mt-4 flex justify-center">
                        <form method="POST">
                            <button type="submit" name="delete_profile_picture"
                                class="py-2 px-4 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                                Remove Profile Picture
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section "Security" -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-600 border-b border-gray-300 pb-2">Security</h3>

            <div class="mb-4">
                <label for="new_password" class="block text-sm font-medium text-gray-600">New Password</label>
                <input id="new_password" type="password" name="new_password"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="block text-sm font-medium text-gray-600">Confirm New Password</label>
                <input id="confirm_password" type="password" name="confirm_password"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>

        <div>
            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php
include '../includes/footer.php';
?>
