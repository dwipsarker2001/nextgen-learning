<?php
// include essentials files
include_once('../includes/db.php');
include_once('../includes/session.php');
include_once('../includes/helpers.php');
include_once('../includes/get_user_by_id.php');

// protection
protected_for(['admin', 'instructor']);

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

</head>

<body>
    <main>
        <!-- ----------------------------------- -->
        <!--            Sidebar Start            -->
        <!-- ----------------------------------- -->
        <nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

            <!-- Navbar brand for xl START -->
            <div class="d-flex align-items-center">
                <?php $home_url = isUser('admin') ? '../admin/dashboard.php' : '../admin/all_courses.php' ?>
                <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $home_url ?>">
                    <img class="navbar-brand-item" src="../assets/images/logo.png" alt="">
                </a>
            </div>
            <!-- Navbar brand for xl END -->

            <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
                <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark">

                    <!-- Sidebar menu START -->
                    <ul class="navbar-nav flex-column" id="navbar-sidebar">

                        <?php if(isUser('admin')): ?>
                        <!-- Dashboard menu item -->
                        <li class="nav-item"><a href="dashboard.php" class="nav-link <?= is_active_page('dashboard.php') ?>">
                            <i class="bi bi-house fa-fw me-2"></i>Dashboard</a>
                        </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#collapsepage" role="button" aria-expanded="false" aria-controls="collapsepage">
                                <i class="bi bi-basket fa-fw me-2"></i>Courses
                            </a>
                            <ul class="nav collapse flex-column" id="collapsepage" data-bs-parent="#navbar-sidebar">
                                <li class="nav-item">
                                    <a class="nav-link <?= is_active_page('all_courses.php') ?>" href="all_courses.php">All Courses</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= is_active_page('create_course.php') ?>" href="create_course.php">Create Course</a>
                                </li>
                            </ul>
                        </li>
                        <?php if(isUser('admin')): ?>
                        <li class="nav-item">
                            <a href="enrollments.php" class="nav-link <?= is_active_page('enrollments.php') ?>">
                                <i class="far fa-chart-bar fa-fw me-2"></i>
                                Enrollments
                            </a>
                        </li>
                         <?php endif; ?>
                        <li class="nav-item">
                            <a href="all_students.php" class="nav-link <?= is_active_page('all_students.php') ?>">
                                <i class="fas fa-user-tie fa-fw me-2"></i>
                                Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="all_instructors.php" class="nav-link <?= is_active_page('all_instructors.php') ?>">
                                <i class="fas fa-chalkboard-teacher fa-fw me-2"></i>
                                Instructor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active_page('profile.php') ?>" href="profile.php">
                                <i class="fas fa-user-cog fa-fw me-2"></i>
                                Account
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- ----------------------------------- -->
        <!--         Page Content Start          -->
        <!-- ----------------------------------- -->
        <div class="page-content">

            <!-- ----------------------------------- -->
            <!--         Top Navbar Start            -->
            <!-- ----------------------------------- -->
            <nav class="navbar top-bar navbar-light border-bottom shadow-none py-0 py-xl-3">
                <div class="container-fluid p-0">
                    <div class="d-flex align-items-center w-100">

                        <!-- Logo START -->
                        <div class="d-flex align-items-center d-xl-none">
                            <a class="navbar-brand" href="index-2.html">
                                <img class="light-mode-item navbar-brand-item h-30px" src="../assets/images/logo-mobile.svg" alt="">
                                <img class="dark-mode-item navbar-brand-item h-30px" src="../assets/images/logo-mobile-light.svg" alt="">
                            </a>
                        </div>
                        <!-- Logo END -->

                        <!-- Toggler for sidebar START -->
                        <div class="navbar-expand-xl sidebar-offcanvas-menu">
                            <button class="navbar-toggler me-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-expanded="false" aria-label="Toggle navigation" data-bs-auto-close="outside">
                                <i class="bi bi-text-right fa-fw h2 lh-0 mb-0 rtl-flip" data-bs-target="#offcanvasMenu"> </i>
                            </button>
                        </div>
                        <!-- Toggler for sidebar END -->

                        <!-- Top bar left -->
                        <div class="navbar-expand-lg ms-auto ms-xl-0">

                            <!-- Toggler for menubar START -->
                            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTopContent" aria-controls="navbarTopContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-animation">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </button>
                            <!-- Toggler for menubar END -->

                            <!-- Topbar menu START -->
                            <div class="collapse navbar-collapse w-100" id="navbarTopContent">
                                <!-- Top search END -->
                            </div>
                            <!-- Topbar menu END -->
                        </div>
                        <!-- Top bar left END -->

                        <!-- Top bar right START -->
                        <div class="ms-xl-auto">
                            <ul class="navbar-nav flex-row align-items-center">


                                <!-- Profile dropdown START -->
                                <li class="nav-item ms-2 ms-md-3 dropdown">
                                    <a class="avatar avatar-sm p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php if ($user['avatar']) { ?>
                                            <img class="avatar-img rounded-circle" src="../uploads/img/users/<?php echo $user['avatar']; ?>" alt="avatar">
                                        <?php } else { ?>
                                            <img class="avatar-img rounded-circle" src="../assets/images/avatar/empty-profile.png" alt="avatar">
                                        <?php } ?>
                                    </a>

                                    <!-- Profile dropdown START -->
                                    <ul class="dropdown-menu dropdown-animation dropdown-menu-end shadow pt-3" aria-labelledby="profileDropdown">
                                        <!-- Profile info -->
                                        <li class="px-3">
                                            <div class="d-flex align-items-center">
                                                <!-- Avatar -->
                                                <div class="avatar me-3">
                                                    <?php if ($user['avatar']) { ?>
                                                        <img class="avatar-img rounded-circle" src="../uploads/img/users/<?php echo $user['avatar']; ?>" alt="avatar">
                                                    <?php } else { ?>
                                                        <img class="avatar-img rounded-circle" src="../assets/images/avatar/empty-profile.png" alt="avatar">
                                                    <?php } ?>
                                                </div>
                                                <div>
                                                    <a class="h6 mt-2 mt-sm-0" href="profile.php"><?= $user['first_name'] ?> <?= $user['last_name'] ?></a>
                                                    <p class="small m-0"><?= $user['email'] ?></p>
                                                </div>
                                            </div>
                                            <hr>
                                        </li>
                                        <!-- Links -->
                                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-gear fa-fw me-2"></i>Account Settings</a></li>
                                        <li><a class="dropdown-item bg-danger-soft-hover" href="../includes/logout.php"><i class="bi bi-power fa-fw me-2"></i>Sign Out</a></li>
                                    </ul>
                                    <!-- Profile dropdown END -->
                                </li>
                                <!-- Profile dropdown END -->
                            </ul>
                        </div>
                        <!-- Top bar right END -->
                    </div>
                </div>
            </nav>

            <!-- ----------------------------------- -->
            <!--         Alert Dialog                -->
            <!-- ----------------------------------- -->
            <?php
            // Display session alert messages
            if (!empty($_SESSION['error_message']) || !empty($_SESSION['success_message'])) {

                // Determine alert type and message
                $isError = !empty($_SESSION['error_message']);
                $alertType = $isError ? 'warning' : 'success';
                $alertLabel = $isError ? 'Error:' : 'Success:';
                $message = $isError ? $_SESSION['error_message'] : $_SESSION['success_message'];
                ?>

                <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

            <?php
                // Clear the displayed message
                if ($isError) {
                    unset($_SESSION['error_message']);
                } else {
                    unset($_SESSION['success_message']);
                }
            }
            ?>

            <!-- ----------------------------------- -->
            <!--        Main Content Start           -->
            <!-- ----------------------------------- -->
            <div>
                <?php echo $content; ?>
            </div>
        </div>
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