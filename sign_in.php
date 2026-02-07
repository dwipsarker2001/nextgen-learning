<?php
session_start();
include('includes/helpers.php');
$page_title = "Sign In | Nextgen Learning";
ob_start();
?>


<section class="p-0 d-flex align-items-center position-relative overflow-hidden">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-6 d-md-flex align-items-center justify-content-center  bg-primary bg-opacity-10">
                <div class="p-3 p-lg-5">
                    <div class="text-center">
                        <h2 class="fw-bold">Welcome to our largest community.</h2>
                        <p class="mb-0 h6 fw-light">Let's learn something new today!</p>
                    </div>
                    <img src="assets/images/element/02.svg" class="mt-5" alt="">
                    <div class="d-sm-flex mt-5 align-items-center justify-content-center">
                        <ul class="avatar-group mb-2 mb-sm-0">
                            <li class="avatar avatar-sm">
                                <img class="avatar-img rounded-circle" src="https://randomuser.me/api/portraits/men/1.jpg" alt="avatar">
                            </li>
                            <li class="avatar avatar-sm">
                                <img class="avatar-img rounded-circle" src="https://randomuser.me/api/portraits/women/2.jpg" alt="avatar">
                            </li>
                            <li class="avatar avatar-sm">
                                <img class="avatar-img rounded-circle" src="https://randomuser.me/api/portraits/men/3.jpg" alt="avatar">
                            </li>
                            <li class="avatar avatar-sm">
                                <img class="avatar-img rounded-circle" src="https://randomuser.me/api/portraits/women/4.jpg" alt="avatar">
                            </li>
                        </ul>
                        <p class="mb-0 h6 fw-light ms-0 ms-sm-3">4,000+ students have joined, now it's your turn.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 m-auto ">
                <div class="row my-5">
                    <div class="col-sm-10 col-xl-8 m-auto">
                        <?php if (isset($_SESSION['error_message']) || isset($_SESSION['success_message'])) {
                                $alert_type = isset($_SESSION['error_message']) ? 'warning' : 'success';
                                $message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : $_SESSION['success_message'];
                            ?>
                                <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
                                    <strong><?= $alert_type === 'warning' ? 'Error:' : 'Success:' ?></strong> <?= htmlspecialchars($message) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php
                                if ($alert_type === 'warning') {
                                    unset($_SESSION['error_message']);
                                } else {
                                    unset($_SESSION['success_message']);
                                }
                            } ?>
                        <span class="mb-0 fs-1">👋</span>
                        <h1 class="fs-2">Login.</h1>
                        <p class="lead mb-4">Login with your email and password.</p>
                        <form action="./includes/process_login.php" method="POST">
                            <div class="mb-4">
                                <label for="exampleInputEmail1" class="form-label">Enter your email address *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light rounded-start border-0 text-secondary px-3"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control border-0 bg-light rounded-end ps-1" placeholder="name@domain.com" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="inputPassword5" class="form-label">Enter your password *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light rounded-start border-0 text-secondary px-3"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control border-0 bg-light rounded-end ps-1" placeholder="●●●●●●●●●" id="pass" name="pass" required>
                                    <span class="input-group-text p-0 border-0" id="password-view-login" style="cursor: pointer;" onclick="togglePassword('pass')">
                                        <i class="far fa-eye cursor-pointer p-2 w-40px"></i>
                                    </span>
                                </div>
                                <div id="passwordHelpBlock" class="form-text">
                                    Your password must be at least 8 characters long.
                                </div>
                            </div>
                            <div class="align-items-center mt-0">
                                <div class="d-grid">
                                    <button class="btn btn-primary mb-0" type="submit">Login</button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <span>Don't have an account? <a href="sign_up.php">Sign up here.</a></span>
                        </div>
                    </div>
                </div> </div>
        </div> </div>
</section>

<?php
$content = ob_get_clean();
include('layouts/website.php');
?>