<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT * FROM tickets
     WHERE ticket_user_id = ?
     ORDER BY ticket_created_at DESC"
);

$stmt->execute([$_SESSION["user_id"]]);

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Tickets · IT Help Desk</title>

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

        .logout {
            background: linear-gradient(105deg, #dc2626, #ef4444);
            color: white;
            padding: 11px 24px;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.95rem;
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 48px 32px 64px;
            width: 100%;
        }

        /* ---------- HEADER ---------- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 20px;
        }

        h1 {
            font-size: 2.1rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #0b2b5c 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .new-ticket {
            background: linear-gradient(105deg, #1e3a8a, #2563eb);
            color: white;
            text-decoration: none;
            padding: 13px 26px;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.98rem;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 12px 24px -8px rgba(37, 99, 235, 0.4);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .new-ticket::before {
            content: "+";
            font-size: 1.3rem;
            font-weight: 600;
            line-height: 1;
        }

        .new-ticket:hover {
            background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
            transform: translateY(-3px);
            box-shadow: 0 22px 30px -12px rgba(37, 99, 235, 0.5);
        }

        /* ---------- TABLE CARD ---------- */
        .table-box {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            position: relative;
            transition: box-shadow 0.3s ease;
        }

        .table-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #1e3a8a);
            opacity: 0.85;
        }

        .table-box:hover {
            box-shadow: var(--shadow-lg);
        }

        /* ---------- TABLE ---------- */
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th,
        td {
            padding: 18px 20px;
            text-align: left;
            border-bottom: 1px solid #eef2f6;
            font-size: 0.95rem;
        }

        th {
            background: #f8faff;
            color: var(--brand-dark);
            font-weight: 600;
            font-size: 0.88rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
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

        /* ---------- TABLE CELLS ---------- */
        td:first-child {
            font-weight: 600;
            color: var(--brand-dark);
            font-size: 0.9rem;
        }

        td:nth-child(2) {
            font-weight: 500;
            color: var(--text-dark);
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ---------- STATUS BADGES ---------- */
        .status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .progress {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .resolved {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .closed {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* ---------- VIEW LINK ---------- */
        .view {
            color: var(--brand-primary);
            font-weight: 600;
            text-decoration: none;
            font-size: 0.92rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s ease;
            border-bottom: 1.5px solid transparent;
            padding-bottom: 2px;
        }

        .view::after {
            content: "→";
            font-size: 1rem;
            transition: transform 0.2s ease;
        }

        .view:hover {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .view:hover::after {
            transform: translateX(3px);
        }

        /* ---------- PRIORITY ---------- */
        .priority {
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* ---------- EMPTY STATE ---------- */
        .empty {
            text-align: center;
            padding: 80px 40px;
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
            margin-bottom: 28px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .empty a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 32px;
            background: linear-gradient(105deg, #1e3a8a, #2563eb);
            color: white;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 12px 24px -8px rgba(37, 99, 235, 0.4);
            border: 2px solid transparent;
            gap: 8px;
        }

        .empty a::before {
            content: "+";
            font-size: 1.3rem;
            font-weight: 600;
            line-height: 1;
        }

        .empty a:hover {
            background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
            transform: translateY(-3px);
            box-shadow: 0 22px 30px -12px rgba(37, 99, 235, 0.5);
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 900px) {
            h1 {
                font-size: 1.8rem;
            }

            th,
            td {
                padding: 15px 16px;
                font-size: 0.9rem;
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

            .logout {
                padding: 9px 18px;
                font-size: 0.9rem;
            }

            .container {
                padding: 32px 20px 48px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            h1 {
                font-size: 1.65rem;
            }

            .new-ticket {
                width: 100%;
                justify-content: center;
                padding: 13px 22px;
            }

            .table-box {
                border-radius: 20px;
            }

            th,
            td {
                padding: 14px 14px;
                font-size: 0.85rem;
            }

            th {
                font-size: 0.78rem;
            }

            td:nth-child(2) {
                max-width: 160px;
            }

            .status {
                padding: 5px 11px;
                font-size: 0.75rem;
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

            .empty p {
                font-size: 0.98rem;
            }

            .empty a {
                padding: 13px 26px;
                font-size: 0.95rem;
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

            .logout {
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .container {
                padding: 24px 16px 40px;
            }

            h1 {
                font-size: 1.45rem;
            }

            .table-box {
                border-radius: 16px;
            }

            th,
            td {
                padding: 12px 12px;
                font-size: 0.8rem;
            }

            th {
                font-size: 0.72rem;
            }

            td:nth-child(2) {
                max-width: 120px;
            }

            .status {
                padding: 4px 9px;
                font-size: 0.7rem;
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

    <div class="logo">
        IT Help Desk
    </div>

    <a href="logout.php" class="logout">
        Logout
    </a>

</nav>

<!-- MAIN CONTENT -->
<div class="container">

    <!-- HEADER -->
    <div class="header">

        <h1>My support tickets</h1>

        <a href="create_ticket.php" class="new-ticket">
            New ticket
        </a>

    </div>

    <!-- TABLE CARD -->
    <div class="table-box">

        <?php if (count($tickets) > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>ID</th>
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

                    <tr>

                        <td>
                            #<?php echo $ticket["ticket_id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($ticket["ticket_title"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($ticket["ticket_category"]); ?>
                        </td>

                        <td class="priority">
                            <?php echo htmlspecialchars($ticket["ticket_priority"]); ?>
                        </td>

                        <td>

                            <?php

                            $status_class = strtolower(
                                str_replace(" ", "-", $ticket["ticket_status"])
                            );

                            ?>

                            <span class="status <?php echo $status_class; ?>">

                                <?php echo htmlspecialchars($ticket["ticket_status"]); ?>

                            </span>

                        </td>

                        <td>
                            <?php echo date(
                                "d M Y",
                                strtotime($ticket["ticket_created_at"])
                            ); ?>
                        </td>

                        <td>

                            <a
                                class="view"
                                href="ticket.php?id=<?php echo $ticket["ticket_id"]; ?>"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                <h3>No tickets yet</h3>

                <p>
                    You haven't submitted any IT support requests.
                    When you do, they'll appear here for you to track.
                </p>

                <a href="create_ticket.php">
                    Submit your first ticket
                </a>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>