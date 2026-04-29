<?php
session_start();
include 'db.php';

/**
 * 1. CHECK LOGIN STATUS
 * If the user isn't logged in, redirect them.
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.html");
    exit();
}

$current_user_id = $_SESSION['user_id']; 

/**
 * 2. FETCH DATA FOR THE LOGGED-IN USER
 * We use the session ID to get the specific name from your database.
 */
$sql_user = "SELECT * FROM users_table WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $current_user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
    
    // Formatting the name based on your database columns
    $first = $user_data['first_name'];
    $last = $user_data['last_name'];
    $middle = !empty($user_data['middle_name']) ? $user_data['middle_name'] : '';

    $display_name = htmlspecialchars($first . ' ' . $last);
    $full_profile_name = htmlspecialchars($first . ' ' . $middle . ' ' . $last);
    $user_email = htmlspecialchars($user_data['email']);
} else {
    // If the ID in session doesn't exist in the DB, clear session and redirect
    session_destroy();
    header("Location: ../Login/login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sky-PH Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* RESET & BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            height: 100vh;
            background: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            overflow: hidden; 
        }

        .page-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }

        /* SIDEBAR */
        .menu-toggle {
            position: fixed;
            top: 20px; left: 20px; z-index: 1100;
            color: white; font-size: 24px; cursor: pointer;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 15px; border-radius: 8px;
            backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.3);
            transition: 0.3s;
        }
        .menu-toggle:hover { background: #3498db; }

        .sidebar {
            position: fixed; left: -280px; width: 250px; height: 100vh;
            background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(25px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 80px 20px 30px 20px; color: white;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0; z-index: 1050;
        }

        .sidebar.active { left: 0; opacity: 1; }
        .sidebar h3 { text-align: center; margin-bottom: 40px; letter-spacing: 3px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }

        .menu-btn {
            width: 100%; padding: 12px; margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px; color: white; cursor: pointer; text-align: left; transition: 0.3s;
        }
        .menu-btn:hover { background: #3498db; transform: translateX(5px); }

        #logout { background: rgba(231, 76, 60, 0.6); border: none; margin-top: 50px; }
        #logout:hover { background: #e74c3c; }

        /* MAIN CONTENT */
        .main { flex: 1; padding: 25px; overflow-y: auto; color: white; padding-top: 80px; }

        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);
            padding: 15px 25px; border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .logo img { height: 45px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.5)); }

        .user { display: flex; align-items: center; gap: 15px; }
        .user-icon { font-size: 30px; color: #3498db; cursor: pointer; transition: 0.3s; }

        /* WELCOME SECTION */
        .welcome {
            background: rgba(255, 255, 255, 0.1); padding: 40px;
            border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center; margin-bottom: 30px;
        }
        .welcome h2 span { color: #3498db; }

        .welcome-search { margin-top: 25px; display: flex; justify-content: center; gap: 10px; }
        .welcome-search input { width: 60%; padding: 15px 25px; border-radius: 30px; border: none; background: rgba(255, 255, 255, 0.9); outline: none; }
        .welcome-search button { padding: 10px 30px; border-radius: 30px; border: none; background: #3498db; color: white; font-weight: bold; cursor: pointer; }

        /* CARDS */
        .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .card { background: rgba(255, 255, 255, 0.95); border-radius: 15px; overflow: hidden; color: #333; transition: 0.3s; }
        .card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
        .card img { width: 100%; height: 180px; object-fit: cover; }
        .card-body { padding: 20px; text-align: center; }
        .card-body button { width: 100%; background: #3498db; color: white; border: none; padding: 10px; border-radius: 8px; margin-top: 15px; cursor: pointer; }

        /* MODALS */
        .popup-overlay, .search-modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7); justify-content: center; align-items: center; z-index: 2000;
        }
        .popup-card, .search-modal-content { background: white; width: 350px; padding: 30px; border-radius: 20px; text-align: center; color: #333; }
        .popup-avatar { font-size: 70px; color: #3498db; margin-bottom: 15px; }
        .popup-card input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; text-align: center; }
        .logout-btn { background: #e74c3c; color: white; border: none; padding: 10px 20px; border-radius: 5px; width: 100%; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>

<div class="page-overlay"></div>

<div class="menu-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars" id="menuIcon"></i>
</div>

<div class="sidebar" id="sidebar">
    <h3>SKY-PH</h3>
    <button class="menu-btn" onclick="focusSearch()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    <button class="menu-btn" onclick="openProfile()"><i class="fa-solid fa-user"></i> Profile</button>
    <button class="menu-btn" id="logout" onclick="window.location.href='logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Log Out</button>
</div>

<div class="main">
    <div class="topbar">
        <div class="logo"><img src="image/logo.png" alt="Logo"></div>
        <div class="user">
            <span>Welcome, <strong><?= $display_name; ?></strong></span>
            <i class="fa-solid fa-circle-user user-icon" onclick="openProfile()"></i>
        </div>
    </div>

    <div class="welcome">
        <h2>Explore the Philippines, <span><?= $display_name; ?></span></h2>
        <p>Plan your trip with real-time weather updates</p>
        <div class="welcome-search">
            <input type="text" id="touristSearch" placeholder="Where do you want to go?">
            <button id="searchBtn">Search</button>
        </div>
    </div>

    <div class="cards">
        <?php
        $sql_spots = "SELECT t.*, w.temperature, w.weather_condition FROM touristspots_table t 
                      LEFT JOIN weather_forecasts w ON t.id = w.tourist_spot_id 
                      WHERE w.id = (SELECT MAX(id) FROM weather_forecasts WHERE tourist_spot_id = t.id) OR w.id IS NULL";
        $result_spots = $conn->query($sql_spots);

        while($row = $result_spots->fetch_assoc()):
            $imageSource = !empty($row['image']) ? 'data:image/jpeg;base64,' . base64_encode($row['image']) : 'image/placeholder.png';
        ?>
            <div class="card">
                <img src="<?= $imageSource; ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                <div class="card-body">
                    <h4><?= htmlspecialchars($row['name']); ?></h4>
                    <p><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($row['city']); ?>, <?= htmlspecialchars($row['province']); ?></p>
                    <button onclick="viewPlace('<?= addslashes($row['name']); ?>')">Check Weather</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="profilePopup" class="popup-overlay">
    <div class="popup-card">
        <div class="popup-avatar"><i class="fa-solid fa-circle-user"></i></div>
        <h3>My Profile</h3>
        <p>Name</p>
        <input type="text" value="<?= $full_profile_name; ?>" readonly>
        <p>Email</p>
        <input type="text" value="<?= $user_email; ?>" readonly>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Sign Out</button>
        <p style="margin-top:15px; cursor:pointer; color:#7f8c8d;" onclick="closeProfile()">Close</p>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const icon = document.getElementById('menuIcon');
        sidebar.classList.toggle('active');
        icon.classList.replace(sidebar.classList.contains('active') ? 'fa-bars' : 'fa-xmark', sidebar.classList.contains('active') ? 'fa-xmark' : 'fa-bars');
    }

    function openProfile() { document.getElementById('profilePopup').style.display = 'flex'; }
    function closeProfile() { document.getElementById('profilePopup').style.display = 'none'; }
    function focusSearch() { toggleSidebar(); document.getElementById('touristSearch').focus(); }

    window.onclick = (e) => {
        if (e.target == document.getElementById('profilePopup')) closeProfile();
    }
</script>

</body>
</html>