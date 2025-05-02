
Built by https://www.blackbox.ai

---

# Vehicle Rental Management System

## Project Overview
The Vehicle Rental Management System is a web-based application designed to facilitate the rental of vehicles, including buses and cars. The application integrates with a GPS tracking API for real-time vehicle location monitoring, providing users with vital information about available vehicles and their statuses. The platform is built on PHP and utilizes a SQLite database to manage vehicle data, user authentication, and error handling.

## Installation

To install and set up the Vehicle Rental Management System, follow these steps:

1. **Clone the repository:**
   ```bash
   git clone https://your-repo-url.git
   ```

2. **Navigate into the project directory:**
   ```bash
   cd vehicle-rental
   ```

3. **Set Up the Database:**
   - Ensure that the SQLite database `rentalbis.db` is created in the `database` directory. The project will handle its creation if it doesn't exist.

4. **Configure the Application:**
   - Open the `config.php` file and replace the `GPS_API_KEY` and `GPS_API_URL` values with your actual API credentials.

5. **Install necessary PHP extensions:**
   - Make sure your PHP installation includes the necessary extensions such as PDO for database interaction and cURL for API requests.

## Usage

1. **Start your local PHP server:**
   ```bash
   php -S localhost:8000
   ```

2. **Access the application:**
   Open your web browser and navigate to `http://localhost:8000`.

3. **User Authentication:**
   - Register a new user or login with existing credentials. Users can access different roles and functionalities based on their login status.

4. **Browse Vehicles:**
   - Users can search for and filter available vehicles based on type and other specifications.

5. **Real-Time GPS Tracking:**
   - Users will receive real-time data about vehicle statuses through the integrated GPS API.

## Features

- User registration and login systems.
- Vehicle search and filter functionality.
- Integration with a GPS API for real-time tracking.
- Error handling and logging mechanism.
- Responsive UI components for better user experience.

## Dependencies

While there is no `package.json` file in the project as it primarily uses PHP, ensure your server has the following PHP extensions installed for proper functionality:
- `PDO` for database operations.
- `cURL` for making API requests.

## Project Structure

The following outlines the primary structure of the project:

```
/vehicle-rental
│
├── config.php              # Configuration file for database and API setup
├── db_connect.php          # Database connection management
├── error_handler.php       # Custom error logging and handling
├── gps_api.php             # Class handling GPS data fetching and operations
├── index.php               # Main entry point for rendering available vehicles
├── login.php               # User login functionality
├── logout.php              # User logout functionality
├── register.php            # User registration functionality
├── uploads/                # Directory for uploading vehicle images
├── database/               # Directory for SQLite database files
├── logs/                   # Directory for error logs
│
└── includes/               # Directory for header, footer, and navigation components
```

## Conclusion

This vehicle rental management system aims to provide an efficient solution for both vehicle management and rental processes. By integrating GPS tracking, it enhances security and convenience for rental users. For any issues or contributions, feel free to raise an issue or submit a pull request.