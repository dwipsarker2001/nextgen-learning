<?php
session_start();
include('includes/helpers.php');
$page_title = "Sign In | Nextgen Learning";

/*---------------------------------------------
    Start Output Buffering
---------------------------------------------*/
ob_start();
?>

<!---------------------------------------------
            LOGIN SECTION START
--------------------------------------------->
<section class="p-0 d-flex align-items-center position-relative overflow-hidden">

    <div class="container-fluid">
        <div class="row">

            <!---------------------------------------------
                        Left Side: Welcome Info
            --------------------------------------------->
            <div class="col-12 col-lg-6 p-3 p-lg-5 d-md-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                <div class="p-3 p-lg-5">

                    <!-- Title and subtitle -->
                    <div class="text-center">
                        <h2 class="fw-bold">Welcome to our largest community.</h2>
                        <p class="mb-0 h6 fw-light">Let's learn something new today!</p>
                    </div>

                    <!-- Decorative image -->
                    <img src="assets/images/element/02.svg" class="mt-5" alt="">
                </div>
            </div>

            <!---------------------------------------------
                        Right Side: Login Form
            --------------------------------------------->
            <div class="col-12 col-lg-6 m-auto">
                <div class="row my-5">
                    <div class="col-sm-10 col-xl-8 m-auto">

                        <!-- Alert messages -->
                        <?php
                        if (isset($_SESSION['error_message']) || isset($_SESSION['success_message'])) {
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
                        }
                        ?>

                        <!-- Emoji + Form Heading -->
                        <span class="mb-0 fs-1">👋</span>
                        <h1 class="fs-2">Login.</h1>
                        <p class="lead mb-4">Login with your email and password.</p>

                        <!---------------------------------------------
                                    Login Form
                        --------------------------------------------->
                        <form action="./includes/process_login.php" method="POST">

                            <!-- Email input -->
                            <div class="mb-4">
                                <label for="email" class="form-label">Enter your email address *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light rounded-start border-0 text-secondary px-3">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="email" class="form-control border-0 bg-light rounded-end ps-1" placeholder="name@domain.com" id="email" name="email" required>
                                </div>
                            </div>

                            <!-- Password input -->
                            <div class="mb-4">
                                <label for="pass" class="form-label">Enter your password *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light rounded-start border-0 text-secondary px-3">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-0 bg-light rounded-end ps-1" placeholder="●●●●●●●●●" id="pass" name="pass" required>
                                    <span class="input-group-text p-0 border-0" id="password-view-login" style="cursor: pointer;" onclick="togglePassword('pass')">
                                        <i class="far fa-eye cursor-pointer p-2 w-40px"></i>
                                    </span>
                                </div>
                                <div id="passwordHelpBlock" class="form-text">
                                    Your password must be at least 8 characters long.
                                </div>
                            </div>

                            <!-- Submit button -->
                            <div class="align-items-center mt-0">
                                <div class="d-grid">
                                    <button class="btn btn-primary mb-0" type="submit">Login</button>
                                </div>
                            </div>
                        </form>

                        <!-- Link to Sign Up -->
                        <div class="mt-4 text-center">
                            <span>Don't have an account? <a href="sign_up.php">Sign up here.</a></span>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php
/*---------------------------------------------
    Capture content and include layout
---------------------------------------------*/
$content = ob_get_clean();
include('layouts/website.php');
?>
