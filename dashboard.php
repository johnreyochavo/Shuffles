<?php
session_start();

// --- 1. CONFIGURATION & DATABASE CONNECTION ---
$host = "sv97.ifastnet.com";
$user = "wohroxas_ochavo";
$pass = "ochavo2025";
$db   = "wohroxas_shuffleDB";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// --- 2. AUTHENTICATION LOGIC ---
$admin_user = "admin";
$admin_pass = "ochavo2025";
$error_msg = "";

// Handle Login
if (isset($_POST['do_login'])) {
    if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error_msg = "Invalid Username or Password";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: dashboard.php");
    exit();
}

// Check Login State
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// --- 3. BACKEND ACTIONS (Only if logged in) ---
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Update Reservation
    if (isset($_POST['action']) && $_POST['action'] === 'update_reservation') {
        $id = intval($_POST['res_id']);
        $status = $conn->real_escape_string($_POST['status']);
        $remarks = $conn->real_escape_string($_POST['admin_remarks']);
        $conn->query("UPDATE reservations SET status='$status', admin_remarks='$remarks' WHERE id=$id");
        header("Location: dashboard.php"); exit();
    }

    // Archive Contact Message
    if (isset($_POST['action']) && $_POST['action'] === 'archive_contact') {
        $id = intval($_POST['msg_id']);
        $conn->query("UPDATE contact_messages SET is_archived=1 WHERE id=$id");
        header("Location: dashboard.php"); exit();
    }

    // Delete Contact Message
    if (isset($_POST['action']) && $_POST['action'] === 'delete_contact') {
        $id = intval($_POST['msg_id']);
        $conn->query("DELETE FROM contact_messages WHERE id=$id");
        header("Location: dashboard.php"); exit();
    }
}

// --- 4. DATA FETCHING (Only if logged in) ---
$stats = [];
$reservations = [];
$contacts = [];
$archived_contacts = [];
$chart_dates = [];
$chart_counts = [];

if ($is_logged_in) {
    // KPI Stats
    $stats['total_res'] = $conn->query("SELECT COUNT(*) FROM reservations")->fetch_row()[0];
    $stats['pending_res'] = $conn->query("SELECT COUNT(*) FROM reservations WHERE status='Pending'")->fetch_row()[0];
    $stats['new_msgs'] = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE is_archived=0")->fetch_row()[0];

    // Fetch Reservations
    $res_result = $conn->query("SELECT * FROM reservations ORDER BY created_at DESC");
    while($row = $res_result->fetch_assoc()) { $reservations[] = $row; }

    // Fetch Active Contacts
    $cont_result = $conn->query("SELECT * FROM contact_messages WHERE is_archived=0 ORDER BY created_at DESC");
    while($row = $cont_result->fetch_assoc()) { $contacts[] = $row; }

    // Fetch Archived Contacts
    $arc_result = $conn->query("SELECT * FROM contact_messages WHERE is_archived=1 ORDER BY created_at DESC");
    while($row = $arc_result->fetch_assoc()) { $archived_contacts[] = $row; }

    // Analytics Data (Reservations per day)
    $chart_sql = "SELECT DATE_FORMAT(reserve_date, '%Y-%m-%d') as rdate, COUNT(*) as count 
                  FROM reservations GROUP BY rdate ORDER BY rdate ASC LIMIT 7";
    $chart_res = $conn->query($chart_sql);
    while($row = $chart_res->fetch_assoc()) {
        $chart_dates[] = $row['rdate'];
        $chart_counts[] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shuffles</title>
    <!-- Icons & Fonts -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bg-dark: #1a1a1a;
            --bg-card: #252525;
            --primary: #d62828;
            --text-light: #f5f5f5;
            --text-muted: #aaa;
            --gold: #d4af37;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #0a0a0a; color: var(--text-light); }

        /* --- LOGIN MODAL STYLE --- */
        .login-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .login-box {
            background: var(--bg-card); padding: 3rem; border-radius: 15px;
            border: 1px solid #333; text-align: center; width: 100%; max-width: 400px;
            box-shadow: 0 0 20px rgba(214, 40, 40, 0.2);
        }
        .login-box h2 { color: var(--primary); margin-bottom: 1.5rem; }
        .login-input {
            width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px;
            border: 1px solid #444; background: #111; color: white;
        }
        .btn {
            padding: 10px 20px; border-radius: 5px; border: none; cursor: pointer;
            font-weight: 600; transition: 0.3s;
        }
        .btn-primary { background: var(--primary); color: white; width: 100%; }
        .btn-primary:hover { background: #b01e1e; }
        .error-txt { color: #ff4d4d; margin-bottom: 10px; font-size: 0.9rem; }

        /* --- DASHBOARD LAYOUT --- */
        .dashboard-container { display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar {
            width: 250px; background: var(--bg-card); padding: 20px;
            border-right: 1px solid #333; position: fixed; height: 100%;
        }
        .brand { font-size: 1.5rem; font-weight: 700; color: var(--gold); margin-bottom: 30px; display: block; }
        .nav-item {
            display: block; padding: 12px; color: var(--text-muted);
            text-decoration: none; border-radius: 5px; margin-bottom: 5px;
        }
        .nav-item:hover, .nav-item.active { background: var(--primary); color: white; }
        .nav-item i { margin-right: 10px; }

        /* Main Content */
        .main-content { margin-left: 250px; flex: 1; padding: 30px; }
        .header-dash { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: var(--bg-card); padding: 20px; border-radius: 10px; border: 1px solid #333; }
        .card h3 { font-size: 2rem; color: var(--primary); }
        .card p { color: var(--text-muted); }

        /* Graph Section */
        .graph-container { background: var(--bg-card); padding: 20px; border-radius: 10px; margin-bottom: 30px; height: 350px; border: 1px solid #333; }

        /* Tables */
        .table-wrapper { background: var(--bg-card); padding: 20px; border-radius: 10px; overflow-x: auto; margin-bottom: 30px; border: 1px solid #333; }
        .table-header { display: flex; justify-content: space-between; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        th { color: var(--gold); }
        tr:hover { background: #2a2a2a; }
        
        /* Badges */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; }
        .badge-pending { background: #b08d00; color: #000; }
        .badge-confirmed { background: #155724; color: #d4edda; }
        .badge-cancelled { background: #721c24; color: #f8d7da; }

        /* Action Buttons */
        .action-btn { padding: 5px 10px; border-radius: 5px; border: none; cursor: pointer; color: white; font-size: 0.9rem; }
        .btn-edit { background: #2196F3; }
        .btn-archive { background: #FF9800; }
        .btn-delete { background: #f44336; }
        
        /* Tab Switching */
        .tab-buttons { margin-bottom: 15px; }
        .tab-btn { background: transparent; border: 1px solid var(--primary); color: white; padding: 8px 16px; cursor: pointer; margin-right: 5px; }
        .tab-btn.active { background: var(--primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Edit Modal */
        #editModal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); display: none; justify-content: center; align-items: center; z-index: 2000;
        }

        @media(max-width: 768px) {
            .sidebar { width: 70px; padding: 10px; }
            .brand span, .nav-item span { display: none; }
            .main-content { margin-left: 70px; padding: 15px; }
            .login-box { margin: 20px; }
        }
    </style>
</head>
<body>

    <!-- === STATE 1: LOGGED OUT (SHOW LOGIN) === -->
    <?php if (!$is_logged_in): ?>
    <div class="login-overlay">
        <div class="login-box">
            <h2>Shuffles Admin</h2>
            <?php if($error_msg): ?>
                <p class="error-txt"><?php echo $error_msg; ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" class="login-input" placeholder="Username" required>
                <input type="password" name="password" class="login-input" placeholder="Password" required>
                <button type="submit" name="do_login" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
    <?php else: ?>

    <!-- === STATE 2: LOGGED IN (SHOW DASHBOARD) === -->
    <div class="dashboard-container">
        
        <!-- Sidebar -->
        <nav class="sidebar">
            <a href="#" class="brand"><i class='bx bxs-drink'></i> <span>Shuffles</span></a>
            <a href="#overview" class="nav-item active" onclick="showSection('overview')"><i class='bx bxs-dashboard'></i> <span>Overview</span></a>
            <a href="#reservations" class="nav-item" onclick="showSection('reservations')"><i class='bx bxs-calendar'></i> <span>Reservations</span></a>
            <a href="#contacts" class="nav-item" onclick="showSection('contacts')"><i class='bx bxs-envelope'></i> <span>Messages</span></a>
            <a href="?logout=true" class="nav-item" style="color: #ff4d4d;"><i class='bx bx-log-out'></i> <span>Logout</span></a>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            
            <div class="header-dash">
                <h2>Dashboard Overview</h2>
                <span>Welcome, Admin</span>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="card">
                    <h3><?php echo $stats['total_res']; ?></h3>
                    <p>Total Reservations</p>
                </div>
                <div class="card">
                    <h3><?php echo $stats['pending_res']; ?></h3>
                    <p>Pending Actions</p>
                </div>
                <div class="card">
                    <h3><?php echo $stats['new_msgs']; ?></h3>
                    <p>New Messages</p>
                </div>
            </div>

            <!-- SECTION: Analytics Graph -->
            <div id="section-overview">
                <div class="graph-container">
                    <canvas id="resChart"></canvas>
                </div>
            </div>

            <!-- SECTION: Reservations Table -->
            <div id="section-reservations" class="table-wrapper">
                <div class="table-header">
                    <h3>Manage Reservations</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $res): ?>
                        <tr>
                            <td><?php echo $res['reserve_date'] . ' ' . date('h:i A', strtotime($res['reserve_time'])); ?></td>
                            <td><?php echo htmlspecialchars($res['full_name']); ?><br><small style="color:#777"><?php echo $res['phone']; ?></small></td>
                            <td><?php echo $res['guests']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($res['status'] ?? 'pending'); ?>">
                                    <?php echo $res['status'] ?? 'Pending'; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($res['admin_remarks'] ?? '-'); ?></td>
                            <td>
                                <button class="action-btn btn-edit" onclick="openEditModal(<?php echo $res['id']; ?>, '<?php echo $res['status']; ?>', '<?php echo htmlspecialchars($res['admin_remarks'] ?? ''); ?>')">
                                    <i class='bx bxs-edit'></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- SECTION: Contacts -->
            <div id="section-contacts" class="table-wrapper" style="margin-top: 30px;">
                <div class="table-header">
                    <h3>Contact Messages</h3>
                </div>
                
                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="openTab('activeMsgs')">Inbox</button>
                    <button class="tab-btn" onclick="openTab('archivedMsgs')">Archived</button>
                </div>

                <!-- Active Messages -->
                <div id="activeMsgs" class="tab-content active">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($contacts as $msg): ?>
                            <tr>
                                <td><?php echo date('M d', strtotime($msg['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?><br><small style="color:#777"><?php echo $msg['email']; ?></small></td>
                                <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                <td><?php echo substr(htmlspecialchars($msg['message']), 0, 50) . '...'; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="archive_contact">
                                        <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="action-btn btn-archive" title="Archive"><i class='bx bxs-archive-in'></i></button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="action" value="delete_contact">
                                        <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete" title="Delete"><i class='bx bxs-trash'></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Archived Messages -->
                <div id="archivedMsgs" class="tab-content">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>From</th>
                                <th>Message</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($archived_contacts as $msg): ?>
                            <tr>
                                <td><?php echo date('M d', strtotime($msg['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><?php echo substr(htmlspecialchars($msg['message']), 0, 50) . '...'; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently Delete?');">
                                        <input type="hidden" name="action" value="delete_contact">
                                        <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete"><i class='bx bxs-trash'></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Edit Modal HTML -->
    <div id="editModal">
        <div class="login-box" style="text-align: left;">
            <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                <h3 style="color:var(--primary)">Update Reservation</h3>
                <i class='bx bx-x' style="font-size:1.5rem; cursor:pointer;" onclick="closeEditModal()"></i>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_reservation">
                <input type="hidden" name="res_id" id="edit_res_id">
                
                <label style="color: #aaa; font-size: 0.9rem;">Status</label>
                <select name="status" id="edit_status" class="login-input">
                    <option value="Pending">Pending</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Cancelled">Cancelled</option>
                    <option value="Completed">Completed</option>
                </select>

                <label style="color: #aaa; font-size: 0.9rem;">Admin Remarks</label>
                <textarea name="admin_remarks" id="edit_remarks" class="login-input" rows="4"></textarea>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // --- 1. Graph Logic ---
        const ctx = document.getElementById('resChart').getContext('2d');
        const resChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_dates); ?>,
                datasets: [{
                    label: 'Daily Reservations',
                    data: <?php echo json_encode($chart_counts); ?>,
                    borderColor: '#d62828',
                    backgroundColor: 'rgba(214, 40, 40, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: 'white' } }
                },
                scales: {
                    y: { grid: { color: '#333' }, ticks: { color: '#aaa' }, beginAtZero: true },
                    x: { grid: { color: '#333' }, ticks: { color: '#aaa' } }
                }
            }
        });

        // --- 2. Modal Logic ---
        function openEditModal(id, status, remarks) {
            document.getElementById('edit_res_id').value = id;
            document.getElementById('edit_status').value = status || 'Pending';
            document.getElementById('edit_remarks').value = remarks;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // --- 3. Tabs Logic ---
        function openTab(tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove('active');
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).style.display = "block";
            event.currentTarget.classList.add("active");
        }

        // --- 4. Smooth Scroll for Sidebar ---
        function showSection(id) {
            // In a real single-page app, we might toggle visibility.
            // For now, we scroll to it.
            if(id === 'overview') window.scrollTo(0,0);
            else document.getElementById('section-'+id).scrollIntoView();
            
            // Highlight sidebar
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }
    </script>

    <?php endif; ?>
</body>
</html>