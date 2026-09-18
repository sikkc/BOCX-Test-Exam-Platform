<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempt_id = intval($_POST['attempt_id']);
    $exam_id = intval($_POST['exam_id']);
    $answers = $_POST['answers'] ?? [];

    $score = 0;

    $stmt = $conn->prepare("SELECT id, correct_option FROM questions WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($q = $result->fetch_assoc()) {
        $q_id = $q['id'];
        $selected = $answers[$q_id] ?? null;

        if ($selected) {
            $ans_stmt = $conn->prepare("INSERT INTO student_answers (attempt_id, question_id, selected_option) VALUES (?, ?, ?)");
            $ans_stmt->bind_param("iis", $attempt_id, $q_id, $selected);
            $ans_stmt->execute();

            if ($selected === $q['correct_option']) {
                $score++;
            }
        }
    }

    $update_stmt = $conn->prepare("UPDATE exam_attempts SET end_time = NOW(), score = ?, status = 'completed' WHERE id = ?");
    $update_stmt->bind_param("ii", $score, $attempt_id);
    $update_stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Completed</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; text-align: center; padding-top: 50px; }
        .card { background: white; display: inline-block; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Exam Submitted Successfully!</h2>
    <p style="font-size: 18px;">Your Total Score: <strong><?php echo $score; ?></strong></p>
    <a href="student_dashboard.php" class="btn">Return to Dashboard</a>
</div>

</body>
</html>