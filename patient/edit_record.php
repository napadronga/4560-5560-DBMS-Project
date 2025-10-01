<?php
// include auth and db
include '../includes/auth.php';
include '../includes/db.php';

// get patient id from session
$patient_id = $_SESSION['user_id'];

// fetch current patient info and history (email and medications)
$patient_stmt = $conn->prepare("SELECT email FROM PATIENT_INFO WHERE patient_id = ?");
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
        // update email and add medication logic
        $new_email = trim($_POST['email'] ?? '');
        $new_medication = trim($_POST['medication_name'] ?? '');
        $new_dosage = trim($_POST['dosage'] ?? '');

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'invalid email address';
        } else {
            // update patient email
            $upd_patient = $conn->prepare("UPDATE PATIENT_INFO SET email = ? WHERE patient_id = ?");
            $upd_patient->bind_param("si", $new_email, $patient_id);
            $ok1 = $upd_patient->execute();

            // insert new medication if provided
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
<html>
<head>
    <title>Edit Info</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="records-container">
        <h1>Edit Your Information</h1>

        <?php if (!empty($success)): ?>
            <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" id="edit-form">
            <div class="form-row">
                <label>Email:</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>" required>
            </div>
            <!-- shows current meds -->
            <h3>Current Medications</h3>
            <ul>
            <?php foreach ($medications as $med): ?>
                <li>
                    <?php echo htmlspecialchars($med['medication_name'] . " (" . $med['dosage'] . ")"); ?>
                    <button type="submit" name="remove_med_id" value="<?php echo (int)$med['med_id']; ?>" style="color:red; border:none; background:none; cursor:pointer;">remove</button>
                </li>
            <?php endforeach; ?>
            </ul>


            <!-- 'add new med' form -->
            <h3>Add New Medication</h3>
            <div class="form-row">
                <label>Medication Name:</label>
                <input type="text" name="medication_name">
            </div>
            <div class="form-row">
                <label>Dosage:</label>
                <input type="text" name="dosage">
            </div>

            <button type="submit">Save</button>
            <a href="view_records.php" style="margin-left:10px;">Back</a>
        </form>
    </div>
</body>
</html>
