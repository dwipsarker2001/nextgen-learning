<?php
session_start();
include('includes/helpers.php');
$page_title = "Contact Us | Nextgen Learning";

/*---------------------------------------------
    Start output buffering
---------------------------------------------*/
ob_start();
?>

<main>

  <!---------------------------------------------
              PAGE INTRODUCTION START
  --------------------------------------------->
  <section class="py-4">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="bg-light p-4 text-center rounded-3">
            <!-- Page Title -->
            <h2 class="m-0">Contact Our Support Team</h2>

            <!-- Breadcrumb -->
            <div class="d-flex justify-content-center">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-dots mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!---------------------------------------------
              CONTACT AREA START
  --------------------------------------------->
  <section>
    <div class="container">
      <div class="row g-4 g-lg-0 align-items-center">

        <!-- Left Content: Illustration + Socials -->
        <div class="col-md-6 text-center">
          <!-- Contact Illustration -->
          <img src="assets/images/element/contact.svg" class="h-400px" alt="Course Support">

          <!-- Social Media Links -->
          <div class="d-sm-flex align-items-center justify-content-center mt-2 mt-sm-4">
            <h5 class="mb-0">Join our community</h5>
            <ul class="list-inline mb-0 ms-sm-2">
              <li class="list-inline-item">
                <a class="fs-5 me-1 text-facebook" href="#"><i class="fab fa-fw fa-facebook-square"></i></a>
              </li>
              <li class="list-inline-item">
                <a class="fs-5 me-1 text-instagram" href="#"><i class="fab fa-fw fa-instagram"></i></a>
              </li>
              <li class="list-inline-item">
                <a class="fs-5 me-1 text-linkedin" href="#"><i class="fab fa-fw fa-linkedin-in"></i></a>
              </li>
            </ul>
          </div>
        </div>

        <!-- Right Content: Contact Form -->
        <div class="col-md-6" id="contact">
          <h3 class="mt-4 mt-md-0">Get in Touch</h3>

          <!-- Alert Messages -->
          <?php
          if (isset($_SESSION['error_message']) || isset($_SESSION['success_message'])) {
            $alert_type = isset($_SESSION['error_message']) ? 'warning' : 'success';
            $message = isset($_SESSION['error_message'])
              ? $_SESSION['error_message']
              : $_SESSION['success_message'];
          ?>
            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
              <strong><?= $alert_type === 'warning' ? 'Note:' : 'Thank you!' ?></strong>
              <?= htmlspecialchars($message) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php
            unset($_SESSION['error_message'], $_SESSION['success_message']);
          }
          ?>

          <!-- Intro Text -->
          <p>Have questions about a course or need guidance?</p>

          <!---------------------------------------------
                      CONTACT FORM START
          --------------------------------------------->
          <form action="includes/process_contact_submit.php" method="post">

            <!-- Full Name -->
            <div class="mb-4 bg-light-input">
              <label for="yourName" class="form-label">Full Name *</label>
              <input
                type="text"
                name="name"
                class="form-control form-control-lg"
                id="yourName"
                placeholder="Your full name"
                required
              >
            </div>

            <!-- Phone Number -->
            <div class="mb-4 bg-light-input">
              <label for="phoneInput" class="form-label">Phone Number *</label>
              <input
                type="text"
                name="phone"
                class="form-control form-control-lg"
                id="phoneInput"
                placeholder="Your phone number"
                required
              >
            </div>

            <!-- Message -->
            <div class="mb-4 bg-light-input">
              <label for="textareaBox" class="form-label">How can we help you? (Course name if applicable) *</label>
              <textarea
                class="form-control"
                name="message"
                id="textareaBox"
                rows="4"
                placeholder="Tell us what you’re looking for..."
                required
              ></textarea>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
              <button class="btn btn-lg btn-primary mb-0" type="submit">
                Send Message
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </section>

</main>

<?php
/*---------------------------------------------
    Capture content and include layout
---------------------------------------------*/
$content = ob_get_clean();
include('layouts/website.php');
?>
