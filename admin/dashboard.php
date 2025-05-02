<?php
require_once '../config.php';
require_once '../db_connect.php';
require_once '../includes/session.php';

// Require staff privileges or higher
requireRole('staff');

// Fetch summary statistics
try {
    // Total vehicles count
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_vehicles,
        SUM(CASE WHEN vehicle_type = 'bus' THEN 1 ELSE 0 END) as total_buses,
        SUM(CASE WHEN vehicle_type = 'car' THEN 1 ELSE 0 END) as total_cars,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_vehicles,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_vehicles,
        SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) as rented_vehicles
        FROM vehicles");
    $vehicleStats = $stmt->fetch();

    // Recent bookings
    $stmt = $pdo->query("SELECT b.*, v.vehicle_name, u.username 
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC LIMIT 5");
    $recentBookings = $stmt->fetchAll();

    // Active GPS units
    $stmt = $pdo->query("SELECT COUNT(*) as total_gps FROM vehicles WHERE gps_unit_id IS NOT NULL");
    $gpsStats = $stmt->fetch();

} catch (PDOException $e) {
    logError($e->getMessage());
    $error = 'An error occurred while fetching dashboard data.';
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="min-h-screen bg-gray-100">
    <!-- Main content -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Here's what's happening with your vehicle fleet today.
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <?php if (hasRole('admin')): ?>
                    <a href="/admin/add_vehicle.php" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Vehicle
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Total Vehicles -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-car-side text-2xl text-primary"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Vehicles
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            <?php echo $vehicleStats['total_vehicles']; ?>
                                        </div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold">
                                            <span class="text-gray-500">
                                                (<?php echo $vehicleStats['total_buses']; ?> buses, <?php echo $vehicleStats['total_cars']; ?> cars)
                                            </span>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Vehicles -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-2xl text-green-500"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Available Vehicles
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            <?php echo $vehicleStats['available_vehicles']; ?>
                                        </div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                            <span class="sr-only">Available</span>
                                            Ready to rent
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GPS Tracked Units -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-satellite-dish text-2xl text-blue-500"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        GPS Tracked Units
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            <?php echo $gpsStats['total_gps']; ?>
                                        </div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold text-blue-600">
                                            <span class="sr-only">Tracked</span>
                                            Active units
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="mt-8">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Recent Bookings
                        </h3>
                        <a href="/admin/bookings.php" class="text-sm text-primary hover:text-primary-dark">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="flex flex-col">
                        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Booking ID
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Vehicle
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Customer
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Status
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php if (empty($recentBookings)): ?>
                                            <tr>
                                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    No recent bookings found
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($recentBookings as $booking): ?>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        #<?php echo $booking['id']; ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($booking['vehicle_name']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($booking['username']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            <?php echo match($booking['status']) {
                                                                'confirmed' => 'bg-green-100 text-green-800',
                                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                                'cancelled' => 'bg-red-100 text-red-800',
                                                                default => 'bg-gray-100 text-gray-800'
                                                            }; ?>">
                                                            <?php echo ucfirst($booking['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?php if (hasRole('admin')): ?>
                <!-- Vehicle Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Vehicle Management</h3>
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <a href="/admin/vehicles.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary bg-blue-50 hover:bg-blue-100">
                                <i class="fas fa-list mr-2"></i>
                                View All Vehicles
                            </a>
                            <a href="/admin/add_vehicle.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark">
                                <i class="fas fa-plus mr-2"></i>
                                Add Vehicle
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Booking Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Booking Management</h3>
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <a href="/admin/bookings.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary bg-blue-50 hover:bg-blue-100">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                View Bookings
                            </a>
                            <a href="/admin/bookings.php?status=pending" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark">
                                <i class="fas fa-clock mr-2"></i>
                                Pending Approvals
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
