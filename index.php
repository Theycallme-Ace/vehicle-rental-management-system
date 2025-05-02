<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'gps_api.php';

// Initialize GPS Tracker
$gpsTracker = new GPSTracker();

// Get vehicle type filter
$type_filter = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';

// Prepare the SQL query with filters
$sql = "SELECT v.*, GROUP_CONCAT(f.name) as facilities 
        FROM vehicles v 
        LEFT JOIN vehicle_facilities vf ON v.id = vf.vehicle_id 
        LEFT JOIN facilities f ON vf.facility_id = f.id 
        WHERE v.status = 'available'";

if ($type_filter !== 'all') {
    $sql .= " AND v.vehicle_type = :type";
}

if ($search) {
    $sql .= " AND (v.vehicle_name LIKE :search OR v.description LIKE :search)";
}

$sql .= " GROUP BY v.id ORDER BY v.created_at DESC";

$stmt = $pdo->prepare($sql);

if ($type_filter !== 'all') {
    $stmt->bindValue(':type', $type_filter);
}

if ($search) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();
$vehicles = $stmt->fetchAll();

// Include header and navigation
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Hero Section -->
<div class="relative bg-primary">
    <div class="absolute inset-0">
        <img class="w-full h-full object-cover" src="https://images.pexels.com/photos/385998/pexels-photo-385998.jpeg" alt="Fleet">
        <div class="absolute inset-0 bg-primary mix-blend-multiply"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">Premium Vehicle Rental</h1>
        <p class="mt-6 text-xl text-gray-300 max-w-3xl">
            Choose from our wide selection of well-maintained buses and cars. All vehicles come with GPS tracking for your safety and peace of mind.
        </p>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 md:space-x-4">
        <!-- Search Form -->
        <form class="w-full md:w-1/2">
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
        <div class="flex space-x-2">
            <a href="?type=all" 
               class="<?php echo $type_filter === 'all' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> px-4 py-2 rounded-md text-sm font-medium border transition-colors">
                All Vehicles
            </a>
            <a href="?type=bus" 
               class="<?php echo $type_filter === 'bus' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> px-4 py-2 rounded-md text-sm font-medium border transition-colors">
                Buses
            </a>
            <a href="?type=car" 
               class="<?php echo $type_filter === 'car' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> px-4 py-2 rounded-md text-sm font-medium border transition-colors">
                Cars
            </a>
        </div>
    </div>
</div>

<!-- Vehicles Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (empty($vehicles)): ?>
    <div class="text-center py-12">
        <i class="fas fa-car text-gray-400 text-5xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900">No vehicles found</h3>
        <p class="mt-2 text-sm text-gray-500">Try adjusting your search or filter criteria</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($vehicles as $vehicle): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <!-- Vehicle Image -->
            <div class="relative h-48">
                <?php if ($vehicle['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($vehicle['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($vehicle['vehicle_name']); ?>"
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <img src="https://images.pexels.com/photos/385998/pexels-photo-385998.jpeg" 
                         alt="Default vehicle image"
                         class="w-full h-full object-cover">
                <?php endif; ?>
                
                <!-- Vehicle Type Badge -->
                <div class="absolute top-4 right-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white text-primary shadow">
                        <?php if ($vehicle['vehicle_type'] === 'bus'): ?>
                            <i class="fas fa-bus mr-1"></i> Bus
                        <?php else: ?>
                            <i class="fas fa-car mr-1"></i> Car
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- Vehicle Details -->
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-900">
                    <?php echo htmlspecialchars($vehicle['vehicle_name']); ?>
                </h3>
                
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Year: <?php echo htmlspecialchars($vehicle['year']); ?>
                </div>

                <!-- Facilities -->
                <?php if ($vehicle['facilities']): ?>
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-900">Facilities:</h4>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <?php foreach (explode(',', $vehicle['facilities']) as $facility): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo htmlspecialchars($facility); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- GPS Status -->
                <?php if ($vehicle['gps_unit_id']): 
                    $gpsStatus = $gpsTracker->isUnitActive($vehicle['gps_unit_id']);
                ?>
                <div class="mt-4 flex items-center">
                    <span class="inline-flex items-center text-sm">
                        <span class="flex-shrink-0 h-2 w-2 rounded-full <?php echo $gpsStatus ? 'bg-green-500' : 'bg-gray-400'; ?> mr-2"></span>
                        <?php echo $gpsStatus ? 'GPS Active' : 'GPS Inactive'; ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- Price and Book Button -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-primary">
                            Rp <?php echo number_format($vehicle['daily_rate'], 0, ',', '.'); ?>
                        </span>
                        <span class="text-sm text-gray-500 ml-1">/day</span>
                    </div>
                    
                    <a href="/booking.php?vehicle_id=<?php echo $vehicle['id']; ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
