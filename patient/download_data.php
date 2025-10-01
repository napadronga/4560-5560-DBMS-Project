<?php
// include auth and db
include '../includes/auth.php';
include '../includes/db.php';

$patient_id = $_SESSION['user_id'];

// fetch recent hospital visits joined with returned visit data
$stmt = $conn->prepare(
    "SELECT hv.visit_date, hv.visit_reason, hv.diagnosis,
            rvd.height, rvd.weight, rvd.blood_pressure, rvd.heart_rate, rvd.temperature,
            rvd.respiration_rate, rvd.new_medicines, rvd.new_conditions, rvd.urgent_concern, rvd.extra_notes
     FROM HOSPITAL_VISITS hv
     LEFT JOIN RETURNED_VISIT_DATA rvd ON hv.visit_id = rvd.visit_id
     WHERE hv.patient_id = ?
     ORDER BY hv.visit_date DESC
     LIMIT 10"
);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$visits = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Data</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="records-container">
        <h1>Your Recent Visits</h1>
        <?php if ($visits->num_rows === 0): ?>
            <p>no visit data available yet</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Reason</th>
                    <th>Diagnosis</th>
                    <th>BP</th>
                    <th>HR</th>
                    <th>Temp</th>
                    <th>Notes</th>
                </tr>
                <?php while ($v = $visits->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($v['visit_date']); ?></td>
                        <td><?php echo htmlspecialchars($v['visit_reason'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($v['diagnosis'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($v['blood_pressure'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($v['heart_rate'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($v['temperature'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($v['extra_notes'] ?? ''); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>

        <br>
        <a href="view_records.php">Back</a>
    </div>
</body>
</html>
