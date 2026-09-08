<?php
include "db/config.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

/* ================= CLAIM COUPON ================= */
if(isset($_POST['claim_coupon'])){
    mysqli_query($conn,"
        UPDATE rewards 
        SET redeemed=1 
        WHERE user_id=$user_id AND redeemed=0
    ");
    header("Location: dashboard.php");
    exit();
}

/* ================= TOTAL POINTS ================= */
$result = mysqli_query($conn,"
SELECT SUM(eco_points) as total, COUNT(*) as entries    
FROM waste_logs WHERE user_id=$user_id
");
$data = mysqli_fetch_assoc($result);
$total_points = $data['total'] ?? 0;
$total_entries = $data['entries'] ?? 0;

/* ================= WEEKLY DATA ================= */
$weekly = [0,0,0,0,0,0,0];
$days = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
$query = "
SELECT DAYOFWEEK(created_at) as day,
SUM(eco_points) as total
FROM waste_logs
WHERE user_id=$user_id
AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)
GROUP BY day
";
$res = mysqli_query($conn,$query);
while($r = mysqli_fetch_assoc($res)){
    $index = $r['day'] - 1;
    $weekly[$index] = $r['total'];
}
$this_week = array_sum($weekly);
$max_value = max($weekly);
if($max_value == 0) $max_value = 1;

/* ================= LAST WEEK ================= */
$last_week = 0;
$last_query = "
SELECT SUM(eco_points) as total
FROM waste_logs
WHERE user_id=$user_id
AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)-1
";
$last_res = mysqli_query($conn,$last_query);
$last_row = mysqli_fetch_assoc($last_res);
$last_week = $last_row['total'] ?? 0;

/* ================= CATEGORY ================= */
$categories = [];
$cat_query = mysqli_query($conn,"
SELECT waste_type, SUM(eco_points) as pts
FROM waste_logs WHERE user_id=$user_id
GROUP BY waste_type
");
while($row = mysqli_fetch_assoc($cat_query)){
    $categories[$row['waste_type']] = $row['pts'];
}

/* ================= STREAK ================= */
$user = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT streak FROM users WHERE id=$user_id
"));
$streak = $user['streak'] ?? 0;

/* ================= LEVEL ================= */
if($total_points < 500){ $level = "🌱 Beginner"; }
elseif($total_points < 1500){ $level = "🌿 Eco Saver"; }
else{ $level = "🌎 Eco Champion"; }

/* ================= COUPON ================= */
$coupon = "";
$company = "";
if($total_points >= 500){
    $companies = ["GreenKart","EcoMart","NatureBasket","RecycleHub","EarthStore"];
    $company = $companies[array_rand($companies)];
    $check = mysqli_query($conn,"
        SELECT * FROM rewards 
        WHERE user_id=$user_id AND redeemed=0
    ");
    if(mysqli_num_rows($check)==0){
        $code = "ECO".rand(1000,9999);
        mysqli_query($conn,"
            INSERT INTO rewards(user_id,coupon_code,points_required,redeemed)
            VALUES($user_id,'$code',500,0)
        ");
        $coupon = $code;
    } else {
        $row = mysqli_fetch_assoc($check);
        $coupon = $row['coupon_code'];
    }
}

$goal = 2000;
$percent = min(100, round(($total_points/$goal)*100));
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zero Waste Dashboard</title>

<style>
/* CSS COMPLETELY UNCHANGED */
body{
    background:
    linear-gradient(rgba(0,60,40,0.85), rgba(0,0,0,0.85)),
    url('https://images.unsplash.com/photo-1501004318641-b39e6451bec6') no-repeat center center/cover;
    background-attachment:fixed;
    font-family:Segoe UI;
    color:white;
    margin:0;
    padding:30px;
    overflow-x:hidden;
}
.card, .reward-box, .category-box{ transition:0.3s ease; }
.card:hover, .reward-box:hover, .category-box:hover{ transform:translateY(-4px); }
.back-btn{
    background:linear-gradient(90deg,#00ffd5,#00c853);
    padding:14px 28px;
    border-radius:14px;
    color:#003333;
    text-decoration:none;
    font-weight:bold;
}
.level-badge{
    position:absolute;
    top:25px;
    right:30px;
    background:linear-gradient(90deg,#00ffd5,#00c853);
    padding:18px 35px;
    border-radius:40px;
    color:#003333;
    font-weight:900;
    font-size:22px;
}
.main-container{ display:flex; gap:30px; margin-top:40px; }
.flowchart{ width:300px; }
.category-box{
    background:linear-gradient(90deg,#00ffd5,#00c853);
    padding:20px;
    border-radius:20px;
    margin:30px 0;
    text-align:center;
    font-weight:bold;
    color:#003333;
}
.dashboard{ flex:1; }
.cards{ display:flex; gap:20px; }
.card{
    flex:1;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(10px);
    padding:20px;
    border-radius:20px;
    text-align:center;
}
.donut{ width:120px; height:120px; margin:auto; position:relative; }
.donut svg{ transform:rotate(-90deg); }
.donut circle{ fill:none; stroke-width:12; }
.bg-circle{ stroke:rgba(255,255,255,0.1); }
.progress-circle{
    stroke:#00ffd5;
    stroke-dasharray:377;
    stroke-dashoffset:377;
    transition:stroke-dashoffset 1.5s ease;
}
.donut-text{
    position:absolute;
    top:50%; left:50%;
    transform:translate(-50%,-50%);
    font-weight:bold;
    font-size:18px;
}
.chart-reward-container{ display:flex; gap:25px; margin-top:30px; }
.histogram-card{ flex:2; }
.chart-container{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    height:160px;
    margin-top:20px;
}
.bar{
    width:18px;
    background:linear-gradient(to top,#00c853,#69f0ae);
    border-radius:6px 6px 0 0;
    transition:0.5s;
}
.reward-side{ flex:1; display:flex; flex-direction:column; gap:20px; }
.reward-box{
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(8px);
    padding:20px;
    border-radius:15px;
}
.claim-btn{
    margin-top:10px;
    background:#00ffd5;
    border:none;
    padding:8px 15px;
    border-radius:8px;
    font-weight:bold;
    cursor:pointer;
}
.confetti{
    position:fixed;
    width:8px;
    height:8px;
    top:-10px;
    animation:fall linear forwards;
    z-index:1;
}
@keyframes fall{
    to{ transform:translateY(100vh) rotate(720deg); opacity:0; }
}
</style>
</head>
<body>

<h2>🌿 Zero Waste Lifestyle Planner</h2>
<a href="home.php" class="back-btn">⬅ Back to Home</a>

<div class="level-badge">🏆 <?php echo $level; ?></div>

<div class="main-container">

<div class="flowchart">
<h3 style="color:#00ffd5;">📊 Category Performance</h3>
<?php foreach($categories as $type=>$pts): ?>
<div class="category-box">
<?php echo $type; ?><br>
<?php echo $pts;?> pts
</div>
<?php endforeach; ?>
</div>

<div class="dashboard">

<div class="cards">

<div class="card">
<div class="donut">
<svg width="120" height="120">
<circle class="bg-circle" cx="60" cy="60" r="60"></circle>
<circle class="progress-circle" cx="60" cy="60" r="60"></circle>
</svg>
<div class="donut-text"><?php echo $percent;?>%</div>
</div>
<h3 id="pointCounter" style="font-size:28px;"><?php echo $total_points;?></h3>
<div style="font-size:18px;">Total Points</div>
<div style="font-size:14px;color:#69f0ae;margin-top:5px;">🌟 Keep Growing Green!</div>
</div>

<div class="card">
<h3 style="font-size:26px;">📘 <?php echo $total_entries;?></h3>
<div style="font-size:18px;">Entries</div>
<div style="font-size:14px;color:#69f0ae;margin-top:5px;">👏 Great Consistency!</div>
</div>

<div class="card">
<h3 style="font-size:24px;"><?php echo $this_week;?> pts</h3>
<div style="font-size:18px;">This Week</div>
<div style="font-size:14px;margin-top:5px;">Last Week: <?php echo $last_week;?> pts</div>
<div style="font-size:14px;color:#69f0ae;margin-top:5px;">📈 Weekly Progress!</div>
</div>

<div class="card">
<h3 style="font-size:30px;">🔥 <?php echo $streak;?></h3>
<div style="font-size:20px;">Streak</div>
<div style="font-size:14px;color:#69f0ae;margin-top:5px;">🚀 You're On Fire!</div>
</div>

</div>

<div class="chart-reward-container">

<div class="card histogram-card">
<h3>📊 Weekly Eco Points</h3>
<div class="chart-container">
<?php
for($i=0;$i<7;$i++){
$height = ($weekly[$i] / $max_value) * 140;
?>
<div>
<div class="bar" style="height:<?php echo $height;?>px;"></div>
<small><?php echo $days[$i];?></small>
</div>
<?php } ?>
</div>
</div>

<div class="reward-side">

<div class="reward-box">
<h3>🎁 Special Offer</h3>
<?php if($coupon!=""){ ?>
<p><strong><?php echo $company;?></strong> gives you 20% OFF</p>
<h2><?php echo $coupon;?></h2>
<form method="POST">
<button name="claim_coupon" class="claim-btn">Claim Reward</button>
</form>
<?php } else { ?>
<p>Reach 500 points to unlock reward!</p>
<?php } ?>
</div>

</div>
</div>
</div>
</div>

<script>
// Donut
let percent = <?php echo $percent;?>;
let circle = document.querySelector(".progress-circle");
let offset = 377 - (377 * percent / 100);
setTimeout(()=>{ circle.style.strokeDashoffset = offset; },300);

// Counter
let counter = document.getElementById("pointCounter");
let target = <?php echo $total_points;?>;
let count = 0;
let speed = target / 50;
let update = setInterval(()=>{
    count += speed;
    if(count >= target){
        counter.innerText = target;
        clearInterval(update);
    } else {
        counter.innerText = Math.floor(count);
    }
},20);

// Confetti
function createConfetti(){
    const colors=["#00ff99","#00c853","#69f0ae","#b9f6ca","#ffffff"];
    for(let i=0;i<80;i++){
        let c=document.createElement("div");
        c.classList.add("confetti");
        c.style.left=Math.random()*100+"vw";
        c.style.backgroundColor=colors[Math.floor(Math.random()*colors.length)];
        c.style.animationDuration=(Math.random()*3+2)+"s";
        document.body.appendChild(c);
        setTimeout(()=>{c.remove()},5000);
    }
}
window.onload=createConfetti;
</script>

</body>
</html>
