</div> <!-- End of main content container from header.php -->
    
    <!-- Footer -->
    <footer class="bg-white shadow-inner mt-auto">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold text-primary mb-4">
                        <i class="fas fa-bus-alt mr-2"></i>RentalBis
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Your trusted partner for bus and car rentals. We provide high-quality vehicles with GPS tracking for your safety and convenience.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-span-1">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="/" class="text-gray-600 hover:text-primary transition-colors">Home</a>
                        </li>
                        <li>
                            <a href="/about.php" class="text-gray-600 hover:text-primary transition-colors">About Us</a>
                        </li>
                        <li>
                            <a href="/vehicles.php" class="text-gray-600 hover:text-primary transition-colors">Our Vehicles</a>
                        </li>
                        <li>
                            <a href="/contact.php" class="text-gray-600 hover:text-primary transition-colors">Contact</a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-span-1">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase mb-4">Contact Us</h4>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt text-primary mt-1 mr-2"></i>
                            <span class="text-gray-600">123 Rental Street, Jakarta, Indonesia</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone text-primary mr-2"></i>
                            <span class="text-gray-600">+62 123 456 7890</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-primary mr-2"></i>
                            <span class="text-gray-600">info@rentalbis.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-200 mt-8 pt-8">
                <p class="text-center text-gray-500 text-sm">
                    © <?php echo date('Y'); ?> RentalBis. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Custom JavaScript -->
    <script>
        // Add smooth scrolling to all links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
