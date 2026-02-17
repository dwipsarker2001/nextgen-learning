<?php
// include essential files
include '../includes/db.php';
include('../includes/session.php');
include '../includes/get_user_by_id.php';

$user_role = $_SESSION['user_role'];
print_r($user_role);

// variables
$page_title = "Profile | Nextgen Learning";
$user = get_user($conn, $_SESSION['user_id']);
ob_start();
?>


<!-- Page main content START -->
<div class="page-content-wrapper h-auto border-none shadow-none m-0">
    <div class="card col-xl-8 mx-auto">
        <!-- Card body START -->
        <div class="card-body">
            <form class="row g-4 align-items-top" action="../includes/process_update_admin_profile.php" method="POST"
                enctype="multipart/form-data">

                <div class="col-12 d-sm-flex justify-content-between align-items-center">
                  <h4 class="mb-sm-0" style="text-transform: capitalize;"><?= $user_role ?> Profile</h4>
                  <button type="submit" class="btn btn-primary mb-0">Update</button>
                </div>
                <!-- Upload image START -->
                <div class="col-12">
                    <div
                        class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                        <!-- Image -->
                       <?php if ($user['avatar']) { ?>
                            <img class="h-50px rounded-circle" src="../uploads/img/users/<?php echo $user['avatar']; ?>" alt="avatar">
                        <?php } else { ?>
                            <img class="h-50px rounded-circle" src="../assets/images/avatar/empty-profile.png" alt="avatar">
                        <?php } ?>
                        <!-- <img src="../assets/images/element/gallery.svg" class="h-50px" alt=""> -->
                        <div>
                            <h6 class="my-2">Upload course profile picture here, or<a href="#!" class="text-primary">
                                    Browse</a></h6>
                            <label style="cursor:pointer;">
                                <span>
                                    <input name="profile" class="form-control stretched-link" type="file" id="image"
                                        accept="image/gif, image/jpeg, image/png" />
                                </span>
                            </label>
                            <p class="small mb-0 mt-2"><b>Note:</b> Only JPG, JPEG and PNG. Our suggested dimensions are
                                450px * 450px. Larger image will be cropped to 1:1 to fit our profile/previews.</p>
                        </div>
                    </div>
                </div>
                <!-- Upload image END -->


                <!-- Input item -->
                <div class="col-lg-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" class="form-control" placeholder="Enter your first name."
                        value="<?= $user['first_name'] ?>" required>
                </div>

                <!-- Input item -->
                <div class="col-lg-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" class="form-control" placeholder="Enter your last name."
                        value="<?= $user['last_name'] ?>" required>
                </div>

                <!-- Email -->
                <div class="col-lg-6">
                    <label class="form-label">Your email address</label>
                    <input type="text" name="email" class="form-control" placeholder="Enter your last name."
                        value="<?= $user['email'] ?>" required>
                </div>

                <!-- Phone -->
                <div class="col-lg-6">
                    <label class="form-label">Your mobile number</label>
                    <input type="text" name="phone" class="form-control" placeholder="Enter your last name."
                        value="<?= $user['phone'] ?>" required>
                </div>

                <!-- Password -->
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
include('../layouts/admin.php');
?>