<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Check login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check admin role
|--------------------------------------------------------------------------
*/

if ($_SESSION["user_role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Ticket statistics
|--------------------------------------------------------------------------
*/

$total_stmt = $conn->query(
    "SELECT COUNT(*) FROM tickets"
);

$total_tickets = $total_stmt->fetchColumn();

$pending_stmt = $conn->query(
    "SELECT COUNT(*) FROM tickets
     WHERE ticket_status = 'Pending'"
);

$pending_tickets = $pending_stmt->fetchColumn();

$progress_stmt = $conn->query(
    "SELECT COUNT(*) FROM tickets
     WHERE ticket_status = 'In Progress'"
);

$progress_tickets = $progress_stmt->fetchColumn();

$resolved_stmt = $conn->query(
    "SELECT COUNT(*) FROM tickets
     WHERE ticket_status = 'Resolved'"
);

$resolved_tickets = $resolved_stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Total users
|--------------------------------------------------------------------------
*/

$users_stmt = $conn->query(
    "SELECT COUNT(*) FROM users
     WHERE user_role = 'user'"
);

$total_users = $users_stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard · IT Help Desk</title>

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

        /* ---------- NAVBAR (Admin variant) ---------- */
        .navbar {
            background: linear-gradient(105deg, #0b1f3b 0%, #172554 100%);
            color: white;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(2px);
            border-bottom: 1px solid rgba(59, 130, 246, 0.15);
        }

        .brand-block {
            display: flex;
            flex-direction: column;
            gap: 2px;
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

        .admin-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #93c5fd;
            margin-left: 2px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .user-greeting {
            font-size: 0.95rem;
            font-weight: 500;
            color: #e0e7ff;
            letter-spacing: -0.01em;
        }

        .user-greeting strong {
            color: white;
            font-weight: 600;
        }

        .logout {
            background: linear-gradient(105deg, #dc2626, #ef4444);
            color: white;
            padding: 11px 24px;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.92rem;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 8px 18px -8px rgba(220, 38, 38, 0.5);
            border: 2px solid transparent;
        }

        .logout:hover {
            background: linear-gradient(105deg, #b91c1c, #dc2626);
            transform: translateY(-2px);
            box-shadow: 0 16px 26px -10px rgba(220, 38, 38, 0.6);
        }

        /* ---------- CONTAINER ---------- */
        .container {
            max-width: 1280px;
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
            background: linear-gradient(135deg, #0b1f3b 0%, #2563eb 100%);
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

        /* ---------- STATS CARDS GRID ---------- */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 44px;
        }

        .card {
            background: var(--card-bg);
            padding: 30px 28px;
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

        .card::before {
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

        .card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-color: #cbdffc;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-title {
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
        .card-title::before {
            font-size: 1.1rem;
            line-height: 1;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.06));
        }

        .card:nth-child(1) .card-title::before { content: "🎫"; }
        .card:nth-child(2) .card-title::before { content: "⏳"; }
        .card:nth-child(3) .card-title::before { content: "🔄"; }
        .card:nth-child(4) .card-title::before { content: "✅"; }
        .card:nth-child(5) .card-title::before { content: "👥"; }

        .number {
            font-size: 2.6rem;
            font-weight: 700;
            letter-spacing: -0.04em;
            color: var(--brand-dark);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        /* Accent colors for specific cards */
        .card:nth-child(2) .number { color: #b45309; }
        .card:nth-child(3) .number { color: #1e40af; }
        .card:nth-child(4) .number { color: #15803d; }
        .card:nth-child(5) .number { color: #6d28d9; }

        /* ---------- ACTIONS ---------- */
        .actions {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .action {
            background: linear-gradient(105deg, #1e3a8a, #2563eb);
            color: white;
            padding: 16px 30px;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 12px 24px -8px rgba(37, 99, 235, 0.4);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .action::before {
            font-size: 1.2rem;
            line-height: 1;
        }

        .action:first-child::before {
            content: "🎫";
        }

        .action:hover {
            background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
            transform: translateY(-3px);
            box-shadow: 0 22px 30px -12px rgba(37, 99, 235, 0.5);
        }

        .action:active {
            transform: translateY(-1px);
        }

        .secondary {
            background: linear-gradient(105deg, #334155, #475569);
            box-shadow: 0 12px 24px -8px rgba(51, 65, 85, 0.4);
        }

        .secondary::before {
            content: "🏠";
        }

        .secondary:hover {
            background: linear-gradient(105deg, #1e293b, #334155);
            box-shadow: 0 22px 30px -12px rgba(51, 65, 85, 0.5);
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 900px) {
            .welcome h1 {
                font-size: 1.9rem;
            }

            .number {
                font-size: 2.2rem;
            }

            .card {
                padding: 26px 24px;
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

            .admin-label {
                font-size: 0.65rem;
            }

            .user-greeting {
                display: none;
            }

            .logout {
                padding: 9px 18px;
                font-size: 0.88rem;
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

            .cards {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 18px;
                margin-bottom: 34px;
            }

            .card {
                padding: 22px 20px;
                border-radius: 20px;
            }

            .number {
                font-size: 2rem;
            }

            .actions {
                flex-direction: column;
                gap: 14px;
            }

            .action {
                width: 100%;
                justify-content: center;
                padding: 15px 24px;
                font-size: 0.98rem;
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

            .logout {
                padding: 8px 16px;
                font-size: 0.84rem;
            }

            .container {
                padding: 24px 16px 40px;
            }

            .welcome h1 {
                font-size: 1.5rem;
            }

            .welcome p {
                font-size: 0.95rem;
            }

            .cards {
                grid-template-columns: 1fr 1fr;
                gap: 14px;
                margin-bottom: 28px;
            }

            .card {
                padding: 20px 16px;
                border-radius: 16px;
            }

            .card-title {
                font-size: 0.7rem;
                gap: 5px;
            }

            .card-title::before {
                font-size: 0.95rem;
            }

            .number {
                font-size: 1.75rem;
            }

            .action {
                padding: 14px 20px;
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
<nav class="navbar">

    <div class="brand-block">

        <div class="logo">
            IT Help Desk
        </div>

        <div class="admin-label">
            Administrator Panel
        </div>

    </div>

    <div class="nav-right">

        <span class="user-greeting">
            Signed in as <strong><?php echo htmlspecialchars($_SESSION["user_name"]); ?></strong>
        </span>

        <a href="../logout.php" class="logout">
            Logout
        </a>

    </div>

</nav>

<!-- MAIN CONTENT -->
<div class="container">

    <!-- WELCOME -->
    <div class="welcome">

        <h1>Admin dashboard</h1>

        <p>
            Monitor and manage IT support requests from a single overview.
        </p>

    </div>

    <!-- STATS CARDS -->
    <div class="cards">

        <div class="card">

            <div class="card-title">
                Total tickets
            </div>

            <div class="number">
                <?php echo $total_tickets; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Pending
            </div>

            <div class="number">
                <?php echo $pending_tickets; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-title">
                In progress
            </div>

            <div class="number">
                <?php echo $progress_tickets; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Resolved
            </div>

            <div class="number">
                <?php echo $resolved_tickets; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Registered users
            </div>

            <div class="number">
                <?php echo $total_users; ?>
            </div>

        </div>

    </div>


    <!-- ACTIONS -->
    <div class="actions">

        <a href="tickets.php" class="action">
            Manage tickets
        </a>

        

    </div>

</div>

</body>

</html>