<?php
include "db/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

/* ================= REGISTER ================= */
if(isset($_POST['register'])){
$name = $_POST['name'];
$email = $_POST['email'];
$address = $_POST['address'];
$pincode = $_POST['pincode'];
$city = $_POST['city'];
$family_members = $_POST['family_members'];
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
$message = "Registration successful. Please login.";
}
}
}

/* ================= LOGIN ================= */
if(isset($_POST['login'])){
$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($query) == 0){
$message = "Account not found.";
} else {
$row = mysqli_fetch_assoc($query);
if(password_verify($password,$row['password'])){
$_SESSION['user_id'] = $row['id'];
} else {
$message = "Incorrect password.";
}
}
}

/* ================= FETCH PROFILE ================= */
$loggedIn = false;
if(isset($_SESSION['user_id'])){
$loggedIn = true;
$user_id = $_SESSION['user_id'];
$user = mysqli_query($conn,"SELECT * FROM users WHERE id=$user_id");
$data = mysqli_fetch_assoc($user);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>User System</title>
<meta charset="UTF-8">

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
background:url('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee') no-repeat center center/cover;
background-size:cover;
position:relative;
color:white;
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

/* Main Card */
.container{
position:relative;
z-index:1;
width:450px;
padding:40px;
border-radius:20px;
background:rgba(255,255,255,0.15);
backdrop-filter:blur(15px);
box-shadow:0 0 40px rgba(0,0,0,0.6);
text-align:center;
animation:fadeIn 0.8s ease-in-out;
}

/* 👤 Profile Icon */
.logo{
width:90px;
height:90px;
margin:0 auto 15px auto;
border-radius:50%;
background:linear-gradient(to right,#00c853,#2e7d32);
display:flex;
justify-content:center;
align-items:center;
font-size:40px;
box-shadow:0 0 20px rgba(0,255,0,0.6);
animation:float 3s ease-in-out infinite alternate;
}

@keyframes float{
from{transform:translateY(0);}
to{transform:translateY(-10px);}
}

@keyframes fadeIn{
from{opacity:0; transform:translateY(20px);}
to{opacity:1; transform:translateY(0);}
}

input{
width:100%;
padding:12px;
margin:8px 0;
border-radius:10px;
border:none;
background:rgba(255,255,255,0.2);
color:white;
}

input::placeholder{
color:#e0f2f1;
}

button{
width:100%;
padding:12px;
border:none;
border-radius:10px;
background:linear-gradient(to right,#00c853,#2e7d32);
color:white;
cursor:pointer;
margin-top:10px;
transition:0.3s;
}

button:hover{
transform:scale(1.05);
box-shadow:0 0 15px #00ff88;
}

.switch{
margin-top:15px;
cursor:pointer;
color:#b9f6ca;
}

.message{
color:#ffcccb;
margin-bottom:10px;
}

/* Profile Info */
.info{
margin:10px 0;
padding:10px;
border-radius:10px;
background:rgba(255,255,255,0.15);
}

/* Back Home Button */
.home-btn{
margin-top:20px;
display:inline-block;
padding:10px 25px;
background:linear-gradient(to right,#00c853,#2e7d32);
color:white;
text-decoration:none;
border-radius:25px;
transition:0.3s;
}

.home-btn:hover{
transform:scale(1.05);
box-shadow:0 0 15px #00ff88;
}
</style>
</head>

<body>

<div class="container">

<div class="logo">👤</div>

<?php if(!$loggedIn){ ?>

<h2 id="title">Login</h2>

<?php if(!empty($message)) echo "<div class='message'>$message</div>"; ?>

<form method="post" id="loginForm">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit" name="login">Login</button>
</form>

<form method="post" id="registerForm" style="display:none;">
<input type="text" name="name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="text" name="address" placeholder="Address" required>
<input type="text" name="pincode" placeholder="Pincode" required>
<input type="text" name="city" placeholder="City" required>
<input type="number" name="family_members" placeholder="Family Members" required>
<input type="password" name="password" placeholder="Create Password" required>
<input type="password" name="confirm_password" placeholder="Confirm Password" required>
<button type="submit" name="register">Register</button>
</form>

<div class="switch" onclick="toggleForm()" id="switchText">
Don't have an account? Register
</div>

<?php } else { ?>

<h2>My Profile</h2>

<div class="info"><strong>Name:</strong> <?php echo htmlspecialchars($data['name']); ?></div>
<div class="info"><strong>Email:</strong> <?php echo htmlspecialchars($data['email']); ?></div>
<div class="info"><strong>Address:</strong> <?php echo htmlspecialchars($data['address']); ?></div>
<div class="info"><strong>Pincode:</strong> <?php echo htmlspecialchars($data['pincode']); ?></div>
<div class="info"><strong>City:</strong> <?php echo htmlspecialchars($data['city']); ?></div>
<div class="info"><strong>Family Members:</strong> <?php echo htmlspecialchars($data['family_members']); ?></div>

<a href="home.php" class="home-btn">⬅ Back to Home</a>

<?php } ?>

</div>

<script>
function toggleForm(){
const login = document.getElementById("loginForm");
const register = document.getElementById("registerForm");
const switchText = document.getElementById("switchText");

if(register.style.display === "none"){
register.style.display = "block";
login.style.display = "none";
switchText.innerText = "Already have an account? Login";
} else {
register.style.display = "none";
login.style.display = "block";
switchText.innerText = "Don't have an account? Register";
}
}
</script>

</body>
</html>
