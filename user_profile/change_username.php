<?php
// Sanitized and validated 11-10-2023

include "../DB_connect.php";
session_start();

if (isset($_SESSION['username']) && isset($_SESSION["id"])) {
    $userid = $_SESSION['id'];
}

// prevent cross-site scripting
$newUsername = filter_var($_POST['new_username'], FILTER_SANITIZE_SPECIAL_CHARS); 

// prepare sql
$sql_newUsername = "SELECT * FROM users WHERE username = ?";
$stmt = $link->prepare($sql_newUsername);
$stmt->bind_param("s", $newUsername);
$usernameexists = $stmt->execute();
$usernameexists = $stmt->get_result();

if ($usernameexists->num_rows > 0) {
    $message = urlencode("Error: Username is already connected to an account");
    header("Location:edit_myprofile.php?Message=" . $message);
    die;
}
$stmt->close(); // allow for more sql queries to run

$sql = "UPDATE users SET username=? WHERE userid=?";
$stmt2 = $link->prepare($sql);
$stmt2->bind_param("ss", $newUsername, $userid);
$result = $stmt2->execute();

if ($result) {
    $message = urlencode("Username changed successfully");
    header("Location:edit_myprofile.php?Message=" . $message);
    $_SESSION['username']=$newUsername;
    die;
} else {
    $message = urlencode("Error: Username could not be changed");
    header("Location:edit_myprofile.php?Message=" . $message);
    die;
}

?>