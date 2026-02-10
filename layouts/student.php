<?php
/*-----------------------------------------
| Includes
------------------------------------------*/
include_once('../includes/db.php');
include_once('../includes/session.php');
include_once('../includes/helpers.php');
include_once('../includes/get_user_by_id.php');

/*-----------------------------------------
| Protection
------------------------------------------*/
protected_for('student');

/*-----------------------------------------
| User Data
------------------------------------------*/
$user_id = $_SESSION['user_id'];
$user    = get_user($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $page_title ?? 'Student Dashboard | Digital Shikkhok'; ?></title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Md. Sharif Ahmed">
    <meta name="description" content="Digital Shikkhok - Online Learning Platform">

    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/vendor/choices/css/choices.min.css">
    <link rel="stylesheet" href="../assets/vendor/aos/aos.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body, h1, h2, h3, h4, h5, h6, span, p {
            font-family: "Inter", sans-serif;
        }
    </style>
</head>

<body>


<!-- --------------------------
        Page Banner
--------------------------- -->
<section class="pt-0">
    <div class="container-fluid px-0">
        <div class="card bg-blue h-200px rounded-0"></div>
    </div>

    <div class="container mt-n4">
        <div class="card bg-transparent border-0">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="avatar avatar-xxl">
                        <img class="avatar-img rounded-circle"
                             src="../uploads/img/users/<?= $user['avatar'] ?: 'blank.png'; ?>">
                    </div>
                </div>
                <div class="col">
                    <h1 class="fs-4 mb-1"><?= $user['first_name'].' '.$user['last_name']; ?></h1>
                    <p class="mb-0"><i class="fas fa-phone me-1"></i><?= $user['phone']; ?></p>
                </div>
                <div class="col-auto">
                    <a href="student_edit_profile.php" class="btn btn-outline-primary">Manage Profile</a>
                    <a href="student_edit_profile.php" class="btn btn-outline-primary">Visit Website</a>
                    <a href="student_edit_profile.php" class="btn btn-outline-primary">Logout</a>
                </div>
            </div>
        </div>  
    </div>
</section>

<!---------------------------------------------
    Main Content
----------------------------------- -->
<main class="py-4">
    <div class="container">
        <div class="row">
            <?= $content; ?>
        </div>
    </div>
</main>


<!-- JS -->
<script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/choices/js/choices.min.js"></script>
<script src="../assets/vendor/aos/aos.js"></script>
<script src="../assets/js/functions.js"></script>

</body>
</html>
