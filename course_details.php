<?php
session_start();
include('includes/db.php');
include('includes/helpers.php');
include('includes/get_course_by_id.php');
$page_title = "Our courses | Nextgen Learning";
$course = get_detailed_course($conn, $_GET['id']);
ob_start();
?>

<!--Page content START -->
<section class="pt-3 pt-xl-5">
  <div class="container" data-sticky-container>
    <div class="row g-4">
      <!-- Main content START -->
      <div class="col-xl-8">

        <div class="row g-4">
          <div class="col-12">
            <h2><?= $course['title'] ?></h2>
            <p><?= $course['short_desc'] ?></p>
          </div>
          <!-- Title END -->

          <!-- Image and video -->

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

          <!-- About course START -->
          <div class="col-12">
            <div class="card border">
              <!-- Card header START -->
              <div class="card-header border-bottom">
                <h3 class="mb-0">Course description</h3>
              </div>
              <!-- Card header END -->

              <!-- Card body START -->
              <div class="card-body">
                <?= $course['description'] ?>
              </div>
              <!-- Card body START -->
            </div>
          </div>
          <!-- About course END -->

          <!-- Curriculum START -->
          <div class="col-12">
            <div class="card border rounded-3">
              <!-- Card header START -->
              <div class="card-header border-bottom">
                <h3 class="mb-0">Curriculum</h3>
              </div>
              <!-- Card header END -->

              <!-- Card body START -->
              <div class="card-body">
                <div class="row g-5">
                  <!-- Lecture item START -->
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
                          <!-- <a href="#" class="btn btn-sm btn-success mb-0">Play</a> -->
                        </div>
                        <?php if ($index !== array_key_last($lecture['topics'])): ?>
                          <hr>
                        <?php endif; ?>
                      <?php endforeach ?>
                    </div>
                  <?php endforeach ?>
                  <!-- Collapse button -->
                  <a class="mb-0 mt-4 btn-more d-flex align-items-center justify-content-center" data-bs-toggle="collapse" href="#collapseCourse" role="button" aria-expanded="false" aria-controls="collapseCourse">
                    See <span class="see-more mx-1">more</span><span class="see-less mx-1">less</span> video<i class="fas fa-angle-down ms-2"></i>
                  </a>

                </div>
              </div>
              <!-- Card body START -->
            </div>
          </div>
          <!-- Curriculum END -->
        </div>
      </div>
      <!-- Main content END -->

      <!-- Right sidebar START -->
      <div class="col-xl-4">
        <div data-sticky data-margin-top="80" data-sticky-for="768">
          <div class="row g-4">
            <div class="col-md-6 col-xl-12">
              <!-- Course info START -->
              <div class="card card-body border p-4">
                <!-- Price and share button -->
                <div class="d-flex justify-content-between align-items-center">
                  <!-- Price -->
                  <h3 class="fw-bold mb-0 me-2">৳ <?= $course['price'] ?></h3>
                  <!-- Share button with dropdown -->
                </div>

                <!-- Buttons -->
                <div class="mt-3 d-grid">
                  <a hrefx="<?= get_checkout_link($course['id']) ?>" class="btn btn-success">Buy now</a>
                </div>
                <!-- Divider -->
                <hr>

                <!-- Title -->
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
                <!-- Divider -->
                <hr>

                <!-- Instructor info -->
                <div class="d-sm-flex align-items-center">
                  <!-- Avatar image -->
                  <div class="avatar avatar">
                    <img class="avatar-img rounded-circle" src="./uploads/img/users/<?= $course['instructor']['avatar']; ?>" alt="avatar">
                  </div>
                  <div class="ms-sm-3 mt-2 mt-sm-0">
                    <h5 class="mb-0">
                      <?= $course['instructor']['name']; ?>
                    </h5>
                    <p class="mb-0 small">Course Instructor</p>
                  </div>
                </div>
              </div>
              <!-- Course info END -->
            </div>
          </div><!-- Row End -->
        </div>
      </div>
    </div>
  </div>
</section>



<?php
$content = ob_get_clean();
include('layouts/website.php');
?>