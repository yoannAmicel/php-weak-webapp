<!DOCTYPE html>

<?php
    include '../includes/header.php';
?>

<head>
    <title>Contact</title>
</head>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">CONTACT</h1>

        <!-- Message Flash -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- Contact Information -->
        <div class="text-center mb-8">
            <p class="text-lg font-bold text-gray-800">Avenix</p>
            <p>12 rue de Paris - 75000 Paris (FRANCE)</p>
            <p>+33 (0)6 98 08 28 60</p>
            <a href="mailto:contact@avenix.com" class="text-indigo-600 hover:text-indigo-800">contact@avenix.com</a>
        </div>

        <!-- Contact Form -->
        <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
            <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700">Name*</label>
                    <input type="text" id="name" name="name" required
                        class="w-full mt-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700">Email*</label>
                    <input type="email" id="email" name="email" required
                        class="w-full mt-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="message" class="block text-sm font-bold text-gray-700">Your request*</label>
                    <textarea id="message" name="message" rows="4" required
                        class="w-full mt-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700">Submit</button>
                </div>
            </form>
        </div>
    </div>

<?php
    include '../includes/footer.php';
?>
