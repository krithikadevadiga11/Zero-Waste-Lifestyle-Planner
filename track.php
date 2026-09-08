<?php
include "db/config.php";

date_default_timezone_set("Asia/Kolkata");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$earned_points = 0;

/* ================= HANDLE FORM SUBMIT ================= */
if(isset($_POST['submit']) && isset($_POST['waste_type'])){

    $type   = $_POST['waste_type'];
    $amount = floatval($_POST['amount']);
    $unit   = $_POST['unit'];
    $method = $_POST['method'];
    $notes  = isset($_POST['notes']) ? $_POST['notes'] : "";

    /* ===== POINT LOGIC ===== */
    if($type == "Plastic"){
        $earned_points = max(1, floor($amount)) * 1;
    }
    elseif($type == "Organic"){
        $earned_points = ceil($amount) * 3;
    }
    elseif($type == "Recyclable"){
        $earned_points = ceil($amount) * 4;
    }
    elseif($type == "E-Waste"){
        $earned_points = ceil($amount) * 6;
    }

    /* INSERT LOG */
    $stmt = $conn->prepare("INSERT INTO waste_logs 
        (user_id, waste_type, amount, unit, disposal_method, notes, eco_points)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdsssi", $user_id, $type, $amount, $unit, $method, $notes, $earned_points);
    $stmt->execute();
    $stmt->close();

    /* ================= STREAK LOGIC ================= */

    $today = date("Y-m-d");

    $check = $conn->prepare("SELECT last_entry, streak FROM users WHERE id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $resultUser = $check->get_result();
    $userData = $resultUser->fetch_assoc();
    $check->close();

    $last_entry = $userData['last_entry'];
    $current_streak = $userData['streak'];

    if($last_entry == $today){
        // Already logged today → no streak change
        $new_streak = $current_streak;
    }
    elseif($last_entry == date("Y-m-d", strtotime("-1 day"))){
        // Consecutive day
        $new_streak = $current_streak + 1;
    }
    else{
        // Break in streak
        $new_streak = 1;
    }

    $update = $conn->prepare("UPDATE users SET streak=?, last_entry=? WHERE id=?");
    $update->bind_param("isi", $new_streak, $today, $user_id);
    $update->execute();
    $update->close();

    $message = "Yay! You earned $earned_points Eco Points 🎉";
}

/* ================= TODAY TOTAL ================= */
$total_query = $conn->prepare("
SELECT COALESCE(SUM(eco_points),0) as total 
FROM waste_logs 
WHERE user_id = ? 
AND DATE(created_at) = CURDATE()
");
$total_query->bind_param("i", $user_id);
$total_query->execute();
$result = $total_query->get_result();
$row = $result->fetch_assoc();
$today_total = $row['total'];
$total_query->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>Log Your Waste</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}

body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:linear-gradient(135deg,#063d1e,#0b5d2a,#021c0f);
color:white;
}

.container{
width:400px;
padding:35px;
border-radius:25px;
background:rgba(42,102,55,0.85);
backdrop-filter:blur(15px);
box-shadow:0 0 40px rgba(0,255,136,0.4);
animation:fadeIn 0.6s ease;
}

@keyframes fadeIn{
from{opacity:0;transform:translateY(20px);}
to{opacity:1;transform:translateY(0);}
}

h1{text-align:center;margin-bottom:10px;}

.date{text-align:center;margin-bottom:15px;font-size:14px;color:#c8facc;}

.success{
background:#00c853;
padding:10px;
border-radius:10px;
margin-bottom:10px;
text-align:center;
animation:pop 0.4s ease;
}

@keyframes pop{
from{transform:scale(0.8);opacity:0;}
to{transform:scale(1);opacity:1;}
}

.total{text-align:center;margin-bottom:15px;font-size:14px;color:#b9f6ca;}

.grid{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:10px;
margin-bottom:15px;
}

.card{
padding:15px;
border-radius:15px;
background:rgba(255,255,255,0.15);
text-align:center;
cursor:pointer;
transition:0.3s;
}

.card:hover{
transform:translateY(-5px);
box-shadow:0 0 15px #00ff88;
}

.card.active{
background:linear-gradient(to right,#00c853,#2e7d32);
}

input,select,textarea{
width:100%;
padding:10px;
margin-top:10px;
border-radius:10px;
border:none;
outline:none;
}

button{
width:100%;
padding:12px;
margin-top:15px;
border:none;
border-radius:10px;
background:linear-gradient(to right,#00c853,#2e7d32);
color:white;
cursor:pointer;
}

button:hover{
transform:scale(1.05);
box-shadow:0 0 20px #00ff88;
}

.hidden{display:none;}

.back{text-align:center;margin-top:10px;}
.back a{color:#b9f6ca;text-decoration:none;}
</style>
</head>

<body>

<div class="container">

<h1>🌿 Log Your Waste</h1>
<div class="date">Date: <?php echo date("Y-m-d"); ?></div>

<?php if($message!=""){ ?>
<div class="success"><?php echo $message; ?></div>
<?php } ?>

<div class="total">Today’s Total Eco Points: <?php echo $today_total; ?></div>

<form method="POST">

<input type="hidden" name="waste_type" id="waste_type">

<div class="grid">
<div class="card" onclick="selectType('Plastic',this)">♻ Plastic</div>
<div class="card" onclick="selectType('Organic',this)">🌱 Organic</div>
<div class="card" onclick="selectType('Recyclable',this)">📦 Recyclable</div>
<div class="card" onclick="selectType('E-Waste',this)">💻 E-Waste</div>
</div>

<div id="formArea" class="hidden">

<input type="number" step="0.01" name="amount" placeholder="Enter Amount" required>

<select name="unit" required>
<option value="">Select Unit</option>
<option>kg</option>
<option>grams</option>
<option>items</option>
</select>

<select name="method" id="methodSelect" required>
<option value="">Select Disposal Method</option>
</select>

<textarea name="notes" placeholder="Optional Notes"></textarea>

</div>

<button type="submit" name="submit">✔ Log Waste Entry</button>

</form>

<div class="back">
<a href="home.php">← Back to Home</a>
</div>

</div>

<script>
function selectType(type,element){

document.querySelectorAll('.card').forEach(c=>c.classList.remove('active'));
element.classList.add('active');

document.getElementById("waste_type").value = type;
document.getElementById("formArea").classList.remove("hidden");

document.querySelector("[name='amount']").value="";
document.querySelector("[name='unit']").selectedIndex=0;
document.querySelector("[name='notes']").value="";

let method = document.getElementById("methodSelect");
method.innerHTML = "<option value=''>Select Disposal Method</option>";

if(type=="Plastic"){
method.innerHTML += "<option>Recycled</option><option>Reused</option><option>Thrown in Bin</option>";
}
else if(type=="Organic"){
method.innerHTML += "<option>Composted</option><option>Fed to Animals</option><option>Thrown in Bin</option>";
}
else if(type=="Recyclable"){
method.innerHTML += "<option>Recycling Center</option><option>Sold to Scrap</option>";
}
else if(type=="E-Waste"){
method.innerHTML += "<option>Authorized Center</option><option>Stored Safely</option>";
}
}
</script>

</body>
</html>