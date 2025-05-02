-- Users table with role-based access control
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('superadmin', 'admin', 'manager', 'staff', 'user')) DEFAULT 'user',
    full_name TEXT,
    phone TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    is_active INTEGER DEFAULT 1
);

-- Vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vehicle_name TEXT NOT NULL,
    vehicle_type TEXT NOT NULL CHECK (vehicle_type IN ('bus', 'car')),
    year INTEGER NOT NULL,
    license_plate TEXT UNIQUE NOT NULL,
    passenger_capacity INTEGER NOT NULL,
    gps_unit_id TEXT UNIQUE,
    daily_rate DECIMAL(10,2) NOT NULL,
    image_path TEXT,
    description TEXT,
    status TEXT CHECK (status IN ('available', 'maintenance', 'rented')) DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Facilities table
CREATE TABLE IF NOT EXISTS facilities (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    icon TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicle facilities relationship table
CREATE TABLE IF NOT EXISTS vehicle_facilities (
    vehicle_id INTEGER,
    facility_id INTEGER,
    PRIMARY KEY (vehicle_id, facility_id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vehicle_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status TEXT CHECK (status IN ('pending', 'confirmed', 'cancelled', 'completed')) DEFAULT 'pending',
    payment_status TEXT CHECK (payment_status IN ('unpaid', 'partial', 'paid')) DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- Insert default facilities
INSERT OR IGNORE INTO facilities (name, icon, description) VALUES
('Air Conditioning', 'fas fa-snowflake', 'Climate control system'),
('WiFi', 'fas fa-wifi', 'Internet connectivity'),
('TV/DVD', 'fas fa-tv', 'Entertainment system'),
('Audio System', 'fas fa-music', 'Premium sound system'),
('Reclining Seats', 'fas fa-chair', 'Comfortable seating'),
('Luggage Space', 'fas fa-suitcase', 'Large storage capacity'),
('USB Charging', 'fas fa-plug', 'Device charging ports'),
('GPS Navigation', 'fas fa-location-dot', 'Real-time tracking system');

-- Insert default superadmin user (password: admin123)
INSERT OR IGNORE INTO users (username, email, password, role, full_name) VALUES
('superadmin', 'admin@rentalbis.com', '$2y$10$e0NRXq6q6q6q6q6q6q6q6u6q6q6q6q6q6q6q6q6q6q6q6q6q6q6q6', 'superadmin', 'System Administrator');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_vehicles_type ON vehicles(vehicle_type);
CREATE INDEX IF NOT EXISTS idx_vehicles_status ON vehicles(status);
CREATE INDEX IF NOT EXISTS idx_bookings_dates ON bookings(start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
