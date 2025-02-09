<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';
    // Déclaration explicite de $pdo en global pour accéder à la connexion à la base de données
    global $pdo;

    // Vérification et démarrage de la session si nécessaire
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifie si l'utilisateur est administrateur, sinon redirige vers la page d'accueil
    if (!isset($_SESSION['user']) || !hasPermission('admin')) {
        header('Location: /?page=home');
        exit;
    }

    // Gestion de la pagination
    $itemsPerPage = 10; // Nombre d'utilisateurs affichés par page
    $pageNumber = isset($_GET['page_number']) ? max(1, intval($_GET['page_number'])) : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    // Récupération des utilisateurs avec pagination
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, role, is_blocked FROM users LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupération du nombre total d'utilisateurs pour la pagination
        $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
        $totalUsers = $countStmt->fetchColumn();
        $totalPages = ceil($totalUsers / $itemsPerPage);
    } catch (PDOException $e) {
        $users = [];
        $totalPages = 1;
        $_SESSION['error_message'] = 'Error fetching users.';
        header('Location: /?page=users');
        exit;
    }

    // Récupération des statistiques utilisateur (uniquement sur la première page)
    if ($pageNumber === 1) {
        try {
            $totalUsers = $pdo->query("SELECT COUNT(*) AS count FROM users")->fetchColumn();
            $totalAdmins = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetchColumn();
            $totalUsersRole = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetchColumn();
            $totalGuests = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'guest'")->fetchColumn();
            $totalBlocked = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE is_blocked = 1")->fetchColumn();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error fetching statistics.';
            header('Location: /?page=users');
            exit;
        }
    }

    // Gestion du tri des colonnes
    $validSortColumns = ['name', 'email', 'role']; // Colonnes valides pour le tri
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $validSortColumns) ? $_GET['sort'] : 'name';
    $order = isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc']) ? $_GET['order'] : 'asc';

    // Récupération des utilisateurs avec tri et pagination
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, email, role, is_blocked 
            FROM users 
            ORDER BY $sort $order 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $users = [];
        $_SESSION['error_message'] = 'Error fetching users.';
        header('Location: /?page=users');
        exit;
    }




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
