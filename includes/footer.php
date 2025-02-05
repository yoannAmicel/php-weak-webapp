<!DOCTYPE html>

<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

    <!-- Footer -->
    <footer class="bg-gray-200 py-6">
        <div class="container mx-auto text-center">
            <p>&copy; <?php echo date('Y'); ?> Avenix. All rights reserved.</p>
            <div class="flex justify-center space-x-4 mt-4">
                <a href="#" class="text-gray-600 hover:underline">Legal Notice</a>
                <a href="#" class="text-gray-600 hover:underline">Privacy Policy</a>
            </div>
        </div>
    </footer>
</body>

</html>
