<?php
//include auth/session check and database connection
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/activity_logger.php';

//get patient's id from the session
$patient_id = $_SESSION['user_id'];

//log patient viewing their records
logUserAction($conn, $patient_id, 'patient', 'RECORD_VIEW', 'Patient viewed their medical records');

//retrieve basic patient info
$sql = "SELECT * FROM PATIENT_INFO WHERE patient_id='$patient_id'";
$result = $conn->query($sql);
$patient = $result->fetch_assoc();

//retrieve patient's medical history
$sql_history = "SELECT * FROM PREEXISTING_MEDICAL_HISTORY WHERE patient_id='$patient_id'";
$history_result = $conn->query($sql_history);
$history = $history_result->fetch_assoc();

// retrieve patient's medications from medications table
$med_stmt = $conn->prepare("SELECT medication_name, dosage, start_date FROM PATIENT_MEDICATIONS WHERE patient_id = ?");
$med_stmt->bind_param("i", $patient_id);
$med_stmt->execute();
$medications_result = $med_stmt->get_result();
$medications = $medications_result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Health Records</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($patient['first_name']); ?>!</h1>
        </div>

        <div class="dashboard-grid">
            <!-- Personal Information Card -->
            <div class="dashboard-card">
                <h3>Personal Information</h3>
                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($patient['first_name']." ".$patient['last_name']); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['contact_email']); ?></p>
                <form action="edit_record.php" method="get" style="margin-top: 1rem;">
                    <button type="submit">Update Information</button>
                </form>
            </div>

            <!-- Medical History Card -->
            <div class="dashboard-card">
                <h3>Medical History</h3>
                <?php if($history): ?>
                    <p><strong>Conditions:</strong> 
                        <?php echo !empty($history['conditions']) ? htmlspecialchars($history['conditions']) : "No conditions recorded"; ?>
                    </p>
                    <p><strong>Allergies:</strong> 
                        <?php echo !empty($history['allergies']) ? htmlspecialchars($history['allergies']) : "No allergies recorded"; ?>
                    </p>
                    <p><strong>Last Updated:</strong> 
                        <?php echo !empty($history['last_time_updated']) ? htmlspecialchars($history['last_time_updated']) : "Not updated yet"; ?>
                    </p>
                <?php else: ?>
                    <p>No medical history available. Please update your information.</p>
                <?php endif; ?>
            </div>

            <!-- medications card -->
            <div class="dashboard-card">
                <h3>Current Medications</h3>
                <?php if (!empty($medications)): ?>
                    <ul class="med-list">
                        <?php foreach ($medications as $med): ?>
                            <li class="med-list-item">
                                <span class="med-list-item-title"><?php echo htmlspecialchars($med['medication_name']); ?></span>
                                <span class="med-list-item-meta"><?php echo htmlspecialchars($med['dosage']); ?></span>
                                <span class="med-list-item-meta">started: <?php echo htmlspecialchars($med['start_date']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No medications or prescriptions recorded</p>
                <?php endif; ?>
            </div>

            <!-- Actions Card -->
            <div class="dashboard-card">
                <h3>Quick Actions</h3>
                <p>Manage your health data and records</p>
                <form action="download_data.php" method="get" style="margin: 1rem 0;">
                    <button type="submit">Download My Data</button>
                </form>
                <a href="../logout.php" style="display: inline-block; margin-top: 1rem; color: var(--text-secondary); text-decoration: none;">Sign Out</a>
            </div>
        </div>
    </div>
</body>
</html>
