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
        <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>

        <!-- non functional button --> 
        <button>Edit Info</button>

        <!-- displaying medical history -->
        <h2>Medical History & Prescriptions</h2>
        <?php if($history): ?>
            <p><strong>Conditions:</strong> 
                <?php echo !empty($history['conditions']) ? htmlspecialchars($history['conditions']) : "No conditions recorded"; ?>
            </p>
            <p><strong>Allergies:</strong> 
                <?php echo !empty($history['allergies']) ? htmlspecialchars($history['allergies']) : "No allergies recorded"; ?>
            </p>
            <p><strong>Medications / Prescriptions:</strong> 
                <?php echo !empty($history['medications']) ? htmlspecialchars($history['medications']) : "No medications or prescriptions recorded"; ?>
            </p>
            <p><strong>Last Updated:</strong> 
                <?php echo !empty($history['last_time_updated']) ? htmlspecialchars($history['last_time_updated']) : "Not updated yet"; ?>
            </p>
        <?php else: ?>
            <!-- case where no medical history is available for patient -->
            <p>No medical history available. Please update your info.</p>
        <?php endif; ?>

        <!-- non functional button --> 
        <button>Download Data</button>

        <br><br>
        <a href="../logout.php">Logout</a>
    </div>
</body>
</html>
