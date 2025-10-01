<?php
//start session
session_start();

//include the database connection
include 'includes/db.php';

//error messages stored here
$error = "";

//checks if login was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //getting email and password entered by the user
    $email = $_POST['email'];
    $password = $_POST['password'];

    //querying if the email exists in the patient table
    $sql_patient = "SELECT patient_id, password_hash FROM PATIENT_USERS WHERE login_email='$email'";
    $result_patient = $conn->query($sql_patient);

    //querying if the email exists in the doctor table
    $sql_doctor = "SELECT doctor_id, password_hash FROM DOCTOR_USERS WHERE login_email='$email'";
    $result_doctor = $conn->query($sql_doctor);

    //if user is in the patient table
    if ($result_patient->num_rows > 0) {
        $row = $result_patient->fetch_assoc();
	    $patientHash = $row['password_hash'];
	
	    //verify password
	    if (password_verify($password, $patientHash)) {
            //storing patient id and role in session
            $_SESSION['user_id'] = $row['patient_id'];
            $_SESSION['role'] = "patient";

            //redirecting to user's dash
            header("Location: dashboard.php");
            exit();
        }
    } 
    //if user is in the doctor table
    elseif ($result_doctor->num_rows > 0) {
        $row = $result_doctor->fetch_assoc();
	    $doctorHash = $row['password_hash'];

	    //verify password
	    if (password_verify($password, $doctorHash)) {
            //storing doctor id and role in session
            $_SESSION['user_id'] = $row['doctor_id'];
            $_SESSION['role'] = "doctor";

            //redirecting to the user's dash
            header("Location: dashboard.php");
            exit();
        }
    } 
    //if no match was found in patient OR doctor
    $error = "Invalid login";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Health Records Portal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- login form container -->
    <div class="login-container">
        <h1>Health Records Portal</h1>
        
        <!-- login form -->
        <form method="POST" class="login-form">
            <div class="form-row">
                <label>Email:</label>
                <input type="text" name="email" required>
            </div>
            <div class="form-row">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

	    <!-- register link -->
        <p>Don’t have an account?</p>
        <a href="register.php">
	        <button>Register Here</button>
	    </a>

        <!-- displaying error if login fails -->
        <p style="color:red;"><?php echo $error; ?></p>
    </div>
</body>
</html>
