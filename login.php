<?php
include "db/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

/* ---------------- REGISTER ---------------- */
if(isset($_POST['register'])){

$name = mysqli_real_escape_string($conn,$_POST['name']);
$email = mysqli_real_escape_string($conn,$_POST['email']);
$address = mysqli_real_escape_string($conn,$_POST['address']);
$pincode = mysqli_real_escape_string($conn,$_POST['pincode']);
$city = mysqli_real_escape_string($conn,$_POST['city']);
$family_members = mysqli_real_escape_string($conn,$_POST['family_members']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if($password != $confirm_password){
$message = "Passwords do not match.";
} else {

$check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($check) > 0){
$message = "Email already registered.";
} else {

$hashed = password_hash($password,PASSWORD_DEFAULT);

mysqli_query($conn,"INSERT INTO users
(name,email,address,pincode,city,family_members,password)
VALUES
('$name','$email','$address','$pincode','$city','$family_members','$hashed')");

header("Location: login.php?success=1");
exit();
}
}
}

/* ---------------- LOGIN ---------------- */
if(isset($_POST['login'])){

$email = mysqli_real_escape_string($conn,$_POST['email']);
$password = $_POST['password'];

$query = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($query) == 0){
$message = "Account not found.";
} else {

$row = mysqli_fetch_assoc($query);

if(password_verify($password,$row['password'])){

$_SESSION['user_id'] = $row['id'];
$_SESSION['name'] = $row['name'];

header("Location: home.php");
exit();

} else {
$message = "Incorrect password.";
}
}
}

/* Success message after redirect */
if(isset($_GET['success'])){
$message = "Registration successful. Please login.";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Zero Waste Lifestyle Planner</title>

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI', sans-serif;
}

body{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:url('https://images.unsplash.com/photo-1621451537084-482c73073a0f') no-repeat center center/cover;
background-size:cover;
position:relative;
}

/* Overlay */
body::before{
content:"";
position:absolute;
top:0;
left:0;
width:100%;
height:100%;
background:linear-gradient(to right, rgba(0,100,0,0.85), rgba(0,0,0,0.7));
z-index:0;
}

.container{
position:relative;
z-index:1;
width:420px;
padding:40px;
border-radius:20px;
background:rgba(255,255,255,0.15);
backdrop-filter:blur(15px);
box-shadow:0 0 40px rgba(0,0,0,0.6);
text-align:center;
color:white;
animation:fadeIn 0.8s ease-in-out;
}

.logo{
width:90px;
height:90px;
margin:0 auto 15px auto;
border-radius:50%;
background:rgba(255,255,255,0.2);
display:flex;
justify-content:center;
align-items:center;
font-size:45px;
animation:plantFloat 3s ease-in-out infinite alternate;
box-shadow:0 0 20px rgba(0,255,0,0.5);
}

@keyframes plantFloat{
0%{transform:translateY(0) scale(1);}
100%{transform:translateY(-12px) scale(1.1);}
}

@keyframes fadeIn{
from{opacity:0; transform:translateY(20px);}
to{opacity:1; transform:translateY(0);}
}

.app-name{
font-size:20px;
font-weight:600;
margin-bottom:20px;
}

h2{
margin-bottom:15px;
}

form{
display:none;
}

form.active{
display:block;
}

input{
width:100%;
padding:12px;
margin:10px 0;
border-radius:10px;
border:none;
background:rgba(255,255,255,0.2);
color:white;
outline:none;
transition:0.3s;
}

input::placeholder{
color:#e0f2f1;
}

input:focus{
background:rgba(255,255,255,0.3);
box-shadow:0 0 10px #00ff88;
}

button{
width:100%;
padding:12px;
border:none;
border-radius:10px;
background:linear-gradient(to right,#00c853,#2e7d32);
color:white;
font-size:15px;
cursor:pointer;
transition:0.3s;
margin-top:10px;
}

button:hover{
transform:scale(1.05);
box-shadow:0 0 20px #00ff88;
}

.switch{
margin-top:18px;
cursor:pointer;
font-size:14px;
color:#b9f6ca;
}

.switch:hover{
text-decoration:underline;
color:white;
}

.message{
color:#ffcccb;
font-size:14px;
margin-bottom:10px;
}
</style>
</head>

<body>

<div class="container">

<div class="logo">🌱</div>
<div class="app-name">Zero Waste Lifestyle Planner</div>

<h2 id="title">Login</h2>

<?php if(!empty($message)) echo "<div class='message'>$message</div>"; ?>

<!-- LOGIN FORM -->
<form method="post" id="loginForm" class="active" autocomplete="off">
<input type="email" name="email" placeholder="Email" required autocomplete="off">
<input type="password" name="password" placeholder="Password" required autocomplete="new-password">
<button type="submit" name="login">Login</button>
</form>

<!-- REGISTER FORM -->
<form method="post" id="registerForm" autocomplete="off">
<input type="text" name="name" placeholder="Full Name" required autocomplete="off">
<input type="email" name="email" placeholder="Email" required autocomplete="off">
<input type="text" name="address" placeholder="Address" required autocomplete="off">
<input type="text" name="pincode" placeholder="Pincode" required autocomplete="off">
<input type="text" name="city" placeholder="City" required autocomplete="off">
<input type="number" name="family_members" placeholder="Family Members" required autocomplete="off">
<input type="password" name="password" placeholder="Create Password" required autocomplete="new-password">
<input type="password" name="confirm_password" placeholder="Confirm Password" required autocomplete="new-password">
<button type="submit" name="register">Register</button>
</form>

<div class="switch" onclick="toggleForm()" id="switchText">
Don't have an account? Register
</div>

</div>

<script>
function toggleForm(){
const login = document.getElementById("loginForm");
const register = document.getElementById("registerForm");
const title = document.getElementById("title");
const switchText = document.getElementById("switchText");

login.classList.toggle("active");
register.classList.toggle("active");

if(login.classList.contains("active")){
title.innerText = "Login";
switchText.innerText = "Don't have an account? Register";
} else {
title.innerText = "Register";
switchText.innerText = "Already have an account? Login";
}
}
</script>

</body>
</html>
