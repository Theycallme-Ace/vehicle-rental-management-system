<?php
require_once '../config.php';
require_once '../db_connect.php';
require_once '../includes/session.php';
require_once '../gps_api.php';

// Require admin privileges or higher
requireRole('admin');

// Initialize GPS Tracker
$gpsTracker = new GPSTracker();

// Handle vehicle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vehicle'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $vehicle_id = $_POST['vehicle_id'] ?? '';
    
    try {
        // Get vehicle image path before deletion
        $stmt = $pdo->prepare("SELECT image_path FROM vehicles WHERE id = ?");
        $stmt->execute([$vehicle_id]);
        $vehicle = $stmt->fetch();

        // Delete vehicle facilities first (due to foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM vehicle_facilities WHERE vehicle_id = ?");
        $stmt->execute([$vehicle_id]);

        // Delete the vehicle
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$vehicle_id]);

        // Delete the image file if it exists
        if ($vehicle && $vehicle['image_path'] && file_exists('../' . $vehicle['image_path'])) {
            unlink('../' . $vehicle['image_path']);
        }

        $_SESSION['success'] = 'Vehicle deleted successfully.';
    } catch (PDOException $e) {
        logError($e->getMessage());
        $_SESSION['error'] = 'Failed to delete vehicle.';
    }
    
    header('Location: /admin/vehicles.php');
    exit();
}

// Get filters
$type_filter = $_GET['type'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Prepare the SQL query with filters
$sql = "SELECT v.*, GROUP_CONCAT(f.name) as facilities 
        FROM vehicles v 
        LEFT JOIN vehicle_facilities vf ON v.id = vf.vehicle_id 
        LEFT JOIN facilities f ON vf.facility_id = f.id";

$where_conditions = [];
$params = [];

if ($type_filter !== 'all') {
    $where_conditions[] = "v.vehicle_type = ?";
    $params[] = $type_filter;
}

if ($status_filter !== 'all') {
    $where_conditions[] = "v.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(v.vehicle_name LIKE ? OR v.license_plate LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " GROUP BY v.id ORDER BY v.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    logError($e->getMessage());
    $error = 'An error occurred while fetching vehicles.';
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Vehicle Management
                    </h2>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <a href="/admin/add_vehicle.php" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Vehicle
                    </a>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="mt-8 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <!-- Search Form -->
                <form class="flex-1 max-w-lg">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search vehicles..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <button type="submit" class="absolute right-0 top-0 mt-2 mr-2">
                            <i class="fas fa-search text-gray-400"></i>
                        </button>
                    </div>
                </form>

                <!-- Filter Buttons -->
                <div class="flex space-x-4">
                    <select name="type" 
                            onchange="window.location.href='?type='+this.value+'&status=<?php echo $status_filter; ?>'"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                        <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="bus" <?php echo $type_filter === 'bus' ? 'selected' : ''; ?>>Buses</option>
                        <option value="car" <?php echo $type_filter === 'car' ? 'selected' : ''; ?>>Cars</option>
                    </select>

                    <select name="status" 
                            onchange="window.location.href='?type=<?php echo $type_filter; ?>&status='+this.value"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="maintenance" <?php echo $status_filter === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="rented" <?php echo $status_filter === 'rented' ? 'selected' : ''; ?>>Rented</option>
                    </select>
                </div>
            </div>

            <!-- Vehicles Grid -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mt-4 bg-green-50 border-l-4 border-green-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                <ul role="list" class="divide-y divide-gray-200">
                    <?php if (empty($vehicles)): ?>
                        <li class="px-6 py-4 text-center text-gray-500">
                            No vehicles found matching your criteria
                        </li>
                    <?php else: ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <li>
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <!-- Vehicle Image -->
                                            <div class="flex-shrink-0 h-16 w-16">
                                                <?php if ($vehicle['image_path']): ?>
                                                    <img class="h-16 w-16 rounded-lg object-cover" 
                                                         src="/<?php echo htmlspecialchars($vehicle['image_path']); ?>" 
                                                         alt="<?php echo htmlspecialchars($vehicle['vehicle_name']); ?>">
                                                <?php else: ?>
                                                    <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                                        <i class="fas <?php echo $vehicle['vehicle_type'] === 'bus' ? 'fa-bus' : 'fa-car'; ?> text-gray-400 text-2xl"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Vehicle Details -->
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($vehicle['vehicle_name']); ?>
                                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php echo match($vehicle['status']) {
                                                            'available' => 'bg-green-100 text-green-800',
                                                            'maintenance' => 'bg-yellow-100 text-yellow-800',
                                                            'rented' => 'bg-blue-100 text-blue-800',
                                                            default => 'bg-gray-100 text-gray-800'
                                                        }; ?>">
                                                        <?php echo ucfirst($vehicle['status']); ?>
                                                    </span>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    License: <?php echo htmlspecialchars($vehicle['license_plate']); ?>
                                                </div>
                                                <?php if ($vehicle['facilities']): ?>
                                                    <div class="mt-1 flex flex-wrap gap-1">
                                                        <?php foreach (explode(',', $vehicle['facilities']) as $facility): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                <?php echo htmlspecialchars($facility); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center space-x-4">
                                            <?php if ($vehicle['gps_unit_id']): ?>
                                                <?php $gpsStatus = $gpsTracker->isUnitActive($vehicle['gps_unit_id']); ?>
                                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium <?php echo $gpsStatus ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                    <span class="h-2 w-2 rounded-full <?php echo $gpsStatus ? 'bg-green-400' : 'bg-gray-400'; ?> mr-1"></span>
                                                    GPS <?php echo $gpsStatus ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            <?php endif; ?>

                                            <a href="/admin/edit_vehicle.php?id=<?php echo $vehicle['id']; ?>" 
                                               class="text-primary hover:text-primary-dark">
                                                <i class="fas fa-edit"></i>
                                                <span class="sr-only">Edit</span>
                                            </a>

                                            <form action="/admin/vehicles.php" method="POST" class="inline-block" 
                                                  onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                <button type="submit" name="delete_vehicle" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="sr-only">Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
