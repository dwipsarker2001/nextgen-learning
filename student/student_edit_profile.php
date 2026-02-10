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
$page_title = "Edit Profile | Student Panel | Nextgen Learning";

/*-------------------------------------------
 | Output Buffer Start
 -------------------------------------------*/
ob_start();
?>

<div class="col-xl-12">

    <!-- Alert messages -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert <?= $_SESSION['error_type'] ?? 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message'], $_SESSION['error_type']); ?>
    <?php endif; ?>

    <!-- Single Card: Profile + Password Update -->
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom">
            <h3 class="card-header-title mb-0">Update Profile & Password</h3>
        </div>
        <div class="card-body">

            <form action="../includes/update_student.php" method="POST" enctype="multipart/form-data" class="row g-4">

                <!-- Profile Picture -->
                <div class="col-12 d-flex flex-column justify-content-center">
                    <label class="form-label me-3">Profile Picture</label>
					<div class="d-flex">
						<label class="position-relative me-4" for="uploadfile-1" title="Replace this pic">
							<span class="avatar avatar-xl">
								<img class="avatar-img rounded-circle" src="../uploads/img/users/<?= $user['avatar'] ?: 'blank.png'; ?>" alt="avatar">
							</span>
							<?php if ($user['avatar']): ?>
								<button type="button" class="uploadremove" id="uploadremove" data-user-id="<?= $user['id']; ?>">
									<i class="bi bi-x text-white"></i>
								</button>
							<?php endif; ?>
						</label>
						<label class="btn btn-primary-soft mb-0 d-block" for="uploadfile-1">
							Change Profile
							<input id="uploadfile-1" type="file" name="avatar" class="d-none" value="<?= $user['avatar']; ?>">
						</label>
					</div>
                </div>

                <!-- Full Name -->
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" class="form-control" value="<?= htmlspecialchars($user['first_name']); ?>" placeholder="First Name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" class="form-control" value="<?= htmlspecialchars($user['last_name']); ?>" placeholder="Last Name">
                </div>

                <!-- Email & Phone -->
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>" placeholder="Phone Number">
                </div>

                <!-- Password Section -->
                <div class="col-12 mt-5">
                    <h5 class="mb-3">Change Password</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="password" name="current_password" class="form-control" placeholder="Current Password">
                        </div>
                        <div class="col-md-4">
                            <input type="password" name="new_password" class="form-control" placeholder="New Password">
                        </div>
                        <div class="col-md-4">
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password">
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="col-12 d-flex justify-content-end mt-4">
                    <button type="submit" name="update_student" class="btn btn-primary mb-0">Save Changes</button>
                </div>

            </form>

        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include('../layouts/student.php');
?>
