<!DOCTYPE html>

<?php
    include '../includes/header.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>


<head>
    <title>Home</title>
</head>

    <div class="bg-gray-100">

        <!-- Section Slogan -->
        <div class="text-center py-12 bg-white">
            <h2 class="text-5xl font-extrabold text-gray-800">PLANNING SOFTWARE</h2>
            <h3 class="text-3xl font-bold text-red-600">FOR THE SPORTS</h3>
        </div>

        <!-- Section images -->
        <div class="py-12 bg-gray-100">
            <div class="container mx-auto text-center">
                <div class="grid grid-cols-3 gap-4">
                    <img src="../img/compressed/mountain-bike.jpg" alt="Image 1" class="mx-auto">
                    <img src="../img/compressed/american.jpg" alt="Image 2" class="mx-auto">
                    <img src="../img/compressed/horse.jpg" alt="Image 3" class="mx-auto">
                </div>
            </div>
        </div>

    </div>


    <div class="bg-white py-12">

        <!-- Section Slogan -->
        <div class="container mx-auto text-center justify-center items-center w-1/5 py-6">
            <div class="flex flex-col items-center">
                <img src="../img/logo.png" alt="Logo IT4Culture" class="h-20 mb-4">
                <h1 class="text-4xl font-bold text-gray-800">Avenix</h1>
            </div>
        </div>

        <!-- Texte descriptif -->
        <div class="mt-6 text-lg text-gray-700 leading-relaxed max-w-2xl mx-auto text-center">
            <p>
                Avenix is an IT company specialized in software development for sports organizations, 
                fitness centers, stadiums, and many structures in the sports industry. 
                We used to work in sports for several years and we understand the daily challenges to build schedules, 
                manage numerous contracts, coordinate teams, track performance data, and optimize event logistics.
            </p>
            <p class="mt-4">
                We are an international company with offices in Monaco, Zurich and Los Angeles.
            </p>
        </div>

    </div>

    
    <div class="bg-gray-100 py-12">

        <!-- Section Clients -->
        <div class="text-center text-center py-8">
            <h2 class="text-5xl font-extrabold text-gray-800">CLIENTS</h2>
        </div>

        <div class="container mx-auto text-center justify-center items-center w-2/3 py-8">
            <img src="../img/logos-clients.png" alt="Clients logos" class="mx-auto">
        </div>

    </div>

<?php
    include '../includes/footer.php';
?>
