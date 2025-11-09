<?php
$host = "localhost";   // usually says localhost
$user = "root";        // default XAMPP username
$pass = "";            // leave empty unless you set a password
$dbname = "auth_system"; // your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "auth_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

