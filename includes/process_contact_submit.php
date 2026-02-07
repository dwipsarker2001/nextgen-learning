<?php
include '../includes/db.php';
include '../includes/session.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    // Basic validation
    if (empty($name) || empty($phone) || empty($message)) {
        $_SESSION['status'] = "warning";
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: ../contact_us.php#contact");
        exit;
    }

    // Bangladeshi mobile number check 
    if (!preg_match('/^01[0-9]{9}$/', $phone)) {
        $_SESSION['status'] = "warning";
        $_SESSION['error_message'] = "A valid Bangladeshi mobile number is required.";
        header("Location: ../contact_us.php#contact");
        exit;
    }

    $query = "INSERT INTO `contacts` (`name`, `phone`, `message`) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $name, $phone, $message);

    if ($stmt->execute()) {
        $_SESSION['status'] = "success";
        $_SESSION['success_message'] = "Message sent successfully!";
    } else {
        $_SESSION['status'] = "error";
        $_SESSION['error_message'] = "Failed to send the message.";
    }

    header("Location: ../contact_us.php#contact");
    exit;
}
