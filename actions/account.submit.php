<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifie que la connexion à la base de données est bien initialisée
    if (!isset($pdo)) {
        // Renvoie un message d'erreur si la variable n'est pas initialisée
        $_SESSION['error_message'] = "Error: Database connection not defined.";
        header('Location: /?page=home');
        exit;
    }

    $userId = $_SESSION['user']['id'];

    // Récupération des informations de l'utilisateur depuis la base de données
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error_message'] = "Error: User not found.";
            header('Location: /?page=account');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error retrieving user data.";
        header('Location: /?page=account');
        exit;
    }

    // Traitement du formulaire si une requête POST est envoyée
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (hasPermission('admin', $pdo) || hasPermission('user', $pdo)) {
            
            try {
                // Suppression de la photo de profil
                if (isset($_POST['delete_profile_picture'])) {
                    $currentProfilePicture = $user['profile_picture'];

                    // Supprime l'ancienne photo de profil sauf si c'est l'image par défaut
                    if (!empty($currentProfilePicture) && $currentProfilePicture !== '/img/users/everyone.png') {
                        $oldFile = __DIR__ . '/../public' . $currentProfilePicture;
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    // Met à jour la base de données avec l'image par défaut
                    $stmt = $pdo->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :id");
                    $stmt->execute([
                        ':profile_picture' => '/img/users/everyone.png',
                        ':id' => $userId
                    ]);

                    $_SESSION['success_message'] = 'Profile picture successfully removed.';
                    header('Location: /?page=myaccount');
                    exit;
                }


                if (isset($_POST['delete_account']) && $_POST['delete_account'] == 1) {
                    try {
                        // Début d'une transaction pour assurer la cohérence des données
                        $pdo->beginTransaction();
                
                        // Suppression des commentaires de l'utilisateur
                        $stmt = $pdo->prepare("DELETE FROM news_comment WHERE userID = :user_id");
                        $stmt->execute([':user_id' => $userId]);
                
                        // Suppression des informations de l'utilisateur
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                        $stmt->execute([':id' => $userId]);
                
                        // Suppression de la photo de profil si elle existe et n'est pas l'image par défaut
                        if (!empty($user['profile_picture']) && $user['profile_picture'] !== '/img/users/everyone.png') {
                            $profilePicturePath = __DIR__ . '/../public' . $user['profile_picture'];
                            if (file_exists($profilePicturePath)) {
                                unlink($profilePicturePath);
                            }
                        }
                
                        // Validation de la transaction
                        $pdo->commit();
                
                        // Déconnexion de la session
                        session_destroy();
                        $_SESSION['success_message'] = 'Your account has been successfully deleted.';
                        header('Location: /?page=home');
                        exit;
                
                    } catch (PDOException $e) {
                        // Annulation de la transaction en cas d'erreur
                        $pdo->rollBack();
                        $_SESSION['error_message'] = 'Error deleting your account. Please try again.';
                        header('Location: /?page=myaccount');
                        exit;
                    }
                }



                // Récupération et validation des données du formulaire
                $name = htmlspecialchars(trim($_POST['name'] ?? ''));
                $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                $currentProfilePicture = $user['profile_picture'];
                $profilePicturePath = $currentProfilePicture;

                // Gestion de l'upload d'une nouvelle photo de profil
                if (!empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $targetDir = __DIR__ . '/../public/img/users/';
                    $fileTmp = $_FILES['profile_picture']['tmp_name'];
                    $fileName = uniqid() . "_" . basename($_FILES['profile_picture']['name']);
                    $targetFile = $targetDir . $fileName;

                    // Vérifie si le fichier uploadé est bien une image
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = mime_content_type($fileTmp);

                    if (!in_array($fileType, $allowedTypes)) {
                        $_SESSION['error_message'] = 'Only JPG, PNG, and GIF files are allowed.';
                        header('Location: /?page=myaccount');
                        exit;
                    
                    // Si l'image est correcte (taille, extension, etc.)
                    } elseif (move_uploaded_file($fileTmp, $targetFile)) {
                        // Supprime l'ancienne photo si elle existe et n'est pas l'image par défaut
                        if (!empty($currentProfilePicture) && $currentProfilePicture !== '/img/users/everyone.png') {
                            $oldFile = __DIR__ . '/../public' . $currentProfilePicture;
                            if (file_exists($oldFile)) {
                                unlink($oldFile);
                            }
                        }

                        // Met à jour le chemin de la nouvelle photo de profil
                        $profilePicturePath = '/img/users/' . $fileName;
                        
                    } else {
                        $_SESSION['error_message'] = 'Error uploading the file.';
                        header('Location: /?page=myaccount');
                        exit;
                    }
                }



                // Mise à jour des informations utilisateur (nom, email, photo de profil)
                $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, profile_picture = :profile_picture WHERE id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':profile_picture' => $profilePicturePath,
                    ':id' => $userId
                ]);

                // Mise à jour des informations utilisateur (nom, email, photo de profil)
                $updateFields = [];
                $updateData = [':id' => $userId];
                $messageUpdated = false;

                if ($name !== $user['name']) {
                    $updateFields[] = "name = :name";
                    $updateData[':name'] = $name;
                    $messageUpdated = true;
                }

                if ($email !== $user['email']) {
                    $updateFields[] = "email = :email";
                    $updateData[':email'] = $email;
                    $messageUpdated = true;
                }

                if ($profilePicturePath !== $user['profile_picture']) {
                    $updateFields[] = "profile_picture = :profile_picture";
                    $updateData[':profile_picture'] = $profilePicturePath;
                    $pictureUpdated = true;
                }

                if (!empty($updateFields)) {
                    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute($updateData);

                    if ($messageUpdated) {
                        $_SESSION['success_message'] = 'Information successfully updated.';
                    } else if ($pictureUpdated) {
                        $_SESSION['success_message'] = 'Profile picture successfully updated.';
                    }
                }

                // Validation et mise à jour du mot de passe si un nouveau mot de passe est défini
                $passwordPolicy = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/';
                if (!empty($newPassword)) {
                    if ($newPassword !== $confirmPassword) {
                        $_SESSION['error_message'] = 'Passwords do not match.';
                        header('Location: /?page=myaccount');
                        exit;
                    }

                    if (!preg_match($passwordPolicy, $newPassword)) {
                        $_SESSION['error_message'] = 'Password must contain, at least:
                        - 12 characters
                        - 1 uppercase letter
                        - 1 lowercase letter
                        - 1 number
                        - 1 special character';
                        header('Location: /?page=myaccount');
                        exit;
                    }

                    // Hachage du mot de passe et mise à jour en base de données
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                    $stmt->execute([
                        ':password' => $hashedPassword,
                        ':id' => $userId
                    ]);

                    $_SESSION['success_message'] = 'Password updated successfully.';
                }

                // Redirection pour éviter le renvoi du formulaire en cas de rafraîchissement
                header('Location: /?page=myaccount');
                exit;

            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error updating your profile.';
                header('Location: /?page=myaccount');
                exit;
            }
        } else {
            $_SESSION['error_message'] = "You're not allowed to interact with this page.";
            header('Location: /?page=myaccount');
            exit;
        }
    } 
    
