<?php
/*-------------------------------------------
 | Essential Includes
 -------------------------------------------*/
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/helpers.php';
require_once '../includes/get_courses.php';


/*-------------------------------------------
 | Page Setup
 -------------------------------------------*/
$user_id    = $_SESSION['user_id'] ?? null;
$page_title = "Student Dashboard | Student Panel | Nextgen Learning";

if (!$user_id) {
    redirect('../login.php');
}


/*-------------------------------------------
 | Fetch Enrolled Courses
 -------------------------------------------*/
$courses = get_enrolled_course($conn, $user_id);


/*-------------------------------------------
 | Output Buffer Start
 -------------------------------------------*/
ob_start();
?>

<!-- Main Content START -->
<div class="card bg-transparent border rounded-3">

    <!---------------------------------------------
    | Card Header
    --------------------------------------------->
    <div class="card-header bg-transparent border-bottom">
        <h3 class="mb-0">My Courses</h3>
    </div>

    <!---------------------------------------------
    | Card Body
    --------------------------------------------->
    <div class="card-body">

        <!---------------------------------------------
        | Course Table
        --------------------------------------------->
        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">

                <!-- Table Head -->
                <thead>
                    <tr>
                        <th class="border-0 rounded-start">Course Title</th>
                        <th class="border-0">Total Lectures</th>
                        <th class="border-0">Completed Lectures</th>
                        <th class="border-0 rounded-end">Action</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>

                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            No courses found
                        </td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($courses as $course): ?>

                        <?php
                        /*-------------------------------------------
                        | Course Progress (Temporary Values)
                        -------------------------------------------*/
                        $total_lectures     = 10;
                        $completed_lectures = 10;
                        $progress           = ($total_lectures > 0)
                            ? round(($completed_lectures / $total_lectures) * 100)
                            : 0;
                        ?>

                        <tr>
                            <!-- Course Info -->
                            <td>
                                <div class="d-flex align-items-center">

                                    <!-- Thumbnail -->
                                    <div class="w-100px">
                                        <img
                                            src="../uploads/img/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>"
                                            class="rounded"
                                            alt="<?= htmlspecialchars($course['title']) ?>">
                                    </div>

                                    <!-- Title & Progress -->
                                    <div class="ms-3 w-100">
                                        <h6 class="mb-1">
                                            <a href="#" class="text-decoration-none">
                                                <?= htmlspecialchars($course['title']) ?>
                                            </a>
                                        </h6>

                                        <div>
                                            <small class="float-end"><?= $progress ?>%</small>
                                            <div class="progress progress-sm bg-primary bg-opacity-10">
                                                <div
                                                    class="progress-bar bg-primary"
                                                    role="progressbar"
                                                    style="width: <?= $progress ?>%;"
                                                    aria-valuenow="<?= $progress ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </td>

                            <!-- Total Lectures -->
                            <td><?= $total_lectures ?></td>

                            <!-- Completed Lectures -->
                            <td><?= $completed_lectures ?></td>

                            <!-- Action -->
                            <td>
                                <a href="#" class="btn btn-sm btn-primary-soft">
                                    <i class="bi bi-play-circle me-1"></i>
                                    Continue
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
        <!-- Course Table END -->

    </div>
    <!-- Card Body END -->

</div>
<!-- Main Content END -->

<?php
/*-------------------------------------------
 | Layout Render
 -------------------------------------------*/
$content = ob_get_clean();
include('../layouts/student.php');
?>
