<?php
// include essentials files
include_once('../includes/db.php');
include_once('../includes/session.php');
include_once('../includes/helpers.php');
include_once('../includes/get_user_by_id.php');

// protection
protected_for('student');

// variables
$user_id = $_SESSION['user_id'];
$user = get_user($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title><?= $page_title ?></title>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Md. Sharif Ahmed">
    <meta name="description" content="Nextgen Learning - Online Learning Platform">

    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&amp;family=Roboto:wght@400;500;700&amp;display=swap">

    
    <!-- Plugins CSS -->
    <link rel="stylesheet" type="text/css" href="../assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/choices/css/choices.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/glightbox/css/glightbox.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/quill/css/quill.snow.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/stepper/css/bs-stepper.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/overlay-scrollbar/css/overlayscrollbars.min.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <style>
    /* Prevent navbar height from changing */
    .navbar .dropdown-menu {
        position: absolute !important;
        top: 100%;
        right: 0;
        left: auto;
        margin-top: 0.5rem;
    }

    /* Ensure navbar doesn't expand */
    .navbar {
        overflow: visible !important;
    }
    </style>
</head>

<body>

    <!-- ------------------------------------------------ -->
    <!--          Navbar Start                            -->
    <!-- ------------------------------------------------ -->
    <nav class="navbar top-bar border-bottom shadow-none py-0 py-xl-3" style="background:#24292d;">
        <div class="container p-0">
            <div class="d-flex align-items-center w-100">
                <!-- Mobile Logo -->
                <div class="d-flex align-items-center d-xl-none">
                    <a class="navbar-brand" href="../student/dashboard.php">
                        <img class="navbar-brand-item h-30px" src="../assets/images/logo-mobile.svg">
                    </a>
                </div>

                <!-- Sidebar Toggle -->
                <div class="navbar-expand-xl sidebar-offcanvas-menu">
                    <button class="navbar-toggler me-auto" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasSidebar">
                        <i class="bi bi-text-right fa-fw h2 lh-0 mb-0"></i>
                    </button>
                </div>

                <!--  Student Panel Title -->
                <div class="ms-3 d-none d-xl-block">
                    <a href="../student/dashboard.php"><img class="navbar-logo" src="../assets/images/logo.png" alt="logo"></a>
                </div>

                <!-- Right Side -->
                <div class="ms-auto">
                    <ul class="navbar-nav flex-row align-items-center">

                        <li class="nav-item dropdown">

                            <!-- Proper Trigger -->
                            <a class="d-flex align-items-center text-white text-decoration-none"
                            href="#"
                            id="profileDropdown"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                                <span class="me-2 fw-bold">
                                    <?= $user['first_name'] ?> <?= $user['last_name'] ?>
                                </span>

                                <?php if ($user['avatar']) { ?>
                                    <img class="rounded-circle"
                                        src="../uploads/img/users/<?php echo $user['avatar']; ?>"
                                        width="35" height="35">
                                <?php } else { ?>
                                    <img class="rounded-circle"
                                        src="../assets/images/avatar/empty-profile.png"
                                        width="35" height="35">
                                <?php } ?>

                            </a>

                            <!-- Dropdown Menu -->
                            <ul class="dropdown-menu dropdown-menu-end shadow pt-3"
                                aria-labelledby="profileDropdown">

                                <li class="px-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <?php if ($user['avatar']) { ?>
                                                <img class="rounded-circle"
                                                    src="../uploads/img/users/<?php echo $user['avatar']; ?>"
                                                    width="40">
                                            <?php } else { ?>
                                                <img class="rounded-circle"
                                                    src="../assets/images/avatar/empty-profile.png"
                                                    width="40">
                                            <?php } ?>
                                        </div>

                                        <div>
                                            <a class="h6" href="profile.php">
                                                <?= $user['first_name'] ?>
                                                <?= $user['last_name'] ?>
                                            </a>
                                            <p class="small m-0"><?= $user['email'] ?></p>
                                        </div>
                                    </div>
                                    <hr>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                    href="student_edit_profile.php">
                                        <i class="bi bi-gear me-2"></i>
                                        Account Settings
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item bg-danger-soft-hover"
                                    href="../includes/logout.php">
                                        <i class="bi bi-power me-2"></i>
                                        Sign Out
                                    </a>
                                </li>

                            </ul>
                        </li>

                    </ul>
                </div>


            </div>
        </div>

    </nav>

    <main>
        <?php echo $content; ?>
    </main>

    <!-- Bootstrap JS -->
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Vendors -->
    <script src="../assets/vendor/purecounterjs/dist/purecounter_vanilla.js"></script>
    <script src="../assets/vendor/choices/js/choices.min.js"></script>
    <script src="../assets/vendor/glightbox/js/glightbox.js"></script>
    <script src="../https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="../assets/vendor/stepper/js/bs-stepper.min.js"></script>
    <script src="../assets/vendor/overlay-scrollbar/js/overlayscrollbars.min.js"></script>

    <!-- Template Functions -->
    <script src="../assets/js/functions.js"></script>

</body>

</html>