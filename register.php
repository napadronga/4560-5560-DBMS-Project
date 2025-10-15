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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - HealthCare Portal</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <h1>Create Account</h1>
        <p>Join our secure healthcare platform</p>

        <!-- Registration Form -->
        <form method="POST" class="login-form">
            <div class="form-row">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name">
            </div>

            <div class="form-row">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name">
            </div>

            <div class="form-row">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="form-row">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Create a password">
            </div>

            <div class="form-row" style="justify-content: center;">
                <label style="display: flex; align-items: center; cursor: pointer; margin-left: 2rem;">
                    <input type="checkbox" name="is_doctor" value="1" style="width: auto; margin: 0;">
                    <span style="position: absolute; left: 120px;">I am a healthcare provider</span>
                </label>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <!-- Back to login page -->
        <p>Already have an account?</p>
        <a href="index.php">
            <button>Sign In</button>
        </a>

        <!-- Display error -->
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
