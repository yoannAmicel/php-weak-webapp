<!DOCTYPE html>

<?php
    include '../includes/header.php';
    include_once '../includes/software_helper.php';
?>


<head>
    <title>Software</title>
</head>


    <div class="container mx-auto px-4 py-8">
        
        <!-- Titre principal de la page -->
        <h1 class="text-3xl font-bold text-center mb-8">SOFTWARE</h1>



        <!----------------------------------------------------------------------------------->
        <!-- Affichage des messages de succès et d'erreur ----------------------------------->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded mb-4 w-2/3 mx-auto">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); // Suppression du message après affichage ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4 w-2/3 mx-auto">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); // Suppression du message après affichage ?>
        <?php endif; ?>
        <!----------------------------------------------------------------------------------->




        <!-- Formulaire d'ajout d'un software, visible UNIQUEMENT par les administrateurs -->
        <?php if (isset($_SESSION['user']) && hasPermission('admin', $pdo)): ?>
            <div class="bg-white p-6 rounded-lg justify-center shadow-md mb-8 w-2/3 mx-auto flex flex-col">
                <h2 class="text-2xl font-bold mb-4">Add New Software</h2>
                
                <!-- Formulaire de soumission -->
                <form method="POST" action="?action=software.submit" class="grid gap-4" onsubmit="forceUpperCase(event)">
                    <input type="hidden" name="action" value="add">
                    
                    <!-- Champ pour le nom du software (converti automatiquement en majuscules) -->
                    <div>
                        <label for="name" class="block font-bold mb-2">Name:</label>
                        <input type="text" id="name" name="name" class="w-full p-2 border rounded" required 
                            oninput="this.value = this.value.toUpperCase();">
                    </div>
                    
                    <!-- Champ pour la description -->
                    <div>
                        <label for="description" class="block font-bold mb-2">Description:</label>
                        <textarea id="description" name="description" class="w-full p-2 border rounded" required></textarea>
                    </div>
                    
                    <!-- Champ pour l'URL (du détail du software) -->
                    <div>
                        <label for="more_info_url" class="block font-bold mb-2">More Info URL:</label>
                        <input type="url" id="more_info_url" name="more_info_url" value="https://www." class="w-full p-2 border rounded" required>
                    </div>
                    
                    <!-- Sélecteur de couleur pour le titre du software -->
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
                    
                    <!-- Bouton de soumission -->
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Add
                    </button>
                </form>
            </div>
        <?php endif; ?>



        <!-- Liste des softwares enregistrés -->
        <div class="grid gap-6">
            <?php foreach ($softwareItems as $software): ?>
                <div class="bg-white rounded-lg shadow-md p-6 flex justify-between items-center">
                    
                    <!-- Informations sur le software -->
                    <div class="flex-1 mr-4">
                        <h2 class="text-xl font-bold" style="color: <?= htmlspecialchars($software['title_color']) ?>">
                            <?= htmlspecialchars($software['name']) ?>
                        </h2>
                        <p class="text-gray-600">
                            <?= htmlspecialchars($software['description']) ?>
                        </p>
                    </div>
                    
                    <!-- Boutons d'action : lien vers plus d'infos et suppression (admin uniquement) -->
                    <div class="flex items-center space-x-4">
                        <a href="<?= htmlspecialchars($software['more_info_url']) ?>" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center">
                            More info
                        </a>

                        <?php if (isset($_SESSION['user']) && hasPermission('admin', $pdo)): ?>
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



    <!-- Popup - Confirmation de SUPPRESSION -->
    <div id="delete-popup" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            
            <!-- Message de confirmation de suppression -->
            <h3 class="text-xl font-bold mb-4 text-red-600">Confirm Deletion</h3>
            <p id="popup-message" class="mb-6 text-gray-700">Are you sure you want to delete this software?</p>
            
            <!-- Formulaire de suppression -->
            <form id="delete-form" method="POST" action="?action=software.submit">
                <!-- ID du software à supprimer (caché) -->
                <input type="hidden" id="delete-software-id" name="id" value="">
                <input type="hidden" name="action" value="delete">
                
                <!-- Boutons pour annuler ou confirmer la suppression -->
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

