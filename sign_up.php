<?php
session_start();
include('includes/helpers.php');
$page_title = "Sign Up | Nextgen Learning";

/*---------------------------------------------
    Start Output Buffering
---------------------------------------------*/
ob_start();
?>

<!---------------------------------------------
            REGISTER SECTION START
--------------------------------------------->
<section class="p-0 d-flex align-items-center position-relative overflow-hidden">
    <div class="container-fluid">
        <div class="row">

            <!---------------------------------------------
                        Left Side: Welcome Info
            --------------------------------------------->
            <div class="col-12 col-lg-6 d-md-flex align-items-center justify-content-center bg-primary bg-opacity-10">
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
                        Right Side: Registration Form
            --------------------------------------------->
            <div class="col-12 col-lg-6 m-auto">
                <?php
                /*---------------------------------------------
                    Session Alert Messages
                ---------------------------------------------*/
                if (isset($_SESSION['error_message']) || isset($_SESSION['success_message'])) {
                    $alert_type = isset($_SESSION['error_message']) ? 'warning' : 'success';
                    $message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : $_SESSION['success_message'];
                ?>
                <div class="col-sm-10 col-xl-8 m-auto">
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
                        <strong><?= $alert_type === 'warning' ? 'Error:' : 'Success:' ?></strong> <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                <?php
                    /* Clear session messages */
                    if ($alert_type === 'warning') {
                        unset($_SESSION['error_message']);
                    } else {
                        unset($_SESSION['success_message']);
                    }
                }
                ?>

                <!---------------------------------------------
                            Form Wrapper
                --------------------------------------------->
                <div class="row my-5">
                    <div class="col-sm-10 col-xl-8 m-auto">
                        <!-- Form Heading -->
                        <h2>Register.</h2>
                        <p class="lead mb-4">Register your account with your personal information.</p>

                        <!---------------------------------------------
                                    Registration Form
                        --------------------------------------------->
                        <form action="./includes/process_student_register.php" method="POST">

                            <!-- First name & Last name -->
                            <div class="mb-4">
                                <div class="row">
                                    <div class="col">
                                        <label for="fname" class="form-label">First Name *</label>
                                        <div class="input-group input-group-lg">
                                            <input type="text" class="form-control border-0 bg-light rounded-end" placeholder="First name" aria-label="First name" name="fname" id="fname" required>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label for="lname" class="form-label">Last Name *</label>
                                        <div class="input-group input-group-lg">
                                            <input type="text" class="form-control border-0 bg-light rounded-end" placeholder="Last name" aria-label="Last name" name="lname" id="lname" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email input -->
                            <div class="mb-4">
                                <label for="email" class="form-label">Enter your email address *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light rounded-start border-0 text-secondary px-3">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="email" class="form-control border-0 bg-light rounded-end ps-1" placeholder="E-mail" name="email" id="email" required>
                                </div>
                            </div>

                            <!-- Phone input -->
                            <div class="mb-4">
                                <label for="phone" class="form-label">Enter your phone number *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light rounded-start border-0 text-secondary px-3">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input
                                        type="tel"
                                        class="form-control border-0 bg-light rounded-end ps-1"
                                        placeholder="Phone"
                                        name="phone"
                                        id="phone"
                                        pattern="[0-9]{11}"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Password input -->
                            <div class="mb-4">
                                <label for="inputPassword5" class="form-label">Enter your password *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light rounded-start border-0 text-secondary px-3">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-0 bg-light rounded-end ps-1" placeholder="*********" name="pass" id="inputPassword5" required>
                                    <span class="input-group-text p-0 border-0" id="password-view-login" style="cursor: pointer;" onclick="togglePassword('inputPassword5')">
                                        <i class="far fa-eye cursor-pointer p-2 w-40px"></i>
                                    </span>
                                </div>
                                <div id="passwordHelpBlock" class="form-text">
                                    Your password must be at least 8 characters long.
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label for="inputPassword6" class="form-label">Confirm Password *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light rounded-start border-0 text-secondary px-3">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-0 bg-light rounded-end ps-1" placeholder="*********" name="cpass" id="inputPassword6" required>
                                    <span class="input-group-text p-0 border-0" id="password-view-login" style="cursor: pointer;" onclick="togglePassword('inputPassword6')">
                                        <i class="far fa-eye cursor-pointer p-2 w-40px"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="align-items-center mt-0">
                                <div class="d-grid">
                                    <button class="btn btn-primary mb-0" type="submit">Sign Up</button>
                                </div>
                            </div>
                        </form>

                        <!-- Link to Sign In -->
                        <div class="mt-4 text-center">
                            <span>Already have an account? <a href="sign_in.php">Sign in here.</a></span>
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
