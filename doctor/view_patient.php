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
$sql = "SELECT patient_id, first_name, last_name, date_of_birth, email 
        FROM PATIENT_INFO
        WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%'
        LIMIT 10";

//executing query        
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="records-container">
        <h1>Doctor Dashboard</h1>
        <!-- search form -->
        <form method="GET">
            <input type="text" name="search" placeholder="Search patients... " value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <h2>Patients</h2>
        <!-- table with retrieved patients' information -->
        <table>
            <tr><th>ID</th><th>Name</th><th>DOB</th><th>Email</th><th>Actions</th></tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['patient_id']; ?></td>
                    <td><?php echo $row['first_name']." ".$row['last_name']; ?></td>
                    <td><?php echo $row['date_of_birth']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <!-- doctor actions with patient_id -->
                        <form action="add_visit.php" method="get" style="display:inline;">
                            <input type="hidden" name="patient_id" value="<?php echo (int)$row['patient_id']; ?>">
                            <button type="submit">Add Visit</button>
                        </form>
                        <form action="generate_report.php" method="get" style="display:inline; margin-left:6px;">
                            <input type="hidden" name="patient_id" value="<?php echo (int)$row['patient_id']; ?>">
                            <button type="submit">Generate Report</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- logout hyperlink -->
        <p></p>
        <a href="../logout.php">Logout</a>
    </div>
</body>
</html>
