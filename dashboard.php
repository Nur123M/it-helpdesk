<?php

session_start();
require_once "config/database.php";

/* Check if user is logged in */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* Get ticket statistics */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM tickets
    WHERE ticket_user_id = ?
");
$stmt->execute([$user_id]);
$total_tickets = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM tickets
    WHERE ticket_user_id = ?
    AND ticket_status = 'Pending'
");
$stmt->execute([$user_id]);
$pending_tickets = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM tickets
    WHERE ticket_user_id = ?
    AND ticket_status = 'In Progress'
");
$stmt->execute([$user_id]);
$progress_tickets = $stmt->fetchColumn();


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM tickets
    WHERE ticket_user_id = ?
    AND ticket_status = 'Resolved'
");
$stmt->execute([$user_id]);
$resolved_tickets = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard · IT Help Desk</title>

    <!-- Google Fonts for a clean, modern feel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">

    <style>

        /* ---------- GLOBAL RESET & VARIABLES ---------- */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --brand-dark: #0b2b5c;
            --brand-primary: #1e3a8a;
            --brand-light: #eef3fc;
            --accent: #3b82f6;
            --accent-soft: #dbeafe;
            --text-dark: #0f172a;
            --text-muted: #475569;
            --card-bg: #ffffff;
            --shadow-sm: 0 8px 20px rgba(0, 0, 0, 0.04), 0 2px 6px rgba(0, 0, 0, 0.02);
            --shadow-md: 0 20px 35px -8px rgba(0, 20, 60, 0.12), 0 8px 18px -6px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 30px 50px -12px rgba(0, 20, 60, 0.2);
            --radius-md: 14px;
            --radius-lg: 28px;
            --radius-sm: 10px;
            --border-light: #e2e8f0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: #fafcff;
            color: var(--text-dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;

            /* subtle background pattern */
            background-image: radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.03) 0%, transparent 45%),
                              radial-gradient(circle at 85% 80%, rgba(30, 58, 138, 0.03) 0%, transparent 45%);
            background-size: cover;
            background-attachment: fixed;
        }

        /* ---------- NAVBAR ---------- */
        .navbar {
            background: linear-gradient(105deg, #0b2b5c 0%, #1e3a8a 100%);
            color: white;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(2px);
        }

        .logo {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
        }

        .logo::before {
            content: "🛡️";
            font-size: 1.8rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 10px 20px;
            border-radius: 40px;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            letter-spacing: -0.01em;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        .nav-links a.logout-link {
            background: linear-gradient(105deg, #dc2626, #ef4444);
            border-color: transparent;
            box-shadow: 0 8px 18px -8px rgba(220, 38, 38, 0.5);
        }

        .nav-links a.logout-link:hover {
            background: linear-gradient(105deg, #b91c1c, #dc2626);
            box-shadow: 0 16px 26px -10px rgba(220, 38, 38, 0.6);
        }

        /* ---------- CONTAINER ---------- */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 48px 32px 64px;
            width: 100%;
        }

        /* ---------- WELCOME ---------- */
        .welcome {
            margin-bottom: 40px;
        }

        .welcome h1 {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #0b2b5c 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 8px;
        }

        .welcome p {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.6;
            letter-spacing: -0.01em;
        }

        /* ---------- STATS GRID ---------- */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 44px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 28px 26px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-color: #cbdffc;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card h3 {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* subtle stat icons via pseudo */
        .stat-card h3::before {
            font-size: 1.1rem;
            line-height: 1;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.06));
        }

        .stat-card:nth-child(1) h3::before { content: "🎫"; }
        .stat-card:nth-child(2) h3::before { content: "⏳"; }
        .stat-card:nth-child(3) h3::before { content: "🔄"; }
        .stat-card:nth-child(4) h3::before { content: "✅"; }

        .stat-number {
            font-size: 2.6rem;
            font-weight: 700;
            letter-spacing: -0.04em;
            color: var(--brand-dark);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        /* Accent colors for specific cards */
        .stat-card:nth-child(2) .stat-number { color: #b45309; }
        .stat-card:nth-child(3) .stat-number { color: #1e40af; }
        .stat-card:nth-child(4) .stat-number { color: #15803d; }

        /* ---------- ACTION CARDS ---------- */
        .actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 28px;
        }

        .action-card {
            background: var(--card-bg);
            padding: 40px 36px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-md);
            border-color: #cbdffc;
        }

        .action-card:hover::before {
            opacity: 1;
        }

        .action-card h2 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--brand-dark);
            margin-bottom: 14px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .action-card h2::before {
            font-size: 2.8rem;
            line-height: 1;
            display: block;
            filter: drop-shadow(0 6px 8px rgba(0, 0, 0, 0.06));
        }

        .action-card:nth-child(1) h2::before {
            content: "🎫";
        }

        .action-card:nth-child(2) h2::before {
            content: "📋";
        }

        .action-card p {
            color: var(--text-muted);
            line-height: 1.7;
            font-size: 1.02rem;
            margin-bottom: 26px;
            flex-grow: 1;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 28px;
            border-radius: 60px;
            text-decoration: none;
            color: white;
            background: linear-gradient(105deg, #1e3a8a, #2563eb);
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 12px 24px -8px rgba(37, 99, 235, 0.4);
            border: 2px solid transparent;
            align-self: flex-start;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: "→";
            font-size: 1.2rem;
            transition: transform 0.25s ease;
            margin-left: 4px;
        }

        .btn:hover {
            background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
            transform: translateY(-3px);
            box-shadow: 0 22px 30px -12px rgba(37, 99, 235, 0.5);
        }

        .btn:hover::after {
            transform: translateX(4px);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .secondary-btn {
            background: linear-gradient(105deg, #334155, #475569);
            box-shadow: 0 12px 24px -8px rgba(51, 65, 85, 0.4);
        }

        .secondary-btn:hover {
            background: linear-gradient(105deg, #1e293b, #334155);
            box-shadow: 0 22px 30px -12px rgba(51, 65, 85, 0.5);
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 1000px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 900px) {
            .welcome h1 {
                font-size: 1.9rem;
            }

            .welcome p {
                font-size: 1.05rem;
            }

            .stat-number {
                font-size: 2.2rem;
            }

            .stat-card {
                padding: 24px 22px;
            }

            .action-card {
                padding: 32px 28px;
            }
        }

        @media (max-width: 700px) {
            .navbar {
                padding: 14px 20px;
            }

            .logo {
                font-size: 1.4rem;
            }

            .logo::before {
                font-size: 1.6rem;
            }

            .nav-links a {
                padding: 8px 14px;
                font-size: 0.85rem;
            }

            .container {
                padding: 32px 20px 48px;
            }

            .welcome {
                margin-bottom: 32px;
            }

            .welcome h1 {
                font-size: 1.7rem;
            }

            .welcome p {
                font-size: 1rem;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 18px;
                margin-bottom: 34px;
            }

            .stat-card {
                padding: 22px 20px;
                border-radius: 20px;
            }

            .stat-number {
                font-size: 2rem;
            }

            .actions {
                grid-template-columns: 1fr;
                gap: 22px;
            }

            .action-card {
                padding: 32px 28px;
                border-radius: 20px;
            }

            .action-card h2 {
                font-size: 1.35rem;
            }

            .action-card h2::before {
                font-size: 2.4rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 12px 16px;
            }

            .logo {
                font-size: 1.2rem;
            }

            .logo::before {
                font-size: 1.4rem;
            }

            .nav-links a {
                padding: 7px 12px;
                font-size: 0.8rem;
            }

            .container {
                padding: 24px 16px 40px;
            }

            .welcome h1 {
                font-size: 1.45rem;
            }

            .welcome p {
                font-size: 0.94rem;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
                gap: 14px;
                margin-bottom: 28px;
            }

            .stat-card {
                padding: 18px 16px;
                border-radius: 16px;
            }

            .stat-card h3 {
                font-size: 0.68rem;
                gap: 5px;
            }

            .stat-card h3::before {
                font-size: 0.95rem;
            }

            .stat-number {
                font-size: 1.7rem;
            }

            .action-card {
                padding: 26px 22px;
                border-radius: 20px;
            }

            .action-card h2 {
                font-size: 1.25rem;
            }

            .action-card h2::before {
                font-size: 2.2rem;
            }

            .action-card p {
                font-size: 0.94rem;
            }

            .btn {
                padding: 13px 22px;
                font-size: 0.94rem;
            }
        }

        /* focus outline for accessibility */
        a:focus-visible,
        button:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 3px;
            border-radius: 60px;
        }

    </style>

</head>

<body>

    <!-- NAVBAR -->
    <div class="navbar">

        <div class="logo">
            IT Help Desk
        </div>

        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="my_tickets.php">My Tickets</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>

    </div>


    <!-- MAIN CONTENT -->
    <div class="container">

        <!-- WELCOME -->
        <div class="welcome">

            <h1>
                Welcome, <?= htmlspecialchars($_SESSION["user_name"]) ?>!
            </h1>

            <p>
                Manage your IT support requests from your dashboard.
            </p>

        </div>


        <!-- STATISTICS -->
        <div class="stats">

            <div class="stat-card">

                <h3>Total tickets</h3>

                <div class="stat-number">
                    <?= $total_tickets ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>Pending</h3>

                <div class="stat-number">
                    <?= $pending_tickets ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>In progress</h3>

                <div class="stat-number">
                    <?= $progress_tickets ?>
                </div>

            </div>


            <div class="stat-card">

                <h3>Resolved</h3>

                <div class="stat-number">
                    <?= $resolved_tickets ?>
                </div>

            </div>

        </div>


        <!-- ACTIONS -->
        <div class="actions">

            <div class="action-card">

                <h2>Report an issue</h2>

                <p>
                    Experiencing a technical problem?
                    Submit a support ticket and our IT team will assist you.
                </p>

                <a href="create_ticket.php" class="btn">
                    Create ticket
                </a>

            </div>


            <div class="action-card">

                <h2>My tickets</h2>

                <p>
                    View your submitted tickets, check their status,
                    and communicate with the IT support team.
                </p>

                <a href="my_tickets.php" class="btn secondary-btn">
                    View my tickets
                </a>

            </div>

        </div>

    </div>

</body>

</html>