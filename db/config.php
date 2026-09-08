<?php
$conn = mysqli_connect("localhost","root","","zero_waste_db");

if(!$conn){
    die("Database connection failed");
}

session_start();
?>