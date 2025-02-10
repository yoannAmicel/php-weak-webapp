<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';


    // Gestion des actions POST (mise à jour des rôles, blocage des utilisateurs)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = intval($_POST['user_id'] ?? 0); // Récupère l'ID de l'utilisateur à partir du formulaire


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
                $_SESSION['error_message'] = 'Error unblocking user.';
            }
        }
        
        header('Location: /?page=users');
        exit;
    }
