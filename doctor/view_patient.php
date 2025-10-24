<?php
include '../includes/auth.php';
include '../includes/db.php';

//makes sure only users with the doctor role can view page
if ($_SESSION['role'] != 'doctor') {
    die("Restricted access"); //terminates script and shows message if user isn't doctor
}

//retrieves value of the search parameter
$search = $_GET['search'] ?? '';

//prepares query for patients through either first name, last name, dob, or email
//uses 'LIKE' for similar matches and limits to 10 results, for now
$sql = "SELECT patient_id, first_name, last_name, date_of_birth, contact_email 
        FROM PATIENT_INFO
        WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR contact_email LIKE '%$search%'
        LIMIT 10";

//executing query        
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Doctor Dashboard</h1>
        </div>
        <!-- search form -->
        <div class="dashboard-card">
            <h3>Search Patients</h3>
            <form method="GET" class="search-form">
                <div class="search-field">
                    <label for="search">Search by name or email:</label>
                    <input type="text" id="search" name="search" placeholder="Enter patient name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
   				 <button type="submit">Search</button>
            </form>
        </div>

        <div class="dashboard-card">
            <h3>Patient Records</h3>
            <?php if ($result->num_rows === 0): ?>
                <p>No patients found.</p>
            <?php else: ?>
                <div class="table-cards">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="table-card">
                            <div class="table-card-header">
                                <?php echo htmlspecialchars($row['first_name']." ".$row['last_name']); ?>
                                <span style="font-size: 0.9rem; font-weight: 400; color: var(--text-secondary);">(ID: <?php echo $row['patient_id']; ?>)</span>
                            </div>
                            
                            <div class="table-card-row">
                                <div class="table-card-label">Date of Birth:</div>
                                <div class="table-card-value"><?php echo htmlspecialchars($row['date_of_birth']); ?></div>
                            </div>
                            
                            <div class="table-card-row">
                                <div class="table-card-label">Email:</div>
                                <div class="table-card-value"><?php echo htmlspecialchars($row['contact_email']); ?></div>
                            </div>
                            
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <form action="add_visit.php" method="get" style="flex: 1;">
                                    <input type="hidden" name="patient_id" value="<?php echo (int)$row['patient_id']; ?>">
                                    <button type="submit" style="width: 100%;">Add Visit</button>
                                </form>
                                <form action="generate_report.php" method="get" style="flex: 1;">
                                    <input type="hidden" name="patient_id" value="<?php echo (int)$row['patient_id']; ?>">
                                    <button type="submit" style="width: 100%; background: var(--secondary-color);">Generate Report</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-card" style="text-align: center;">
            <a href="../logout.php" style="color: var(--text-secondary); text-decoration: none;">Sign Out</a>
        </div>
    </div>
</body>
</html>
