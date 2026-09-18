<?php
// C:\xampp\htdocs\BOCX-Test-Exam-Platform\import_questions.php
$conn = new mysqli("localhost", "root", "", "exam_db");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $vendor = trim($_POST['vendor']);
    $exam_name = trim($_POST['exam_name']);
    $version = trim($_POST['version']);
    $time_limit = intval($_POST['time_limit']);
    
    // Construct standardized full exam title
    $full_exam_title = "[{$vendor}] {$exam_name} {$version}";

    // 1. Check if Exam Already Exists or Insert New Exam
    $stmt = $conn->prepare("SELECT id FROM exams WHERE title = ?");
    $stmt->bind_param("s", $full_exam_title);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $exam_id = $row['id'];
    } else {
        $insert_exam = $conn->prepare("INSERT INTO exams (title, time_limit_minutes) VALUES (?, ?)");
        $insert_exam->bind_param("si", $full_exam_title, $time_limit);
        if ($insert_exam->execute()) {
            $exam_id = $insert_exam->insert_id;
        } else {
            die("Error creating exam: " . $conn->error);
        }
    }

    // 2. Process CSV File and Insert Questions
    $csv_file = $_FILES["csv_file"]["tmp_name"];
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle, 1000, ",");
        
        $stmt_q = $conn->prepare("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $imported_count = 0;
        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if (count($data) >= 6 && !empty($data[0])) {
                $q_text  = trim($data[0]);
                $opt_a   = trim($data[1]);
                $opt_b   = trim($data[2]);
                $opt_c   = trim($data[3]);
                $opt_d   = trim($data[4]);
                $correct = strtoupper(trim($data[5]));
                
                $stmt_q->bind_param("issssss", $exam_id, $q_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct);
                if ($stmt_q->execute()) {
                    $imported_count++;
                }
            }
        }
        fclose($handle);
        $message = "Successfully imported {$imported_count} questions into '{$full_exam_title}'!";
    } else {
        $message = "Failed to open CSV file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Import Questions | BOCX Test Exam Platform</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 40px; }
        .card { background: white; padding: 25px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], input[type="file"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; }
        button:hover { background: #218838; }
        .alert { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Question Bank Bulk Importer</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Vendor Name (e.g., EC-Council):</label>
            <input type="text" name="vendor" value="EC-Council" required>
        </div>
        <div class="form-group">
            <label>Exam Name (e.g., CEH):</label>
            <input type="text" name="exam_name" value="CEH" required>
        </div>
        <div class="form-group">
            <label>Version (e.g., v13):</label>
            <input type="text" name="version" value="v13" required>
        </div>
        <div class="form-group">
            <label>Time Limit (Minutes):</label>
            <input type="number" name="time_limit" value="240" required>
        </div>
        <div class="form-group">
            <label>Select CSV File:</label>
            <input type="file" name="csv_file" accept=".csv" required>
        </div>
        <button type="submit">Process and Import All Questions</button>
    </form>
</div>

</body>
</html>