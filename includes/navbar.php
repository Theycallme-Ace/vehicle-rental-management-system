<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Navigation -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo and Primary Nav -->
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="text-2xl font-bold text-primary">
                        <i class="fas fa-bus-alt mr-2"></i>RentalBis
                    </a>
                </div>
                
                <!-- Primary Navigation -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="/" class="<?php echo $current_page === 'index.php' ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Home
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('staff')): ?>
                        <a href="/admin/dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasRole('admin')): ?>
                        <a href="/admin/vehicles.php" class="<?php echo $current_page === 'vehicles.php' ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Manage Vehicles
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasRole('superadmin')): ?>
                        <a href="/admin/users.php" class="<?php echo $current_page === 'users.php' ? 'border-primary text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Manage Users
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Secondary Navigation / User Menu -->
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <?php if (isLoggedIn()): ?>
                    <!-- User Dropdown -->
                    <div class="ml-3 relative group">
                        <button type="button" class="bg-white rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" id="user-menu-button">
                            <span class="sr-only">Open user menu</span>
                            <div class="h-8 w-8 rounded-full bg-primary text-white flex items-center justify-center">
                                <span class="text-sm font-medium"><?php echo substr($_SESSION['username'] ?? 'U', 0, 1); ?></span>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="hidden group-hover:block absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5" role="menu">
                            <div class="px-4 py-2 text-xs text-gray-500">
                                Logged in as <span class="font-medium"><?php echo $_SESSION['username'] ?? ''; ?></span>
                            </div>
                            
                            <a href="/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Profile</a>
                            
                            <?php if (hasRole('staff')): ?>
                            <a href="/admin/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Dashboard</a>
                            <?php endif; ?>
                            
                            <div class="border-t border-gray-100"></div>
                            
                            <a href="/logout.php" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50" role="menuitem">
                                Sign out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-4">
                        <a href="/login.php" class="text-gray-500 hover:text-gray-700">Login</a>
                        <a href="/register.php" class="bg-primary text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-primary-dark transition-colors">
                            Register
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile menu button -->
            <div class="flex items-center sm:hidden">
                <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="sm:hidden hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
            <a href="/" class="<?php echo $current_page === 'index.php' ? 'bg-primary border-primary text-white' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                Home
            </a>
            
            <?php if (isLoggedIn()): ?>
                <?php if (hasRole('staff')): ?>
                <a href="/admin/dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'bg-primary border-primary text-white' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Dashboard
                </a>
                <?php endif; ?>
                
                <a href="/profile.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Profile
                </a>
                
                <a href="/logout.php" class="border-transparent text-red-500 hover:bg-red-50 hover:border-red-300 hover:text-red-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Sign out
                </a>
            <?php else: ?>
                <a href="/login.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Login
                </a>
                <a href="/register.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Mobile menu toggle script -->
<script>
document.querySelector('.mobile-menu-button').addEventListener('click', function() {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});
</script>
