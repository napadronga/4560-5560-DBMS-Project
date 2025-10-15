<?php
//start the session
session_start();

//destroy the session
session_destroy();

//redirect to landing page
header("Location: index.php");

//terminates script
exit();
?>
