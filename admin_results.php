<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: Administrator account required.");
}

$query = "SELECT u.username, e.title, a.score, a.start_time, a.end_time, a.status 
          FROM exam_attempts a
          JOIN users u ON a.user_id = u.id
          JOIN exams e ON a.exam_id = e.id
          ORDER BY a.start_time DESC";

$results = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Exam Logs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background: #343a40; color: white; }
    </style>
</head>
<body>

<h2>Conducted Exam Log</h2>

<table>
    <tr>
        <th>Student</th>
        <th>Exam Title</th>
        <th>Status</th>
        <th>Score</th>
        <th>Start Time</th>
        <th>End Time</th>
    </tr>
    <?php while ($row = $results->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['username']); ?></td>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['status']); ?></td>
        <td><strong><?php echo $row['score']; ?></strong></td>
        <td><?php echo $row['start_time']; ?></td>
        <td><?php echo $row['end_time'] ?? 'In Progress'; ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>