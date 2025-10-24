<?php
session_start();

//ensure user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../includes/db.php';

//get statistics for reports
$reports = [];

//getting total patients
$result = $conn->query("SELECT COUNT(*) as count FROM PATIENT_USERS");
$reports['total_patients'] = $result->fetch_assoc()['count'];

//same for doctors
$result = $conn->query("SELECT COUNT(*) as count FROM DOCTOR_USERS");
$reports['total_doctors'] = $result->fetch_assoc()['count'];

//same for active admins
$result = $conn->query("SELECT COUNT(*) as count FROM ADMIN_USERS WHERE is_active=1");
$reports['total_admins'] = $result->fetch_assoc()['count'];

//querying and counting total hospital visits
$result = $conn->query("SELECT COUNT(*) as count FROM HOSPITAL_VISITS");
$reports['total_visits'] = $result->fetch_assoc()['count'];

//querying and counting total hospital visits in past 30 days
$result = $conn->query("SELECT COUNT(*) as count FROM HOSPITAL_VISITS WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$reports['visits_last_30_days'] = $result->fetch_assoc()['count'];

//querying and counting total hospital visits in past 7 days
$result = $conn->query("SELECT COUNT(*) as count FROM HOSPITAL_VISITS WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$reports['visits_last_7_days'] = $result->fetch_assoc()['count'];

//querying and counting total activities in past 24 hours
$result = $conn->query("SELECT COUNT(*) as count FROM ACTIVITY_LOG WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$reports['activities_last_24h'] = $result->fetch_assoc()['count'];

//querying and counting total activities in past 7 days
$result = $conn->query("SELECT COUNT(*) as count FROM ACTIVITY_LOG WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$reports['activities_last_7_days'] = $result->fetch_assoc()['count'];

//most active users in the past 7 days
$result = $conn->query("
    SELECT user_id, user_role, COUNT(*) as activity_count 
    FROM ACTIVITY_LOG 
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
    GROUP BY user_id, user_role 
    ORDER BY activity_count DESC 
    LIMIT 10
");
$reports['most_active_users'] = $result->fetch_all(MYSQLI_ASSOC);

//reporting recent patient and doctor registrations
$result = $conn->query("
    SELECT 'patient' as role, COUNT(*) as count 
    FROM PATIENT_USERS 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    UNION ALL
    SELECT 'doctor' as role, COUNT(*) as count 
    FROM DOCTOR_USERS 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$reports['recent_registrations'] = $result->fetch_all(MYSQLI_ASSOC);

//querying and counting suspended patients
$result = $conn->query("SELECT COUNT(*) as count FROM PATIENT_USERS WHERE is_suspended = 1");
$reports['suspended_patients'] = $result->fetch_assoc()['count'];

//querying and counting suspended doctors
$result = $conn->query("SELECT COUNT(*) as count FROM DOCTOR_USERS WHERE is_suspended = 1");
$reports['suspended_doctors'] = $result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports</title>
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
        
        .nav-button.danger {
            background: #dc3545;
        }
        
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .reports-grid-bottom {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .report-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .report-card h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            color: #666;
        }
        
        .stat-value {
            font-weight: 600;
            color: #333;
        }
        
        .activity-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .activity-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-user {
            font-weight: 500;
            color: #667eea;
        }
        
        .activity-count {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>System Reports</h1>
            <p>Comprehensive system analytics and health metrics</p>
        </div>
        
        <div class="admin-nav">
            <a href="dashboard.php" class="nav-button">Dashboard</a>
            <a href="users.php" class="nav-button">Manage Users</a>
            <a href="activity.php" class="nav-button">Activity Logs</a>
            <a href="../logout.php" class="nav-button danger">Logout</a>
        </div>
        
        <div class="reports-grid">
            <div class="report-card">
                <h3>User Statistics</h3>
                <div class="stat-item">
                    <span class="stat-label">Total Patients</span>
                    <span class="stat-value"><?php echo $reports['total_patients']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Total Doctors</span>
                    <span class="stat-value"><?php echo $reports['total_doctors']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Active Admins</span>
                    <span class="stat-value"><?php echo $reports['total_admins']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Suspended Patients</span>
                    <span class="stat-value"><?php echo $reports['suspended_patients']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Suspended Doctors</span>
                    <span class="stat-value"><?php echo $reports['suspended_doctors']; ?></span>
                </div>
            </div>
            
            <div class="report-card">
                <h3>Visit Statistics</h3>
                <div class="stat-item">
                    <span class="stat-label">Total Visits</span>
                    <span class="stat-value"><?php echo $reports['total_visits']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Last 30 Days</span>
                    <span class="stat-value"><?php echo $reports['visits_last_30_days']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Last 7 Days</span>
                    <span class="stat-value"><?php echo $reports['visits_last_7_days']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Avg per Day (30d)</span>
                    <span class="stat-value"><?php echo round($reports['visits_last_30_days'] / 30, 1); ?></span>
                </div>
            </div>
            
            <div class="report-card">
                <h3>System Activity</h3>
                <div class="stat-item">
                    <span class="stat-label">Activities (24h)</span>
                    <span class="stat-value"><?php echo $reports['activities_last_24h']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Activities (7d)</span>
                    <span class="stat-value"><?php echo $reports['activities_last_7_days']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Avg per Day (7d)</span>
                    <span class="stat-value"><?php echo round($reports['activities_last_7_days'] / 7, 1); ?></span>
                </div>
            </div>
        </div>
        
        <div class="reports-grid-bottom">
            <div class="report-card">
                <h3>Recent Registrations (30 days)</h3>
                <?php foreach ($reports['recent_registrations'] as $reg): ?>
                    <div class="stat-item">
                        <span class="stat-label"><?php echo ucfirst($reg['role']); ?>s</span>
                        <span class="stat-value"><?php echo $reg['count']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="report-card">
                <h3>Most Active Users (7 days)</h3>
                <div class="activity-list">
                    <?php if (empty($reports['most_active_users'])): ?>
                        <p>No activity data available.</p>
                    <?php else: ?>
                        <?php foreach ($reports['most_active_users'] as $user): ?>
                            <div class="activity-item">
                                <div class="activity-user">
                                    <?php echo ucfirst($user['user_role']); ?> #<?php echo $user['user_id']; ?>
                                </div>
                                <div class="activity-count">
                                    <?php echo $user['activity_count']; ?> activities
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
