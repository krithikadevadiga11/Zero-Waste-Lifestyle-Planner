<?php
include "db/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Zero Waste Lifestyle Planner</title>
<meta charset="UTF-8">

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI', sans-serif;
}

html{
scroll-behavior:smooth;
}

/* ✅ BACKGROUND FROM 2ND CODE */
body{
background:url('https://images.unsplash.com/photo-1501004318641-b39e6451bec6') no-repeat center center/cover;
background-attachment:fixed;
color:white;
position:relative;
}

/* Overlay */
body::before{
content:"";
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:linear-gradient(to right, rgba(0,80,0,0.85), rgba(0,0,0,0.75));
z-index:-1;
}

/* NAVBAR */
.navbar{
position:sticky;
top:0;
display:flex;
justify-content:space-between;
align-items:center;
padding:15px 50px;
background:rgba(255,255,255,0.08);
backdrop-filter:blur(10px);
}

.logo{
font-weight:600;
font-size:18px;
}

/* NAV LINKS */
.nav-links{
display:flex;
align-items:center;
gap:20px;
}

.nav-links a{
display:flex;
align-items:center;
gap:8px;
text-decoration:none;
color:#e0f2f1;
padding:8px 18px;
border-radius:30px;
transition:0.3s;
font-size:14px;
}

/* Profile Button */
.profile-link{
background:rgba(255,255,255,0.15);
}

.profile-link:hover{
background:linear-gradient(to right,#00c853,#2e7d32);
box-shadow:0 0 12px rgba(0,255,136,0.5);
transform:translateY(-2px);
}

/* Logout Button */
.logout-link{
background:rgba(255,0,0,0.15);
}

.logout-link:hover{
background:linear-gradient(to right,#d32f2f,#b71c1c);
box-shadow:0 0 12px rgba(255,0,0,0.5);
transform:translateY(-2px);
}

/* MAIN CONTAINER */
.main-container{
display:flex;
justify-content:space-between;
align-items:flex-start;
padding:60px 50px 40px;
gap:50px;
}

.hero{
flex:1.3;
animation:fadeIn 1s ease-in-out;
}

.hero h1{
font-size:38px;
margin-bottom:15px;
}

.hero p{
max-width:650px;
line-height:1.6;
color:#d0f8f3;
font-size:16px;
margin-bottom:12px;
}

/* RIGHT SIDE */
.right-side{
flex:1;
display:flex;
flex-direction:column;
gap:25px;
}

.info-box{
padding:25px;
border-radius:18px;
background:rgba(255,255,255,0.12);
backdrop-filter:blur(10px);
cursor:pointer;
transition:0.4s;
}

.info-box:hover{
box-shadow:0 0 15px rgba(0,255,136,0.3);
}

.info-box h2{
display:flex;
justify-content:space-between;
align-items:center;
font-size:18px;
}

.info-content{
margin-top:15px;
display:none;
line-height:1.6;
color:#d0f8f3;
font-size:14px;
}

.info-box.active .info-content{
display:block;
animation:fadeIn 0.4s ease-in-out;
}

/* QUICK ACCESS */
.quick{
padding:30px 50px 60px;
}

.quick h2{
margin-bottom:25px;
font-size:24px;
}

.quick-grid{
display:flex;
gap:30px;
}

.quick-card{
flex:1;
padding:30px;
border-radius:18px;
background:rgba(255,255,255,0.15);
backdrop-filter:blur(10px);
text-align:center;
transition:0.4s;
}

.quick-card:hover{
transform:translateY(-10px);
box-shadow:0 0 20px rgba(0,255,136,0.4);
}

.quick-card img{
width:60px;
margin-bottom:15px;
}

.quick-card p{
margin:10px 0 20px;
color:#d0f8f3;
}

.quick-card button{
padding:12px 28px;
border:none;
border-radius:30px;
cursor:pointer;
font-size:14px;
background:linear-gradient(to right,#00c853,#2e7d32);
color:white;
transition:0.3s;
}

.quick-card button:hover{
transform:scale(1.05);
box-shadow:0 0 15px #00ff88;
}

/* FOOTER */
footer{
text-align:center;
padding:15px;
background:rgba(0,0,0,0.6);
font-size:13px;
}

/* Animations */
@keyframes fadeIn{
from{opacity:0; transform:translateY(10px);}
to{opacity:1; transform:translateY(0);}
}

/* RESPONSIVE */
@media(max-width:900px){
.main-container{
flex-direction:column;
}
.quick-grid{
flex-direction:column;
}
}
</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">🌱 Zero Waste Lifestyle Planner</div>
    <div class="nav-links">
        <a href="profile.php" class="profile-link">👤 Profile</a>
        <a href="logout.php" class="logout-link">🚪 Logout</a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-container">

    <div class="hero">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?> 🌿</h1>
        <p>
            Zero Waste Lifestyle Planner is your structured sustainability companion.
            Track daily household waste categories including plastic, organic,
            recyclable, and e-waste.
        </p>
        <p>
            Monitor eco streaks, calculate sustainability scores, analyze progress trends,
            and continuously improve your environmental responsibility.
        </p>
        <p>
            Every small action contributes toward building a cleaner,
            greener, and more sustainable future 🌎.
        </p>
    </div>

    <div class="right-side">

        <div class="info-box" onclick="toggleInfo(this)">
            <h2>About The Platform ⬇</h2>
            <div class="info-content">
                This platform enables structured waste tracking, eco performance monitoring, and habit-building through a data-driven approach. Users can visualize progress, maintain sustainability streaks, and unlock eco-based achievements.
            </div>
        </div>

        <div class="info-box" onclick="toggleInfo(this)">
            <h2>Key Features ⬇</h2>
            <div class="info-content">
                ✔ Daily Waste Logging ♻ <br>
                ✔ Eco Score Calculation 📊 <br>
                ✔ Streak Tracking 🔥 <br>
                ✔ Analytics Reports 📈 <br>
                ✔ Reward Mechanism 🏆
            </div>
        </div>

    </div>
</div>

<!-- QUICK ACCESS -->
<div class="quick">
    <h2>⚡ Quick Access</h2>

    <div class="quick-grid">

        <div class="quick-card">
            <img src="https://cdn-icons-png.flaticon.com/512/2913/2913465.png">
            <h3>♻ Track Waste</h3>
            <p>Add daily waste entries and maintain your eco streak 🔥</p>
            <button onclick="window.location.href='track.php'">
                ♻ Start Tracking
            </button>
        </div>

        <div class="quick-card">
            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828884.png">
            <h3>📊 View Dashboard</h3>
            <p>Analyze eco score and performance trends 📈</p>
            <button onclick="window.location.href='dashboard.php'">
                📊 Open Dashboard
            </button>
        </div>

    </div>
</div>

<footer>
© 2026 Zero Waste Lifestyle Planner | Sustainable Living Initiative 🌱
</footer>

<script>
function toggleInfo(element){
    element.classList.toggle("active");
}
</script>

</body>
</html>