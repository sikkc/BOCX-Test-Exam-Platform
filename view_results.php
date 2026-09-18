<?php
// C:\xampp\htdocs\BOCX-Test-Exam-Platform\view_results.php
session_start();
$conn = new mysqli("localhost", "root", "", "exam_db");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$result_id = isset($_GET['result_id']) ? intval($_GET['result_id']) : 0;

// Fetch Exam Result Record
$stmt = $conn->prepare("
    SELECT r.*, e.title AS exam_title 
    FROM results r 
    JOIN exams e ON r.exam_id = e.id 
    WHERE r.id = ?
");
$stmt->bind_param("i", $result_id);
$stmt->execute();
$result_data = $stmt->get_result()->fetch_assoc();

if (!$result_data) {
    die("Result record not found.");
}

$is_pass = ($result_data['status'] === 'PASS');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Results | BOCX Test Exam Platform</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; margin: 40px; }
        .container { max-width: 800px; margin: auto; background: white; border-radius: 8px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .score-card { text-align: center; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .pass { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .fail { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .badge { font-size: 28px; font-weight: bold; margin-bottom: 10px; }
        .score-details { font-size: 18px; margin: 8px 0; }
        .btn-dashboard { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 15px; }
        .btn-dashboard:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h2>Performance Scorecard</h2>
    <p><strong>Exam:</strong> <?php echo htmlspecialchars($result_data['exam_title']); ?></p>

    <div class="score-card <?php echo $is_pass ? 'pass' : 'fail'; ?>">
        <div class="badge"><?php echo $result_data['status']; ?></div>
        <div class="score-details">
            Score: <strong><?php echo $result_data['score']; ?></strong> / <?php echo $result_data['total_questions']; ?>
        </div>
        <div class="score-details">
            Percentage: <strong><?php echo $result_data['percentage']; ?>%</strong>
        </div>
    </div>

    <div style="text-align: center;">
        <a href="student_dashboard.php" class="btn-dashboard">Return to Dashboard</a>
    </div>
</div>

</body>
</html>