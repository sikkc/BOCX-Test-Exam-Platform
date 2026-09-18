<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$exams = $conn->query("SELECT * FROM exams");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f6f9; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn-disabled { background: #6c757d; pointer-events: none; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <a href="login.php" style="color: red;">Logout</a>
    </div>

    <h3>Available Examinations</h3>

    <?php while ($exam = $exams->fetch_assoc()): ?>
        <?php
            $check_attempt = $conn->prepare("SELECT status, score FROM exam_attempts WHERE user_id = ? AND exam_id = ?");
            $check_attempt->bind_param("ii", $user_id, $exam['id']);
            $check_attempt->execute();
            $attempt_res = $check_attempt->get_result()->fetch_assoc();
        ?>
        <div class="card">
            <h4><?php echo htmlspecialchars($exam['title']); ?></h4>
            <p>Time Limit: <?php echo $exam['time_limit_minutes']; ?> Minutes</p>
            
            <?php if ($attempt_res && $attempt_res['status'] === 'completed'): ?>
                <p>Status: <strong style="color: green;">Completed</strong> (Score: <?php echo $attempt_res['score']; ?>)</p>
                <a class="btn btn-disabled">Finished</a>
            <?php else: ?>
                <a href="take_exam.php?exam_id=<?php echo $exam['id']; ?>" class="btn">Start Exam</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

</body>
</html>