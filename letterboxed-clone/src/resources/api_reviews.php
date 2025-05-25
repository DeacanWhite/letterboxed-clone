<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Database connection
$conn = mysqli_connect('feenix-mariadb.swin.edu.au', 's103994779', '020903', 's103994779_db');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

mysqli_set_charset($conn, 'utf8');

switch ($method) {
    case 'GET':
        // Get reviews for a movie
        if (isset($_GET['movie_id'])) {
            $movie_id = mysqli_real_escape_string($conn, $_GET['movie_id']);
            $movie_type = isset($_GET['movie_type']) ? mysqli_real_escape_string($conn, $_GET['movie_type']) : 'movie';
            
            $sql = "SELECT * FROM reviews WHERE movie_id = '$movie_id' AND movie_type = '$movie_type' ORDER BY created_at DESC";
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                $reviews = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $reviews[] = $row;
                }
                echo json_encode(['success' => true, 'reviews' => $reviews]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error fetching reviews']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Movie ID required']);
        }
        break;

    case 'POST':
        // Add a new review
        if (!isset($input['movie_id']) || !isset($input['user_id']) || !isset($input['username']) || 
            !isset($input['rating']) || !isset($input['review_text'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $movie_id = mysqli_real_escape_string($conn, $input['movie_id']);
        $movie_type = isset($input['movie_type']) ? mysqli_real_escape_string($conn, $input['movie_type']) : 'movie';
        $user_id = mysqli_real_escape_string($conn, $input['user_id']);
        $username = mysqli_real_escape_string($conn, $input['username']);
        $rating = (int)$input['rating'];
        $review_text = mysqli_real_escape_string($conn, $input['review_text']);

        // Validate rating
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            exit;
        }

        // Check if user already reviewed this movie
        $check_sql = "SELECT id FROM reviews WHERE movie_id = '$movie_id' AND movie_type = '$movie_type' AND user_id = '$user_id'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            echo json_encode(['success' => false, 'message' => 'You have already reviewed this movie']);
            exit;
        }

        // Insert review
        $sql = "INSERT INTO reviews (movie_id, movie_type, user_id, username, rating, review_text) 
                VALUES ('$movie_id', '$movie_type', '$user_id', '$username', $rating, '$review_text')";
        
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $review_id = mysqli_insert_id($conn);
            echo json_encode([
                'success' => true, 
                'message' => 'Review added successfully',
                'review_id' => $review_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding review: ' . mysqli_error($conn)]);
        }
        break;

    case 'PUT':
        // Update/Edit review
        if (!isset($input['review_id']) || !isset($input['user_id']) || 
            !isset($input['rating']) || !isset($input['review_text'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $review_id = mysqli_real_escape_string($conn, $input['review_id']);
        $user_id = mysqli_real_escape_string($conn, $input['user_id']);
        $rating = (int)$input['rating'];
        $review_text = mysqli_real_escape_string($conn, $input['review_text']);

        // Validate rating
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            exit;
        }

        // Check if the review belongs to the user
        $check_sql = "SELECT user_id FROM reviews WHERE id = '$review_id'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Review not found']);
            exit;
        }

        $review_owner = mysqli_fetch_assoc($check_result);
        if ($review_owner['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'You can only edit your own reviews']);
            exit;
        }

        // Update the review
        $update_sql = "UPDATE reviews SET rating = $rating, review_text = '$review_text' 
                       WHERE id = '$review_id' AND user_id = '$user_id'";
        $result = mysqli_query($conn, $update_sql);

        if ($result && mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update review']);
        }
        break;

    case 'DELETE':
        // Delete review
        if (!isset($_GET['review_id']) || !isset($_GET['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Review ID and User ID required']);
            exit;
        }

        $review_id = mysqli_real_escape_string($conn, $_GET['review_id']);
        $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);

        // Check if the review belongs to the user
        $check_sql = "SELECT user_id FROM reviews WHERE id = '$review_id'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Review not found']);
            exit;
        }

        $review_owner = mysqli_fetch_assoc($check_result);
        if ($review_owner['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'You can only delete your own reviews']);
            exit;
        }

        // Delete the review
        $delete_sql = "DELETE FROM reviews WHERE id = '$review_id' AND user_id = '$user_id'";
        $result = mysqli_query($conn, $delete_sql);

        if ($result && mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete review']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

mysqli_close($conn);
?>