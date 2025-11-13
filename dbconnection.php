<?php
$conn = new mysqli('sql305.infinityfree.com','if0_40394857','jcqsKeC3JaP', 'if0_40394857_crudoperations');

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>