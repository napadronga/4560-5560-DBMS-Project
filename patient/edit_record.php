<?php
// include auth and db
include '../includes/auth.php';
include '../includes/db.php';

// get patient id from session
$patient_id = $_SESSION['user_id'];

// fetch current patient info and history (email and medications)
$patient_stmt = $conn->prepare("SELECT contact_email FROM PATIENT_INFO WHERE patient_id = ?");
$patient_stmt->bind_param("i", $patient_id);
$patient_stmt->execute();
$patient_result = $patient_stmt->get_result();
$patient = $patient_result->fetch_assoc();

$med_stmt = $conn->prepare("SELECT med_id, medication_name, dosage, start_date FROM PATIENT_MEDICATIONS WHERE patient_id = ?");
$med_stmt->bind_param("i", $patient_id);
$med_stmt->execute();
$med_result = $med_stmt->get_result();
$medications = $med_result->fetch_all(MYSQLI_ASSOC);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // if user clicked remove
    if (isset($_POST['remove_med_id'])) {
        $remove_id = (int)$_POST['remove_med_id'];
        $del_med = $conn->prepare("DELETE FROM PATIENT_MEDICATIONS WHERE med_id = ? AND patient_id = ?");
        $del_med->bind_param("ii", $remove_id, $patient_id);
        if ($del_med->execute()) {
            $success = "Medication removed successfully.";
            // also update patient's last_time_updated
            $ts = $conn->prepare("UPDATE PREEXISTING_MEDICAL_HISTORY SET last_time_updated = NOW() WHERE patient_id = ?");
            $ts->bind_param("i", $patient_id);
            $ts->execute();
        } else {
            $error = "Failed to remove medication.";
        }
    } else {
        // update email and medication logic
        $new_email = trim($_POST['contact_email'] ?? '');
        $new_medication = trim($_POST['medication_name'] ?? '');
        $new_dosage = trim($_POST['dosage'] ?? '');

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'invalid email address';
        } else {
            // update patient email
            $upd_patient = $conn->prepare("UPDATE PATIENT_INFO SET contact_email = ? WHERE patient_id = ?");
            $upd_patient->bind_param("si", $new_email, $patient_id);
            $ok1 = $upd_patient->execute();

            // insert new medication if added
            $ok2 = true;
            if (!empty($new_medication)) {
                $ins_med = $conn->prepare("INSERT INTO PATIENT_MEDICATIONS (patient_id, medication_name, dosage, start_date) VALUES (?, ?, ?, NOW())");
                $ins_med->bind_param("iss", $patient_id, $new_medication, $new_dosage);
                $ok2 = $ins_med->execute();
            }

            if ($ok1 && $ok2) {
                $success = 'information updated successfully';
                // ensure a history row exists and update timestamp
                $exists = $conn->prepare("SELECT history_id FROM PREEXISTING_MEDICAL_HISTORY WHERE patient_id = ?");
                $exists->bind_param("i", $patient_id);
                $exists->execute();
                $exists_res = $exists->get_result();
                if ($exists_res->num_rows > 0) {
                    $ts = $conn->prepare("UPDATE PREEXISTING_MEDICAL_HISTORY SET last_time_updated = NOW() WHERE patient_id = ?");
                    $ts->bind_param("i", $patient_id);
                    $ts->execute();
                } else {
                    $ins_hist = $conn->prepare("INSERT INTO PREEXISTING_MEDICAL_HISTORY (patient_id, last_time_updated) VALUES (?, NOW())");
                    $ins_hist->bind_param("i", $patient_id);
                    $ins_hist->execute();
                }
            } else {
                $error = 'failed to update information';
            }
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Information - HealthCare Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Edit Your Information</h1>
        </div>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Contact Information Card -->
            <div class="dashboard-card">
                <h3>Contact Information</h3>
                <form method="POST" id="edit-form">
                    <div class="form-row">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="contact_email" value="<?php echo htmlspecialchars($patient['contact_email'] ?? ''); ?>" required>
                    </div>
                    <button type="submit">Update Contact Info</button>
                </form>
            </div>

            <!-- Current Medications Card -->
            <div class="dashboard-card">
                <h3>Current Medications</h3>
                <?php if (!empty($medications)): ?>
                    <div class="table-cards">
                        <?php foreach ($medications as $med): ?>
                            <div class="table-card">
                                <div class="table-card-header"><?php echo htmlspecialchars($med['medication_name']); ?></div>
                                <div class="table-card-row">
                                    <div class="table-card-label">Dosage:</div>
                                    <div class="table-card-value"><?php echo htmlspecialchars($med['dosage']); ?></div>
                                </div>
                                <div class="table-card-row">
                                    <div class="table-card-label">Started:</div>
                                    <div class="table-card-value"><?php echo htmlspecialchars($med['start_date']); ?></div>
                                </div>
                                <form method="POST" style="margin-top: 1rem;">
                                    <button type="submit" name="remove_med_id" value="<?php echo (int)$med['med_id']; ?>" 
                                            style="background: var(--error-color); color: white; padding: 0.5rem 1rem; font-size: 0.9rem;">
                                        Remove Medication
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No medications recorded</p>
                <?php endif; ?>
            </div>

            <!-- Add New Medication Card -->
            <div class="dashboard-card">
                <h3>Add New Medication</h3>
                <form method="POST">
                    <div class="form-row">
                        <label for="medication_name">Medication Name:</label>
                        <input type="text" id="medication_name" name="medication_name" placeholder="Enter medication name">
                    </div>
                    <div class="form-row">
                        <label for="dosage">Dosage:</label>
                        <input type="text" id="dosage" name="dosage" placeholder="e.g., 500mg daily">
                    </div>
                    <button type="submit">Add Medication</button>
                </form>
            </div>

            <!-- Navigation Card -->
            <div class="dashboard-card">
                <h3>Navigation</h3>
                <p>Return to your dashboard or sign out</p>
                <a href="view_records.php" style="display: inline-block; margin: 1rem 0;">
                    <button style="background: var(--secondary-color);">Back to Dashboard</button>
                </a>
                <a href="../logout.php" style="display: inline-block; margin: 1rem 0; color: var(--text-secondary); text-decoration: none;">Sign Out</a>
            </div>
        </div>
    </div>
</body>
</html>
