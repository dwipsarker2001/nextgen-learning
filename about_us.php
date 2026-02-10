<?php
session_start();
include 'includes/helpers.php';
$page_title = "About Us | Nextgen Learning";
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
          <h2 class="m-0">About NextGen Learning</h2>

          <!-- Breadcrumb -->
          <div class="d-flex justify-content-center">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb breadcrumb-dots mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">About Us</li>
              </ol>
            </nav>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<!---------------------------------------------
            ABOUT CONTENT START
--------------------------------------------->

<section class="pt-0 pt-md-5">
  <div class="container">
    <div class="row align-items-top">
      <div class="col-lg-8 mt-4 mt-lg-0 mx-auto">

        <!-- Title -->
        <h3 class="mb-2">NextGen Learning</h3>
        <strong class="d-block mb-3">Empowering the Next Generation of Learners</strong>

        <!-- Content -->
        <p>NextGen Learning is a forward-thinking, inclusive e-learning platform dedicated to breaking down the barriers to quality education. Based at the Shahjalal University of Science and Technology, we recognize that while technology is transforming how we learn, many students in Bangladesh still face challenges like high costs and a lack of localized, interactive content.</p>

        <p><strong>Our mission</strong> is to bridge this gap by providing a digital environment that is both globally competitive and locally relevant.</p>

        <h5 class="mt-4">Why Choose NextGen Learning?</h5>
        <p>We offer a balanced learning experience that combines the diversity of global platforms with the affordability and community focus required for local success.</p>

        <ul class="list-group list-group-borderless mt-4">
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>Diverse Course Catalog:</strong> STEM to Arts, Humanities, Business, and Design.</span>
          </li>
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>Interactive Learning:</strong> Live classes, real-time Q&A forums, and community engagement.</span>
          </li>
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>Affordability & Accessibility:</strong> Secure local payment gateways like bKash and Nagad.</span>
          </li>
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>Expert Instruction:</strong> Skilled educators passionate about sharing knowledge.</span>
          </li>
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>Flexible Progression:</strong> Learn at your own pace with progress tracking.</span>
          </li>
        </ul>

        <h5 class="mt-4">Our Vision for the Future</h5>
        <p>We are committed to evolving our platform with innovative features, including:</p>

        <ul class="list-group list-group-borderless mt-4">
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>AI-Powered Personalization:</strong> Tailored course recommendations.</span>
          </li>
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>Gamification:</strong> Engaging techniques to boost motivation.</span>
          </li>
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>Official Recognition:</strong> Digital certificates for career advancement.</span>
          </li>
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
            <span><strong>Mobile Learning:</strong> Learn anytime, anywhere via our dedicated app.</span>
          </li>
        </ul>

        <p class="mt-4">Join NextGen Learning today and take your skills to new heights!</p>

      </div>
    </div>
  </div>
</section>
</main>

<?php
$content = ob_get_clean();
include('layouts/website.php');
?>
