<?php
require_once 'config.php';
require_once 'error_handler.php';

class GPSTracker {
    private $apiUrl;
    private $apiKey;
    private $timeout = 10; // seconds

    public function __construct() {
        $this->apiUrl = GPS_API_URL;
        $this->apiKey = GPS_API_KEY;
    }

    /**
     * Fetch GPS data for a specific vehicle unit
     * @param string $gps_unit_id The GPS unit ID of the vehicle
     * @return array GPS data including latitude, longitude, and timestamp
     */
    public function fetchGPSData($gps_unit_id) {
        try {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiUrl . '/' . urlencode($gps_unit_id),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Accept: application/json'
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                throw new Exception('GPS API request failed: ' . curl_error($ch));
            }
            
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception('GPS API returned error code: ' . $httpCode);
            }

            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response from GPS API');
            }

            return [
                'success' => true,
                'data' => [
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'timestamp' => $data['timestamp'] ?? null,
                    'speed' => $data['speed'] ?? null,
                    'status' => $data['status'] ?? 'unknown'
                ]
            ];

        } catch (Exception $e) {
            logError('GPS API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Location data unavailable',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if a GPS unit is currently active
     * @param string $gps_unit_id The GPS unit ID to check
     * @return bool True if the unit is active and reporting data
     */
    public function isUnitActive($gps_unit_id) {
        $result = $this->fetchGPSData($gps_unit_id);
        if (!$result['success']) {
            return false;
        }
        
        // Check if we received data in the last 5 minutes
        $lastUpdate = strtotime($result['data']['timestamp']);
        return (time() - $lastUpdate) <= 300; // 5 minutes in seconds
    }

    /**
     * Get the last known location of a vehicle
     * @param string $gps_unit_id The GPS unit ID
     * @return array|null Location data or null if unavailable
     */
    public function getLastLocation($gps_unit_id) {
        $result = $this->fetchGPSData($gps_unit_id);
        if (!$result['success']) {
            return null;
        }
        
        return [
            'latitude' => $result['data']['latitude'],
            'longitude' => $result['data']['longitude'],
            'last_update' => $result['data']['timestamp'],
            'status' => $result['data']['status']
        ];
    }
}
?>
