<?php
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/activity_logger.php';

// only patients can use this page
if ($_SESSION['role'] !== 'patient') {
    die('restricted access');
}

$patient_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visit_date   = $_POST['visit_date']   ?? '';
    $visit_reason = trim($_POST['visit_reason'] ?? '');
    $diagnosis    = trim($_POST['diagnosis'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    if ($visit_date === '' || $visit_reason === '') {
        $error = "Please enter at least a visit date and reason.";
    } else {
        // adjust column list if your HOSPITAL_VISITS schema is different
        $stmt = $conn->prepare(
            "INSERT INTO HOSPITAL_VISITS (patient_id, visit_date, visit_reason, diagnosis, notes)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issss", $patient_id, $visit_date, $visit_reason, $diagnosis, $notes);

        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;
            if (function_exists('logRecordCreate')) {
                logRecordCreate($conn, $patient_id, 'patient',
                                'HOSPITAL_VISITS', $new_id,
                                'Patient added a self-reported visit');
            }
            $success = "Visit was added successfully.";
        } else {
            $error = "Error saving visit: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Visit – Patient</title>
    <link rel="stylesheet" href="/healthcare/css/styles.css">
</head>

    <body>
    <div class="records-container">
        <h1>Add a New Visit</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form" style="margin-top: 1.5rem;">
            <div class="form-row">
                <label for="visit_date">Visit Date</label>
                <input type="date" id="visit_date" name="visit_date" required>
            </div>

            <div class="form-row">
                <label for="visit_reason">Reason for Visit</label>
                <input type="text" id="visit_reason" name="visit_reason"
                       placeholder="e.g., Follow-up, check-up, headache" required>
            </div>

            <div class="form-row">
                <label for="diagnosis">Diagnosis (optional)</label>
                <input type="text" id="diagnosis" name="diagnosis"
                       placeholder="Diagnosis if known">
            </div>

            <div class="form-row">
                <label for="notes">Extra Notes</label>
                <input type="text" id="notes" name="notes"
                       placeholder="Any symptoms or details you want to remember">
            </div>

            <button type="submit">Save Visit</button>
        </form>

        <a href="view_records.php" style="display:inline-block; margin-top:1rem; text-decoration:none;">
            <button type="button">Back to My Dashboard</button>
        </a>
    </div>
</body>
</html>
