<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: my_tickets.php");
    exit;
}

$ticket_id = (int) $_GET["id"];

/*
|--------------------------------------------------------------------------
| Get ticket
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT tickets.*, users.user_name, users.user_email
     FROM tickets
     INNER JOIN users
     ON tickets.ticket_user_id = users.user_id
     WHERE tickets.ticket_id = ?
     AND tickets.ticket_user_id = ?"
);

$stmt->execute([
    $ticket_id,
    $_SESSION["user_id"]
]);

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("Ticket not found or you do not have permission to view it.");
}

/*
|--------------------------------------------------------------------------
| Add response
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $response_message = trim($_POST["response_message"]);

    if (!empty($response_message)) {

        $stmt = $conn->prepare(
            "INSERT INTO responses
            (
                response_ticket_id,
                response_user_id,
                response_message
            )
            VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $ticket_id,
            $_SESSION["user_id"],
            $response_message
        ]);

        header("Location: ticket.php?id=" . $ticket_id);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Get responses
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT responses.*, users.user_name, users.user_role
     FROM responses
     INNER JOIN users
     ON responses.response_user_id = users.user_id
     WHERE responses.response_ticket_id = ?
     ORDER BY responses.response_created_at ASC"
);

$stmt->execute([$ticket_id]);

$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Ticket #<?php echo $ticket_id; ?> · IT Help Desk
    </title>

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
            color: white;
            background: linear-gradient(105deg, #dc2626, #ef4444);
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
            max-width: 960px;
            margin: 0 auto;
            padding: 48px 32px 64px;
            width: 100%;
        }

        /* ---------- BACK LINK ---------- */
        .back {
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

        .back:hover {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .back::before {
            content: "←";
            font-size: 1.2rem;
            transition: transform 0.2s ease;
        }

        .back:hover::before {
            transform: translateX(-3px);
        }

        /* ---------- TICKET CARD ---------- */
        .ticket-box {
            background: var(--card-bg);
            padding: 44px 44px 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255, 255, 255, 0.6);
            margin-bottom: 36px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.35s ease;
        }

        .ticket-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #1e3a8a);
            opacity: 0.85;
        }

        .ticket-box:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .ticket-id {
            display: inline-block;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--brand-primary);
            background: var(--accent-soft);
            padding: 6px 14px;
            border-radius: 40px;
            margin-bottom: 14px;
        }

        .ticket-header h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #0b2b5c 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.25;
        }

        /* ---------- DETAILS / BADGES ---------- */
        .details {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 24px 0;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            background: var(--accent-soft);
            color: var(--brand-primary);
            letter-spacing: -0.01em;
            border: 1px solid #bfdbfe;
            white-space: nowrap;
        }

        .badge-priority {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
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

        /* ---------- META ROWS ---------- */
        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 28px;
            margin-bottom: 24px;
        }

        .meta-item {
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .meta-item strong {
            color: var(--brand-dark);
            font-weight: 600;
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
            background: #f8faff;
            padding: 24px 26px;
            border-radius: var(--radius-md);
            line-height: 1.75;
            white-space: pre-wrap;
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            font-size: 0.98rem;
        }

        /* ---------- SECTION TITLE ---------- */
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--brand-dark);
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: "💬";
            font-size: 1.6rem;
        }

        /* ---------- RESPONSES ---------- */
        .response {
            background: var(--card-bg);
            padding: 26px 28px;
            border-radius: var(--radius-md);
            margin-bottom: 18px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            transition: transform 0.25s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .response:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .response.admin {
            border-left: 5px solid var(--brand-primary);
            background: linear-gradient(105deg, #f8faff, #ffffff);
        }

        .response.user {
            border-left: 5px solid #16a34a;
        }

        .response-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .response-name {
            font-weight: 600;
            color: var(--brand-dark);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .response-name .role-tag {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: white;
            background: var(--brand-primary);
            padding: 4px 10px;
            border-radius: 40px;
        }

        .response-date {
            color: #94a3b8;
            font-size: 0.83rem;
            font-weight: 500;
        }

        .response-message {
            line-height: 1.75;
            white-space: pre-wrap;
            color: var(--text-dark);
            font-size: 0.98rem;
        }

        /* ---------- NO RESPONSES ---------- */
        .no-responses {
            background: var(--card-bg);
            padding: 48px 32px;
            text-align: center;
            border-radius: var(--radius-lg);
            color: var(--text-muted);
            border: 1px dashed var(--border-light);
            font-size: 1rem;
            margin-bottom: 18px;
        }

        .no-responses::before {
            content: "💭";
            display: block;
            font-size: 2.6rem;
            margin-bottom: 14px;
            line-height: 1;
            opacity: 0.7;
        }

        /* ---------- REPLY BOX ---------- */
        .reply-box {
            background: var(--card-bg);
            padding: 40px 44px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255, 255, 255, 0.6);
            margin-top: 36px;
            position: relative;
            overflow: hidden;
        }

        .reply-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #1e3a8a);
            opacity: 0.85;
        }

        .reply-box .section-title::before {
            content: "✍️";
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
            margin-bottom: 20px;
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

        button {
            background: linear-gradient(105deg, #1e3a8a, #2563eb);
            color: white;
            border: none;
            padding: 16px 36px;
            border-radius: 60px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 12px 24px -8px rgba(37, 99, 235, 0.4);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        button::after {
            content: "→";
            font-size: 1.2rem;
            transition: transform 0.25s ease;
        }

        button:hover {
            background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
            transform: translateY(-3px);
            box-shadow: 0 22px 30px -12px rgba(37, 99, 235, 0.5);
        }

        button:hover::after {
            transform: translateX(4px);
        }

        button:active {
            transform: translateY(-1px);
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

            .logout {
                padding: 9px 18px;
                font-size: 0.9rem;
            }

            .container {
                padding: 32px 20px 48px;
            }

            .ticket-box {
                padding: 32px 26px 28px;
            }

            .ticket-header {
                flex-direction: column;
                gap: 12px;
            }

            .ticket-header h1 {
                font-size: 1.6rem;
            }

            .meta {
                gap: 18px;
            }

            .response {
                padding: 22px 22px;
            }

            .response-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .reply-box {
                padding: 32px 26px;
            }

            .section-title {
                font-size: 1.3rem;
            }

            button {
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

            .ticket-box {
                padding: 26px 20px 24px;
                border-radius: 20px;
            }

            .ticket-header h1 {
                font-size: 1.35rem;
            }

            .badge {
                padding: 6px 12px;
                font-size: 0.78rem;
            }

            .description {
                padding: 18px 20px;
                font-size: 0.93rem;
            }

            .response {
                padding: 20px 18px;
                border-radius: 12px;
            }

            .response-message {
                font-size: 0.94rem;
            }

            .reply-box {
                padding: 28px 20px;
                border-radius: 20px;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .section-title::before {
                font-size: 1.3rem;
            }

            textarea {
                padding: 14px 16px;
                font-size: 0.95rem;
            }

            button {
                padding: 14px 24px;
                font-size: 0.95rem;
            }

            .no-responses {
                padding: 36px 20px;
                font-size: 0.95rem;
            }

            .no-responses::before {
                font-size: 2.2rem;
            }
        }

        /* focus outline for accessibility */
        a:focus-visible,
        button:focus-visible,
        textarea:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 3px;
            border-radius: 60px;
        }

        textarea:focus-visible {
            border-radius: var(--radius-sm);
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

    <a href="my_tickets.php" class="back">
        Back to my tickets
    </a>

    <!-- TICKET INFORMATION -->
    <div class="ticket-box">

        <div class="ticket-header">

            <div>

                <div class="ticket-id">
                    Ticket #<?php echo $ticket["ticket_id"]; ?>
                </div>

                <h1>
                    <?php echo htmlspecialchars($ticket["ticket_title"]); ?>
                </h1>

            </div>

        </div>

        <div class="details">

            <span class="badge">
                📁 <?php echo htmlspecialchars($ticket["ticket_category"]); ?>
            </span>

            <span class="badge badge-priority">
                ⚡ <?php echo htmlspecialchars($ticket["ticket_priority"]); ?>
            </span>

            <?php
            $status_class = "badge-status-" . strtolower(str_replace(" ", "-", $ticket["ticket_status"]));
            ?>

            <span class="badge <?php echo $status_class; ?>">
                <?php echo htmlspecialchars($ticket["ticket_status"]); ?>
            </span>

        </div>

        <div class="meta">

            <div class="meta-item">
                <strong>Submitted by:</strong>
                <?php echo htmlspecialchars($ticket["user_name"]); ?>
            </div>

            <div class="meta-item">
                <strong>Date:</strong>
                <?php
                echo date(
                    "d M Y, H:i",
                    strtotime($ticket["ticket_created_at"])
                );
                ?>
            </div>

        </div>

        <div class="description-label">
            Problem description
        </div>

        <div class="description">
            <?php echo htmlspecialchars($ticket["ticket_description"]); ?>
        </div>

    </div>


    <!-- RESPONSES -->
    <h2 class="section-title">
        Conversation
    </h2>

    <?php if (count($responses) > 0): ?>

        <?php foreach ($responses as $response): ?>

            <div class="response
                <?php echo $response["user_role"] === "admin"
                    ? "admin"
                    : "user"; ?>">

                <div class="response-header">

                    <span class="response-name">

                        <?php echo htmlspecialchars(
                            $response["user_name"]
                        ); ?>

                        <?php if ($response["user_role"] === "admin"): ?>

                            <span class="role-tag">
                                IT Support
                            </span>

                        <?php endif; ?>

                    </span>

                    <span class="response-date">

                        <?php
                        echo date(
                            "d M Y, H:i",
                            strtotime($response["response_created_at"])
                        );
                        ?>

                    </span>

                </div>

                <div class="response-message">

                    <?php echo htmlspecialchars(
                        $response["response_message"]
                    ); ?>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="no-responses">

            No responses yet. The IT support team will respond here.

        </div>

    <?php endif; ?>


    <!-- REPLY -->
    <?php if ($ticket["ticket_status"] !== "Resolved"): ?>

        <div class="reply-box">

            <h2 class="section-title">
                Add a reply
            </h2>

            <form method="POST">

                <textarea
                    name="response_message"
                    placeholder="Add more information about your issue..."
                    required
                ></textarea>

                <button type="submit">
                    Send reply
                </button>

            </form>

        </div>

    <?php else: ?>

        <div class="no-responses">

            This ticket has been resolved. You can no longer add replies.

        </div>

    <?php endif; ?>

</div>

</body>

</html>