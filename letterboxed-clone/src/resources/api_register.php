<?php
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Use correct database credentials
$conn = mysqli_connect('feenix-mariadb.swin.edu.au', 's103994779', '020903', 's103994779_db');

// local development
//$conn = mysqli_connect('localhost', 'root', '', 'letterboxed_clone_db');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

mysqli_set_charset($conn, 'utf8');

if ($method == 'POST') {
    if (!isset($input['username']) || !isset($input['password']) || !isset($input['email'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $username = mysqli_real_escape_string($conn, $input['username']);
    $password = mysqli_real_escape_string($conn, $input['password']);
    $email = mysqli_real_escape_string($conn, $input['email']); // Add this line
    
    // Check if username already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
    } else {
        // Get next available ID
        $maxId = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(id) as max_id FROM users"));
        $nextId = (isset($maxId['max_id']) ? $maxId['max_id'] : 0) + 1;
        
        // Insert new user
        $result = mysqli_query($conn, "INSERT INTO users (id, username, password, email) VALUES ($nextId, '$username', '$password', '$email')");
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . mysqli_error($conn)]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($conn);
?>