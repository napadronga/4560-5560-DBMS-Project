<?php
session_start();

//confirm user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php"); //redirect if not admin
    exit();
}

include '../includes/db.php';

//for handling user data filters
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

//building where query
$where_conditions = [];
$params = [];

if ($filter_action) {
    $where_conditions[] = "action_type = ?";
    $params[] = $filter_action;
}

if ($filter_role) {
    $where_conditions[] = "user_role = ?";
    $params[] = $filter_role;
}

if ($filter_date) {
    $where_conditions[] = "DATE(timestamp) = ?";
    $params[] = $filter_date;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

//getting activity logs, most recent first
$sql = "SELECT * FROM ACTIVITY_LOG $where_clause ORDER BY timestamp DESC LIMIT 100";
$stmt = $conn->prepare($sql);

if ($params){
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$activities = $result->fetch_all(MYSQLI_ASSOC);

//action types for filter
$action_types = $conn->query("SELECT DISTINCT action_type FROM ACTIVITY_LOG ORDER BY action_type")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- page specific styles -->
    <style>
        /* main container */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* header section */
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
        
        /* navigation bar */
        .admin-nav {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        /* navigation buttons/'bar' */
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
        
        /* red buttons >:) */
        .nav-button.danger {
            background: #dc3545;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-weight: 500;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-button {
            background: #667eea;
            color: white;
            height: 50px;
            width: 100px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transform: translateY(16px)
        }
        
        .activity-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .role-patient {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .role-doctor {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .role-admin {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .action-type {
            font-weight: 500;
            color: #333;
        }
        
        .timestamp {
            color: #666;
            font-size: 0.9rem;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Activity Logs</h1>
        </div>
        
        <div class="admin-nav">
            <a href="dashboard.php" class="nav-button">Dashboard</a>
            <a href="users.php" class="nav-button">Manage Users</a>
            <a href="reports.php" class="nav-button">System Reports</a>
            <a href="../logout.php" class="nav-button danger">Logout</a>
        </div>
        
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="action">Action Type</label>
                    <select name="action" id="action">
                        <option value="">All Actions</option>
                        <?php foreach ($action_types as $type): ?>
                            <option value="<?php echo $type['action_type']; ?>" 
                                    <?php echo $filter_action === $type['action_type'] ? 'selected' : ''; ?>>
                                <?php echo $type['action_type']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="role">User Role</label>
                    <select name="role" id="role">
                        <option value="">All Roles</option>
                        <option value="patient" <?php echo $filter_role === 'patient' ? 'selected' : ''; ?>>Patient</option>
                        <option value="doctor" <?php echo $filter_role === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                        <option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date">Date</label>
                    <input type="date" name="date" id="date" value="<?php echo $filter_date; ?>">
                </div>
                
                <button type="submit" class="filter-button">Filter</button>
                <a href="activity.php" class="nav-button secondary">Clear</a>
            </form>
        </div>
        
        <div class="activity-table">
            <?php if (empty($activities)): ?>
                <div class="no-data">
                    <h3>No activity logs found</h3>
                    <p>No activities match your current filters.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td class="timestamp">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?>
                                </td>
                                <td><?php echo $activity['user_id'] ?: 'System'; ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $activity['user_role']; ?>">
                                        <?php echo ucfirst($activity['user_role']); ?>
                                    </span>
                                </td>
                                <td class="action-type">
                                    <?php echo htmlspecialchars($activity['action_type']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($activity['action_description']); ?></td>
                                <td><?php echo htmlspecialchars($activity['ip_address'] ?: 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
