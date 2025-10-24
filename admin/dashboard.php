<?php
session_start();

//ensure use is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../includes/db.php';

//get statistics
$stats = [];

//querying and counting total patients
$result = $conn->query("SELECT COUNT(*) as count FROM PATIENT_USERS");
$stats['total_patients'] = $result->fetch_assoc()['count'];

//same for doctors
$result = $conn->query("SELECT COUNT(*) as count FROM DOCTOR_USERS");
$stats['total_doctors'] = $result->fetch_assoc()['count'];

//same for active admins
$result = $conn->query("SELECT COUNT(*) as count FROM ADMIN_USERS WHERE is_active=1");
$stats['total_admins'] = $result->fetch_assoc()['count'];

//querying and counting total hospitalvisits
$result = $conn->query("SELECT COUNT(*) as count FROM HOSPITAL_VISITS");
$stats['total_visits'] = $result->fetch_assoc()['count'];

//querying and counting total hospital visits this week
$result = $conn->query("SELECT COUNT(*) as count FROM HOSPITAL_VISITS WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['visits_this_week'] = $result->fetch_assoc()['count'];

//average vists per week
$result = $conn->query("SELECT COUNT(*) as count FROM HOSPITAL_VISITS WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 28 DAY)");
$stats['avg_visits_per_week'] = round($result->fetch_assoc()['count'] / 4, 1);

//recent activity
$result = $conn->query("SELECT * FROM ACTIVITY_LOG ORDER BY timestamp DESC LIMIT 15"); //limiting to 15 activites
$recent_activities = $result->fetch_all(MYSQLI_ASSOC);

//logins from last seven days
$result = $conn->query("SELECT COUNT(*) as count FROM ACTIVITY_LOG WHERE action_type = 'LOGIN' AND timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['recent_logins'] = $result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #ffffff 0%, #4facfe 100%);
            color: #333;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .admin-header p {
            margin: 10px 0 0 0;
            opacity: 0.8;
            color: #555;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .nav-button {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .nav-button:hover {
            background: #5a6fd8;
        }
        
        .nav-button.secondary {
            background: #6c757d;
        }
        
        .nav-button.secondary:hover {
            background: #5a6268;
        }
        
        .nav-button.danger {
            background: #dc3545;
        }
        
        .nav-button.danger:hover {
            background: #c82333;
        }
        
        .recent-activity {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .recent-activity h3 {
            margin-top: 0;
            color: #333;
        }
        
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-time {
            color: #666;
            font-size: 0.9rem;
        }
        
        .activity-description {
            margin: 5px 0;
        }
        
        .activity-user {
            color: #667eea;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Administrator Dashboard</h1>
        </div>
        
        <div class="admin-nav">
            <a href="users.php" class="nav-button">Manage Users</a>
            <a href="activity.php" class="nav-button">Activity Logs</a>
            <a href="reports.php" class="nav-button">System Reports</a>
            <a href="../logout.php" class="nav-button danger">Logout</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_patients']; ?></div>
                <div class="stat-label">Total Patients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_doctors']; ?></div>
                <div class="stat-label">Total Doctors</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_visits']; ?></div>
                <div class="stat-label">Total Visits</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['visits_this_week']; ?></div>
                <div class="stat-label">Visits This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['avg_visits_per_week']; ?></div>
                <div class="stat-label">Avg Visits/Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['recent_logins']; ?></div>
                <div class="stat-label">Recent Logins (7 days)</div>
            </div>
        </div>
        
        <div class="recent-activity">
            <h3>Recent System Activity</h3>
            <?php if (empty($recent_activities)): ?>
                <p>No recent activity to display.</p>
            <?php else: ?>
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-description">
                            <span class="activity-user"><?php echo htmlspecialchars($activity['user_role']); ?></span>
                            <?php echo htmlspecialchars($activity['action_description']); ?>
                        </div>
                        <div class="activity-time">
                            <?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
