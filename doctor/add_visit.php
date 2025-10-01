<?php
include '../includes/auth.php';
include '../includes/db.php';

// ensure doctor role
if ($_SESSION['role'] != 'doctor') {
    die('restricted access');
}

$doctor_id = $_SESSION['user_id'];
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$success = '';
$error = '';

// on submit, insert into HOSPITAL_VISITS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $visit_date = trim($_POST['visit_date'] ?? '');
    $visit_reason = trim($_POST['visit_reason'] ?? '');
    $diagnosis = trim($_POST['diagnosis'] ?? '');

    if (empty($patient_id) || empty($visit_date) || empty($visit_reason)) {
        $error = 'please provide patient, date, and reason';
    } else {
        $stmt = $conn->prepare("INSERT INTO HOSPITAL_VISITS (patient_id, doctor_id, visit_date, visit_reason, diagnosis) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $patient_id, $doctor_id, $visit_date, $visit_reason, $diagnosis);
        if ($stmt->execute()) {
            $success = 'visit added successfully';
        } else {
            $error = 'failed to add visit';
        }
    }
}

// data that will be used to display patient info on doctor dashboard
$patient = null;
if ($patient_id) {
    $p = $conn->prepare("SELECT patient_id, first_name, last_name, date_of_birth FROM PATIENT_INFO WHERE patient_id = ?");
    $p->bind_param("i", $patient_id);
    $p->execute();
    $patient = $p->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Visit</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="records-container">
        <h1>Add Visit</h1>
        <?php if (!empty($success)): ?>
            <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($patient): ?>
            <p><strong>Patient:</strong> <?php echo htmlspecialchars($patient['first_name'].' '.$patient['last_name']); ?> (ID: <?php echo (int)$patient['patient_id']; ?>)</p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>">
            <div class="form-row">
                <label>Date:</label>
                <input type="date" name="visit_date" required>
            </div>
            <div class="form-row">
                <label>Reason:</label>
                <input type="text" name="visit_reason" required>
            </div>
            <div class="form-row">
                <label>Diagnosis:</label>
                <input type="text" name="diagnosis">
            </div>
            <button type="submit">Save Visit</button>
            <a href="view_patient.php" style="margin-left:10px;">Back</a>
        </form>
    </div>
</body>
</html>
