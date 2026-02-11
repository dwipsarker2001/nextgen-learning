<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/helpers.php';
require_once '../includes/get_courses.php';

/*-------------------------------------------
 | Page Setup
 -------------------------------------------*/
$user_id    = $_SESSION['user_id'] ?? null;
$page_title = "My Learning | Nextgen Learning";

if (!$user_id) redirect('../login.php');
$courses = get_enrolled_course($conn, $user_id);
ob_start();
?>

<style>
.course-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    background: white;
}
.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.thumb-container {
    position: relative;
    overflow: hidden;
    height: 160px;
}
.thumb-container img {
    object-fit: cover;
    width: 100%;
    height: 100%;
}
.badge-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    backdrop-filter: blur(4px);
    background: rgba(0,0,0,0.5);
}
</style>

<div class="container py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-1" style="#4d4d4d;">
                <?php if(count($courses) > 0): ?>
                    Welcome Back! Continue Your Learning 👋
                <?php else: ?>
                    Welcome! Start Your Learning Journey 👋
                <?php endif; ?>
            </h3>
            <p class="m-0">
                <?php if(count($courses) > 0): ?>
                    You have <?= count($courses) ?> active <?= count($courses) === 1 ? 'course' : 'courses' ?> in your library.
                <?php else: ?>
                    Explore courses and start learning today.
                <?php endif; ?>
            </p>
        </div>
        <div class="d-none d-md-block">
            <a href="../our_courses.php?type=paid" class="btn btn-outline-primary rounded-pill px-4">
                Browse Courses
            </a>
        </div>
    </div>

    <div class="row g-4">

        <!-- ================= EMPTY STATE ================= -->
        <?php if (empty($courses)): ?>
        <div class="col-12">
            <div class="d-flex flex-column align-items-center justify-content-center text-center">
                <img 
                    src="../assets/images/empty.jpg" 
                    alt="No Courses Found"
                    class="img-fluid mb-4"
                    style="max-width: 360px;"
                >

                <h3 class="fw-bold mb-2">No Courses Yet 📚</h3>
                <p class=" mb-4" style="max-width:520px;">
                    You haven’t enrolled in any courses yet.
                    Start learning today by exploring our curated courses
                    and begin your learning journey with Nextgen Learning.
                </p>
                <div class="d-flex gap-3">
                    <a href="../our_courses.php?type=paid" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-search me-1"></i> Browse Courses
                    </a>

                    <a href="../our_courses.php?type=free" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-lightning-charge me-1"></i> Free Courses
                    </a>
                </div>
            </div>
        </div>

        <!-- ================= COURSE LIST ================= -->
        <?php else: ?>
        <?php foreach ($courses as $course):?>
            <?php if ($course['isEnrolled'] == "success") :?>
                <div class="col-md-4 action-trigger-hover">
                    <div class="card h-100 border course-card">
                        <div class="thumb-container">
                            <img 
                                class="card-img-top"
                                src="../uploads/img/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>"
                                alt="Course Image"
                            >
                            <span class="badge badge-overlay text-white px-3 py-2 rounded-pill">
                                <i class="bi bi-clock me-1"></i> 12h 30m
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mt-1 mb-3">
                                <?= htmlspecialchars($course['title']) ?>
                            </h5>
                            <div class="d-grid">
                                <a href="watch_course.php?course_id=<?= $course['id'] ?>"
                                    class="btn btn-dark rounded-pill py-2 fw-bold">
                                    <i class="bi bi-play-fill me-2"></i> Resume Learning
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="col-md-4 action-trigger-hover" style="opacity: 0.6; pointer-events: none;">
                    <div class="card h-100 border course-card">
                        <div class="thumb-container">
                            <img 
                                class="card-img-top"
                                src="../uploads/img/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>"
                                alt="Course Image"
                            >
                            <span class="badge badge-overlay text-white px-3 py-2 rounded-pill">
                                <i class="bi bi-clock me-1"></i> 12h 30m
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mt-1 mb-3">
                                <?= htmlspecialchars($course['title']) ?>
                            </h5>
                            <div class="d-grid">
                                <a href="#" 
                                class="btn btn-dark rounded-pill py-2 fw-bold disabled" 
                                style="pointer-events: none; opacity: 0.6;">
                                    <i class="bi bi-play-fill me-2"></i> Resume Learning
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<?php
$content = ob_get_clean();
include('../layouts/student.php');
?>
