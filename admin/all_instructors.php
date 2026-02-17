<?php
// include essential files
include('../includes/db.php');
include('../includes/session.php');
include('../includes/helpers.php');
include('../includes/get_course_by_id.php');
include('../includes/get_records.php');
include('../includes/get_totals.php');
include('../includes/fetch.php');

// Pagination variables
$limit = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Conditions
$phone_condition = isset($_GET['phone']) ? " AND phone LIKE '%" . $_GET['phone'] . "%'" : '';
$conditions = "role = 'instructor'" . $phone_condition;

// query parameters
$query_params = [
  'conditions' => $conditions,
  'limit' => $limit,
  'offset' => $offset
];

// impotent data
$result = fetch_records($conn, 'users', $query_params);
$users = $result['data'];
$total_records = $result['total'];
$total_pages = ceil($total_records / $limit);
$start_record = ($page - 1) * $limit + 1;
$end_record = min($start_record + $limit - 1, $total_records);
$page_title = "Enrollments | Nextgen Learning";
ob_start();
?>


<!-- Page main content START -->
<div class="page-content-wrapper border-none shadow-none m-0">

  <!-- Title -->
  <div class="row mb-3">
    <div class="col-12 d-sm-flex justify-content-between align-items-center">
      <h4 class=" mb-2 mb-sm-0">Instructors Table</h4>
      <div class="nav my-3 my-xl-0 flex-nowrap align-items-center">
        <div class="nav-item w-100">
          <form class="position-relative" action="all_instructors.php" method="get">
            <input class="form-control pe-5 bg-secondary bg-opacity-10 border-0" name="phone" type="search" placeholder="Mobile number..." aria-label="Search">
            <button class="bg-transparent px-2 py-0 border-0 position-absolute top-50 end-0 translate-middle-y" type="submit"><i class="fas fa-search fs-6 text-primary"></i></button>
          </form>
        </div>
      </div>
      <!-- <a href="create_course.php" class="btn btn-sm btn-primary mb-0">Create a Course</a> -->
    </div>
  </div>

  <!-- Card body START -->
  <div class="card-body">
    <!-- Course table START -->
    <div class="table-responsive border-0 rounded-3">
      <!-- Table START -->
      <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
        <!-- Table head -->
        <thead>
          <tr>
            <th scope="col" class="border-0 rounded-start">Instructors name</th>
            <th scope="col" class="border-0">Join date</th>
            <th scope="col" class="border-0">Email</th>
            <th scope="col" class="border-0">Phone</th>
            <?php if(isUser('admin')): ?>
            <th scope="col" class="border-0 rounded-end text-end">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>

        <!-- Table body START -->
        <tbody>
          <?php foreach ($users as $user) : ?>
            <tr>
              <!-- Table data -->
              <td>
                <div class="d-flex align-items-center position-relative">
                  <!-- Image -->
                  <div class="avatar avatar-md">
                    <img class="custom-avatar" src="../uploads/img/users/<?= $user['avatar'] ? $user['avatar'] : 'blank.png' ?>" alt="">
                  </div>
                  <div class="mb-0 ms-3">
                    <!-- Title -->
                    <h6 class="mb-0">
                      <a href="#" class="stretched-link">
                        <?= $user['first_name'] ?>
                        <?= $user['last_name'] ?>
                      </a>
                    </h6>
                    <span class="text-body small">
                      <!-- <i class="fas fa-phone text-info me-1 mt-1"></i> -->
                      <?php //$user['phone'] 
                      ?>
                    </span>
                  </div>
                </div>
              </td>

              <!-- Table data -->
              <td><?= format_date($user['created_at']) ?></td>

              <!-- Table data -->
              <td><?= $user['email'] ?></td>
              <td><?= $user['phone'] ?></td>

              <!-- Table data -->
              <?php if(isUser("admin")): ?>
              <td class="text-end">

                <!-- Demote Button -->
                <form method="POST" action="../includes/process_update_role.php" class="d-inline">
                  <input type="hidden" name="id" value="<?= $user['id'] ?>">
                  <input type="hidden" name="role" value="student">
                  <button class="btn btn-warning btn-sm me-1" type="submit">
                    <i class="fas fa-angle-double-down me-1"></i>
                    Demote
                  </button>
                </form>

                <!-- Delete Button -->
                <form method="POST" action="../includes/process_delete_user.php" class="d-inline">
                  <input type="hidden" name="id" value="<?= $user['id'] ?>">
                  <input type="hidden" name="table" value="users">
                  <input type="hidden" name="page" value="instructor">
                  <button class="btn btn-danger btn-sm"
                    onclick="return confirm('Are you sure you want to delete this account?');"
                    type="submit">
                    <i class="bi bi-trash me-1"></i>
                    Delete
                  </button>
                </form>
              </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <!-- Table body END -->
      </table>
    </div>
    <!-- Course table END -->
  </div>
  <!-- Card body END -->

  <!-- Card footer START -->
  <?php include('../components/pagination.php') ?>
</div>
<!-- Page main content END -->



<?php
$content = ob_get_clean();
include('../layouts/admin.php');
?>