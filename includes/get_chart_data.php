<?php

/*----------------------------------
  Get monthly enrollment counts for the last N months (zero-filled)
----------------------------------*/
function get_enrollment_trend($conn, $months = 6)
{
  $labels = [];
  $counts = [];
  $by_month = [];

  $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
          FROM enrollments
          WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
          GROUP BY ym";
  $result = mysqli_query($conn, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $by_month[$row['ym']] = (int) $row['cnt'];
  }

  for ($i = $months - 1; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $labels[] = date('M', strtotime($ym . '-01'));
    $counts[] = $by_month[$ym] ?? 0;
  }

  return ['labels' => $labels, 'data' => $counts];
}

/*----------------------------------
  Get monthly revenue from successful enrollments for the last N months (zero-filled)
----------------------------------*/
function get_revenue_trend($conn, $months = 6)
{
  $labels = [];
  $totals = [];
  $by_month = [];

  $sql = "SELECT DATE_FORMAT(e.created_at, '%Y-%m') AS ym, SUM(c.price) AS revenue
          FROM enrollments e
          JOIN courses c ON c.id = e.course_id
          WHERE e.status = 'success' AND e.created_at >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
          GROUP BY ym";
  $result = mysqli_query($conn, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $by_month[$row['ym']] = (float) $row['revenue'];
  }

  for ($i = $months - 1; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $labels[] = date('M', strtotime($ym . '-01'));
    $totals[] = $by_month[$ym] ?? 0;
  }

  return ['labels' => $labels, 'data' => $totals];
}

/*----------------------------------
  Get enrollment count per course, highest first
----------------------------------*/
function get_course_popularity($conn, $limit = 10)
{
  $labels = [];
  $counts = [];

  $stmt = mysqli_prepare($conn, "SELECT c.title, COUNT(e.id) AS cnt
          FROM courses c
          LEFT JOIN enrollments e ON e.course_id = c.id
          GROUP BY c.id
          ORDER BY cnt DESC, c.title ASC
          LIMIT ?");
  mysqli_stmt_bind_param($stmt, 'i', $limit);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['title'];
    $counts[] = (int) $row['cnt'];
  }
  mysqli_stmt_close($stmt);

  return ['labels' => $labels, 'data' => $counts];
}

/*----------------------------------
  Get enrollment status breakdown (success / pending / cancel)
----------------------------------*/
function get_enrollment_status_breakdown($conn)
{
  $counts = ['success' => 0, 'pending' => 0, 'cancel' => 0];

  $sql = "SELECT status, COUNT(*) AS cnt FROM enrollments GROUP BY status";
  $result = mysqli_query($conn, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    if (isset($counts[$row['status']])) {
      $counts[$row['status']] = (int) $row['cnt'];
    }
  }

  return $counts;
}

/*----------------------------------
  Get top courses compared across enrollments, revenue, and lecture count,
  each normalized to 0-100 of the group's max so the three differently
  scaled metrics can share one radar chart.
----------------------------------*/
function get_course_performance_radar($conn, $limit = 4)
{
  $sql = "SELECT c.id, c.title, c.total_lectures,
                 COUNT(e.id) AS enrollments,
                 SUM(CASE WHEN e.status = 'success' THEN c.price ELSE 0 END) AS revenue
          FROM courses c
          LEFT JOIN enrollments e ON e.course_id = c.id
          GROUP BY c.id
          ORDER BY enrollments DESC, revenue DESC
          LIMIT $limit";
  $result = mysqli_query($conn, $sql);

  $courses = [];
  $max_enrollments = 0;
  $max_revenue = 0;
  $max_lectures = 0;

  while ($row = mysqli_fetch_assoc($result)) {
    $enrollments = (int) $row['enrollments'];
    $revenue = (float) $row['revenue'];
    $lectures = (int) $row['total_lectures'];

    $courses[] = ['title' => $row['title'], 'enrollments' => $enrollments, 'revenue' => $revenue, 'lectures' => $lectures];

    $max_enrollments = max($max_enrollments, $enrollments);
    $max_revenue = max($max_revenue, $revenue);
    $max_lectures = max($max_lectures, $lectures);
  }

  $datasets = [];
  foreach ($courses as $course) {
    $datasets[] = [
      'title' => $course['title'],
      'values' => [
        $max_enrollments ? round($course['enrollments'] / $max_enrollments * 100) : 0,
        $max_revenue ? round($course['revenue'] / $max_revenue * 100) : 0,
        $max_lectures ? round($course['lectures'] / $max_lectures * 100) : 0,
      ],
    ];
  }

  return ['metrics' => ['Enrollments', 'Revenue', 'Lectures'], 'datasets' => $datasets];
}
