<?php
session_start();
include('includes/db.php');
include('includes/helpers.php');
include('includes/get_course_by_id.php');

$page_title = "Our courses | Nextgen Learning";
$course = get_detailed_course($conn, $_GET['id']);
ob_start();
?>

<!---------------------------------------------
            PAGE CONTENT START
--------------------------------------------->

<section class="pt-3 pt-xl-5">
  <div class="container" data-sticky-container>
    <div class="row g-4">

      <!---------------------------------------------
                MAIN CONTENT START
      --------------------------------------------->
      <div class="col-xl-8">
        <div class="row g-4">

          <!-- Course Title -->
          <div class="col-12">
            <h2><?= $course['title'] ?></h2>
            <p><?= $course['short_desc'] ?></p>
          </div>

          <!-- Video START -->
          <div class="col-12 position-relative">
            <div style="position: relative; width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden;">
              <iframe
                class="rounded-3"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
                src="<?= $course['video'] ?>"
                title="YouTube video player"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen>
              </iframe>
            </div>
          </div>

          <!---------------------------------------------
                ABOUT COURSE START
          --------------------------------------------->
          <div class="col-12">
            <div class="card border">
              <div class="card-header border-bottom">
                <h3 class="mb-0">Course description</h3>
              </div>
              <div class="card-body">
                <?= $course['description'] ?>
              </div>
            </div>
          </div>

          <!---------------------------------------------
                CURRICULUM START
          --------------------------------------------->
          <div class="col-12">
            <div class="card border rounded-3">
              <div class="card-header border-bottom">
                <h3 class="mb-0">Curriculum</h3>
              </div>
              <div class="card-body">
                <div class="row g-5">
                  <?php foreach ($course['lectures'] as $lecture): ?>
                    <div class="col-12">
                      <h5 class="mb-4"><?= $lecture['title'] ?> (<?= count($lecture['topics']) ?> lectures)</h5>
                      <?php foreach ($lecture['topics'] as $index => $topic): ?>
                        <div class="d-sm-flex justify-content-sm-between align-items-center">
                          <div class="d-flex">
                            <a href="#" class="btn d-flex align-items-center justify-content-center btn-danger-soft btn-round mb-0 text-center">
                                <i class="fas fa-play"></i>
                            </a>
                            <div class="ms-2 ms-sm-3 mt-1 mt-sm-0">
                              <h6 class="mb-0"><?= $topic['title'] ?></h6>
                              <p class="mb-2 mb-sm-0 small">10m 56s</p>
                            </div>
                          </div>
                        </div>
                        <?php if ($index !== array_key_last($lecture['topics'])): ?>
                          <hr>
                        <?php endif; ?>
                      <?php endforeach ?>
                    </div>
                  <?php endforeach ?>
                  <a class="mb-0 mt-4 btn-more d-flex align-items-center justify-content-center" data-bs-toggle="collapse" href="#collapseCourse" role="button" aria-expanded="false" aria-controls="collapseCourse">
                    See <span class="see-more mx-1">more</span><span class="see-less mx-1">less</span> video<i class="fas fa-angle-down ms-2"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!---------------------------------------------
                RIGHT SIDEBAR START
      --------------------------------------------->
      <div class="col-xl-4">
        <div data-sticky data-margin-top="80" data-sticky-for="768">
          <div class="row g-4">
            <div class="col-md-6 col-xl-12">
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
                      <strong><?= $alertLabel ?></strong> <?= htmlspecialchars($message) ?>
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
              <div class="card card-body border p-4">

                <!-- Price & Buy Button -->
                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="fw-bold mb-0 me-2">৳ <?= $course['price'] ?></h3>
                </div>
                <div class="mt-3 d-grid">
                  <a href="<?= get_checkout_link($course['id']) ?>" class="btn btn-success">Buy now</a>
                </div>
                <hr>

                <!-- Course details -->
                <h5 class="mb-3">This course includes</h5>
                <ul class="list-group list-group-borderless border-0">
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="h6 fw-light mb-0"><i class="fas fa-fw fa-book-open text-primary"></i>Lectures</span>
                    <span><?= get_total_lectures($course) ?></span>
                  </li>
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="h6 fw-light mb-0"><i class="fas fa-fw fa-clock text-primary"></i>Duration</span>
                    <span><?= $course['duration'] ?></span>
                  </li>
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="h6 fw-light mb-0"><i class="fas fa-fw fa-signal text-primary"></i>Skills</span>
                    <span>Beginner</span>
                  </li>
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="h6 fw-light mb-0"><i class="fas fa-fw fa-globe text-primary"></i>Language</span>
                    <span><?= $course['language'] ?></span>
                  </li>
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="h6 fw-light mb-0"><i class="fas fa-fw fa-medal text-primary"></i>Certificate</span>
                    <span>Yes</span>
                  </li>
                </ul>
                <hr>

                <!-- Instructor info -->
                <div class="d-sm-flex align-items-center">
                  <div class="avatar avatar">
                    <img class="avatar-img rounded-circle" src="./uploads/img/users/<?= $course['instructor']['avatar']; ?>" alt="avatar">
                  </div>
                  <div class="ms-sm-3 mt-2 mt-sm-0">
                    <h5 class="mb-0"><?= $course['instructor']['name']; ?></h5>
                    <p class="mb-0 small">Course Instructor</p>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include('layouts/website.php');
?>
