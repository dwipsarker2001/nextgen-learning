<?php
/*-------------------------------------------
 | Update Student Profile Handler
 -------------------------------------------*/

// Start session
session_start();

// Redirect URL constant
define('REDIRECT_URL', '../student/student_edit_profile.php');

try {
    /*-------------------------------------------
     | Validate Request Method
     -------------------------------------------*/
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // Include database connection
    include('../includes/db.php');
    include('../includes/helpers.php');

    /*-------------------------------------------
     | Validate Session
     -------------------------------------------*/
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Please login to continue.";
        header("Location: ../login.php");
        exit();
    }

    $user_id = (int)$_SESSION['user_id'];

    /*-------------------------------------------
     | Sanitize and Validate Input
     -------------------------------------------*/
    $first_name = trim($_POST['fname'] ?? '');
    $last_name = trim($_POST['lname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation errors array
    $errors = [];

    // Validate required fields
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }

    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }

    // Validate phone number format (basic validation)
    if (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Phone number must be between 10-15 digits.";
    }

    // If there are validation errors, throw exception
    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }

    /*-------------------------------------------
     | Start Building Update Query
     -------------------------------------------*/
    $update_query = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW()";
    $types = "sss";
    $params = [$first_name, $last_name, $phone];

    /*-------------------------------------------
     | Handle Password Update
     -------------------------------------------*/
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // All password fields must be filled
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception("All password fields are required to change password.");
        }

        // Validate new password length
        if (strlen($new_password) < 8) {
            throw new Exception("New password must be at least 8 characters long.");
        }

        // Check if new password and confirm password match
        if ($new_password !== $confirm_password) {
            throw new Exception("New password and confirm password do not match.");
        }

        // Verify current password
        $verify_query = "SELECT password FROM users WHERE id = ?";
        $stmt_verify = $conn->prepare($verify_query);
        
        if (!$stmt_verify) {
            throw new Exception("Failed to prepare password verification query.");
        }

        $stmt_verify->bind_param("i", $user_id);
        $stmt_verify->execute();
        $result = $stmt_verify->get_result();
        
        if ($result->num_rows !== 1) {
            $stmt_verify->close();
            throw new Exception("User not found.");
        }

        $row = $result->fetch_assoc();
        
        if (!password_verify($current_password, $row['password'])) {
            $stmt_verify->close();
            throw new Exception("Current password is incorrect.");
        }

        $stmt_verify->close();

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query .= ", password = ?";
        $types .= "s";
        $params[] = $hashed_password;
    }

    /*-------------------------------------------
     | Handle Profile Image Upload
     -------------------------------------------*/
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $profile = $_FILES['avatar'];
        
        // Validate file type using MIME type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $profile['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
        }
        
        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB in bytes
        if ($profile['size'] > $max_size) {
            throw new Exception("File size must be less than 5MB.");
        }
        
        // Generate unique filename
        $file_extension = strtolower(pathinfo($profile['name'], PATHINFO_EXTENSION));
        $unique_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
        $upload_dir = '../uploads/img/users/';
        $profile_path = $upload_dir . $unique_name;
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("Failed to create upload directory.");
            }
        }
        
        // Move uploaded file
        if (!move_uploaded_file($profile['tmp_name'], $profile_path)) {
            throw new Exception("Failed to upload the profile image.");
        }

        // Delete old avatar if exists (except blank.png)
        $old_avatar_query = "SELECT avatar FROM users WHERE id = ?";
        $stmt_old = $conn->prepare($old_avatar_query);
        
        if ($stmt_old) {
            $stmt_old->bind_param("i", $user_id);
            $stmt_old->execute();
            $result = $stmt_old->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $old_avatar = $row['avatar'];
                if ($old_avatar && $old_avatar !== 'blank.png') {
                    $old_file = $upload_dir . $old_avatar;
                    if (file_exists($old_file)) {
                        @unlink($old_file); // Suppress errors if file deletion fails
                    }
                }
            }
            $stmt_old->close();
        }
        
        // Add avatar to update query
        $update_query .= ", avatar = ?";
        $types .= "s";
        $params[] = $unique_name;
        
    } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle file upload errors
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $error_code = $_FILES['avatar']['error'];
        $error_message = $upload_errors[$error_code] ?? 'Unknown upload error';
        
        throw new Exception("Upload error: " . $error_message);
    }

    /*-------------------------------------------
     | Complete Query and Execute
     -------------------------------------------*/
    $update_query .= " WHERE id = ?";
    $types .= "i";
    $params[] = $user_id;

    // Prepare and execute the update statement
    $stmt = $conn->prepare($update_query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare update query: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating profile: " . $stmt->error);
    }

    // Success message
    $_SESSION['error_message'] = "Profile updated successfully!";
    $_SESSION['error_type'] = 'alert-success';
    
    // Regenerate session ID for security (especially after password change)
    if (!empty($new_password)) {
        session_regenerate_id(true);
    }

    $stmt->close();

} catch (Exception $e) {
    // Handle all errors here
    $_SESSION['error_message'] = $e->getMessage();
    $_SESSION['error_type'] = 'alert-danger';
} finally {
    // Close database connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
    
    // Single redirect point
    header("Location: " . REDIRECT_URL);
    exit();
}
?>