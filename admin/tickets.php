<?php

session_start();
require_once "../config/database.php";

/* Check if user is logged in */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

/* Check if user is admin */
if ($_SESSION["user_role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

/* Get filter */
$status_filter = $_GET["status"] ?? "All";

/* Get tickets */
if ($status_filter === "All") {

    $stmt = $conn->prepare("
        SELECT tickets.*, users.user_name, users.user_email
        FROM tickets
        INNER JOIN users
            ON tickets.ticket_user_id = users.user_id
        ORDER BY tickets.ticket_created_at DESC
    ");

    $stmt->execute();

} else {

    $stmt = $conn->prepare("
        SELECT tickets.*, users.user_name, users.user_email
        FROM tickets
        INNER JOIN users
            ON tickets.ticket_user_id = users.user_id
        WHERE tickets.ticket_status = ?
        ORDER BY tickets.ticket_created_at DESC
    ");

    $stmt->execute([$status_filter]);
}

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Tickets · IT Help Desk</title>

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
            --brand-dark: #0b1f3b;
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

            background-image: radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.03) 0%, transparent 45%),
                              radial-gradient(circle at 85% 80%, rgba(30, 58, 138, 0.03) 0%, transparent 45%);
            background-size: cover;
            background-attachment: fixed;
        }

        /* ---------- NAVBAR (Admin) ---------- */
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
            max-width: 1300px;
            margin: 0 auto;
            padding: 48px 32px 64px;
            width: 100%;
        }

        /* ---------- PAGE HEADER ---------- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #0b1f3b 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 6px;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 1.05rem;
            letter-spacing: -0.01em;
        }

        /* ---------- FILTER BAR ---------- */
        .filter-box {
            background: var(--card-bg);
            padding: 20px 26px;
            border-radius: var(--radius-md);
            margin-bottom: 28px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            transition: box-shadow 0.3s ease;
        }

        .filter-box:hover {
            box-shadow: var(--shadow-md);
        }

        .filter-box label {
            font-weight: 600;
            font-size: 0.92rem;
            color: var(--brand-dark);
            letter-spacing: -0.01em;
            white-space: nowrap;
        }

        .filter-box form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-box select {
            padding: 12px 44px 12px 16px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            min-width: 200px;
            font-weight: 500;
        }

        .filter-box select:hover {
            border-color: #cbd5e1;
        }

        .filter-box select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        /* ---------- TABLE CARD ---------- */
        .table-container {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            position: relative;
            transition: box-shadow 0.3s ease;
        }

        .table-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #1e3a8a);
            opacity: 0.85;
        }

        .table-container:hover {
            box-shadow: var(--shadow-lg);
        }

        .table-scroll {
            overflow-x: auto;
        }

        /* ---------- TABLE ---------- */
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th {
            background: #f8faff;
            text-align: left;
            padding: 18px 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--brand-dark);
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #eef2f6;
            font-size: 0.93rem;
            vertical-align: middle;
        }

        tbody tr {
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: #f8faff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* ---------- CELL STYLES ---------- */
        .ticket-id {
            font-weight: 700;
            color: var(--brand-primary);
            font-size: 0.92rem;
            white-space: nowrap;
        }

        .user-cell {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.94rem;
        }

        .user-email {
            color: #94a3b8;
            font-size: 0.82rem;
            font-weight: 400;
        }

        .ticket-title {
            font-weight: 500;
            color: var(--text-dark);
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ---------- BADGES ---------- */
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            text-transform: capitalize;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .badge-status-pending {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .badge-status-in-progress {
            background: #dbeafe;
            color: #1e40af;
            border-color: #bfdbfe;
        }

        .badge-status-resolved {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .badge-priority-low {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        .badge-priority-medium {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .badge-priority-high {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .badge-priority-urgent {
            background: #fecaca;
            color: #7f1d1d;
            border-color: #fca5a5;
        }

        /* ---------- MANAGE BUTTON ---------- */
        .manage-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(105deg, #1e3a8a, #2563eb);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 0.86rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 8px 16px -6px rgba(37, 99, 235, 0.4);
            border: 2px solid transparent;
            white-space: nowrap;
        }

        .manage-btn::after {
            content: "→";
            font-size: 1rem;
            transition: transform 0.2s ease;
        }

        .manage-btn:hover {
            background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 16px 24px -10px rgba(37, 99, 235, 0.5);
        }

        .manage-btn:hover::after {
            transform: translateX(3px);
        }

        /* ---------- EMPTY STATE ---------- */
        .empty {
            padding: 80px 40px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--brand-dark);
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }

        .empty h3::before {
            content: "📭";
            display: block;
            font-size: 4rem;
            margin-bottom: 16px;
            line-height: 1;
        }

        .empty p {
            font-size: 1.05rem;
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 900px) {
            .page-header h1 {
                font-size: 1.85rem;
            }

            th,
            td {
                padding: 15px 16px;
                font-size: 0.88rem;
            }

            .ticket-title {
                max-width: 180px;
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

            .nav-links a {
                padding: 8px 14px;
                font-size: 0.85rem;
            }

            .container {
                padding: 32px 20px 48px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .page-header h1 {
                font-size: 1.65rem;
            }

            .page-header p {
                font-size: 1rem;
            }

            .filter-box {
                flex-direction: column;
                align-items: flex-start;
                padding: 18px 20px;
                gap: 12px;
            }

            .filter-box form,
            .filter-box select {
                width: 100%;
                min-width: unset;
            }

            .table-container {
                border-radius: 20px;
            }

            th,
            td {
                padding: 14px 14px;
                font-size: 0.84rem;
            }

            th {
                font-size: 0.72rem;
            }

            .ticket-title {
                max-width: 140px;
            }

            .badge {
                padding: 5px 11px;
                font-size: 0.72rem;
            }

            .manage-btn {
                padding: 8px 14px;
                font-size: 0.8rem;
            }

            .empty {
                padding: 60px 24px;
            }

            .empty h3 {
                font-size: 1.35rem;
            }

            .empty h3::before {
                font-size: 3.2rem;
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

            .page-header h1 {
                font-size: 1.45rem;
            }

            .page-header p {
                font-size: 0.94rem;
            }

            .filter-box {
                padding: 16px 16px;
                border-radius: 16px;
            }

            .filter-box label {
                font-size: 0.86rem;
            }

            .filter-box select {
                padding: 10px 36px 10px 14px;
                font-size: 0.9rem;
            }

            .table-container {
                border-radius: 16px;
            }

            th,
            td {
                padding: 12px 12px;
                font-size: 0.8rem;
            }

            th {
                font-size: 0.68rem;
            }

            .ticket-title {
                max-width: 110px;
            }

            .badge {
                padding: 4px 9px;
                font-size: 0.68rem;
            }

            .manage-btn {
                padding: 7px 12px;
                font-size: 0.75rem;
            }

            .manage-btn::after {
                display: none;
            }

            .empty {
                padding: 48px 18px;
            }

            .empty h3 {
                font-size: 1.2rem;
            }

            .empty h3::before {
                font-size: 2.8rem;
            }

            .empty p {
                font-size: 0.92rem;
            }
        }

        /* focus outline for accessibility */
        a:focus-visible,
        button:focus-visible,
        select:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 3px;
            border-radius: 60px;
        }

        select:focus-visible {
            border-radius: var(--radius-sm);
        }

    </style>

</head>

<body>

    <!-- NAVBAR -->
    <div class="navbar">

        <div class="brand-block">
            <div class="logo">IT Help Desk</div>
            <div class="admin-label">Administrator Panel</div>
        </div>

        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="tickets.php">Manage Tickets</a>
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>

    </div>


    <!-- MAIN CONTENT -->
    <div class="container">

        <!-- PAGE HEADER -->
        <div class="page-header">

            <div>
                <h1>Manage tickets</h1>
                <p>View, manage and respond to support tickets.</p>
            </div>

        </div>


        <!-- FILTER BAR -->
        <div class="filter-box">

            <label for="status">Filter by status</label>

            <form method="GET">

                <select
                    name="status"
                    id="status"
                    onchange="this.form.submit()"
                >

                    <option value="All"
                        <?= $status_filter === "All" ? "selected" : "" ?>>
                        All tickets
                    </option>

                    <option value="Pending"
                        <?= $status_filter === "Pending" ? "selected" : "" ?>>
                        Pending
                    </option>

                    <option value="In Progress"
                        <?= $status_filter === "In Progress" ? "selected" : "" ?>>
                        In Progress
                    </option>

                    <option value="Resolved"
                        <?= $status_filter === "Resolved" ? "selected" : "" ?>>
                        Resolved
                    </option>

                </select>

            </form>

        </div>


        <!-- TICKETS TABLE -->
        <div class="table-container">

            <?php if (count($tickets) > 0): ?>

                <div class="table-scroll">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>User</th>
                                <th>Issue</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($tickets as $ticket): ?>

                                <?php

                                $status_class = "badge-status-" . strtolower(
                                    str_replace(" ", "-", $ticket["ticket_status"])
                                );

                                $priority_class = "badge-priority-" . strtolower(
                                    $ticket["ticket_priority"]
                                );

                                ?>

                                <tr>

                                    <td class="ticket-id">

                                        #<?= htmlspecialchars($ticket["ticket_id"]) ?>

                                    </td>


                                    <td>

                                        <div class="user-cell">

                                            <span class="user-name">

                                                <?= htmlspecialchars($ticket["user_name"]) ?>

                                            </span>

                                            <span class="user-email">

                                                <?= htmlspecialchars($ticket["user_email"]) ?>

                                            </span>

                                        </div>

                                    </td>


                                    <td class="ticket-title">

                                        <?= htmlspecialchars($ticket["ticket_title"]) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars($ticket["ticket_category"]) ?>

                                    </td>


                                    <td>

                                        <span class="badge <?= $priority_class ?>">

                                            <?= htmlspecialchars($ticket["ticket_priority"]) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="badge <?= $status_class ?>">

                                            <?= htmlspecialchars($ticket["ticket_status"]) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars($ticket["ticket_created_at"]) ?>

                                    </td>


                                    <td>

                                        <a
                                            href="ticket.php?id=<?= $ticket["ticket_id"] ?>"
                                            class="manage-btn"
                                        >
                                            Manage
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty">

                    <h3>No tickets found</h3>

                    <p>
                        There are currently no tickets under this filter.
                        Try selecting a different status.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</body>

</html>