<?php

/*----------------------------------
  Get total number of students
----------------------------------*/
function get_total_students($conn)
{
  $sql = "SELECT COUNT(*) AS count FROM users WHERE role = 'student'";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  return $row['count'];
}

/*----------------------------------
  Get total number of courses
----------------------------------*/
function get_total_courses($conn)
{
  $sql = "SELECT COUNT(*) AS count FROM courses";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  return $row['count'];
}

/*----------------------------------
  Get total instructors (including admins)
----------------------------------*/
function get_total_instructors($conn)
{
  $sql = "SELECT COUNT(*) AS count FROM users WHERE role = 'instructor' OR role = 'admin'";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  return $row['count'];
}

/*----------------------------------
  Get total earnings from successful enrollments
----------------------------------*/
function get_total_earnings($conn)
{
  $sql = "SELECT SUM(c.price) AS total_earnings FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.status = 'success'";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  return $row['total_earnings'] ?? 0;
}

/*----------------------------------
  Get total courses enrolled by a user
----------------------------------*/
function total_course_enrolled($conn, $user_id)
{
  $sql = "SELECT COUNT(*) AS count FROM enrollments WHERE user_id = $user_id AND status = 'success'";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  return $row['count'] ?? 0;
}

/*----------------------------------
  Get total payment made by a user
----------------------------------*/
function total_payment($conn, $user_id)
{
  $sql = "SELECT SUM(c.price) AS total_payment FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = $user_id AND e.status = 'success'";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  return $row['total_payment'] ?? 0;
}

/*----------------------------------
  Get total successful enrollments for a course
----------------------------------*/
function total_enrolled($conn, $course_id)
{
  $sql = "SELECT COUNT(*) AS count FROM enrollments WHERE course_id = $course_id AND status = 'success'";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  return $row['count'] ?? 0;
}
