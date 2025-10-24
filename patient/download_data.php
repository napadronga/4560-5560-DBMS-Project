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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Data</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Your Visit History</h1>
        </div>

        <?php if ($visits->num_rows === 0): ?>
            <div class="dashboard-card">
                <h3>No Visit Data</h3>
                <p>You don't have any visit records yet.</p>
            </div>
        <?php else: ?>
            <div class="table-cards">
                <?php while ($v = $visits->fetch_assoc()): ?>
                    <div class="table-card">
                    <div class="table-card">
                    <div class="table-card-header">
                        Visit on <?php echo htmlspecialchars($v['visit_date']); ?>
                    </div>

                    <div class="sub-card">
                        <h4>Reason for Visit</h4>
                        <p><?php echo htmlspecialchars($v['visit_reason'] ?? 'Not specified'); ?></p>
                    </div>

                    <?php if (!empty($v['diagnosis'])): ?>
                    <div class="sub-card">
                        <h4>Diagnosis</h4>
                        <p><?php echo htmlspecialchars($v['diagnosis']); ?></p>
                    </div>
                    <?php endif; ?>
                        
                        <h4>Vitals</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin: 1rem 0; padding: 1rem; background: rgba(37, 99, 235, 0.05); border-radius: 8px;">
                            <div>
                                <div class="table-card-label">Blood Pressure</div>
                                <div class="table-card-value" style="font-weight: 600;"><?php echo htmlspecialchars($v['blood_pressure'] ?? 'N/A'); ?></div>
                            </div>
                            <div>
                                <div class="table-card-label">Heart Rate</div>
                                <div class="table-card-value" style="font-weight: 600;"><?php echo htmlspecialchars($v['heart_rate'] ?? 'N/A'); ?> bpm</div>
                            </div>
                            <div>
                                <div class="table-card-label">Temperature</div>
                                <div class="table-card-value" style="font-weight: 600;"><?php echo htmlspecialchars($v['temperature'] ?? 'N/A'); ?>°C</div>
                            </div>
                        </div>
                        
                        <?php if (!empty($v['extra_notes'])): ?>
                        <div class="sub-card">
                        <h4>Notes</h4>
                        <p><?php echo htmlspecialchars($v['extra_notes']); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($v['new_medicines'])): ?>
                        <div class="sub-card">
                        <h4>New Medications</h4>
                        <p><?php echo htmlspecialchars($v['new_medicines']); ?></p>
                        </div>
                        <?php endif; ?> 
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-card" style="text-align: center; margin-top: 2rem;">
        <a href="view_records.php">Back</a>
        </div>
    </div>
</body>
</html>
