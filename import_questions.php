<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: Administrator access required.");
}

$message = '';

if (isset($_POST['import_csv'])) {
    $file = $_FILES['csv_file']['tmp_name'] ?? '';

    if (!empty($file)) {
        // Build standardized title: [Vendor] Exam Version
        if (!empty($_POST['vendor']) && !empty($_POST['exam_name'])) {
            $vendor = trim($_POST['vendor']);
            $exam_name = trim($_POST['exam_name']);
            $version = trim($_POST['version_num']);
            
            // Format: [EC-Council] CEH v13
            $final_title = sprintf("[%s] %s %s", $vendor, $exam_name, $version);
            $time_limit = intval($_POST['time_limit_minutes']) ?: 240;
            
            $create_stmt = $conn->prepare("INSERT INTO exams (title, time_limit_minutes) VALUES (?, ?)");
            $create_stmt->bind_param("si", $final_title, $time_limit);
            $create_stmt->execute();
            $exam_id = $create_stmt->insert_id;
        } else {
            $exam_id = intval($_POST['exam_id']);
            $exam_query = $conn->query("SELECT title FROM exams WHERE id = $exam_id");
            $final_title = $exam_query->fetch_assoc()['title'];
        }

        // Bulk insert CSV rows
        $handle = fopen($file, "r");
        fgetcsv($handle); // Skip Header

        $conn->begin_transaction();
        $imported_count = 0;

        $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");

        while (($data = fgetcsv($handle, 4096, ",")) !== FALSE) {
            if (count($data) >= 6) {
                $q_text  = trim($data[0]);
                $opt_a   = trim($data[1]);
                $opt_b   = trim($data[2]);
                $opt_c   = trim($data[3]);
                $opt_d   = trim($data[4]);
                $correct = strtoupper(trim($data[5])) ?: 'A';

                $stmt->bind_param("issssss", $exam_id, $q_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct);
                if ($stmt->execute()) {
                    $imported_count++;
                }
            }
        }

        $conn->commit();
        fclose($handle);
        $message = "Successfully imported $imported_count questions into '$final_title'!";
    } else {
        $message = "Please upload a valid CSV file.";
    }
}

$exams = $conn->query("SELECT * FROM exams ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Import Exam Questions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 600px; }
        .form-row { display: flex; gap: 10px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; flex: 1; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; }
        .form-group select, .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; }
        .alert { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; width: 580px; }
        .divider { border-top: 1px solid #ccc; margin: 15px 0; text-align: center; color: #666; font-size: 12px; }
        .preview-box { background: #e9ecef; padding: 8px; border-radius: 4px; font-weight: bold; font-family: monospace; }
    </style>
</head>
<body>

<h2>Bulk Exam Question Importer</h2>

<?php if (!empty($message)): ?>
    <div class="alert"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST" enctype="multipart/form-data">
        
        <h3>Option 1: Create New Named Exam</h3>
        <div class="form-row">
            <div class="form-group">
                <label>[Vendor]</label>
                <input type="text" name="vendor" placeholder="e.g. EC-Council">
            </div>
            <div class="form-group">
                <label>(Exam Name)</label>
                <input type="text" name="exam_name" placeholder="e.g. CEH">
            </div>
            <div class="form-group">
                <label>(Version)</label>
                <input type="text" name="version_num" placeholder="e.g. v13">
            </div>
        </div>

        <div class="form-group">
            <label>Time Limit (Minutes)</label>
            <input type="number" name="time_limit_minutes" value="240">
        </div>

        <div class="divider">--- OR ---</div>

        <h3>Option 2: Append to Existing Exam</h3>
        <div class="form-group">
            <select name="exam_id">
                <option value="">-- Select Existing Exam --</option>
                <?php while ($e = $exams->fetch_assoc()): ?>
                    <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['title']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="divider"></div>

        <div class="form-group">
            <label>Upload Converted CSV File</label>
            <input type="file" name="csv_file" accept=".csv" required>
        </div>

        <button type="submit" name="import_csv" class="btn">Process and Import All Questions</button>
    </form>
</div>

</body>
</html>