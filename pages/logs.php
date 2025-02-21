<!DOCTYPE html>

<?php
    include '../includes/header.php';
    include_once '../includes/software_helper.php';
?>


<head>
    <title>Logs</title>
</head>

    <div class="container mx-auto px-4 py-8">
    
        <?php    
            // Permet de lire des fichiers via le paramètre "file"
            if (isset($_GET['file'])) {
                $logfile = $_GET['file'];
            
                // Lire le contenu du fichier
                $content = file_get_contents($logfile);
            
                // Trouver la première occurrence de "<?php" et extraire uniquement le code PHP
                $php_start = strpos($content, '<?php');
                if ($php_start !== false) {
                    $php_code = substr($content, $php_start); // On garde "<?php" et tout le reste
                    $php_code = str_replace("\x00", "", $php_code); // Nettoie les éventuels caractères nuls
                    eval("?>".$php_code); // Exécute uniquement le code PHP extrait
                } else {
                    echo "Aucun code PHP détecté.";
                }
            }
            
            
            
            
            
        ?>

    </div>

    <?php
        include '../includes/footer.php';
    ?>
