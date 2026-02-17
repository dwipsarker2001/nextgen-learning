<?php
include_once('includes/db.php');
include_once('includes/helpers.php');
include_once('includes/get_record.php');

$user = null;

// Check if user is logged in and has a valid role
if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_role'])) {
    $user_id = $_SESSION['user_id'];
    $user = get_record($conn, 'users', $user_id);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?= $page_title ?? 'Nextgen Learning' ?></title>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Md. Sharif Ahmed">
    <meta name="description" content="Nextgen Learning - Online Education Platform">

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- Plugins CSS -->
    <link rel="stylesheet" type="text/css" href="assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <!-- Inline font family -->
    <style>
        body, h1, h2, h3, h4, h5, h6, span, p {
            font-family: "Inter", sans-serif;
        }
    </style>
</head>

<body>

    <!---------------------------------------------
                HEADER START
    --------------------------------------------->
    <header class="navbar-light navbar-sticky header-static py-3 mb-2">
        <div class="container">
            <nav class="navbar navbar-expand-xl">
                <div class="container-fluid" style="padding: 0;">
                    <!-- Logo -->
                    <a href="#"><img class="navbar-logo" src="assets/images/logo.png" alt="logo"></a>

                    <!-- Main navbar START -->
                    <div class="navbar-collapse w-100 collapse" id="navbarCollapse">
                        <ul class="navbar-nav navbar-nav-scroll me-auto"></ul>
                        <ul class="navbar-nav navbar-nav-scroll me-auto" style="font-size: 17px;">
                            <li class="nav-item">
                                <a class="nav-link <?= is_active_page('index.php') ?>" href="index.php">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= is_active_page('about_us.php') ?>" href="about_us.php">About Us</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= is_active_page('our_courses.php', 'type', 'free') ?>" href="our_courses.php?type=free">Free Courses</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= is_active_page('our_courses.php', 'type', 'paid') ?>" href="our_courses.php?type=paid">Paid Courses</a>
                            </li>
                        </ul>
                    </div>

                    <!-- User / Login -->
                    <?php if ($user) { ?>
                        <!-- Profile Dropdown -->
                        <div class="dropdown ms-1 ms-lg-0 ms-auto">
                            <a class="avatar avatar-sm p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
                                <img class="avatar-img rounded-circle" src="uploads/img/users/<?= $user['avatar'] ?: 'blank.png'; ?>" alt="avatar">
                            </a>
                            <ul class="dropdown-menu dropdown-animation dropdown-menu-end shadow pt-3" aria-labelledby="profileDropdown">
                                <!-- Profile info -->
                                <li class="px-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <img class="avatar-img rounded-circle" src="uploads/img/users/<?= $user['avatar'] ?: 'blank.png'; ?>" alt="avatar">
                                        </div>
                                        <div>
                                            <?php
                                                switch ($_SESSION['user_role']) {
                                                    case 'student':
                                                        $dashboard_link= 'student/dashboard.php';
                                                        break;
                                                    case 'instructor':
                                                        $dashboard_link = 'admin/all_courses.php';
                                                        break;
                                                    case 'admin':
                                                    default:
                                                        $dashboard_link = 'admin/dashboard.php';
                                                        break;
                                                }
                                            ?>
                                            <a class="h6" href="<?= $dashboard_link ?>">
                                                <?= $user['first_name'] ?> <?= $user['last_name'] ?>
                                            </a>
                                            <p class="small m-0"><?= $user['email'] ?></p>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?= $dashboard_link ?>">
                                        <i class="bi bi-person fa-fw me-2"></i>
                                        <?= ucfirst($_SESSION['user_role']) ?> Panel
                                    </a>
                                </li>
                                <li><a class="dropdown-item bg-danger-soft-hover" href="includes/logout.php"><i class="bi bi-power fa-fw me-2"></i>Sign Out</a></li>
                            </ul>
                        </div>
                    <?php } else { ?>
                        <a href="sign_in.php" class="px-4 py-2 bg-primary d-inline-block rounded-5 shadow-lg text-white ms-auto" style="white-space: nowrap;">Login / Sign Up</a>
                    <?php } ?>

                    <!-- Navbar toggler -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="navbar-toggler-animation"><span></span><span></span><span></span></span>
                    </button>

                </div>
            </nav>
        </div>
    </header>

    <!---------------------------------------------
                MAIN CONTENT START
    --------------------------------------------->
    <main>
        <?= $content; ?>
    </main>

    <!---------------------------------------------
                FOOTER START
    --------------------------------------------->
    <footer class="pt-5 bg-blue text-white">
        <div class="container">
            <div class="row g-4">
                <!-- Widget 1: Logo & About -->
                <div class="col-lg-3">
                    <a href="index.php">
                        <img class="light-mode-item h-60px" src="assets/images/logo.png" alt="logo">
                        <img class="dark-mode-item h-40px" src="assets/images/logo-light.svg" alt="logo">
                    </a>
                    <p class="my-3 text-white">NextGen Learning empowers students with affordable, interactive courses, connecting learners and teachers through modern, accessible digital education.</p>
                    <ul class="list-inline mb-0 mt-3">
                        <li class="list-inline-item"><a class="btn btn-white btn-sm shadow px-2 text-facebook" href="#"><i class="fab fa-fw fa-facebook-f"></i></a></li>
                        <li class="list-inline-item"><a class="btn btn-white btn-sm shadow px-2 text-instagram" href="#"><i class="fab fa-fw fa-instagram"></i></a></li>
                        <li class="list-inline-item"><a class="btn btn-white btn-sm shadow px-2 text-twitter" href="#"><i class="fab fa-fw fa-twitter"></i></a></li>
                        <li class="list-inline-item"><a class="btn btn-white btn-sm shadow px-2 text-linkedin" href="#"><i class="fab fa-fw fa-linkedin-in"></i></a></li>
                    </ul>
                </div>

                <!-- Widget 2: Company / Community / Teaching links -->
                <div class="col-lg-6">
                    <div class="row g-4">
                        <div class="col-6 col-md-4">
                            <h5 class="mb-2 mb-md-4 text-white">Company</h5>
                            <ul class="nav flex-column">
                                <li class="nav-item"><a class="nav-link text-white" href="about_us.php">About Us</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="contact_us.php">Contact Us</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="coming_soon.php">News &amp; Blog</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="coming_soon.php">Library</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="">Careers</a></li>
                            </ul>
                        </div>
                        <div class="col-6 col-md-4">
                            <h5 class="mb-2 mb-md-4 text-white">Community</h5>
                            <ul class="nav flex-column">
                                <li class="nav-item"><a class="nav-link text-white" href="http://facebook.com/">Facebook</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="https://web.whatsapp.com/">WhatsApp</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="https://www.instagram.com/">Instagram</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="http://youtube.com/">YouTube</a></li>
                            </ul>
                        </div>
                        <div class="col-6 col-md-4">
                            <h5 class="mb-2 mb-md-4 text-white">Teaching</h5>
                            <ul class="nav flex-column">
                                <li class="nav-item"><a class="nav-link text-white" href="sign_in.php">Become an Instructor</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="sign_in.php">How to Guide</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="sign_in.php">Terms &amp; Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Widget 3: Contact Info -->
                <div class="col-lg-3">
                    <h5 class="mb-2 mb-md-4 text-white">Contact Us</h5>
                    <p class="mb-2 text-white">Phone Number: <span class="h6 fw-light ms-2 text-white">+880 131-649264</span>
                        <span class="d-block small text-white">(9:30 AM to 8:30 BST)</span>
                    </p>
                    <p class="mb-0 text-white">Email: <span class="h6 fw-light ms-2 text-white">admission@nextgen.com</span></p>
                </div>
            </div>

            <hr class="mt-4 mb-0">

            <!-- Bottom Footer -->
            <div class="py-3">
                <div class="d-lg-flex justify-content-between align-items-center py-3 text-center text-md-left">
                   <div class=" text-primary-hover" style="color: white;">
                        Copyright © 2026 NextGen Learning. All rights reserved.
                    </div>
                    <div class="justify-content-center mt-3 mt-lg-0">
                        <ul class="nav list-inline justify-content-center mb-0">
                            <li class="list-inline-item"><a class="nav-link text-white" href="coming_soon.php">Terms of Use</a></li>
                            <li class="list-inline-item"><a class="nav-link pe-0 text-white" href="coming_soon.php">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!---------------------------------------------
                BACK TO TOP BUTTON START
    --------------------------------------------->
    <div class="back-top"><i class="bi bi-arrow-up-short position-absolute top-50 start-50 translate-middle"></i></div>

    <!-- JS Scripts -->
    <script src="assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/purecounterjs/dist/purecounter_vanilla.js"></script>
    <script src="assets/js/pass_show_hide.js"></script>
    <script src="assets/js/functions.js"></script>

</body>

</html>
