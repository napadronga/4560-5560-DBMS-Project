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

// on 'submit' insert into HOSPITAL_VISITS
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Visit</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Add New Visit</h1>
            <p>Record a patient visit and diagnosis</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Patient Information Card -->
            <?php if ($patient): ?>
            <div class="dashboard-card">
                <h3>Patient Information</h3>
                <div class="table-card-row">
                    <div class="table-card-label">Name:</div>
                    <div class="table-card-value"><?php echo htmlspecialchars($patient['first_name'].' '.$patient['last_name']); ?></div>
                </div>
                <div class="table-card-row">
                    <div class="table-card-label">Patient ID:</div>
                    <div class="table-card-value"><?php echo (int)$patient['patient_id']; ?></div>
                </div>
                <div class="table-card-row">
                    <div class="table-card-label">Date of Birth:</div>
                    <div class="table-card-value"><?php echo htmlspecialchars($patient['date_of_birth']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Visit Form Card -->
            <div class="dashboard-card">
                <h3>Visit Details</h3>
                <form method="POST">
                    <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>">
                    
                    <div class="form-row">
                        <label for="visit_date">Visit Date:</label>
                        <input type="date" id="visit_date" name="visit_date" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="visit_reason">Reason for Visit:</label>
                        <input type="text" id="visit_reason" name="visit_reason" required placeholder="e.g., Annual checkup, symptoms">
                    </div>
                    
                    <div class="form-row">
                        <label for="diagnosis">Diagnosis:</label>
                        <input type="text" id="diagnosis" name="diagnosis" placeholder="Enter diagnosis if available">
                    </div>
                    
                    <button type="submit">Save Visit</button>
                </form>
            </div>

            <!-- Navigation Card -->
            <div class="dashboard-card">
                <h3>Navigation</h3>
                <p>Return to the patient dashboard</p>
                <a href="view_patient.php">
                    <button style="background: var(--secondary-color);">Back to Dashboard</button>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
