<?php
// -------------------------------------------------
// Upload a file (image/pdf/docx) and return the new file name
// ---------------------------------------------
function upload_image($path, $field)
{
    // Ensure the upload directory exists
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    // Check if a file was uploaded
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return ''; // No file uploaded, return empty string
    }

    // Get file extension
    $file_extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

    // Allowed file types
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx'];

    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception("File type not allowed. Allowed types: " . implode(', ', $allowed_types));
    }

    // File size limit: 5MB
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
        throw new Exception("File size exceeds 5MB limit.");
    }

    // Generate unique file name
    $new_file_name = time() . '_' . uniqid() . '.' . $file_extension;
    $upload_file = $path . $new_file_name;

    // Move the uploaded file
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_file)) {
        return $new_file_name;
    }

    throw new Exception("There was an error uploading your file.");
}

// --------------------------------------------------------
// Get checkout link for a course based on login status
// ---------------------------------------------------------
function get_checkout_link($course_id)
{
    if (isset($_SESSION['user_email'])) {
        return "./checkout.php?id=$course_id";
    } else {
        return "./sign_up.php";
    }
}

// -----------------------------------------------
// Get payment history of a user
// -----------------------------------------
function get_payment_records($user_id, $conn)
{
    $sql = "SELECT * FROM enrollments WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


// -----------------------------------------------
// Get all courses a user is enrolled in
// ---------------------------------------------
function get_enrolled_course($conn, $user_id)
{
    $sql = "SELECT enrollments.status as isEnrolled, courses.* 
            FROM enrollments 
            JOIN courses ON enrollments.course_id = courses.id 
            WHERE enrollments.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// -------------------------------------------
// Get CSS classes based on status
// -----------------------------------------------
function get_status_classes($status)
{
    $classes = [
        'success' => 'bg-success text-success',
        'pending' => 'bg-orange text-orange',
        'cancel'  => 'bg-danger text-danger'
    ];

    return $classes[$status] ?? 'bg-gray';
}

// ------------------------------------------------------------
// Check if a page is active (for navigation highlighting)
// -------------------------------------------------------------
function is_active_page($page_name, $query_key = null, $query_value = null)
{
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_query = isset($_GET[$query_key]) ? $_GET[$query_key] : null;

    if ($current_page == $page_name) {
        if ($query_key && $query_value) {
            return $current_query == $query_value ? 'active' : '';
        }
        return 'active';
    }

    return '';
}

// --------------------------------------------
// Protect page for specific roles
// ----------------------------------------
function protected_for(string|array $roles)
{
    // Convert single role to array
    $allowed_roles = is_array($roles) ? $roles : [$roles];

    if (
        !isset($_SESSION['user_email']) ||
        !in_array($_SESSION['user_role'], $allowed_roles, true)
    ) {
        header('Location: ../sign_in.php');
        exit();
    }
}

// ----------------------------------------------
// Format a date in "dd M YYYY" format
// ---------------------------------------------
function format_date($date)
{
    $date_obj = new DateTime($date);
    return $date_obj->format('d M Y');
}

// ----------------------------------------------
// Role checker function
// ---------------------------------------------
function isUser($role) {
    return $_SESSION['user_role'] == $role;
}

function isAuthor($user_id) {   
    return $_SESSION['user_id'] == $user_id;
}