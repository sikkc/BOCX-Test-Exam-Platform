<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) ||$_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 1; 
$user_id =$_SESSION['user_id'];

$attempt_query =$conn->prepare("SELECT id FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status = 'in_progress'");
$attempt_query->bind_param("ii", $user_id, $exam_id);$attempt_query->execute();
$attempt_result =$attempt_query->get_result();

if ($attempt_result->num_rows === 0) {
    $start_stmt =$conn->prepare("INSERT INTO exam_attempts (user_id, exam_id) VALUES (?, ?)");
    $start_stmt->bind_param("ii", $user_id, $exam_id);$start_stmt->execute();
    $attempt_id =$start_stmt->insert_id;
} else {
    $attempt =$attempt_result->fetch_assoc();
    $attempt_id =$attempt['id'];
}

$questions =$conn->query("SELECT * FROM questions WHERE exam_id = $exam_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Active Examination</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f8f9fa; }
        #timer { font-size: 20px; font-weight: bold; color: #dc3545; position: fixed; top: 15px; right: 25px; background: white; padding: 10px 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .question-card { background: white; border: 1px solid #dee2e6; padding: 20px; margin-bottom: 20px; border-radius: 6px; }
        .btn-submit { padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>

<div id="timer">Time Left: <span id="time-display">15:00</span></div>

<h2>Live Exam Session</h2>

<form id="examForm" action="process_exam.php" method="POST">
    <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">
    <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">

    <?php $q_num = 1; while ($q =$questions->fetch_assoc()): ?>
        <div class="question-card">
            <p><strong>Q<?php echo $q_num++; ?>: <?php echo htmlspecialchars($q['question_text']); ?></strong></p>
            <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $opt; ?>" required>
                    <?php echo htmlspecialchars($q['option_' . strtolower($opt)]); ?>
                </label>
            <?php endforeach; ?>
        </div>
    <?php endwhile; ?>

    <button type="submit" class="btn-submit">Submit Exam</button>
</form>

<script>
let duration = 900; 
const display = document.getElementById('time-display');

const timerInterval = setInterval(() => {
    let minutes = Math.floor(duration / 60);
    let seconds = duration % 60;
    display.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    
    if (--duration < 0) {
        clearInterval(timerInterval);
        alert("Time expired. Submitting your answers automatically.");
        document.getElementById('examForm').submit();
    }
}, 1000);

let tabSwitches = 0;
document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
        tabSwitches++;
        alert(`Warning ${tabSwitches}/3: Tab switching is strictly prohibited!`);
        if (tabSwitches >= 3) {
            document.getElementById('examForm').submit();
        }
    }
});

document.addEventListener("contextmenu", e => e.preventDefault());
document.addEventListener("copy", e => e.preventDefault());
</script>

</body>
</html>