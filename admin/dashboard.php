<?php
// include essential files
include '../includes/db.php';
include '../includes/session.php';
include '../includes/helpers.php';
include '../includes/get_totals.php';
include '../includes/get_record.php';
include '../includes/get_records.php';
include '../includes/get_chart_data.php';

// variables
$total_students = get_total_students($conn);
$total_courses = get_total_courses($conn);
$total_instructors = get_total_instructors($conn);
$total_earnings = get_total_earnings($conn);
$page_title = "Dashboard | Nextgen Learning";

// chart data
$chart_data = [
  'enrollmentTrend' => get_enrollment_trend($conn, 6),
  'revenueTrend' => get_revenue_trend($conn, 6),
  'coursePopularity' => get_course_popularity($conn, 10),
  'enrollmentStatus' => get_enrollment_status_breakdown($conn),
  'coursePerformance' => get_course_performance_radar($conn, 4),
];

ob_start();
?>


<div class="page-content-wrapper m-0 shadow-none border-none">

	<!-- Title -->
	<div class="row">
		<div class="col-12 mb-3">
			<h1 class="h3 mb-2 mb-sm-0">Dashboard</h1>
		</div>
	</div>

	<!-- Counter boxes START -->
	<div class="row g-4 mb-4">
		<!-- Counter item -->
		<div class="col-md-6 col-xxl-3">
			<div class="card card-body bg-warning bg-opacity-15 p-4 h-100">
				<div class="d-flex justify-content-between align-items-center">
					<!-- Digit -->
					<div>
						<h2 class="purecounter mb-0 fw-bold" data-purecounter-delay="200" data-purecounter-start="0" data-purecounter-end="<?= $total_courses ?>">0</h2>
						<span class="mb-0 h6 fw-light">Total Courses</span>
					</div>
					<!-- Icon -->
					<div class="icon-lg rounded-circle bg-warning text-white mb-0"><i class="fas fa-tv fa-fw"></i></div>
				</div>
			</div>
		</div>

		<!-- Counter item -->
		<div class="col-md-6 col-xxl-3">
			<div class="card card-body bg-success bg-opacity-10 p-4 h-100">
				<div class="d-flex justify-content-between align-items-center">
					<!-- Digit -->
					<div>
						<div class="d-flex">
							<span class="mb-0 h2 me-1">৳</span>
							<h2 class="purecounter mb-0 fw-bold" data-purecounter-start="0" data-purecounter-end="<?= $total_earnings ?>" data-purecounter-delay="200">0</h2>
						</div>
						<span class="mb-0 h6 fw-light">Total Earnings</span>
					</div>
					<!-- Icon -->
					<div class="icon-lg rounded-circle bg-success text-white mb-0"><i class="fas fa-money-bill-wave fa-fw"></i></i></div>
				</div>
			</div>
		</div>

		<!-- Counter item -->
		<div class="col-md-6 col-xxl-3">
			<div class="card card-body bg-primary bg-opacity-10 p-4 h-100">
				<div class="d-flex justify-content-between align-items-center">
					<!-- Digit -->
					<div>
						<h2 class="purecounter mb-0 fw-bold" data-purecounter-start="0" data-purecounter-end="<?= $total_students ?>">0</h2>
						<span class="mb-0 h6 fw-light">Total Students</span>
					</div>
					<!-- Icon -->
					<div class="icon-lg rounded-circle bg-primary text-white mb-0"><i class="fas fa-user-graduate fa-fw"></i></div>
				</div>
			</div>
		</div>

		<!-- Counter item -->
		<div class="col-md-6 col-xxl-3">
			<div class="card card-body bg-purple bg-opacity-10 p-4 h-100">
				<div class="d-flex justify-content-between align-items-center">
					<!-- Digit -->
					<div>
						<div class="d-flex">
							<h2 class="purecounter mb-0 fw-bold" data-purecounter-start="0" data-purecounter-end="<?= $total_instructors ?>" data-purecounter-delay="200">0</h2>
						</div>
						<span class="mb-0 h6 fw-light">Total Instructor</span>
					</div>
					<!-- Icon -->
					<div class="icon-lg rounded-circle bg-purple text-white mb-0">
						<i class="fas fa-chalkboard-teacher fa-fw"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Counter boxes END -->

	<!-- Charts row 1 START -->
	<div class="row g-4 mb-4">
		<div class="col-lg-6">
			<div class="card card-body p-4 h-100">
				<h6 class="mb-3">Enrollment Trend <span class="text-body-secondary fw-normal">(last 6 months)</span></h6>
				<div style="height: 260px;">
					<canvas id="enrollmentTrendChart"></canvas>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="card card-body p-4 h-100">
				<h6 class="mb-3">Revenue Trend <span class="text-body-secondary fw-normal">(last 6 months)</span></h6>
				<div style="height: 260px;">
					<canvas id="revenueTrendChart"></canvas>
				</div>
			</div>
		</div>
	</div>
	<!-- Charts row 1 END -->

	<!-- Charts row 2 START -->
	<div class="row g-4 mb-4">
		<div class="col-lg-7">
			<div class="card card-body p-4 h-100">
				<h6 class="mb-3">Most Popular Courses <span class="text-body-secondary fw-normal">(by enrollments)</span></h6>
				<div style="height: 300px;">
					<canvas id="coursePopularityChart"></canvas>
				</div>
			</div>
		</div>
		<div class="col-lg-5">
			<div class="card card-body p-4 h-100">
				<h6 class="mb-3">Enrollment Status</h6>
				<div style="height: 300px;">
					<canvas id="enrollmentStatusChart"></canvas>
				</div>
			</div>
		</div>
	</div>
	<!-- Charts row 2 END -->

	<!-- Charts row 3 START -->
	<div class="row g-4 mb-4">
		<div class="col-lg-8">
			<div class="card card-body p-4 h-100">
				<h6 class="mb-3">Course Performance <span class="text-body-secondary fw-normal">(top courses, normalized)</span></h6>
				<div style="height: 320px;">
					<canvas id="coursePerformanceRadar"></canvas>
				</div>
			</div>
		</div>
	</div>
	<!-- Charts row 3 END -->

</div>
<!-- Page main content END -->
</div>
<!-- Page content END -->

<script>
window.dashboardChartData = <?= json_encode($chart_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<script src="../assets/vendor/chartjs/chart.umd.min.js"></script>
<script src="../assets/js/dashboard-charts.js"></script>



<?php
$content = ob_get_clean();
include('../layouts/admin.php');
?>