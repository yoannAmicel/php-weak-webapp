<!DOCTYPE html>

<?php
    include '../includes/header.php';
?>

<head>
    <title>Software</title>
</head>

<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8">SOFTWARE</h1>

        <!-- List of Software -->
        <div class="grid gap-6">
            <!-- Software Item 1 -->
            <div class="bg-white rounded-lg shadow-md p-6 flex justify-between items-center">
                <div class="flex-1 mr-4">
                    <h2 class="text-xl font-bold text-gray-800">#PLANIT</h2>
                    <p class="text-gray-600">
                        #PLANIT is an innovative planning and resource management tool designed for creative projects. It features advanced scheduling, team collaboration, and financial tracking to streamline your workflow.
                    </p>
                </div>
                <a href="#" class="flex-shrink-0 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center">
                    More info
                </a>
            </div>

            <!-- Software Item 2 -->
            <div class="bg-white rounded-lg shadow-md p-6 flex justify-between items-center">
                <div class="flex-1 mr-4">
                    <h2 class="text-xl font-bold text-orange-600">#WORKHUB</h2>
                    <p class="text-gray-600">
                        #WORKHUB is a personalized platform enabling employees to manage their schedules, track hours, and request time off seamlessly. Designed to enhance workplace efficiency.
                    </p>
                </div>
                <a href="#" class="flex-shrink-0 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center">
                    More info
                </a>
            </div>

            <!-- Software Item 3 -->
            <div class="bg-white rounded-lg shadow-md p-6 flex justify-between items-center">
                <div class="flex-1 mr-4">
                    <h2 class="text-xl font-bold text-green-600">#SHAREPRO</h2>
                    <p class="text-gray-600">
                        #SHAREPRO is a collaborative sharing platform that empowers teams to exchange critical information, organize projects, and drive innovation effortlessly.
                    </p>
                </div>
                <a href="#" class="flex-shrink-0 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center">
                    More info
                </a>
            </div>

            <!-- Software Item 4 -->
            <div class="bg-white rounded-lg shadow-md p-6 flex justify-between items-center">
                <div class="flex-1 mr-4">
                    <h2 class="text-xl font-bold text-gray-800">#ASSISTO</h2>
                    <p class="text-gray-600">
                        #ASSISTO is your digital assistant for managing tasks, tracking assets, and generating reports. Simplify your operations with this versatile and user-friendly tool.
                    </p>
                </div>
                <a href="#" class="flex-shrink-0 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center">
                    More info
                </a>
            </div>
        </div>
    </div>

<?php
    include '../includes/footer.php';
?>