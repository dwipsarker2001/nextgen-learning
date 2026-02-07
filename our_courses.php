<?php
session_start();
include('includes/db.php');
include('includes/helpers.php');
include('includes/get_courses.php');
include('includes/get_course_by_id.php');
include('includes/fetch.php');

// Pagination variables
$limit = 8; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Course price
$conditions = match ($_GET['type'] ?? '') {
  'free' => "price = 0",
  'paid' => "price > 0",
  default => '1'
};

// Fetch courses
$result = fetch_records($conn, 'courses', [
  'conditions' => $conditions,
  'limit' => $limit,
  'offset' => $offset
]);

$courses = $result['data'];
$total_records = $result['total'];
$total_pages = ceil($total_records / $limit);
$start_record = ($page - 1) * $limit + 1;
$end_record = min($start_record + $limit - 1, $total_records);
$page_title = "Our Courses | Nextgen Learning";
ob_start();
?>

<!-- Page Banner START -->
<?php if (!empty($courses)) { 
  $type = $_GET['type'] ?? null;
  $type_labels = ['free' => 'Free Courses', 'paid' => 'Paid Courses'];
?>
<section class="py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="bg-light p-4 text-center rounded-3">

          <h2 class="m-0">
            Explore <?= $type_labels[$type] ?? 'Courses' ?>
          </h2>

          <!-- Breadcrumb -->
          <div class="d-flex justify-content-center">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb breadcrumb-dots mb-0">
                <li class="breadcrumb-item">
                  <a href="index.php">Home</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                  <?= $type_labels[$type] ?? 'Our Courses' ?>
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php } ?>
<!-- Page Banner END -->

<!-- Page content START -->
<section class="pt-0">
  <div class="container">
    <div class="row mt-3">
      <!-- Main content START -->
      <div class="col-12">
        <div class="row g-4">
          <?php
          if (!empty($courses)) {
            foreach ($courses as $course):
              $instructor = fetch_record($conn, 'users', $course['instructor_id']);
              $instructor_name = $instructor['first_name'] . " " . $instructor['last_name'];
              $instructor_avatar = $instructor['avatar'];
              include './components/course_card_v2.php';
            endforeach;

            // pagination
            include './components/pagination_v2.php';
          } else { ?>
            <div class="col-12 text-center">
              <h1 class="display-5 text-danger mb-0">No Courses Yet</h1>
              <h2>Courses have not been uploaded.</h2>
              <p class="mb-4">You will be able to access them as soon as they are uploaded.</p>
              <a href="index.php" class="btn btn-primary mb-0">Go to Homepage</a>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include('layouts/website.php');
?>
