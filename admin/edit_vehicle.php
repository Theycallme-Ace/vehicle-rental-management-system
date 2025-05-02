<?php
require_once '../config.php';
require_once '../db_connect.php';
require_once '../includes/session.php';

// Require admin privileges or higher
requireRole('admin');

// Get vehicle ID from URL
$vehicle_id = $_GET['id'] ?? null;

if (!$vehicle_id) {
    $_SESSION['error'] = 'No vehicle specified.';
    header('Location: /admin/vehicles.php');
    exit();
}

// Fetch vehicle data
try {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        $_SESSION['error'] = 'Vehicle not found.';
        header('Location: /admin/vehicles.php');
        exit();
    }

    // Fetch vehicle's facilities
    $stmt = $pdo->prepare("SELECT facility_id FROM vehicle_facilities WHERE vehicle_id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle_facilities = array_column($stmt->fetchAll(), 'facility_id');

    // Fetch all available facilities
    $stmt = $pdo->query("SELECT * FROM facilities ORDER BY name");
    $facilities = $stmt->fetchAll();

} catch (PDOException $e) {
    logError($e->getMessage());
    $_SESSION['error'] = 'Failed to fetch vehicle data.';
    header('Location: /admin/vehicles.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $vehicle_name = $_POST['vehicle_name'] ?? '';
    $vehicle_type = $_POST['vehicle_type'] ?? '';
    $year = $_POST['year'] ?? '';
    $license_plate = $_POST['license_plate'] ?? '';
    $passenger_capacity = $_POST['passenger_capacity'] ?? '';
    $daily_rate = $_POST['daily_rate'] ?? '';
    $gps_unit_id = $_POST['gps_unit_id'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? '';
    $selected_facilities = $_POST['facilities'] ?? [];

    $errors = [];

    // Validate inputs
    if (empty($vehicle_name)) $errors[] = 'Vehicle name is required.';
    if (empty($vehicle_type)) $errors[] = 'Vehicle type is required.';
    if (empty($year) || !is_numeric($year)) $errors[] = 'Valid year is required.';
    if (empty($license_plate)) $errors[] = 'License plate is required.';
    if (empty($passenger_capacity) || !is_numeric($passenger_capacity)) $errors[] = 'Valid passenger capacity is required.';
    if (empty($daily_rate) || !is_numeric($daily_rate)) $errors[] = 'Valid daily rate is required.';

    // Handle image upload if new image is provided
    $image_path = $vehicle['image_path'];
    if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['vehicle_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Invalid file type. Only JPG, PNG, and WebP images are allowed.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'File size too large. Maximum size is 5MB.';
        } else {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $upload_path = '../uploads/vehicles/' . $filename;

            // Create directory if it doesn't exist
            if (!is_dir('../uploads/vehicles')) {
                mkdir('../uploads/vehicles', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($vehicle['image_path'] && file_exists('../' . $vehicle['image_path'])) {
                    unlink('../' . $vehicle['image_path']);
                }
                $image_path = 'uploads/vehicles/' . $filename;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Update vehicle
            $stmt = $pdo->prepare("UPDATE vehicles SET 
                                    vehicle_name = ?, 
                                    vehicle_type = ?, 
                                    year = ?, 
                                    license_plate = ?,
                                    passenger_capacity = ?, 
                                    daily_rate = ?, 
                                    gps_unit_id = ?, 
                                    description = ?,
                                    status = ?,
                                    image_path = ?
                                 WHERE id = ?");
            
            $stmt->execute([
                $vehicle_name, $vehicle_type, $year, $license_plate,
                $passenger_capacity, $daily_rate, $gps_unit_id ?: null, $description,
                $status, $image_path, $vehicle_id
            ]);

            // Update facilities
            $stmt = $pdo->prepare("DELETE FROM vehicle_facilities WHERE vehicle_id = ?");
            $stmt->execute([$vehicle_id]);

            if (!empty($selected_facilities)) {
                $facility_values = array_fill(0, count($selected_facilities), "($vehicle_id, ?)");
                $sql = "INSERT INTO vehicle_facilities (vehicle_id, facility_id) VALUES " . implode(', ', $facility_values);
                $stmt = $pdo->prepare($sql);
                
                $position = 1;
                foreach ($selected_facilities as $facility_id) {
                    $stmt->bindValue($position++, $facility_id);
                }
                
                $stmt->execute();
            }

            $pdo->commit();
            $_SESSION['success'] = 'Vehicle updated successfully.';
            header('Location: /admin/vehicles.php');
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            logError($e->getMessage());
            $errors[] = 'Failed to update vehicle. Please try again.';

            // Delete newly uploaded image if database update failed
            if ($image_path !== $vehicle['image_path'] && file_exists('../' . $image_path)) {
                unlink('../' . $image_path);
            }
        }
    }
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
                        Edit Vehicle: <?php echo htmlspecialchars($vehicle['vehicle_name']); ?>
                    </h2>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <a href="/admin/vehicles.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Vehicles
                    </a>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Please correct the following errors:
                            </h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Edit Vehicle Form -->
            <div class="mt-8">
                <form action="/admin/edit_vehicle.php?id=<?php echo $vehicle_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-8">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="bg-white shadow sm:rounded-md">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <!-- Vehicle Name -->
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="vehicle_name" class="block text-sm font-medium text-gray-700">
                                        Vehicle Name
                                    </label>
                                    <input type="text" 
                                           name="vehicle_name" 
                                           id="vehicle_name"
                                           value="<?php echo htmlspecialchars($vehicle['vehicle_name']); ?>"
                                           required
                                           class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <!-- Vehicle Type -->
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="vehicle_type" class="block text-sm font-medium text-gray-700">
                                        Vehicle Type
                                    </label>
                                    <select name="vehicle_type" 
                                            id="vehicle_type"
                                            required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                        <option value="bus" <?php echo $vehicle['vehicle_type'] === 'bus' ? 'selected' : ''; ?>>Bus</option>
                                        <option value="car" <?php echo $vehicle['vehicle_type'] === 'car' ? 'selected' : ''; ?>>Car</option>
                                    </select>
                                </div>

                                <!-- Year -->
                                <div class="col-span-6 sm:col-span-2">
                                    <label for="year" class="block text-sm font-medium text-gray-700">
                                        Year
                                    </label>
                                    <input type="number" 
                                           name="year" 
                                           id="year"
                                           min="1900"
                                           max="<?php echo date('Y') + 1; ?>"
                                           value="<?php echo htmlspecialchars($vehicle['year']); ?>"
                                           required
                                           class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <!-- License Plate -->
                                <div class="col-span-6 sm:col-span-2">
                                    <label for="license_plate" class="block text-sm font-medium text-gray-700">
                                        License Plate
                                    </label>
                                    <input type="text" 
                                           name="license_plate" 
                                           id="license_plate"
                                           value="<?php echo htmlspecialchars($vehicle['license_plate']); ?>"
                                           required
                                           class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <!-- Status -->
                                <div class="col-span-6 sm:col-span-2">
                                    <label for="status" class="block text-sm font-medium text-gray-700">
                                        Status
                                    </label>
                                    <select name="status" 
                                            id="status"
                                            required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                        <option value="available" <?php echo $vehicle['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="maintenance" <?php echo $vehicle['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        <option value="rented" <?php echo $vehicle['status'] === 'rented' ? 'selected' : ''; ?>>Rented</option>
                                    </select>
                                </div>

                                <!-- Passenger Capacity -->
                                <div class="col-span-6 sm:col-span-2">
                                    <label for="passenger_capacity" class="block text-sm font-medium text-gray-700">
                                        Passenger Capacity
                                    </label>
                                    <input type="number" 
                                           name="passenger_capacity" 
                                           id="passenger_capacity"
                                           min="1"
                                           value="<?php echo htmlspecialchars($vehicle['passenger_capacity']); ?>"
                                           required
                                           class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <!-- Daily Rate -->
                                <div class="col-span-6 sm:col-span-2">
                                    <label for="daily_rate" class="block text-sm font-medium text-gray-700">
                                        Daily Rate (Rp)
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" 
                                               name="daily_rate" 
                                               id="daily_rate"
                                               min="0"
                                               step="1000"
                                               value="<?php echo htmlspecialchars($vehicle['daily_rate']); ?>"
                                               required
                                               class="focus:ring-primary focus:border-primary block w-full pl-12 sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <!-- GPS Unit ID -->
                                <div class="col-span-6 sm:col-span-2">
                                    <label for="gps_unit_id" class="block text-sm font-medium text-gray-700">
                                        GPS Unit ID (Optional)
                                    </label>
                                    <input type="text" 
                                           name="gps_unit_id" 
                                           id="gps_unit_id"
                                           value="<?php echo htmlspecialchars($vehicle['gps_unit_id'] ?? ''); ?>"
                                           class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <!-- Vehicle Image -->
                                <div class="col-span-6">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Vehicle Image
                                    </label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <?php if ($vehicle['image_path']): ?>
                                                <img src="/<?php echo htmlspecialchars($vehicle['image_path']); ?>" 
                                                     alt="Current vehicle image"
                                                     class="mx-auto h-32 w-auto rounded-lg mb-4">
                                            <?php endif; ?>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="vehicle_image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                    <span>Upload a new image</span>
                                                    <input id="vehicle_image" 
                                                           name="vehicle_image" 
                                                           type="file"
                                                           accept="image/jpeg,image/png,image/webp"
                                                           class="sr-only">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PNG, JPG, WebP up to 5MB
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Facilities -->
                                <div class="col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Facilities
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        <?php foreach ($facilities as $facility): ?>
                                            <div class="relative flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input type="checkbox"
                                                           name="facilities[]"
                                                           value="<?php echo $facility['id']; ?>"
                                                           <?php echo in_array($facility['id'], $vehicle_facilities) ? 'checked' : ''; ?>
                                                           class="focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label class="font-medium text-gray-700">
                                                        <?php echo htmlspecialchars($facility['name']); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-span-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Description
                                    </label>
                                    <textarea name="description" 
                                              id="description"
                                              rows="4"
                                              class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($vehicle['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <button type="submit" 
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Update Vehicle
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Script -->
<script>
document.getElementById('vehicle_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.createElement('img');
            preview.src = e.target.result;
            preview.className = 'mx-auto h-32 w-auto rounded-lg mb-4';
            
            const container = document.querySelector('.space-y-1');
            const existingPreview = container.querySelector('img');
            if (existingPreview) {
                container.removeChild(existingPreview);
            }
            container.insertBefore(preview, container.firstChild);
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
