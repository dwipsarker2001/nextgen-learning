<?php
/*-------------------------------------------
 | Essential Includes
 -------------------------------------------*/
include('../includes/db.php');
include('../includes/session.php');
include('../includes/helpers.php');
include('../includes/get_user_by_id.php');

/*-------------------------------------------
 | Page Setup
 -------------------------------------------*/
$user_id    = $_SESSION['user_id'];
$user       = get_user($conn, $user_id);
$page_title = "Profile | Student Panel | Nextgen Learning";

/*-------------------------------------------
 | Output Buffer Start
 -------------------------------------------*/
ob_start();
?>

<!-- Page main content START -->
<div class="page-content-wrapper h-auto border-none shadow-none m-0">
    <div class="card col-xl-8 mx-auto">
        <!-- Card body START -->
        <div class="card-body">

            <!-- Alert messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert <?= $_SESSION['error_type'] ?? 'alert-danger' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message'], $_SESSION['error_type']); ?>
            <?php endif; ?>

            <form class="row g-4 align-items-top" action="../includes/process_update_student_profile.php" method="POST" enctype="multipart/form-data">

                <div class="col-12 d-sm-flex justify-content-between align-items-center">
                    <h4 class="mb-sm-0">Student Profile</h4>
                    <button type="submit" class="btn btn-primary mb-0">Update</button>
                </div>

                <!-- Upload image START -->
                <div class="col-12">
                    <div class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                        <img src="../uploads/img/users/<?= $user['avatar'] ?: 'blank.png'; ?>" class="h-50px rounded-circle" alt="Profile Picture">
                        <div class="mt-3">
                            <h6 class="my-2">Upload profile picture here, or <a href="#!" class="text-primary">Browse</a></h6>
                            <label style="cursor:pointer;">
                                <input name="avatar" class="form-control stretched-link" type="file" id="image" accept="image/gif, image/jpeg, image/png" />
                            </label>
                            <p class="small mb-0 mt-2"><b>Note:</b> Only JPG, JPEG, PNG. Recommended size: 450px * 450px. Larger images will be cropped to 1:1 ratio.</p>
                        </div>
                    </div>
                </div>
                <!-- Upload image END -->

                <!-- First Name -->
                <div class="col-lg-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" class="form-control" placeholder="Enter your first name" value="<?= htmlspecialchars($user['first_name']); ?>" required>
                </div>

                <!-- Last Name -->
                <div class="col-lg-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" class="form-control" placeholder="Enter your last name" value="<?= htmlspecialchars($user['last_name']); ?>" required>
                </div>

                <!-- Email (disabled) -->
                <div class="col-lg-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" disabled>
                </div>

                <!-- Phone -->
                <div class="col-lg-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" value="<?= htmlspecialchars($user['phone']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Change password</label>
                    <div class="input-group">
                        <input name="password" 
                            type="password" 
                            class="form-control" 
                            placeholder="●●●●●●●●●"
                            id="inputPasswordProfile">
                        <span class="input-group-text px-2 border-0" 
                            style="cursor: pointer;" 
                            onclick="togglePassword('inputPasswordProfile')">
                            <i class="far fa-eye p-2"></i>
                        </span>
                    </div>
                </div>

            </form>
        </div>
        <!-- Card body END -->
    </div>
</div>
<!-- Page main content END -->

<?php
$content = ob_get_clean();
include('../layouts/student.php');
?>
