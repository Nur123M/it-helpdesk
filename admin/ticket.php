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

/* Check ticket ID */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: tickets.php");
    exit;
}

$ticket_id = $_GET["id"];
$message = "";

/* Get ticket */
$stmt = $conn->prepare("
    SELECT tickets.*, users.user_name, users.user_email
    FROM tickets
    INNER JOIN users 
        ON tickets.ticket_user_id = users.user_id
    WHERE tickets.ticket_id = ?
");

$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("Ticket not found.");
}

/* Handle admin reply */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reply_ticket"])) {

    $response_message = trim($_POST["response_message"]);

    if (!empty($response_message)) {

        $stmt = $conn->prepare("
            INSERT INTO responses 
            (response_ticket_id, response_user_id, response_message)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $ticket_id,
            $_SESSION["user_id"],
            $response_message
        ]);

        header("Location: ticket.php?id=" . $ticket_id);
        exit;

    } else {
        $message = "Please enter a response.";
    }
}

/* Handle status update */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $ticket_status = $_POST["ticket_status"];

    $allowed_statuses = [
        "Pending",
        "In Progress",
        "Resolved"
    ];

    if (in_array($ticket_status, $allowed_statuses)) {

        $stmt = $conn->prepare("
            UPDATE tickets
            SET ticket_status = ?
            WHERE ticket_id = ?
        ");

        $stmt->execute([
            $ticket_status,
            $ticket_id
        ]);

        header("Location: ticket.php?id=" . $ticket_id);
        exit;
    }
}

/* Get ticket responses */
$stmt = $conn->prepare("
    SELECT responses.*, users.user_name, users.user_role
    FROM responses
    INNER JOIN users
        ON responses.response_user_id = users.user_id
    WHERE responses.response_ticket_id = ?
    ORDER BY responses.response_created_at ASC
");

$stmt->execute([$ticket_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Ticket · IT Help Desk</title>

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
            max-width: 1040px;
            margin: 0 auto;
            padding: 48px 32px 64px;
            width: 100%;
        }

        /* ---------- BACK LINK ---------- */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 28px;
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.98rem;
            transition: color 0.2s ease;
            border-bottom: 1.5px solid transparent;
            padding-bottom: 2px;
        }

        .back-link:hover {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .back-link::before {
            content: "←";
            font-size: 1.2rem;
            transition: transform 0.2s ease;
        }

        .back-link:hover::before {
            transform: translateX(-3px);
        }

        /* ---------- TICKET CARD ---------- */
        .ticket-card {
            background: var(--card-bg);
            padding: 44px 44px 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255, 255, 255, 0.6);
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.35s ease;
        }

        .ticket-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #1e3a8a);
            opacity: 0.85;
        }

        .ticket-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        /* ---------- TICKET HEADER ---------- */
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .ticket-header h1 {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #0b1f3b 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .ticket-id {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--brand-primary);
            background: var(--accent-soft);
            padding: 6px 14px;
            border-radius: 40px;
            display: inline-block;
        }

        /* ---------- STATUS BADGES ---------- */
        .status {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            white-space: nowrap;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-in-progress {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .status-resolved {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        /* ---------- DETAILS GRID ---------- */
        .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .detail-box {
            background: #f8faff;
            padding: 18px 20px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-light);
            transition: all 0.2s ease;
        }

        .detail-box:hover {
            border-color: #cbdffc;
            background: #f2f7ff;
        }

        .detail-box strong {
            display: block;
            margin-bottom: 6px;
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .detail-box span {
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.98rem;
            word-break: break-word;
        }

        /* ---------- DESCRIPTION ---------- */
        .description-label {
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--brand-primary);
            margin-bottom: 10px;
        }

        .description {
            line-height: 1.75;
            background: #f8faff;
            padding: 24px 26px;
            border-radius: var(--radius-md);
            white-space: pre-wrap;
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            font-size: 0.98rem;
            margin-bottom: 28px;
        }

        /* ---------- STATUS FORM ---------- */
        .status-form {
            display: flex;
            gap: 14px;
            align-items: center;
            flex-wrap: wrap;
            padding-top: 24px;
            border-top: 1px solid #eef2f6;
        }

        .status-form-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--brand-dark);
            letter-spacing: -0.01em;
        }

        select {
            padding: 14px 18px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-sm);
            font-size: 0.98rem;
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            transition: all 0.2s ease;
            color: var(--text-dark);
            cursor: pointer;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            padding-right: 46px;
            min-width: 200px;
        }

        select:hover {
            border-color: #cbd5e1;
        }

        select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        /* ---------- BUTTONS ---------- */
        button {
            border: none;
            background: linear-gradient(105deg, #1e3a8a, #2563eb);
            color: white;
            padding: 15px 28px;
            border-radius: 60px;
            cursor: pointer;
            font-size: 0.98rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 12px 24px -8px rgba(37, 99, 235, 0.4);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
            transform: translateY(-3px);
            box-shadow: 0 22px 30px -12px rgba(37, 99, 235, 0.5);
        }

        button:active {
            transform: translateY(-1px);
        }

        /* ---------- CONVERSATION ---------- */
        .conversation-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--brand-dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .conversation-title::before {
            content: "💬";
            font-size: 1.6rem;
        }

        .message {
            padding: 24px 26px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            background: #f8faff;
            border: 1px solid var(--border-light);
            transition: transform 0.25s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .message:hover {
            box-shadow: var(--shadow-sm);
            transform: translateY(-2px);
        }

        .message.admin {
            background: linear-gradient(105deg, #f0f7ff, #ffffff);
            border-left: 5px solid var(--brand-primary);
        }

        .message:not(.admin) {
            border-left: 5px solid #16a34a;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .message-header strong {
            color: var(--brand-dark);
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-tag {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: white;
            background: var(--brand-primary);
            padding: 4px 10px;
            border-radius: 40px;
        }

        .message-date {
            color: #94a3b8;
            font-size: 0.83rem;
            font-weight: 500;
        }

        .message p {
            line-height: 1.75;
            white-space: pre-wrap;
            color: var(--text-dark);
            font-size: 0.98rem;
        }

        /* ---------- REPLY BOX ---------- */
        .reply-box {
            margin-top: 32px;
            padding-top: 28px;
            border-top: 1px solid #eef2f6;
        }

        .reply-box h3 {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--brand-dark);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reply-box h3::before {
            content: "✍️";
            font-size: 1.2rem;
        }

        textarea {
            width: 100%;
            min-height: 140px;
            padding: 16px 18px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-sm);
            resize: vertical;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            margin-bottom: 18px;
            transition: all 0.2s ease;
            color: var(--text-dark);
            line-height: 1.6;
            background: #ffffff;
        }

        textarea::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        textarea:hover {
            border-color: #cbd5e1;
        }

        textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        /* ---------- ERROR MESSAGE ---------- */
        .error {
            background: #fef2f2;
            color: #991b1b;
            padding: 16px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            text-align: center;
            font-weight: 500;
            border-left: 5px solid #dc2626;
            letter-spacing: -0.01em;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.06);
            animation: fadeSlide 0.3s ease;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ---------- RESOLVED NOTE ---------- */
        .resolved-note {
            margin-top: 24px;
            padding: 20px 24px;
            background: #f0fdf4;
            border-radius: var(--radius-sm);
            border-left: 5px solid #22c55e;
            color: #166534;
            font-weight: 500;
            text-align: center;
            letter-spacing: -0.01em;
        }

        /* ---------- EMPTY STATE ---------- */
        .no-responses {
            padding: 40px 24px;
            text-align: center;
            color: var(--text-muted);
            font-size: 1rem;
            border: 1px dashed var(--border-light);
            border-radius: var(--radius-md);
            background: #fafcff;
        }

        .no-responses::before {
            content: "💭";
            display: block;
            font-size: 2.4rem;
            margin-bottom: 12px;
            line-height: 1;
            opacity: 0.7;
        }

        /* ---------- RESPONSIVE ---------- */
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

            .ticket-card {
                padding: 32px 26px 28px;
            }

            .ticket-header {
                flex-direction: column;
                gap: 14px;
            }

            .ticket-header h1 {
                font-size: 1.5rem;
            }

            .details {
                grid-template-columns: 1fr 1fr;
            }

            .status-form {
                flex-direction: column;
                align-items: stretch;
            }

            .status-form select {
                width: 100%;
                min-width: unset;
            }

            .status-form button {
                width: 100%;
                justify-content: center;
            }

            .message {
                padding: 20px 20px;
            }

            .message-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .conversation-title {
                font-size: 1.3rem;
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

            .ticket-card {
                padding: 26px 20px 24px;
                border-radius: 20px;
            }

            .ticket-header h1 {
                font-size: 1.35rem;
            }

            .ticket-id {
                font-size: 0.78rem;
                padding: 5px 11px;
            }

            .status {
                padding: 6px 14px;
                font-size: 0.78rem;
            }

            .details {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .detail-box {
                padding: 14px 16px;
            }

            .description {
                padding: 18px 20px;
                font-size: 0.94rem;
            }

            .message {
                padding: 18px 18px;
                border-radius: 12px;
            }

            .message p {
                font-size: 0.94rem;
            }

            .conversation-title {
                font-size: 1.2rem;
            }

            .conversation-title::before {
                font-size: 1.3rem;
            }

            .reply-box h3 {
                font-size: 1.05rem;
            }

            textarea {
                padding: 14px 16px;
                font-size: 0.95rem;
                min-height: 120px;
            }

            button {
                padding: 14px 22px;
                font-size: 0.94rem;
            }
        }

        /* focus outline for accessibility */
        a:focus-visible,
        button:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 3px;
            border-radius: 60px;
        }

        textarea:focus-visible,
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
            <a href="tickets.php">Tickets</a>
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>

    </div>


    <!-- MAIN CONTENT -->
    <div class="container">

        <a href="tickets.php" class="back-link">
            Back to tickets
        </a>


        <?php if (!empty($message)): ?>

            <div class="error">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <!-- TICKET INFORMATION -->
        <div class="ticket-card">

            <div class="ticket-header">

                <div>

                    <div class="ticket-id">
                        Ticket #<?= htmlspecialchars($ticket["ticket_id"]) ?>
                    </div>

                    <h1>
                        <?= htmlspecialchars($ticket["ticket_title"]) ?>
                    </h1>

                </div>

                <?php
                $status_class = "status-" . strtolower(str_replace(" ", "-", $ticket["ticket_status"]));
                ?>

                <span class="status <?= $status_class ?>">
                    <?= htmlspecialchars($ticket["ticket_status"]) ?>
                </span>

            </div>


            <div class="details">

                <div class="detail-box">
                    <strong>Submitted by</strong>
                    <span><?= htmlspecialchars($ticket["user_name"]) ?></span>
                </div>


                <div class="detail-box">
                    <strong>Email</strong>
                    <span><?= htmlspecialchars($ticket["user_email"]) ?></span>
                </div>


                <div class="detail-box">
                    <strong>Category</strong>
                    <span><?= htmlspecialchars($ticket["ticket_category"]) ?></span>
                </div>


                <div class="detail-box">
                    <strong>Priority</strong>
                    <span><?= htmlspecialchars($ticket["ticket_priority"]) ?></span>
                </div>


                <div class="detail-box">
                    <strong>Date submitted</strong>
                    <span><?= htmlspecialchars($ticket["ticket_created_at"]) ?></span>
                </div>

            </div>


            <div class="description-label">
                Problem description
            </div>

            <div class="description">

                <?= htmlspecialchars($ticket["ticket_description"]) ?>

            </div>


            <!-- CHANGE STATUS -->
            <form method="POST" class="status-form">

                <span class="status-form-label">Update status:</span>

                <select name="ticket_status">

                    <option value="Pending"
                        <?= $ticket["ticket_status"] === "Pending" ? "selected" : "" ?>>
                        Pending
                    </option>

                    <option value="In Progress"
                        <?= $ticket["ticket_status"] === "In Progress" ? "selected" : "" ?>>
                        In Progress
                    </option>

                    <option value="Resolved"
                        <?= $ticket["ticket_status"] === "Resolved" ? "selected" : "" ?>>
                        Resolved
                    </option>

                </select>

                <button type="submit" name="update_status">
                    Update status
                </button>

            </form>

        </div>


        <!-- CONVERSATION -->
        <div class="ticket-card">

            <h2 class="conversation-title">
                Conversation
            </h2>


            <?php if (count($responses) > 0): ?>

                <?php foreach ($responses as $response): ?>

                    <div class="message <?= $response["user_role"] === "admin" ? "admin" : "" ?>">

                        <div class="message-header">

                            <strong>

                                <?= htmlspecialchars($response["user_name"]) ?>

                                <?php if ($response["user_role"] === "admin"): ?>
                                    <span class="admin-tag">IT Support</span>
                                <?php endif; ?>

                            </strong>

                            <span class="message-date">
                                <?= htmlspecialchars($response["response_created_at"]) ?>
                            </span>

                        </div>

                        <p>
                            <?= htmlspecialchars($response["response_message"]) ?>
                        </p>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="no-responses">
                    No responses yet. Start the conversation by replying below.
                </div>

            <?php endif; ?>


            <!-- ADMIN REPLY -->
            <?php if ($ticket["ticket_status"] !== "Resolved"): ?>

                <div class="reply-box">

                    <h3>Reply to user</h3>

                    <form method="POST">

                        <textarea
                            name="response_message"
                            placeholder="Write your response to the user..."
                            required
                        ></textarea>

                        <button type="submit" name="reply_ticket">
                            Send response
                        </button>

                    </form>

                </div>

            <?php else: ?>

                <div class="resolved-note">
                    This ticket has been resolved. Replies are now closed.
                </div>

            <?php endif; ?>

        </div>

    </div>

</body>

</html>