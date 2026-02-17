<div class="col-md-4 action-trigger-hover">
  <div class="card h-100 border">
    <!-- Image -->
    <img
      src="./uploads/img/thumbnails/<?= $course['thumbnail'] ?>"
      class="card-img-top"
      alt="course image"
      style="height: 200px; object-fit: cover;" />
    <!-- Card body -->
    <div class="card-body pb-0">
      <!-- Badge and favorite -->
      <div class="d-flex gap-2 mb-3">
        <span class="hstack gap-2">
          <a href="#" class="badge text-bg-dark">
            <i class="far fa-clock text-white me-2"></i>
            <?= $course['duration'] ?>
          </a>
          <a
            href="#"
            class="badge bg-primary bg-opacity-10 text-primary">
            <i class="fas fa-table text-primary me-2"></i>
            <?= count_topics($conn, $course['id']) ?> lectures
          </a>
        </span>
      </div>
      <!-- Title -->
      <h5 class="card-title fw-normal">
        <a href="course_details.php?id=<?= $course['id'] ?>">
          <?= $course['title'] ?>
        </a>
      </h5>
    </div>
    <!-- Card footer -->
    <div class="card-footer pt-0 bg-transparent">
      <hr />
      <div
        class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-sm">
            <img
              class="custom-avatar custom-avatar-sm"
              src="uploads/img/users/<?= $instructor_avatar ? $instructor_avatar : 'blank.png'; ?>"
              alt="avatar" />
          </div>
          <p class="mb-0 ms-2 ">
            <strong class="h6 mb-0 d-block"><?= $instructor_name ?></strong>
            <small>Course Instructor</small>
          </p>
        </div>
        <div>
          <h4 class="text-success mb-0 item-show">
            <span >
              <?= $course['price'] == 0 ? 'Free' : '৳ ' . $course['price'] ?>
            </span>
          </h4>
          <a
            href="course_details.php?id=<?= $course['id'] ?>"
            class="btn btn-sm btn-success-soft item-show-hover"><i class="fas fa-shopping-cart me-2"></i>Buy Now</a>
        </div>
      </div>
    </div>
  </div>
</div>