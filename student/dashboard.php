<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/helpers.php';
require_once '../includes/get_courses.php';
require_once '../includes/get_totals.php';
require_once '../includes/get_progress.php';
require_once '../includes/get_watched.php';
require_once '../includes/get_recommendations.php';

$user_id    = $_SESSION['user_id'] ?? null;
$page_title = "My Learning | Nextgen Learning";

if (!$user_id) redirect('../sign_in.php');

$courses = get_enrolled_course($conn, $user_id);
$recently_watched = get_recently_watched($conn, $user_id, 6);
$recommendations = get_course_recommendations($conn, $user_id, 4);
$streak = get_learning_streak($conn, $user_id);
$total_watched = get_total_watched_topics($conn, $user_id);
$completed_courses = get_completed_courses_count($conn, $user_id);
$in_progress_count = get_in_progress_courses_count($conn, $user_id);
$enrolled_count = total_course_enrolled($conn, $user_id);

$active_courses = array_filter($courses, fn($c) => $c['isEnrolled'] === 'success');
$pending_courses = array_filter($courses, fn($c) => $c['isEnrolled'] === 'pending');

function time_ago($datetime)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 6) return floor($diff->d / 7) . 'w ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}

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
.stat-card {
    border: none;
    border-radius: 16px;
    transition: transform 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
}
.progress-bar-custom {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
}
.progress-bar-custom .progress-fill {
    height: 100%;
    border-radius: 4px;
    background: linear-gradient(90deg, #e11d48, #f43f5e);
    transition: width 0.6s ease;
}
.recent-card {
    transition: all 0.25s ease;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    overflow: hidden;
}
.recent-card:hover {
    border-color: #e11d48;
    box-shadow: 0 8px 20px rgba(225, 29, 72, 0.1);
    transform: translateY(-2px);
}
.recent-thumb {
    width: 110px;
    min-height: 80px;
    object-fit: cover;
    flex-shrink: 0;
}
.recent-resume-btn {
    white-space: nowrap;
    border-radius: 8px;
    padding: 4px 14px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #1e293b;
    transition: all 0.2s ease;
}
.recent-resume-btn:hover {
    background: #e11d48;
    color: #fff;
}
.recent-time {
    font-size: 0.75rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 4px;
}
@media (max-width: 575px) {
    .recent-thumb {
        width: 80px;
        min-height: 60px;
    }
    .recent-card-body {
        padding: 0.6rem !important;
    }
}
.section-title {
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #1e293b;
}
.rec-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.rec-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.08);
}
.nav-tabs .nav-link {
    font-weight: 600;
    color: #64748b;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 0.75rem 1.25rem;
}
.nav-tabs .nav-link:hover {
    color: #e11d48;
    border-bottom-color: #f1f5f9;
}
.nav-tabs .nav-link.active {
    color: #e11d48;
    border-bottom-color: #e11d48;
    background: none;
}
</style>

<div class="container pb-4">

    <!-- ================= HEADER ================= -->
    <div class="d-flex justify-content-between align-items-center mb-3 pt-4">
        <div>
            <h3 class="fw-bold mb-1" style="color:#4d4d4d;">
                <?php if ($enrolled_count > 0): ?>
                    Welcome Back! Continue Your Learning 👋
                <?php else: ?>
                    Welcome! Start Your Learning Journey 👋
                <?php endif; ?>
            </h3>
            <p class="m-0 text-muted">
                <?php if ($enrolled_count > 0): ?>
                    You have <?= $enrolled_count ?> active <?= $enrolled_count === 1 ? 'course' : 'courses' ?> in your library.
                <?php else: ?>
                    Explore courses and start learning today.
                <?php endif; ?>
            </p>
        </div>
        <div class="d-none d-md-block">
            <a href="../our_courses.php?type=paid" class="btn btn-outline-primary rounded-pill px-4">
                <i class="bi bi-search me-1"></i> Browse Courses
            </a>
        </div>
    </div>

    <?php if (empty($courses)): ?>

    <!-- ================= EMPTY STATE ================= -->
    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5">
        <img src="../assets/images/empty.jpg" alt="No Courses Found" class="img-fluid mb-4" style="max-width: 360px;">
        <h3 class="fw-bold mb-2">No Courses Yet 📚</h3>
        <p class="mb-4" style="max-width:520px;">
            You haven't enrolled in any courses yet.
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

    <?php else: ?>

    <!-- ================= TABS ================= -->
    <ul class="nav nav-tabs mb-4" id="dashboardTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-speedometer2 me-1"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">
                <i class="bi bi-collection-play me-1"></i> My Courses
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="explore-tab" data-bs-toggle="tab" data-bs-target="#explore" type="button" role="tab">
                <i class="bi bi-star me-1"></i> Explore
            </button>
        </li>
    </ul>

    <div class="tab-content" id="dashboardTabContent">

        <!-- ================= TAB 1: OVERVIEW ================= -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card card card-body bg-primary bg-opacity-10 p-3 p-md-4 h-100"
                         role="button"
                         tabindex="0"
                         onclick="const t = document.getElementById('courses-tab'); if (t) { new bootstrap.Tab(t).show(); }"
                         style="cursor: pointer;"
                         title="Go to My Courses">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="fw-bold mb-0 text-primary"><?= $enrolled_count ?></h2>
                                <span class="small fw-semibold text-muted">Enrolled</span>
                            </div>
                            <div class="icon-lg rounded-circle bg-primary text-white">
                                <i class="fas fa-book-open fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card card card-body bg-success bg-opacity-10 p-3 p-md-4 h-100"
                         role="button"
                         tabindex="0"
                         onclick="const t = document.getElementById('courses-tab'); if (t) { new bootstrap.Tab(t).show(); }"
                         style="cursor: pointer;"
                         title="Go to My Courses">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="fw-bold mb-0 text-success"><?= $completed_courses ?></h2>
                                <span class="small fw-semibold text-muted">Completed</span>
                            </div>
                            <div class="icon-lg rounded-circle bg-success text-white">
                                <i class="fas fa-check-circle fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card card card-body bg-warning bg-opacity-10 p-3 p-md-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="fw-bold mb-0 text-warning"><?= $streak ?> 🔥</h2>
                                <span class="small fw-semibold text-muted">Day Streak</span>
                            </div>
                            <div class="icon-lg rounded-circle bg-warning text-white">
                                <i class="fas fa-fire fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card card card-body bg-info bg-opacity-10 p-3 p-md-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="fw-bold mb-0 text-info"><?= $total_watched ?></h2>
                                <span class="small fw-semibold text-muted">Lessons Done</span>
                            </div>
                            <div class="icon-lg rounded-circle bg-info text-white">
                                <i class="fas fa-play-circle fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($recently_watched)): ?>
            <div>
                <h5 class="section-title mb-3"><i class="bi bi-clock-history me-2"></i>Recently Watched</h5>
                <div class="row g-3">
                    <?php foreach ($recently_watched as $item): ?>
                    <div class="col-12 col-md-6">
                        <a href="watch_course.php?course_id=<?= $item['course_id'] ?>&topic_id=<?= $item['topic_id'] ?>" class="text-decoration-none">
                            <div class="recent-card d-flex align-items-stretch">
                                <img src="../uploads/img/thumbnails/<?= htmlspecialchars($item['thumbnail']) ?>"
                                     alt="" class="recent-thumb">
                                <div class="recent-card-body d-flex flex-column justify-content-center p-3 w-100">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="min-width-0">
                                            <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size:0.95rem;">
                                                <?= htmlspecialchars($item['topic_title']) ?>
                                            </h6>
                                            <small class="text-muted text-truncate d-block">
                                                <?= htmlspecialchars($item['course_title']) ?>
                                            </small>
                                        </div>
                                        <span class="recent-resume-btn text-decoration-none flex-shrink-0">
                                            <i class="fas fa-play me-1"></i> Resume
                                        </span>
                                    </div>
                                    <span class="recent-time mt-1">
                                        <i class="far fa-clock"></i> <?= time_ago($item['watched_at']) ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ================= TAB 2: MY COURSES ================= -->
        <div class="tab-pane fade" id="courses" role="tabpanel">

            <div class="mb-4">
                <h5 class="section-title mb-3"><i class="bi bi-collection-play me-2"></i>Active Courses</h5>
                <div class="row g-4">
                    <?php foreach ($active_courses as $course):
                        $progress = get_course_progress($conn, $user_id, $course['id']);
                    ?>
                    <div class="col-md-4 action-trigger-hover">
                        <div class="card h-100 border course-card">
                            <div class="thumb-container">
                                <img class="card-img-top"
                                     src="../uploads/img/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>"
                                     alt="Course Image">
                                <span class="badge badge-overlay text-white px-3 py-2 rounded-pill">
                                    <i class="bi bi-clock me-1"></i> <?= htmlspecialchars($course['duration']) ?>
                                </span>
                                <?php if ($progress >= 100): ?>
                                <span class="badge bg-success position-absolute bottom-0 start-0 m-2 px-3 py-1 rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i> Completed
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold mt-1 mb-3"><?= htmlspecialchars($course['title']) ?></h5>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-semibold"><?= $progress ?>% complete</span>
                                        <span class="text-muted"><?= $progress >= 100 ? 'Done!' : 'In progress' ?></span>
                                    </div>
                                    <div class="progress-bar-custom">
                                        <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <a href="watch_course.php?course_id=<?= $course['id'] ?>"
                                       class="btn btn-dark rounded-pill py-2 fw-bold">
                                        <i class="bi bi-play-fill me-2"></i>
                                        <?= $progress >= 100 ? 'Review Course' : 'Resume Learning' ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($pending_courses)): ?>
            <div>
                <h5 class="section-title mb-3"><i class="bi bi-hourglass-split me-2"></i>Pending Enrollments</h5>
                <div class="row g-4">
                    <?php foreach ($pending_courses as $course): ?>
                    <div class="col-md-4 action-trigger-hover" style="opacity:0.6; pointer-events:none;">
                        <div class="card h-100 border course-card">
                            <div class="thumb-container">
                                <img class="card-img-top"
                                     src="../uploads/img/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>"
                                     alt="Course Image">
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 px-3 py-1 rounded-pill">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold mt-1 mb-3"><?= htmlspecialchars($course['title']) ?></h5>
                                <p class="text-muted small mb-2">Awaiting payment confirmation</p>
                                <div class="d-grid">
                                    <a href="#" class="btn btn-dark rounded-pill py-2 fw-bold disabled">
                                        <i class="bi bi-lock-fill me-2"></i> Pending Approval
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ================= TAB 3: EXPLORE ================= -->
        <div class="tab-pane fade" id="explore" role="tabpanel">

            <div class="mb-4">
                <h5 class="section-title mb-3"><i class="bi bi-star me-2"></i>Recommended for You</h5>
                <?php if (!empty($recommendations)): ?>
                <div class="row g-4">
                    <?php foreach ($recommendations as $course): ?>
                    <div class="col-6 col-md-3">
                        <a href="../course_details.php?id=<?= $course['id'] ?>" class="text-decoration-none">
                            <div class="rec-card">
                                <div style="height:120px; overflow:hidden;">
                                    <img src="../uploads/img/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>"
                                         alt="" class="w-100 h-100" style="object-fit:cover;">
                                </div>
                                <div class="p-3">
                                    <h6 class="fw-bold text-dark mb-1 text-truncate"><?= htmlspecialchars($course['title']) ?></h6>
                                    <span class="badge bg-dark rounded-pill">
                                        <?= $course['price'] == 0 ? 'Free' : '৳ ' . $course['price'] ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted">No recommendations available yet. Keep learning!</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <a href="../our_courses.php" class="btn btn-outline-primary rounded-pill px-5">
                    <i class="bi bi-grid me-1"></i> Browse All Courses
                </a>
            </div>

        </div>

    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam === 'courses' || window.location.hash === '#courses') {
        const coursesTabBtn = document.getElementById('courses-tab');
        if (coursesTabBtn) {
            new bootstrap.Tab(coursesTabBtn).show();
        }
    } else if (tabParam === 'explore' || window.location.hash === '#explore') {
        const exploreTabBtn = document.getElementById('explore-tab');
        if (exploreTabBtn) {
            new bootstrap.Tab(exploreTabBtn).show();
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include('../layouts/student.php');
?>
