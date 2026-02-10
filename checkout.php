<?php
session_start();
include('includes/db.php');
include('includes/get_course_by_id.php');

// protection
$course = get_course($conn, $_GET['id']);
if (in_array($_SESSION['user_role'], ['instructor', 'admin'])) {
    $_SESSION['error_message'] = "Oops! It looks like you're logged in as an instructor/admin. Course purchases are available for students only.";
    header("Location: course_details.php?id=" . $_GET['id']);
    exit();
}

$pageTitle = "Checkout | Nextgen Learning";
ob_start();
?>

<!---------------------------------------------
            PAGE BANNER START
--------------------------------------------->

<section class="py-0">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="bg-light p-4 text-center rounded-3">
          <h1 class="m-0">Checkout</h1>

          <!-- Breadcrumb -->
          <div class="d-flex justify-content-center">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb breadcrumb-dots mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="our_courses.php">Courses</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
              </ol>
            </nav>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<!---------------------------------------------
            CHECKOUT PAGE CONTENT START
--------------------------------------------->

<section class="pt-5">
  <div class="container">
    <div class="row">

      <!---------------------------------------------
                BILLING & PAYMENT START
      --------------------------------------------->

      <div class="col-12 col-md-8">

        <?php if (isset($_SESSION['error_message'])): ?>
          <div class="alert <?= $_SESSION['error_type'] ?? 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php
          unset($_SESSION['error_message'], $_SESSION['error_type']);
          ?>
        <?php endif; ?>

        <!-- Billing Information START -->
        <div class="card card-body shadow-sm border p-4">
          <h5 class="mb-0 mt-3">Billing Details</h5>
          <form action="includes/process_payment.php" method="POST" class="row g-3 mt-0">
            <input type="hidden" name="course_id" value="<?= htmlspecialchars($course['id']) ?>">

            <div class="col-md-6 bg-light-input">
              <label for="mobileNumber" class="form-label">Your bKash Account Number *</label>
              <input type="text" class="form-control" name="phone" id="mobileNumber" placeholder="01700000000" required>
            </div>

            <div class="col-md-6 bg-light-input">
              <label for="trnx_id" class="form-label">bKash Transaction ID *</label>
              <input type="text" class="form-control" name="tnx_id" id="trnx_id" placeholder="BL08CEOGWU" required>
            </div>

            <div class="col-12 text-end">
              <button type="submit" class="btn btn-primary mb-0">Confirm Payment</button>
            </div>
          </form>
        </div>

        <!---------------------------------------------
                HOW TO PAY START
        --------------------------------------------->

        <div class="card card-body shadow-sm border p-4 mt-3">
          <h5 class="mb-3">How to pay</h5>
          <ul class="list-group list-group-borderless pt-3">
            <li class="list-group-item h6 fw-light d-flex mb-0">
              <i class="fas fa-info-circle text-primary me-2"></i>
              <strong style="color: #E2136E;">bKash Number: 01752684239</strong>
            </li>
            <li class="list-group-item h6 fw-light d-flex mb-0">
              <i class="fas fa-info-circle text-primary me-2"></i>
              Send the required amount to the above Personal Number using the bKash app or by dialing *247#.
            </li>
            <li class="list-group-item h6 fw-light d-flex mb-0">
              <i class="fas fa-info-circle text-primary me-2"></i>
              Enter the correct Phone Number and Transaction ID in the Billing Details section.
            </li>
            <li class="list-group-item h6 fw-light d-flex mb-0">
              <i class="fas fa-info-circle text-primary me-2"></i>
              Enter the bKash number you used to send the payment in the "Your bKash Account Number" field.
            </li>
            <li class="list-group-item h6 fw-light d-flex mb-0">
              <i class="fas fa-info-circle text-primary me-2"></i>
              Enter the TrxID received via SMS from bKash in the "bKash Transaction ID" field.
            </li>
            <li class="list-group-item h6 fw-light d-flex mb-0">
              <i class="fas fa-info-circle text-primary me-2"></i>
              After completing steps 01 to 05, click on <strong>Complete Payment</strong>.
              <small>[NB: A 1.85% bKash “Send Money” fee will be added to the net price.]</small>
            </li>
          </ul>
        </div>
      </div>

      <!---------------------------------------------
                ORDER SUMMARY START
      --------------------------------------------->

      <div class="col-12 col-md-4">
        <div class="card shadow-sm card-body border p-4">
          <h5 class="mb-2">Order Summary</h5>
          <hr>

          <div class="row g-3">
            <div class="col-sm-4">
              <img class="rounded" src="./uploads/img/thumbnails/<?= $course['thumbnail'] ?>" alt="">
            </div>
            <div class="col-sm-8">
              <h6 class="mb-0"><a href="#"><?= $course['title'] ?></a></h6>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="text-success">৳ <?= $course['price'] ?></span>
              </div>
            </div>
          </div>

          <hr>

          <ul class="list-group list-group-borderless mb-2" style="border: 0;">
            <li class="list-group-item px-0 d-flex justify-content-between">
              <span class="h5 mb-0">Total</span>
              <span class="h5 mb-0">৳ <?= $course['price'] ?></span>
            </li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include('layouts/website.php');
?>
