<?php
// ENHANCED SECURITY CHALLENGE WEB APPLICATION
// WARNING: Contains intentional vulnerabilities for educational purposes
// DO NOT use in production environments

session_start();
$output = "";
$command = "";
$error = "";
$success = "";
$search_result = "";
$profile_data = [];

// Initialize database connection - SQLite for simplicity
$db_file = 'challenge.db';
$db = new SQLite3($db_file);

// Create tables if they don't exist
$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT,
    role TEXT,
    profile_image TEXT,
    bio TEXT
)');

$db->exec('CREATE TABLE IF NOT EXISTS flags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    flag_name TEXT,
    flag_value TEXT,
    hint TEXT,
    discovered INTEGER DEFAULT 0
)');

$db->exec('CREATE TABLE IF NOT EXISTS notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    title TEXT,
    content TEXT,
    is_private INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');

// Initialize with sample data if empty
$result = $db->query('SELECT COUNT(*) as count FROM users');
$row = $result->fetchArray();
if ($row['count'] == 0) {
    // Add admin user
    $db->exec("INSERT INTO users (username, password, role, profile_image, bio) VALUES 
    ('admin', 'S3cur3P@ssw0rd!', 'administrator', 'admin.jpg', 'System administrator')");

    // Add regular user
    $db->exec("INSERT INTO users (username, password, role, profile_image, bio) VALUES 
    ('user', 'password123', 'user', 'user.jpg', 'Regular user account')");

    // Add the flags
    $db->exec("INSERT INTO flags (flag_name, flag_value, hint) VALUES 
    ('flag1', 'CTF{N3tw0rk_3num3r4t10n_m4st3r}', 'Look for network enumeration vulnerabilities')");
    $db->exec("INSERT INTO flags (flag_name, flag_value, hint) VALUES 
    ('flag2', 'CTF{C0mm4nd_1nj3ct10n_w1z4rd}', 'Command sanitization isn\'t perfect')");
    $db->exec("INSERT INTO flags (flag_name, flag_value, hint) VALUES 
    ('flag3', 'CTF{H1dd3n_F1l3_Hunt3r}', 'Not all files are visible')");
    $db->exec("INSERT INTO flags (flag_name, flag_value, hint) VALUES 
    ('flag4', 'CTF{SQL_1nj3ct10n_N1nj4}', 'SQL queries aren\'t always safe')");
    $db->exec("INSERT INTO flags (flag_name, flag_value, hint) VALUES 
    ('flag5', 'CTF{X55_M4st3r_H4ck3r}', 'Input sanitization is key')");
    $db->exec("INSERT INTO flags (flag_name, flag_value, hint) VALUES 
    ('flag6', 'CTF{LF1_P4th_Tr4v3rs4l}', 'Files can be included from various places')");
    $db->exec("INSERT INTO flags (flag_name, flag_value, hint) VALUES 
    ('flag7', 'CTF{CSRF_T0k3n_St34l3r}', 'Not all requests are legitimate')");

    // Add some notes
    $db->exec("INSERT INTO notes (user_id, title, content, is_private) VALUES 
    (1, 'Admin credentials backup', 'Reminder: Admin password is S3cur3P@ssw0rd!', 1)");
    $db->exec("INSERT INTO notes (user_id, title, content, is_private) VALUES 
    (1, 'Server configuration', 'Need to fix the file inclusion vulnerability in page parameter', 1)");
    $db->exec("INSERT INTO notes (user_id, title, content, is_private) VALUES 
    (2, 'Welcome note', 'Welcome to NetHunter! Try to find all the vulnerabilities.', 0)");
}

// Define allowed tools and their descriptions
$allowed_tools = [
    'nmap' => 'Network port scanner',
    'ping' => 'ICMP echo request utility',
    'traceroute' => 'Network path tracer',
    'whois' => 'Domain registration lookup',
    'dig' => 'DNS lookup utility',
    'host' => 'DNS lookup utility',
    'netstat' => 'Network statistics',
    'ifconfig' => 'Network interface configuration',
    'ip' => 'IP configuration utility'
];

// VULNERABLE FUNCTIONS

// Function to sanitize input - INTENTIONALLY FLAWED for challenge purposes
function sanitize_command($cmd) {
    // This sanitization is intentionally incomplete
    $cmd = str_replace(['rm -rf', 'chmod 777', 'sudo '], '', $cmd);
    return $cmd;
}

// INTENTIONALLY VULNERABLE - no CSRF token validation
function process_login($username, $password) {
    global $db, $success, $error;

    // INTENTIONALLY VULNERABLE - SQL Injection
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $db->query($query);

    if ($row = $result->fetchArray()) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $success = "Login successful! Welcome, " . htmlentities($row['username']) . ".";
        return true;
    } else {
        $error = "Invalid credentials. Access denied.";
        return false;
    }
}

// INTENTIONALLY VULNERABLE - Local File Inclusion
function include_page($page) {
    // No proper sanitization or path restriction
    if (file_exists($page)) {
        include($page);
    } else {
        echo "<div class='alert alert-error'><i class='fas fa-exclamation-circle'></i><div>Page not found!</div></div>";
    }
}

// INTENTIONALLY VULNERABLE - XSS
function display_user_input($input) {
    // No HTML escaping
    return $input;
}

// INTENTIONALLY VULNERABLE - Blind SQL Injection
function search_notes($query) {
    global $db;
    $results = [];

    // VULNERABLE - no prepared statement
    $sql = "SELECT * FROM notes WHERE is_private = 0 OR user_id = " .
        (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) .
        " AND content LIKE '%$query%'";

    $result = $db->query($sql);
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $results[] = $row;
    }

    return $results;
}

// Process login requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    process_login($username, $password);
}

// Process logout requests
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Process command execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['command'])) {
    $command = $_POST['command'];
    $tool = strtok($command, ' ');

    // Basic restrictions - INTENTIONALLY BYPASSABLE
    if (array_key_exists($tool, $allowed_tools) || ($_SESSION['logged_in'] ?? false)) {
        $sanitized_command = sanitize_command($command);
        ob_start();
        // VULNERABLE CODE - intentional command injection vulnerability
        system($sanitized_command . " 2>&1");
        $output = ob_get_clean();

        // Check if any flags are discovered through command output
        check_flags_in_output($output);
    } else {
        $error = "Access denied. Only specific network tools are allowed.";
    }
}

// Process note search
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $notes = search_notes($search_query);

    if (count($notes) > 0) {
        $search_result = "<div class='search-results'>";
        foreach ($notes as $note) {
            $search_result .= "<div class='note-card'>";
            $search_result .= "<h3>" . htmlspecialchars($note['title']) . "</h3>";
            // VULNERABLE - XSS possible here
            $search_result .= "<div class='note-content'>" . display_user_input($note['content']) . "</div>";
            $search_result .= "</div>";
        }
        $search_result .= "</div>";
    } else {
        $search_result = "<div class='alert alert-error'>No notes found matching your query.</div>";
    }
}

// Process profile view
if (isset($_GET['profile'])) {
    $profile_id = $_GET['profile'];
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->bindValue(':id', $profile_id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $profile_data = $row;
    }
}

// Process note creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_note']) && isset($_SESSION['logged_in'])) {
    $title = $_POST['note_title'] ?? '';
    $content = $_POST['note_content'] ?? '';
    $is_private = isset($_POST['is_private']) ? 1 : 0;

    if (!empty($title) && !empty($content)) {
        $stmt = $db->prepare('INSERT INTO notes (user_id, title, content, is_private) VALUES (:user_id, :title, :content, :is_private)');
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
        $stmt->bindValue(':is_private', $is_private, SQLITE3_INTEGER);
        $stmt->execute();

        $success = "Note created successfully!";
    } else {
        $error = "Title and content are required!";
    }
}

// Check if flags are discovered in the output
function check_flags_in_output($output) {
    global $db;

    $result = $db->query('SELECT flag_name, flag_value FROM flags WHERE discovered = 0');
    while ($row = $result->fetchArray()) {
        if (strpos($output, $row['flag_value']) !== false) {
            // Mark flag as discovered
            $stmt = $db->prepare('UPDATE flags SET discovered = 1 WHERE flag_name = :name');
            $stmt->bindValue(':name', $row['flag_name'], SQLITE3_TEXT);
            $stmt->execute();
        }
    }
}

// Get flags discovered count
function get_flags_discovered() {
    global $db;
    $result = $db->query('SELECT COUNT(*) as count FROM flags WHERE discovered = 1');
    $row = $result->fetchArray();
    return $row['count'];
}

// Get total flags count
function get_total_flags() {
    global $db;
    $result = $db->query('SELECT COUNT(*) as count FROM flags');
    $row = $result->fetchArray();
    return $row['count'];
}

// Get server information for display
$server_info = [
    'hostname' => gethostname(),
    'os' => php_uname('s') . ' ' . php_uname('r'),
    'user' => get_current_user(),
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT']
];

// Secret files with hints - can be discovered through directory traversal
if (!file_exists('hints.txt')) {
    file_put_contents('hints.txt', "Check the /admin directory and try to find vulnerabilities in the command sanitization.");
}

// Create challenge directories and files
if (!file_exists('admin')) {
    mkdir('admin', 0755);
    file_put_contents('admin/config.txt', "Admin username: admin\nPassword hint: The password contains 'S3cur3' and ends with '!'");
    file_put_contents('admin/flag1.txt', "Congratulations! You found flag 1: CTF{N3tw0rk_3num3r4t10n_m4st3r}");

    // Create a more hidden challenge directory
    mkdir('admin/secret', 0755);
    file_put_contents('admin/secret/flag2.txt', "Well done! You found flag 2: CTF{C0mm4nd_1nj3ct10n_w1z4rd}");

    // Create an even more hidden challenge file
    file_put_contents('.hidden_flag3.txt', "Excellent work! You found the most hidden flag: CTF{H1dd3n_F1l3_Hunt3r}");

    // Create SQL Injection flag
    file_put_contents('admin/secret/database_backup.sql', "-- Database backup\n-- Contains flag 4: CTF{SQL_1nj3ct10n_N1nj4}");

    // Create LFI flag
    mkdir('includes', 0755);
    file_put_contents('includes/secret_page.php', "<?php\n// This file contains flag 6: CTF{LF1_P4th_Tr4v3rs4l}\n?>");

    // Create XSS flag
    file_put_contents('js/validate.js', "// Form validation\n// Contains flag 5: CTF{X55_M4st3r_H4ck3r}");

    // Create CSRF flag
    file_put_contents('admin/secret/csrf_token.php', "<?php\n// CSRF protection not implemented\n// Flag 7: CTF{CSRF_T0k3n_St34l3r}\n?>");
}

// Create templates directory for LFI challenge
if (!file_exists('templates')) {
    mkdir('templates', 0755);
    file_put_contents('templates/home.php', "<h2>Welcome to NetHunter</h2><p>This is the home page template.</p>");
    file_put_contents('templates/about.php', "<h2>About NetHunter</h2><p>NetHunter is a security challenge platform.</p>");
    file_put_contents('templates/contact.php', "<h2>Contact Us</h2><p>Email: admin@nethunter.local</p>");
}

// LFI vulnerability via page parameter
$page_to_include = isset($_GET['page']) ? $_GET['page'] : 'templates/home.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetHunter | Enhanced Security Challenge Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2b2d42;
            --secondary: #8d99ae;
            --accent: #ef233c;
            --accent-light: #ff6b6b;
            --light: #edf2f4;
            --dark: #212130;
            --success: #06d6a0;
            --warning: #ffd166;
            --danger: #ef476f;
            --info: #118ab2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Fira Code', monospace, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark);
            color: var(--light);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .header {
            background-color: var(--primary);
            padding: 20px;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--accent);
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .subtitle {
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .status-bar {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: var(--secondary);
        }

        .status-item i {
            color: var(--accent-light);
        }

        /* Navigation */
        .nav {
            background-color: var(--primary);
            margin-bottom: 2px;
            padding: 0 20px;
        }

        .nav ul {
            display: flex;
            list-style: none;
        }

        .nav li {
            padding: 15px 20px;
            position: relative;
        }

        .nav a {
            color: var(--light);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .nav a:hover {
            color: var(--accent);
        }

        .nav li.active {
            background-color: rgba(239, 35, 60, 0.1);
        }

        .nav li.active a {
            color: var(--accent);
        }

        .main {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
        }

        .sidebar {
            background-color: var(--primary);
            padding: 20px;
            border-radius: 0 0 0 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .content {
            background-color: #2f3142;
            padding: 20px;
            border-radius: 0 0 8px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card {
            background-color: var(--primary);
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            background-color: rgba(0, 0, 0, 0.15);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card-header h2 {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: var(--secondary);
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            background-color: var(--dark);
            border: 1px solid #3d405b;
            border-radius: 4px;
            color: var(--light);
            font-family: 'Fira Code', monospace;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(239, 35, 60, 0.2);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 10px 0;
        }

        input[type="checkbox"] {
            accent-color: var(--accent);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--accent);
            color: var(--light);
        }

        .btn-primary:hover {
            background-color: var(--accent-light);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background-color: #a1a9b7;
        }

        .output-container {
            background-color: var(--dark);
            color: #a4ffb0;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Fira Code', monospace;
            height: 350px;
            overflow-y: auto;
            border: 1px solid #3d405b;
        }

        .output-header {
            border-bottom: 1px solid #404258;
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: var(--secondary);
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
        }

        pre {
            margin: 0;
            white-space: pre-wrap;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .server-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-card {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 6px;
            border-left: 3px solid var(--info);
        }

        .info-title {
            color: var(--secondary);
            font-size: 0.8rem;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 0.9rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .tool-list {
            list-style-type: none;
        }

        .tool-item {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 8px;
            background-color: rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .tool-item:hover {
            background-color: rgba(239, 35, 60, 0.1);
        }

        .tool-name {
            color: var(--accent-light);
            font-weight: 600;
        }

        .tool-desc {
            color: var(--secondary);
            font-size: 0.85rem;
            margin-top: 3px;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background-color: rgba(239, 71, 111, 0.2);
            border-left: 4px solid var(--danger);
            color: #ff8fa3;
        }

        .alert-success {
            background-color: rgba(6, 214, 160, 0.2);
            border-left: 4px solid var(--success);
            color: #67e8b8;
        }

        .challenge-progress {
            margin-top: 20px;
        }

        .progress-bar {
            height: 8px;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-fill {
            height: 100%;
            background-color: var(--accent);
            width: 0%;
            transition: width 0.5s;
        }

        .hint-box {
            background-color: rgba(255, 209, 102, 0.1);
            border-left: 4px solid var(--warning);
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }

        .auth-section {
            margin-bottom: 20px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            padding: 20px;
            color: var(--secondary);
            font-size: 0.8rem;
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-form input {
            flex: 1;
        }

        .note-card {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 3px solid var(--info);
        }

        .note-card h3 {
            color: var(--accent-light);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .note-content {
            color: var(--light);
            font-size: 0.9rem;
        }

        .profile-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .profile-image i {
            font-size: 3rem;
            color: var(--dark);
        }

        .profile-details {
            text-align: center;
        }

        .profile-username {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .profile-role {
            color: var(--accent-light);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .profile-bio {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: transparent;
            border: none;
            color: var(--secondary);
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .tab.active {
            color: var(--accent);
            border-bottom: 2px solid var(--accent);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .main {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-radius: 0;
            }

            .content {
                border-radius: 0 0 8px 8px;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .status-bar {
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav ul {
                flex-wrap: wrap;
            }
        }

        /* Terminal typing effect */
        .typing-effect::after {
            content: '|';
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        /* Flag discovery animation */
        @keyframes flagFound {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .flag-found {
            animation: flagFound 0.5s ease-in-out;
            background-color: rgba(6, 214, 160, 0.2) !important;
            border-left-color: var(--success) !important;
        }

        /* Vulnerable comment feature - XSS */
        .comments-section {
            margin-top: 20px;
        }

        .comment {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: var(--secondary);
        }

        .comment-author {
            font-weight: 600;
            color: var(--accent-light);
        }

        /* File browser for LFI challenge */
        .file-browser {
            border: 1px solid #3d405b;
            border-radius: 4px;
            overflow: hidden;
        }

        .file-browser-header {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #3d405b;
        }

        .file-path {
            font-size: 0.85rem;
            color: var(--secondary);
        }

        .file-list {
            list-style-type: none;
            padding: 0;
        }

        .file-item {
            padding: 8px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-item:hover {
            background-color: rgba(0, 0, 0, 0.3);
        }

        .file-item i {
            color: var(--secondary);
        }

        .file-item.directory i {
            color: var(--warning);
        }

        .file-viewer {
            background-color: var(--dark);
            padding: 15px;
            border-radius: 0 0 4px 4px;
            font-family: 'Fira Code', monospace;
            height: 300px;
            overflow-y: auto;
            color: var(--light);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container">
    <header class="header">
        <div class="logo">
            <i class="fas fa-shield-alt fa-2x"></i>
            <div>
                <h1>NetHunter</h1>
                <div class="subtitle">Enhanced Security Challenge Platform v3.0.0</div>
            </div>
        </div>
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-server"></i>
                <span><?php echo $server_info['hostname']; ?></span>
            </div>
            <div class="status-item">
                <i class="fas fa-user-secret"></i>
                <span><?php echo isset($_SESSION['logged_in']) ? ($_SESSION['role'] == 'administrator' ? "Admin" : "User") : "Guest"; ?></span>
            </div>
            <div class="status-item">
                <i class="fas fa-clock"></i>
                <span><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
            <div class="status-item">
                <i class="fas fa-flag"></i>
                <span>Flags: <?php echo get_flags_discovered(); ?>/<?php echo get_total_flags(); ?></span>
            </div>
        </div>
    </header>

    <nav class="nav">
        <ul>
            <li class="<?php echo (!isset($_GET['page']) || $_GET['page'] == 'templates/home.php') ? 'active' : ''; ?>">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'templates/about.php') ? 'active' : ''; ?>">
                <a href="index.php?page=templates/about.php"><i class="fas fa-info-circle"></i> About</a>
            </li>
            <li class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'tools') ? 'active' : ''; ?>">
                <a href="index.php?section=tools"><i class="fas fa-tools"></i> Tools</a>
            </li>
            <li class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'notes') ? 'active' : ''; ?>">
                <a href="index.php?section=notes"><i class="fas fa-sticky-note"></i> Notes</a>
            </li>
            <?php if (isset($_SESSION['logged_in'])): ?>
                <li class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'profile') ? 'active' : ''; ?>">
                    <a href="index.php?section=profile&profile=<?php echo $_SESSION['user_id']; ?>"><i class="fas fa-user"></i> Profile</a>
                </li>
            <?php endif; ?>
            <li class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'file_explorer') ? 'active' : ''; ?>">
                <a href="index.php?section=file_explorer"><i class="fas fa-folder"></i> Files</a>
            </li>
        </ul>
    </nav>

    <div class="main">
        <div class="sidebar">
            <?php if (!isset($_SESSION['logged_in'])): ?>
                <div class="card auth-section">
                    <div class="card-header">
                        <h2><i class="fas fa-lock"></i> Authentication</h2>
                    </div>
                    <div class="card-body">
                        <!-- No CSRF protection here - intentional vulnerability -->
                        <form method="POST">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" placeholder="Enter username">
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter password">
                            </div>
                            <button type="submit" name="login" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-user-circle"></i> Account</h2>
                    </div>
                    <div class="card-body">
                        <p>Logged in as: <strong><?php echo htmlentities($_SESSION['username']); ?></strong></p>
                        <p>Role: <strong><?php echo htmlentities($_SESSION['role']); ?></strong></p>
                        <a href="?logout=1" class="btn btn-secondary" style="margin-top: 10px;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-search"></i> Search Notes</h2>
                </div>
                <div class="card-body">
                    <!-- SQL Injection vulnerability in search -->
                    <form method="GET" class="search-form">
                        <input type="hidden" name="section" value="notes">
                        <input type="text" name="search" placeholder="Search notes..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-tools"></i> Available Tools</h2>
                </div>
                <div class="card-body">
                    <ul class="tool-list">
                        <?php foreach ($allowed_tools as $tool => $description): ?>
                            <li class="tool-item">
                                <div>
                                    <div class="tool-name"><?php echo $tool; ?></div>
                                    <div class="tool-desc"><?php echo $description; ?></div>
                                </div>
                                <i class="fas fa-terminal"></i>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="challenge-progress">
                <div class="card-header" style="border-radius: 8px 8px 0 0;">
                    <h2><i class="fas fa-flag"></i> Challenge Progress</h2>
                </div>
                <div class="card-body" style="background-color: var(--primary); border-radius: 0 0 8px 8px;">
                    <div>Flags Found: <?php echo get_flags_discovered(); ?>/<?php echo get_total_flags(); ?></div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo (get_flags_discovered() / get_total_flags()) * 100; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo $success; ?></div>
                </div>
            <?php endif; ?>

            <?php
            // Section handling
            $section = $_GET['section'] ?? 'home';

            switch ($section) {
                case 'tools':
                    // Command execution section
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-terminal"></i> Command Execution</h2>
                            <span class="status-item">
                                    <?php if (isset($_SESSION['logged_in'])): ?>
                                        <i class="fas fa-unlock text-success"></i> Admin Mode
                                    <?php else: ?>
                                        <i class="fas fa-lock text-warning"></i> Restricted Mode
                                    <?php endif; ?>
                                </span>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="command">Enter Network Command</label>
                                    <input type="text" id="command" name="command"
                                           placeholder="<?php echo isset($_SESSION['logged_in']) ? 'Enter any system command...' : 'Enter allowed network tool command (e.g., nmap localhost)'; ?>"
                                           value="<?php echo htmlspecialchars($command); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Execute
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if ($output): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-terminal"></i> Command Output</h2>
                        </div>
                        <div class="card-body">
                            <div class="output-container">
                                <div class="output-header">
                                    <span class="typing-effect">$ <?php echo htmlspecialchars($command); ?></span>
                                    <span>Executed at: <?php echo date('H:i:s'); ?></span>
                                </div>
                                <pre><?php echo htmlspecialchars($output); ?></pre>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                    <?php
                    break;

                case 'notes':
                    // Notes section - with SQL injection vulnerability
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-sticky-note"></i> Notes</h2>
                        </div>
                        <div class="card-body">
                            <div class="tabs">
                                <button class="tab active" data-tab="view-notes">View Notes</button>
                                <?php if (isset($_SESSION['logged_in'])): ?>
                                    <button class="tab" data-tab="create-note">Create Note</button>
                                <?php endif; ?>
                            </div>

                            <div class="tab-content active" id="view-notes">
                                <?php if ($search_result): ?>
                                    <?php echo $search_result; ?>
                                <?php else: ?>
                                    <?php
                                    // Display public notes and user's private notes
                                    $notes_query = "SELECT notes.*, users.username FROM notes 
                                                      LEFT JOIN users ON notes.user_id = users.id 
                                                      WHERE is_private = 0" .
                                        (isset($_SESSION['user_id']) ? " OR user_id = " . $_SESSION['user_id'] : "");
                                    $notes_result = $db->query($notes_query);

                                    if ($notes_result->fetchArray()) {
                                        $notes_result->reset();
                                        echo "<div class='search-results'>";
                                        while ($note = $notes_result->fetchArray(SQLITE3_ASSOC)) {
                                            echo "<div class='note-card'>";
                                            echo "<h3>" . htmlspecialchars($note['title']) . "</h3>";
                                            echo "<div class='note-header'>";
                                            echo "<span>By: " . htmlspecialchars($note['username']) . "</span>";
                                            echo "<span>" . ($note['is_private'] == 1 ? '<i class="fas fa-lock"></i> Private' : '<i class="fas fa-globe"></i> Public') . "</span>";
                                            echo "</div>";
                                            // XSS vulnerability - using display_user_input which doesn't sanitize
                                            echo "<div class='note-content'>" . display_user_input($note['content']) . "</div>";
                                            echo "</div>";
                                        }
                                        echo "</div>";
                                    } else {
                                        echo "<div class='alert alert-error'>No notes found.</div>";
                                    }
                                    ?>
                                <?php endif; ?>
                            </div>

                            <?php if (isset($_SESSION['logged_in'])): ?>
                                <div class="tab-content" id="create-note">
                                    <form method="POST">
                                        <div class="form-group">
                                            <label for="note_title">Title</label>
                                            <input type="text" id="note_title" name="note_title" placeholder="Note title">
                                        </div>
                                        <div class="form-group">
                                            <label for="note_content">Content</label>
                                            <textarea id="note_content" name="note_content" placeholder="Note content"></textarea>
                                        </div>
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="is_private" name="is_private" checked>
                                            <label for="is_private">Private note</label>
                                        </div>
                                        <button type="submit" name="create_note" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Note
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    break;

                case 'profile':
                    // Profile section
                    if (!empty($profile_data)):
                        ?>
                        <div class="card">
                            <div class="card-header">
                                <h2><i class="fas fa-user"></i> User Profile</h2>
                            </div>
                            <div class="card-body">
                                <div class="profile-card">
                                    <div class="profile-image">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="profile-details">
                                        <div class="profile-username"><?php echo htmlspecialchars($profile_data['username']); ?></div>
                                        <div class="profile-role"><?php echo htmlspecialchars($profile_data['role']); ?></div>
                                        <!-- Potential XSS vulnerability here - bio not sanitized -->
                                        <div class="profile-bio"><?php echo display_user_input($profile_data['bio']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comments section with XSS vulnerability -->
                        <div class="card">
                            <div class="card-header">
                                <h2><i class="fas fa-comments"></i> Comments</h2>
                            </div>
                            <div class="card-body">
                                <div class="comments-section">
                                    <?php
                                    // For demonstration, add some comments with XSS payload
                                    $comments = [
                                        [
                                            'author' => 'user',
                                            'date' => '2023-05-12',
                                            'content' => 'Great profile! Keep up the good work.'
                                        ],
                                        [
                                            'author' => 'hacker',
                                            'date' => '2023-05-13',
                                            'content' => 'Check out this link <a href="#" onclick="alert(\'XSS\')">click me</a>'
                                        ]
                                    ];

                                    foreach ($comments as $comment) {
                                        echo '<div class="comment">';
                                        echo '<div class="comment-header">';
                                        echo '<span class="comment-author">' . htmlspecialchars($comment['author']) . '</span>';
                                        echo '<span class="comment-date">' . htmlspecialchars($comment['date']) . '</span>';
                                        echo '</div>';
                                        // XSS vulnerability - display_user_input doesn't sanitize
                                        echo '<div class="comment-content">' . display_user_input($comment['content']) . '</div>';
                                        echo '</div>';
                                    }
                                    ?>

                                    <!-- Comment form with no CSRF protection -->
                                    <form method="POST">
                                        <div class="form-group">
                                            <label for="comment">Add a comment</label>
                                            <textarea id="comment" name="comment" placeholder="Your comment"></textarea>
                                        </div>
                                        <button type="submit" name="add_comment" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Post Comment
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php
                    else:
                        echo '<div class="alert alert-error">Profile not found!</div>';
                    endif;
                    break;

                case 'file_explorer':
                    // File explorer with LFI vulnerability
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-folder-open"></i> File Explorer</h2>
                        </div>
                        <div class="card-body">
                            <div class="file-browser">
                                <div class="file-browser-header">
                                    <div class="file-path">/templates/</div>
                                    <!-- LFI vulnerability in the viewer -->
                                    <form method="GET" style="display: inline;">
                                        <input type="hidden" name="section" value="file_explorer">
                                        <input type="text" name="view" placeholder="Enter file path..." style="width: 300px;">
                                        <button type="submit" class="btn btn-primary btn-sm">View</button>
                                    </form>
                                </div>
                                <div class="file-list">
                                    <?php
                                    $template_files = [
                                        ['name' => 'home.php', 'type' => 'file'],
                                        ['name' => 'about.php', 'type' => 'file'],
                                        ['name' => 'contact.php', 'type' => 'file'],
                                        ['name' => '..', 'type' => 'directory']
                                    ];

                                    foreach ($template_files as $file) {
                                        echo '<div class="file-item ' . $file['type'] . '">';
                                        if ($file['type'] == 'directory') {
                                            echo '<i class="fas fa-folder"></i>';
                                        } else {
                                            echo '<i class="fas fa-file-code"></i>';
                                        }
                                        echo htmlspecialchars($file['name']);
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <?php if (isset($_GET['view'])): ?>
                                    <div class="file-viewer">
                                        <?php
                                        $file_to_view = $_GET['view'];
                                        if (file_exists($file_to_view)) {
                                            $content = file_get_contents($file_to_view);
                                            echo '<pre>' . htmlspecialchars($content) . '</pre>';
                                        } else {
                                            echo '<div class="alert alert-error">File not found: ' . htmlspecialchars($file_to_view) . '</div>';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    break;

                default:
                    // Default home page with LFI vulnerability
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-network-wired"></i> System Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="server-info">
                                <?php foreach ($server_info as $key => $value): ?>
                                    <div class="info-card">
                                        <div class="info-title"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></div>
                                        <div class="info-value"><?php echo $value; ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Content with LFI vulnerability -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-file-alt"></i> Page Content</h2>
                        </div>
                        <div class="card-body">
                            <?php include_page($page_to_include); ?>
                        </div>
                    </div>
                <?php
            }
            ?>
        </div>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> NetHunter Enhanced Security Challenge Platform | For educational purposes only</p>
        <p>Find hidden flags and exploit vulnerabilities to complete the challenge</p>
    </div>
</div>

<script>
    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));

                // Add active class to clicked tab
                this.classList.add('active');

                // Hide all tab contents
                const tabContents = document.querySelectorAll('.tab-content');
                tabContents.forEach(content => content.classList.remove('active'));

                // Show content for clicked tab
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // XSS vulnerability demonstration - checking for flags in the output
        function checkForXSSFlag() {
            const pageContent = document.body.innerHTML;
            if (pageContent.includes('CTF{X55_M4st3r_H4ck3r}')) {
                const flagsFound = document.querySelector('.challenge-progress .card-body div:first-child');
                const currentFlags = parseInt(flagsFound.textContent.match(/\d+/)[0]);
                const totalFlags = parseInt(flagsFound.textContent.match(/\/(\d+)/)[1]);

                // Update progress if flag not already counted
                if (!sessionStorage.getItem('xss_flag_found')) {
                    sessionStorage.setItem('xss_flag_found', 'true');
                    const newFlags = currentFlags + 1;
                    flagsFound.textContent = `Flags Found: ${newFlags}/${totalFlags}`;

                    const progressBar = document.querySelector('.progress-fill');
                    progressBar.style.width = ((newFlags / totalFlags) * 100) + '%';

                    // Show notification
                    alert('Congratulations! You found the XSS flag: CTF{X55_M4st3r_H4ck3r}');
                }
            }
        }

        // Call the function after a slight delay to ensure DOM is fully loaded
        setTimeout(checkForXSSFlag, 500);

        // CSRF vulnerability demonstration
        // This code would normally be on attacker's site
        function createCsrfPayload() {
            // Example: Create an invisible form that would submit on load
            // This is for demonstration only and won't actually execute
            const csrfCode = `
                <form id="csrf-form" action="http://victim-site/change_password.php" method="POST" style="display:none">
                    <input type="hidden" name="new_password" value="hacked123">
                    <input type="submit" value="Submit">
                </form>
                <script>
                    document.getElementById("csrf-form").submit();
                </script>
`;

console.log("CSRF Payload:", csrfCode);
}
});
</script>
</body>
</html>