<?php
/**
 * Database Migration System
 *
 * Provides a simple migration system for managing database schema changes
 */
class Migration {
    private $conn;
    private $migrationsTable = 'migrations';

    public function __construct($conn) {
        $this->conn = $conn;
        $this->ensureMigrationsTable();
    }

    /**
     * Ensure migrations table exists
     */
    private function ensureMigrationsTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('success', 'failed') DEFAULT 'success',
                error_message TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        if (!$this->conn->query($sql)) {
            throw new Exception("Failed to create migrations table: " . $this->conn->error);
        }
    }

    /**
     * Run a migration
     */
    public function run($migrationName, $upCallback) {
        // Check if migration already ran
        if ($this->hasRun($migrationName)) {
            echo "Migration '$migrationName' already executed. Skipping...\n";
            return true;
        }

        echo "Running migration: $migrationName\n";

        try {
            // Start transaction
            $this->conn->begin_transaction();

            // Execute migration
            $result = $upCallback($this->conn);

            if ($result === false) {
                throw new Exception("Migration callback returned false");
            }

            // Record successful migration
            $this->recordMigration($migrationName, 'success');

            // Commit transaction
            $this->conn->commit();

            echo "Migration '$migrationName' completed successfully!\n";
            return true;

        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollback();

            // Record failed migration
            $this->recordMigration($migrationName, 'failed', $e->getMessage());

            echo "Migration '$migrationName' failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Check if migration has already run
     */
    private function hasRun($migrationName) {
        $stmt = $this->conn->prepare("SELECT id FROM {$this->migrationsTable} WHERE migration_name = ? AND status = 'success'");
        $stmt->bind_param("s", $migrationName);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasRun = $result->num_rows > 0;
        $stmt->close();
        return $hasRun;
    }

    /**
     * Record migration execution
     */
    private function recordMigration($migrationName, $status, $errorMessage = null) {
        $stmt = $this->conn->prepare("INSERT INTO {$this->migrationsTable} (migration_name, status, error_message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $migrationName, $status, $errorMessage);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Get list of executed migrations
     */
    public function getExecutedMigrations() {
        $result = $this->conn->query("SELECT * FROM {$this->migrationsTable} ORDER BY executed_at DESC");
        $migrations = [];

        while ($row = $result->fetch_assoc()) {
            $migrations[] = $row;
        }

        return $migrations;
    }

    /**
     * Rollback a migration (basic implementation)
     */
    public function rollback($migrationName, $downCallback) {
        if (!$this->hasRun($migrationName)) {
            echo "Migration '$migrationName' has not been executed. Nothing to rollback.\n";
            return true;
        }

        echo "Rolling back migration: $migrationName\n";

        try {
            $this->conn->begin_transaction();

            // Execute rollback
            $result = $downCallback($this->conn);

            if ($result === false) {
                throw new Exception("Rollback callback returned false");
            }

            // Remove migration record
            $stmt = $this->conn->prepare("DELETE FROM {$this->migrationsTable} WHERE migration_name = ?");
            $stmt->bind_param("s", $migrationName);
            $stmt->execute();
            $stmt->close();

            $this->conn->commit();

            echo "Migration '$migrationName' rolled back successfully!\n";
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            echo "Migration rollback '$migrationName' failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

/**
 * Migration Runner
 *
 * Utility class to run migrations from files
 */
class MigrationRunner {
    private $conn;
    private $migrationPath;

    public function __construct($conn, $migrationPath = 'migrations/') {
        $this->conn = $conn;
        $this->migrationPath = $migrationPath;
    }

    /**
     * Run all pending migrations
     */
    public function runAll() {
        $migration = new Migration($this->conn);
        $files = glob($this->migrationPath . '*.php');

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');
            require_once $file;

            if (function_exists($migrationName . '_up')) {
                $migration->run($migrationName, $migrationName . '_up');
            }
        }
    }

    /**
     * Create a new migration file
     */
    public function create($name) {
        $timestamp = date('Y_m_d_H_i_s');
        $filename = $timestamp . '_' . $name . '.php';
        $filepath = $this->migrationPath . $filename;

        $template = "<?php
/**
 * Migration: $name
 * Created: " . date('Y-m-d H:i:s') . "
 */

function {$timestamp}_{$name}_up(\$conn) {
    // Add your migration SQL here
    \$sql = \"
        -- Your migration SQL goes here
        -- Example:
        -- ALTER TABLE users ADD COLUMN phone_verified BOOLEAN DEFAULT FALSE;
    \";

    return \$conn->query(\$sql);
}

function {$timestamp}_{$name}_down(\$conn) {
    // Add your rollback SQL here
    \$sql = \"
        -- Your rollback SQL goes here
        -- Example:
        -- ALTER TABLE users DROP COLUMN phone_verified;
    \";

    return \$conn->query(\$sql);
}
?>";

        if (file_put_contents($filepath, $template)) {
            echo "Migration file created: $filename\n";
            return $filepath;
        } else {
            echo "Failed to create migration file: $filename\n";
            return false;
        }
    }
}
?>