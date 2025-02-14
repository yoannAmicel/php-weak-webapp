<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'users.submit.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    } 
    
    // Vérifie que la connexion à la base de données est bien initialisée
    if (!isset($pdo)) {
        // Renvoie un message d'erreur si la variable n'est pas initialisée
        $_SESSION['error_message'] = "Error: Database connection not defined.";
        header('Location: /?page=home');
        exit;
    }

    // Gestion des actions POST (mise à jour des rôles, blocage des utilisateurs)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Vérification du token CSRF pour éviter les attaques CSRF
        if (isset($_POST['csrf_token'])) {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
                http_response_code(403); 
                $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
                header('Location: /?page=users'); 
                exit;
            }
        } else {
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=users'); 
            exit;
        }

        if (hasPermission('admin', $pdo)) {

            $userId = intval($_POST['user_id'] ?? 0); // Récupère l'ID de l'utilisateur à partir du formulaire

            // Vérifier que l'ID utilisateur est valide (entier strictement positif)
            $userId = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
            if (!$userId || $userId <= 0) {
                $_SESSION['error_message'] = 'Invalid user ID.';
                header('Location: /?page=users');
                exit;
            }

            if (isset($_POST['role'])) {

                /**************************************/
                /*         Update du role             */
                /**************************************/
                
                $newRole = $_POST['role'];
                try {
                    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$newRole, $userId]);

                    // Enregistrement de l'action dans les logs d'audit
                    $logStmt = $pdo->prepare("
                        INSERT INTO audit_logs (admin_id, user_id, action, details) 
                        VALUES (?, ?, 'update_role', ?)
                    ");
                    $logStmt->execute([
                        $_SESSION['user']['id'],
                        $userId,
                        json_encode(['new_role' => $newRole])
                    ]);

                    $_SESSION['success_message'] = 'Role updated successfully!';
                } catch (PDOException $e) {
                    error_log("Updating user role error: " . $e->getMessage(), 3, '../logs/error.log');
                    $_SESSION['error_message'] = 'Error updating role.';
                }
            }    


            if (isset($_POST['block'])) {

                /**************************************/
                /*         Blocage user               */
                /**************************************/

                try {
                    $stmt = $pdo->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
                    $stmt->execute([$userId]);

                    // Ajout de l'action dans les logs d'audit
                    $logStmt = $pdo->prepare("
                        INSERT INTO audit_logs (admin_id, user_id, action, details) 
                        VALUES (?, ?, 'block', NULL)
                    ");
                    $logStmt->execute([$_SESSION['user']['id'], $userId]);

                    $_SESSION['success_message'] = 'User blocked successfully!';
                } catch (PDOException $e) {
                    error_log("Blocking user error: " . $e->getMessage(), 3, '../logs/error.log');
                    $_SESSION['error_message'] = 'Error blocking user.';
                }
            } 

            
            elseif (isset($_POST['unblock'])) {

                /**************************************/
                /*         Déblocage user             */
                /**************************************/

                try {
                    $stmt = $pdo->prepare("UPDATE users SET is_blocked = 0 WHERE id = ?");
                    $stmt->execute([$userId]);

                    // Ajout de l'action dans les logs d'audit
                    $logStmt = $pdo->prepare("
                        INSERT INTO audit_logs (admin_id, user_id, action, details) 
                        VALUES (?, ?, 'unblock', NULL)
                    ");
                    $logStmt->execute([$_SESSION['user']['id'], $userId]);

                    $_SESSION['success_message'] = 'User unblocked successfully!';
                } catch (PDOException $e) {
                    error_log("Unblocking user error: " . $e->getMessage(), 3, '../logs/error.log');
                    $_SESSION['error_message'] = 'Error unblocking user.';
                }
            }
            
            header('Location: /?page=users');
            exit;
    
        } else {
            // L'utilisateur n'est pas admin
            $_SESSION['error_message'] = "You're not allowed to view this page.";
            header('Location: /?page=login');
            exit;
        }
    }
