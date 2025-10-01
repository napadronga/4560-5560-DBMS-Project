<?php
//start session
session_start();

//include the database connection
include 'includes/db.php';

//error messages stored here
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //collect user email and password
    $email = $_POST['email'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $isDoctor = isset($_POST['is_doctor']);

    if ($isDoctor) {
        if (!empty($email) && !empty($password) && !empty($first_name) && !empty($last_name)) {
	    $stmt1 = $conn->prepare("INSERT INTO DOCTOR_INFO (first_name, last_name) VALUES (?, ?)");
	    $stmt1->bind_param("ss", $first_name, $last_name);
	    $stmt1->execute();

	    //get recently auto incremented patient_id
	    $doctor_id = $conn->insert_id;

	    //hash the password
	    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

	    //insert into users table
	    $stmt2 = $conn->prepare("INSERT INTO DOCTOR_USERS (doctor_id, login_email, password_hash) VALUES (?, ?, ?)");
	    $stmt2->bind_param("iss", $doctor_id, $email, $passwordHash);

	    if ($stmt2->execute()) {
	        header("Location: index.php");
		exit();
	    }
	    else {
	        $error = "Invalid login";
	    }
	}
	else {
	    $error = "Please fill in all the fields.";
	}
    }

    else {
        if (!empty($email) && !empty($password) && !empty($first_name) && !empty($last_name)) {
            $stmt1 = $conn->prepare("INSERT INTO PATIENT_INFO (first_name, last_name) VALUES (?, ?)");
	    $stmt1->bind_param("ss", $first_name, $last_name);
	    $stmt1->execute();

	    //get recently auto incremented patient_id
	    $patient_id = $conn->insert_id;

	    //hash the password
	    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

	    //insert into users table
	    $stmt2 = $conn->prepare("INSERT INTO PATIENT_USERS (patient_id, login_email, password_hash) VALUES (?, ?, ?)");
	    $stmt2->bind_param("iss", $patient_id, $email, $passwordHash);

	    if ($stmt2->execute()) {
	        header("Location: index.php");
	        exit();
	    }
            else {
	        $error = "Invalid login";
	    }
        }
        else {
            $error = "Please fill in all the fields.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Health Records Portal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="login-container">
        <h1>Create an Account</h1>

        <!-- Registration Form -->
        <form method="POST" class="login-form">
            <div class="form-row">
                <label>First Name:</label>
                <input type="text" name="first_name" required>
            </div>

            <div class="form-row">
                <label>Last Name:</label>
                <input type="text" name="last_name" required>
            </div>

            <div class="form-row">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-row">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>

	    <div class="form-row">
                <label>Sign up as a Doctor</label>
                <input type="checkbox" name="is_doctor" value="1">
            </div>

            <button type="submit">Register</button>
        </form>

        <!-- Back to login page -->
        <p style="margin-top: 15px;">Already have an account?</p>
        <a href="index.php">
	    <button>Back to Login</button>
	</a>

        <!-- Display error -->
        <p style="color:red;"><?php echo $error; ?></p>
    </div>
</body>
</html>
