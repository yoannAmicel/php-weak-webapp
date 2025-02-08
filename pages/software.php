<!DOCTYPE html>

<?php
    include '../includes/header.php';
    require_once '../config/config.php'; // Inclusion du fichier de configuration
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si la connexion PDO est définie
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=error');
        exit;
    }

    // Gestion du formulaire d'ajout
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
        if (hasPermission('admin')) {
            $name = strtoupper($_POST['name'] ?? ''); // Forcer en majuscules côté PHP
            
            // Ajouter un "#" au début du titre s'il n'est pas déjà présent
            if (substr($name, 0, 1) !== '#') {
                $name = '#' . $name;
            }
            
            $description = $_POST['description'] ?? '';
            $more_info_url = $_POST['more_info_url'] ?? '';
            $title_color = $_POST['title_color'] ?? '#000000';

            if (!empty($name) && !empty($description) && !empty($more_info_url)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO software (name, description, more_info_url, title_color) 
                        VALUES (:name, :description, :more_info_url, :title_color)
                    ");
                    $stmt->execute([
                        ':name' => $name,
                        ':description' => $description,
                        ':more_info_url' => $more_info_url,
                        ':title_color' => $title_color
                    ]);
                    $_SESSION['flash_message'] = 'Software successfully added!';
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'An error occurred while adding the software.';
                    header('Location: /?page=software');
                    exit;
                }
            } else {
                $_SESSION['error_message'] = 'Please fill out all fields.';
                header('Location: /?page=software');
                exit;
            }
        } else {
            $_SESSION['error_message'] = "You're not allowed to add software.";
            header('Location: /?page=software');
            exit;
        }
    }


    // Gestion de la suppression
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (hasPermission('admin')) {
            $id = $_POST['id'] ?? '';

            if (!empty($id)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM software WHERE id = :id");
                    $stmt->execute([':id' => $id]);
                    $_SESSION['flash_message'] = 'Software successfully deleted!';
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'An error occurred while deleting the software.';
                    header('Location: /?page=software');
                    exit;
                }
            } else {
                $_SESSION['error_message'] = 'Invalid ID for deletion.';
                header('Location: /?page=software');
                exit;
            }
        } else {
            $_SESSION['error_message'] = "You're not allowed to delete software.";
            header('Location: /?page=software');
            exit;
        }
    }

    // Récupérer les données de la table software
    try {
        $query = $pdo->query("SELECT * FROM software");
        $softwareItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'An error occurred while fetching the data.';
        header('Location: /?page=software');
        exit;
    }
?>




<head>
    <title>Software</title>
</head>




    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8">SOFTWARE</h1>

        <!-- Message Flash -->
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
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

        <!-- Formulaire d'ajout visible uniquement si l'utilisateur est connecté -->
        <?php if (isset($_SESSION['user']) && hasPermission('admin')): ?>
            <div class="bg-white p-6 rounded-lg justify-center shadow-md mb-8 w-2/3 mx-auto flex flex-col">
                <h2 class="text-2xl font-bold mb-4">Add New Software</h2>
                <form method="POST" action="" class="grid gap-4" onsubmit="forceUpperCase(event)">
                    <input type="hidden" name="action" value="add">
                    <div>
                        <label for="name" class="block font-bold mb-2">Name:</label>
                        <input type="text" id="name" name="name" class="w-full p-2 border rounded" required oninput="this.value = this.value.toUpperCase();">
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-2">Description:</label>
                        <textarea id="description" name="description" class="w-full p-2 border rounded" required></textarea>
                    </div>
                    <div>
                        <label for="more_info_url" class="block font-bold mb-2">More Info URL:</label>
                        <input type="url" id="more_info_url" name="more_info_url" value="https://www." class="w-full p-2 border rounded" required>
                    </div>
                    <div>
                        <label for="title_color" class="block font-bold mb-2">Title Color:</label>
                        <select id="title_color" name="title_color" class="w-full p-2 border rounded">
                            <option value="#000000" style="background-color: #000000; color: #ffffff;">Black</option>
                            <option value="#1E90FF" style="background-color: #1E90FF; color: #ffffff;">Dodger Blue</option>
                            <option value="#32CD32" style="background-color: #32CD32; color: #ffffff;">Lime Green</option>
                            <option value="#FFA500" style="background-color: #FFA500; color: #ffffff;">Orange</option>
                            <option value="#FF4500" style="background-color: #FF4500; color: #ffffff;">Red Orange</option>
                            <option value="#9400D3" style="background-color: #9400D3; color: #ffffff;">Dark Violet</option>
                            <option value="#FFD700" style="background-color: #FFD700; color: #000000;">Gold</option>
                            <option value="#00CED1" style="background-color: #00CED1; color: #ffffff;">Dark Turquoise</option>
                            <option value="#FF1493" style="background-color: #FF1493; color: #ffffff;">Deep Pink</option>
                            <option value="#8B4513" style="background-color: #8B4513; color: #ffffff;">Saddle Brown</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Add
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Liste des logiciels -->
        <div class="grid gap-6">
            <?php foreach ($softwareItems as $software): ?>
                <div class="bg-white rounded-lg shadow-md p-6 flex justify-between items-center">
                    <div class="flex-1 mr-4">
                        <h2 class="text-xl font-bold" style="color: <?= htmlspecialchars($software['title_color']) ?>">
                            <?= htmlspecialchars($software['name']) ?>
                        </h2>
                        <p class="text-gray-600">
                            <?= htmlspecialchars($software['description']) ?>
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="<?= htmlspecialchars($software['more_info_url']) ?>" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center">
                            More info
                        </a>
                        <?php if (isset($_SESSION['user'])  && hasPermission('admin')): ?>
                            <button type="button" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"
                                    onclick="openPopup(<?= htmlspecialchars($software['id']) ?>, '<?= htmlspecialchars($software['name']) ?>')">
                                Delete
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    </div>


    <!-- Popup - Confirmation de suppression -->
    <div id="delete-popup" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h3 class="text-xl font-bold mb-4 text-red-600">Confirm Deletion</h3>
            <p id="popup-message" class="mb-6 text-gray-700">Are you sure you want to delete this software?</p>
            <form id="delete-form" method="POST" action="">
                <input type="hidden" id="delete-software-id" name="id" value="">
                <input type="hidden" name="action" value="delete">
                <div class="flex justify-end space-x-4">
                    <button type="button" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400" onclick="closePopup()">Cancel</button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </form>
        </div>
    </div>

<script src="/js/software.js"></script>

<?php
    include '../includes/footer.php';
?>

