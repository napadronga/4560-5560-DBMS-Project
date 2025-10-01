<?php
include '../includes/auth.php';
include '../includes/db.php';

// ensure doctor role
if ($_SESSION['role'] != 'doctor') {
    die('restricted access');
}

$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
if (!$patient_id) {
    die('no patient selected');
}

// basic patient info
$pstmt = $conn->prepare("SELECT * FROM PATIENT_INFO WHERE patient_id = ?");
$pstmt->bind_param("i", $patient_id);
$pstmt->execute();
$patient = $pstmt->get_result()->fetch_assoc();

// preexisting history
$hstmt = $conn->prepare("SELECT * FROM PREEXISTING_MEDICAL_HISTORY WHERE patient_id = ?");
$hstmt->bind_param("i", $patient_id);
$hstmt->execute();
$history = $hstmt->get_result()->fetch_assoc();

// visits summary
$vstmt = $conn->prepare("SELECT * FROM HOSPITAL_VISITS WHERE patient_id = ? ORDER BY visit_date DESC");
$vstmt->bind_param("i", $patient_id);
$vstmt->execute();
$visits = $vstmt->get_result();

// returned visit data
$rstmt = $conn->prepare("SELECT * FROM RETURNED_VISIT_DATA WHERE patient_id = ?");
$rstmt->bind_param("i", $patient_id);
$rstmt->execute();
$rvd_res = $rstmt->get_result();
$rvd_by_visit = [];
while ($row = $rvd_res->fetch_assoc()) {
    $rvd_by_visit[$row['visit_id']] = $row;
}

// fetch medications
$mstmt = $conn->prepare("SELECT medication_name, dosage FROM PATIENT_MEDICATIONS WHERE patient_id = ?");
$mstmt->bind_param("i", $patient_id);
$mstmt->execute();
$medications = $mstmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Report</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="records-container">
        <h1>Patient Report</h1>
        <?php if (!$patient): ?>
            <p>patient not found</p>
        <?php else: ?>
            <h2>Personal Information</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($patient['first_name'].' '.$patient['last_name']); ?></p>
            <p><strong>DOB:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email'] ?? ''); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone_number'] ?? ''); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($patient['address'] ?? ''); ?></p>

            <h2>Preexisting Medical History</h2>
            <?php if ($history): ?>
                <p><strong>Conditions:</strong> <?php echo htmlspecialchars($history['conditions'] ?? ''); ?></p>
                <p><strong>Allergies:</strong> <?php echo htmlspecialchars($history['allergies'] ?? ''); ?></p>
                <p><strong>Current Medications</strong></p>
                <?php if ($medications->num_rows > 0): ?>
                    <ul>
                        <?php while ($med = $medications->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($med['medication_name'] . " (" . $med['dosage'] . ")"); ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>no medications recorded</p>
                <?php endif; ?>
                <p><strong>Surgeries:</strong> <?php echo htmlspecialchars($history['surgeries'] ?? ''); ?></p>
                <p><strong>Family History:</strong> <?php echo htmlspecialchars($history['family_history'] ?? ''); ?></p>
                <p><strong>Social History:</strong> <?php echo htmlspecialchars($history['social_history'] ?? ''); ?></p>
                <p><strong>Activity Level:</strong> <?php echo htmlspecialchars($history['activity_level'] ?? ''); ?></p>
                <p><strong>Last Updated:</strong> <?php echo htmlspecialchars($history['last_time_updated'] ?? ''); ?></p>
            <?php else: ?>
                <p>no preexisting history recorded</p>
            <?php endif; ?>

            <h2>Visits</h2>
            <?php if ($visits->num_rows === 0): ?>
                <p>no visits recorded</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Reason</th>
                        <th>Diagnosis</th>
                        <th>Vitals</th>
                        <th>Notes</th>
                    </tr>
                    <?php while ($v = $visits->fetch_assoc()): 
                        $rvd = $rvd_by_visit[$v['visit_id']] ?? null; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($v['visit_date']); ?></td>
                            <td><?php echo htmlspecialchars($v['visit_reason'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($v['diagnosis'] ?? ''); ?></td>
                            <td>
                                <?php if ($rvd): ?>
                                    <?php echo 'BP: '.htmlspecialchars($rvd['blood_pressure'] ?? '').', HR: '.htmlspecialchars($rvd['heart_rate'] ?? '').', Temp: '.htmlspecialchars($rvd['temperature'] ?? '').', RR: '.htmlspecialchars($rvd['respiration_rate'] ?? ''); ?>
                                <?php else: ?>
                                    n/a
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($rvd['extra_notes'] ?? ''); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        <?php endif; ?>

        <br>
        <a href="view_patient.php">Back</a>
    </div>
</body>
</html>
