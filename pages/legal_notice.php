<!DOCTYPE html>

<?php
    include '../includes/header.php';
?>

<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<head>
    <title>Legal Notice</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg mt-20 mb-12">
        <h1 class="text-3xl font-bold mb-6">Legal Notice</h1>
        <p class="text-gray-700 leading-relaxed mb-4">
            This website is owned and operated by Avenix, a company dedicated to providing high-quality services and innovative solutions.
            Our headquarters is located at:
        </p>
        <p class="text-gray-700 font-semibold mb-4">
            Avenix<br>
            12 rue de Paris, 75000 Paris, FRANCE<br>
            +33 (0)6 98 08 28 60<br>
            <a href="mailto:contact@avenix.com" class="text-indigo-600 hover:underline">contact@avenix.com</a>
        </p>
        <h2 class="text-2xl font-bold mt-6 mb-4">Intellectual Property</h2>
        <p class="text-gray-700 leading-relaxed mb-4">
            All content on this website, including but not limited to text, images, graphics, logos, and software, is the intellectual property of Avenix or its licensors.
            Any unauthorized use, reproduction, or distribution of this content is strictly prohibited and may result in legal action.
        </p>
        <h2 class="text-2xl font-bold mt-6 mb-4">Liability</h2>
        <p class="text-gray-700 leading-relaxed mb-4">
            Avenix cannot be held responsible for any direct, indirect, or consequential damages resulting from the use or inability to use this website.
            While we strive to ensure that the information provided is accurate and up-to-date, we make no guarantees regarding the completeness or accuracy of the content.
        </p>
        <h2 class="text-2xl font-bold mt-6 mb-4">External Links</h2>
        <p class="text-gray-700 leading-relaxed mb-4">
            This website may contain links to third-party websites. These links are provided for informational purposes only, and Avenix assumes no responsibility for the content
            or practices of these external sites. We encourage users to review the privacy policies and terms of use of any linked websites before engaging with them.
        </p>
        <p class="text-gray-700 leading-relaxed mb-4">
            For further details about our legal terms and conditions, please reach out to our legal department at
            <a href="mailto:contact@avenix.com" class="text-indigo-600 hover:underline">contact@avenix.com</a>.
        </p>
    </div>

<?php
    include '../includes/footer.php';
?>
</body>
