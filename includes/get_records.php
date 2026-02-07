<?php

/*--------------------------------------------------
  Fetch all records from a table
--------------------------------------------------*/
function get_records($conn, $table_name)
{
  // SQL query to fetch all records ordered by latest ID
  $sql = "SELECT * FROM $table_name ORDER BY id DESC";

  // Execute query
  if ($result = $conn->query($sql)) {
    $results = [];

    // Fetch each row as an associative array
    while ($row = $result->fetch_assoc()) {
      $results[] = $row;
    }

    // Free result memory
    $result->close();

    // Return all records
    return $results;
  } else {
    // Store error message in session if query fails
    $_SESSION['error_message'] = "Error: " . $conn->error;
    return [];
  }
}


/*--------------------------------------------------
  Fetch records with custom WHERE conditions
--------------------------------------------------*/
function get_records_by_conditions($conn, $table_name, $conditions)
{
  // SQL query with dynamic conditions
  $sql = "SELECT * FROM $table_name WHERE $conditions ORDER BY id DESC";

  if ($result = $conn->query($sql)) {
    $results = [];

    // Fetch all matching rows
    while ($row = $result->fetch_assoc()) {
      $results[] = $row;
    }

    $result->close();
    return $results;
  } else {
    $_SESSION['error_message'] = "Error: " . $conn->error;
    return [];
  }
}


/*--------------------------------------------------
  Fetch records with conditions + pagination
--------------------------------------------------*/
function get_records_by_conditions_with_pagination(
  $conn,
  $table_name,
  $conditions,
  $limit,
  $offset
) {
  // SQL query with LIMIT and OFFSET for pagination
  $sql = "
    SELECT *
    FROM $table_name
    WHERE $conditions
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
  ";

  $results = [];

  // Execute main data query
  if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
      $results[] = $row;
    }
    $result->close();
  } else {
    $_SESSION['error_message'] = "Error: " . $conn->error;
  }

  /*----------------------------------
    Get total record count
  ----------------------------------*/
  $count_sql = "
    SELECT COUNT(*) AS total
    FROM $table_name
    WHERE $conditions
  ";

  $total_records = 0;

  if ($count_result = $conn->query($count_sql)) {
    $total_row = $count_result->fetch_assoc();
    $total_records = $total_row['total'];
    $count_result->close();
  }

  // Return paginated data + total record count
  return [
    'data'  => $results,
    'total' => $total_records
  ];
}
