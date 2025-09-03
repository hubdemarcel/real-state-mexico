<?php
require_once 'config.php';

// Test users data
$test_users = [
    [
        'username' => 'test_vendedor',
        'email' => 'vendedor@test.com',
        'password' => 'test',
        'user_type' => 'seller',
        'first_name' => 'Juan',
        'last_name' => 'Vendedor'
    ],
    [
        'username' => 'test_comprador',
        'email' => 'comprador@test.com',
        'password' => 'test',
        'user_type' => 'buyer',
        'first_name' => 'Maria',
        'last_name' => 'Comprador'
    ],
    [
        'username' => 'test_agente',
        'email' => 'agente@test.com',
        'password' => 'test',
        'user_type' => 'agent',
        'first_name' => 'Carlos',
        'last_name' => 'Agente'
    ]
];

echo "Creating test users...<br><br>";

foreach ($test_users as $user_data) {
    // Hash the password
    $hashed_password = password_hash($user_data['password'], PASSWORD_DEFAULT);

    // Check if user already exists
    $check_sql = "SELECT id FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $user_data['email'], $user_data['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "User {$user_data['username']} already exists, skipping...<br>";
        $stmt->close();
        continue;
    }
    $stmt->close();

    // Insert user
    $sql = "INSERT INTO users (username, email, password, user_type, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss",
        $user_data['username'],
        $user_data['email'],
        $hashed_password,
        $user_data['user_type'],
        $user_data['first_name'],
        $user_data['last_name']
    );

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        echo "✓ Created user: {$user_data['username']} ({$user_data['user_type']})<br>";

        // If agent, create agent profile
        if ($user_data['user_type'] === 'agent') {
            $agent_sql = "INSERT INTO agents (user_id, first_name, last_name, bio, company, license_number, experience_years, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $agent_stmt = $conn->prepare($agent_sql);
            $bio = "Agente inmobiliario profesional con experiencia en el mercado mexicano.";
            $company = "Inmobiliaria Test";
            $license = "TEST" . $user_id;
            $experience = 5;
            $location = "CDMX";

            $agent_stmt->bind_param("isssssis", $user_id, $user_data['first_name'], $user_data['last_name'], $bio, $company, $license, $experience, $location);

            if ($agent_stmt->execute()) {
                echo "  └─ Agent profile created<br>";
            } else {
                echo "  └─ Error creating agent profile: " . $agent_stmt->error . "<br>";
            }
            $agent_stmt->close();
        }
    } else {
        echo "✗ Error creating user {$user_data['username']}: " . $stmt->error . "<br>";
    }
    $stmt->close();

    echo "<br>";
}

echo "<br><strong>Test Users Created:</strong><br>";
echo "1. Vendedor: vendedor@test.com / test<br>";
echo "2. Comprador: comprador@test.com / test<br>";
echo "3. Agente: agente@test.com / test<br>";

$conn->close();
?>