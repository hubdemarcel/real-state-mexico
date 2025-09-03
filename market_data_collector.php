<?php
require_once 'config.php';

/*
 * Market Data Collector
 *
 * This script collects market data from various sources:
 * - INEGI API (simulated for now)
 * - Web scraping from real estate websites
 * - Manual data entry for specific locations like Sayulita
 */

class MarketDataCollector
{
    private $conn;
    private $startTime;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->startTime = microtime(true);
    }

    /**
     * Main data collection method
     */
    public function collectAllData()
    {
        echo "Starting market data collection...<br>";

        try {
            // Collect data for Sayulita as example
            $this->collectSayulitaData();

            // Collect general Mexican market data
            $this->collectMexicanMarketData();

            // Calculate trends
            $this->calculateMarketTrends();

            $this->logCollection('all_sources', null, 'success');

            echo "Market data collection completed successfully!<br>";

        } catch (Exception $e) {
            echo "Error during data collection: " . $e->getMessage() . "<br>";
            $this->logCollection('all_sources', null, 'failed', $e->getMessage());
        }
    }

    /**
     * Collect Sayulita-specific market data
     */
    private function collectSayulitaData()
    {
        echo "Collecting Sayulita market data...<br>";

        $sayulitaData = [
            [
                'location' => 'Sayulita',
                'data_type' => 'price_per_sqm',
                'value' => 85000, // MXN per square meter
                'period_start' => date('Y-m-01', strtotime('-1 month')),
                'period_end' => date('Y-m-t', strtotime('-1 month')),
                'data_source' => 'local_research'
            ],
            [
                'location' => 'Sayulita',
                'data_type' => 'avg_price',
                'value' => 3200000, // Average property price
                'period_start' => date('Y-m-01', strtotime('-1 month')),
                'period_end' => date('Y-m-t', strtotime('-1 month')),
                'data_source' => 'local_research'
            ],
            [
                'location' => 'Sayulita',
                'data_type' => 'demand_index',
                'value' => 8.5, // Scale of 1-10
                'period_start' => date('Y-m-01', strtotime('-1 month')),
                'period_end' => date('Y-m-t', strtotime('-1 month')),
                'data_source' => 'local_research'
            ]
        ];

        foreach ($sayulitaData as $data) {
            $this->insertMarketData($data);
        }

        // Add location coordinates for Sayulita
        $this->insertLocationCoordinates('Sayulita', 20.8697, -105.4416, 'city');

        echo "Sayulita data collected successfully<br>";
    }

    /**
     * Collect general Mexican market data (simulated INEGI data)
     */
    private function collectMexicanMarketData()
    {
        echo "Collecting Mexican market data...<br>";

        $mexicoData = [
            [
                'location' => 'Puerto Vallarta',
                'data_type' => 'price_per_sqm',
                'value' => 65000,
                'period_start' => date('Y-m-01', strtotime('-1 month')),
                'period_end' => date('Y-m-t', strtotime('-1 month')),
                'data_source' => 'inegi_simulated'
            ],
            [
                'location' => 'Riviera Nayarit',
                'data_type' => 'price_per_sqm',
                'value' => 55000,
                'period_start' => date('Y-m-01', strtotime('-1 month')),
                'period_end' => date('Y-m-t', strtotime('-1 month')),
                'data_source' => 'inegi_simulated'
            ],
            [
                'location' => 'Mexico City',
                'data_type' => 'price_per_sqm',
                'value' => 185000,
                'period_start' => date('Y-m-01', strtotime('-1 month')),
                'period_end' => date('Y-m-t', strtotime('-1 month')),
                'data_source' => 'inegi_simulated'
            ],
            [
                'location' => 'Guadalajara',
                'data_type' => 'price_per_sqm',
                'value' => 125000,
                'period_start' => date('Y-m-01', strtotime('-1 month')),
                'period_end' => date('Y-m-t', strtotime('-1 month')),
                'data_source' => 'inegi_simulated'
            ]
        ];

        foreach ($mexicoData as $data) {
            $this->insertMarketData($data);
        }

        // Add location coordinates
        $this->insertLocationCoordinates('Puerto Vallarta', 20.6534, -105.2253, 'city');
        $this->insertLocationCoordinates('Riviera Nayarit', 20.8000, -105.5000, 'region');
        $this->insertLocationCoordinates('Mexico City', 19.4326, -99.1332, 'city');
        $this->insertLocationCoordinates('Guadalajara', 20.6597, -103.3496, 'city');

        echo "Mexican market data collected successfully<br>";
    }

    /**
     * Calculate market trends based on collected data
     */
    private function calculateMarketTrends()
    {
        echo "Calculating market trends...<br>";

        // Get current month data
        $currentMonth = date('Y-m-01');
        $lastMonth = date('Y-m-01', strtotime('-1 month'));

        // Calculate price growth for Sayulita
        $this->calculatePriceGrowth('Sayulita', $currentMonth, $lastMonth);

        // Calculate trends for other locations
        $locations = ['Puerto Vallarta', 'Riviera Nayarit', 'Mexico City', 'Guadalajara'];
        foreach ($locations as $location) {
            $this->calculatePriceGrowth($location, $currentMonth, $lastMonth);
        }

        echo "Market trends calculated successfully<br>";
    }

    /**
     * Calculate price growth for a specific location
     */
    private function calculatePriceGrowth($location, $currentMonth, $lastMonth)
    {
        // Get current month average price
        $current_sql = "SELECT AVG(value) as avg_price FROM market_data
                       WHERE location = ? AND data_type = 'price_per_sqm'
                       AND period_start >= ? AND period_start < DATE_ADD(?, INTERVAL 1 MONTH)";
        $stmt = $this->conn->prepare($current_sql);
        $stmt->bind_param("sss", $location, $currentMonth, $currentMonth);
        $stmt->execute();
        $current_result = $stmt->get_result();
        $current_data = $current_result->fetch_assoc();
        $current_price = $current_data['avg_price'] ?? 0;

        // Get last month average price
        $last_sql = "SELECT AVG(value) as avg_price FROM market_data
                    WHERE location = ? AND data_type = 'price_per_sqm'
                    AND period_start >= ? AND period_start < DATE_ADD(?, INTERVAL 1 MONTH)";
        $stmt = $this->conn->prepare($last_sql);
        $stmt->bind_param("sss", $location, $lastMonth, $lastMonth);
        $stmt->execute();
        $last_result = $stmt->get_result();
        $last_data = $last_result->fetch_assoc();
        $last_price = $last_data['avg_price'] ?? 0;

        if ($last_price > 0 && $current_price > 0) {
            $growth_rate = (($current_price - $last_price) / $last_price) * 100;

            // Insert trend data
            $trend_sql = "INSERT INTO property_market_trends
                         (location, property_type, trend_type, value, period_months, confidence_level)
                         VALUES (?, 'residential', 'price_growth', ?, 1, 0.85)
                         ON DUPLICATE KEY UPDATE value = VALUES(value), calculated_at = CURRENT_TIMESTAMP";

            $stmt = $this->conn->prepare($trend_sql);
            $stmt->bind_param("sd", $location, $growth_rate);
            $stmt->execute();
        }

        $stmt->close();
    }

    /**
     * Insert market data into database
     */
    private function insertMarketData($data)
    {
        $sql = "INSERT INTO market_data
                (location, data_type, value, period_start, period_end, data_source, period_type)
                VALUES (?, ?, ?, ?, ?, ?, 'monthly')
                ON DUPLICATE KEY UPDATE
                value = VALUES(value),
                updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssdsss",
            $data['location'],
            $data['data_type'],
            $data['value'],
            $data['period_start'],
            $data['period_end'],
            $data['data_source']
        );

        if ($stmt->execute()) {
            echo "✓ Inserted {$data['data_type']} for {$data['location']}: $" . number_format($data['value']) . "<br>";
        } else {
            echo "✗ Failed to insert data for {$data['location']}: " . $stmt->error . "<br>";
        }

        $stmt->close();
    }

    /**
     * Insert location coordinates
     */
    private function insertLocationCoordinates($location, $lat, $lng, $type)
    {
        $sql = "INSERT INTO location_coordinates
                (location_name, latitude, longitude, location_type)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                latitude = VALUES(latitude),
                longitude = VALUES(longitude)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdds", $location, $lat, $lng, $type);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Log data collection activity
     */
    private function logCollection($source, $location, $status, $error = null)
    {
        $execution_time = microtime(true) - $this->startTime;

        $sql = "INSERT INTO data_collection_log
                (data_source, location, status, error_message, execution_time_seconds)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssd", $source, $location, $status, $error, $execution_time);
        $stmt->execute();
        $stmt->close();
    }
}

// Run data collection
$collector = new MarketDataCollector($conn);
$collector->collectAllData();

$conn->close();
?>