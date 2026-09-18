<?php
session_start();
include 'db.php';

// Ensure user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: Administrator account required.");
}

$message = '';

// Handle New Exam Creation
if (isset($_POST['create_exam'])) {
    $title = trim($_POST['title']);
    $time_limit = intval($_POST['time_limit_minutes']);

    if (!empty($title) && $time_limit > 0) {
        $stmt = $conn->prepare("INSERT INTO exams (title, time_limit_minutes) VALUES (?, ?)");
        $stmt->bind_param("si", $title, $time_limit);
        if ($stmt->execute()) {
            $message = "New exam created successfully!";
        }
    }
}

// Handle New Question Creation
if (isset($_POST['add_question'])) {
    $exam_id = intval($_POST['exam_id']);
    $question_text = trim($_POST['question_text']);
    $opt_a = trim($_POST['option_a']);
    $opt_b = trim($_POST['option_b']);
    $opt_c = trim($_POST['option_c']);
    $opt_d = trim($_POST['option_d']);
    $correct = $_POST['correct_option'];

    if (!empty($question_text) && !empty($opt_a) && !empty($opt_b) && !empty($opt_c) && !empty($opt_d)) {
        $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $exam_id, $question_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct);
        if ($stmt->execute()) {
            $message = "Question added successfully!";
        }
    }
}

$exams = $conn->query("SELECT * FROM exams");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Exams & Questions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 500px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .alert { color: green; font-weight: bold; margin-bottom: 15px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #007bff; font-weight: bold; }
    </style>
</head>
<body>

<div class="nav">
    <a href="admin_results.php">View Exam Results</a>
    <a href="login.php" style="color: red;">Logout</a>
</div>

<h2>Admin Management Console</h2>

<?php if (!empty($message)): ?>
    <div class="alert"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Form 1: Create a New Exam -->
<div class="card">
    <h3>1. Create New Exam</h3>
    <form method="POST">
        <div class="form-group">
            <label>Exam Title</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Time Limit (Minutes)</label>
            <input type="number" name="time_limit_minutes" value="30" required>
        </div>
        <button type="submit" name="create_exam" class="btn">Save Exam</button>
    </form>
</div>

<!-- Form 2: Add Questions to Existing Exams -->
<div class="card">
    <h3>2. Add Question to Exam</h3>
    <form method="POST">
        <div class="form-group">
            <label>Select Exam</label>
            <select name="exam_id" required>
                <?php 
                $exams_list = $conn->query("SELECT * FROM exams");
                while ($e = $exams_list->fetch_assoc()): 
                ?>
                    <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['title']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Question Prompt</label>
            <textarea name="question_text" rows="3" required></textarea>
        </div>
        <div class="form-group"><label>Option A</label><input type="text" name="option_a" required></div>
        <div class="form-group"><label>Option B</label><input type="text" name="option_b" required></div>
        <div class="form-group"><label>Option C</label><input type="text" name="option_c" required></div>
        <div class="form-group"><label>Option D</label><input type="text" name="option_d" required></div>
        <div class="form-group">
            <label>Correct Answer Key</label>
            <select name="correct_option">
                <option value="A">Option A</option>
                <option value="B">Option B</option>
                <option value="C">Option C</option>
                <option value="D">Option D</option>
            </select>
        </div>
        <button type="submit" name="add_question" class="btn" style="background: #28a745;">Save Question</button>
    </form>
</div>

</body>
</html>