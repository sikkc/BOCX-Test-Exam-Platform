<?php
// C:\xampp\htdocs\BOCX-Test-Exam-Platform\take_exam.php
session_start();
$conn = new mysqli("localhost", "root", "", "exam_db");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$exam_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch Exam Details
$exam_query = $conn->prepare("SELECT * FROM exams WHERE id = ?");
$exam_query->bind_param("i", $exam_id);
$exam_query->execute();
$exam = $exam_query->get_result()->fetch_assoc();

if (!$exam) {
    die("Exam not found.");
}

// Fetch All Questions for this Exam
$questions_query = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY id ASC");
$questions_query->bind_param("i", $exam_id);
$questions_query->execute();
$questions = $questions_query->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($exam['title']); ?> | Live Session</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: sticky; top: 10px; z-index: 100; margin-bottom: 20px; }
        .timer-badge { background: #dc3545; color: white; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 16px; }
        .question-card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .question-title { font-weight: bold; font-size: 16px; margin-bottom: 15px; color: #222; line-height: 1.5; }
        .option-label { display: flex; align-items: flex-start; padding: 10px 12px; margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; transition: background 0.2s; white-space: normal; word-break: break-word; }
        .option-label:hover { background: #f8fafc; }
        .option-label input[type="radio"] { margin-top: 3px; margin-right: 12px; flex-shrink: 0; }
        .submit-btn { background: #28a745; color: white; border: none; padding: 15px 30px; font-size: 18px; border-radius: 6px; cursor: pointer; width: 100%; margin-top: 20px; font-weight: bold; }
        .submit-btn:hover { background: #218838; }
    </style>
</head>
<body>

<div style="max-width: 1000px; margin: auto;">
    <div class="header-bar">
        <h2 style="margin: 0;"><?php echo htmlspecialchars($exam['title']); ?></h2>
        <div class="timer-badge">Time Remaining: <span id="timer"><?php echo $exam['time_limit_minutes']; ?>:00</span></div>
    </div>

    <form action="submit_exam.php" method="POST">
        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-card">
                <!-- Displays the full untruncated question text -->
                <div class="question-title">
                    Q<?php echo ($index + 1); ?>: <?php echo htmlspecialchars($q['question_text']); ?>
                </div>

                <!-- Displays full untruncated options A, B, C, D -->
                <label class="option-label">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="A" required>
                    <span><?php echo htmlspecialchars($q['option_a']); ?></span>
                </label>

                <label class="option-label">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="B">
                    <span><?php echo htmlspecialchars($q['option_b']); ?></span>
                </label>

                <label class="option-label">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="C">
                    <span><?php echo htmlspecialchars($q['option_c']); ?></span>
                </label>

                <label class="option-label">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="D">
                    <span><?php echo htmlspecialchars($q['option_d']); ?></span>
                </label>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="submit-btn">Submit Exam</button>
    </form>
</div>

</body>
</html>