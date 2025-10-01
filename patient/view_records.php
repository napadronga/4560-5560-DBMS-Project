<?php
//include auth/session check and database connection
include '../includes/auth.php';
include '../includes/db.php';

//get patient's id from the session
$patient_id = $_SESSION['user_id'];

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
<html>
<head>
    <title>Patient Health Records</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="records-container">
        <h1>Welcome back, <?php echo htmlspecialchars($patient['first_name']); ?>!</h1>

        <!-- displaying the basic patient info -->
        <h2>Personal Information</h2>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($patient['first_name']." ".$patient['last_name']); ?></p>
        <p><strong>DOB:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['contact_email']); ?></p>

        <!-- edit info button -->
        <form action="edit_record.php" method="get" style="display:inline;">
            <button type="submit">Edit Info</button>
        </form>

        <!-- displaying medical history -->
        <h2>Medical History & Prescriptions</h2>
        <?php if($history): ?>
            <p><strong>Conditions:</strong> 
                <?php echo !empty($history['conditions']) ? htmlspecialchars($history['conditions']) : "No conditions recorded"; ?>
            </p>
            <p><strong>Allergies:</strong> 
                <?php echo !empty($history['allergies']) ? htmlspecialchars($history['allergies']) : "No allergies recorded"; ?>
            </p>
            <p><strong>Medications / Prescriptions</strong>
            <?php if (!empty($medications)): ?>
                <ul>
                    <?php foreach ($medications as $med): ?>
                        <li>
                            <?php echo htmlspecialchars($med['medication_name']); ?>
                            (<?php echo htmlspecialchars($med['dosage']); ?>)
                            — Started: <?php echo htmlspecialchars($med['start_date']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No medications or prescriptions recorded</p>
            <?php endif; ?>
            <p><strong>Last Updated:</strong> 
                <?php echo !empty($history['last_time_updated']) ? htmlspecialchars($history['last_time_updated']) : "Not updated yet"; ?>
            </p>
        <?php else: ?>
            <!-- case where no medical history is available for patient -->
            <p>No medical history available. Please update your info.</p>
        <?php endif; ?>

        <!-- view data button -->
        <form action="download_data.php" method="get" style="display:inline;">
            <button type="submit">View Data</button>
        </form>

        <br><br>
        <a href="../logout.php">Logout</a>
    </div>
</body>
</html>
