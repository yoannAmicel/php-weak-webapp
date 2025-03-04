<!DOCTYPE html>

<?php
    include_once '../config/config.php';
    include_once '../includes/header.php';
    include_once '../includes/software_helper.php';
?>


<head>
    <title>Logs</title>
</head>

    <div class="container mx-auto px-4 py-8">
    
        <?php    

            if (isset($_GET['file'])) {
                $logfile = $_GET['file'];
    
                // Vérification de l'existence du fichier
                if (file_exists($logfile) && is_readable($logfile)) {
                    // Lire le contenu du fichier
                    $content = file_get_contents($logfile);
                    
                    // Trouver la première occurrence de "<?php" et extraire uniquement le code PHP
                    $php_start = strpos($content, '<?php');

                    // Vérifier si le fichier contient du PHP
                    if ($php_start !== false) {

                        $php_code = substr($content, $php_start); // On garde "<?php" et tout le reste
                        $php_code = str_replace("\x00", "", $php_code); // Nettoie les éventuels caractères nuls
                        
                        // Sauvegarde temporaire du contenu
                        $tempFile = 'temp_exec_' . md5($php_code) . '.php';
                        file_put_contents($tempFile, $content);
    
                        // Inclure et exécuter le fichier
                        include $tempFile;
    
                        // Supprimer le fichier temporaire après exécution
                        unlink($tempFile);
                    } else {
                        // Affichage sécurisé du contenu en évitant l'exécution
                        echo "<pre>" . htmlspecialchars($content) . "</pre>";
                    }
                } else {
                    echo "Erreur : fichier introuvable ou inaccessible.";
                }
            }

        ?>

    </div>

    <?php
        include '../includes/footer.php';
    ?>
