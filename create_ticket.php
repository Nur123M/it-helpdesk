<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $ticket_title = trim($_POST["ticket_title"]);
    $ticket_description = trim($_POST["ticket_description"]);
    $ticket_category = $_POST["ticket_category"];
    $ticket_priority = $_POST["ticket_priority"];

    if (
        empty($ticket_title) ||
        empty($ticket_description) ||
        empty($ticket_category)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        $sql = "INSERT INTO tickets
                (
                    ticket_user_id,
                    ticket_title,
                    ticket_description,
                    ticket_category,
                    ticket_priority
                )
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            $_SESSION["user_id"],
            $ticket_title,
            $ticket_description,
            $ticket_category,
            $ticket_priority
        ]);

        $message = "Your support ticket has been submitted successfully.";
        $message_type = "success";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Ticket · IT Help Desk</title>

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

        .navbar a {
            color: white;
            text-decoration: none;
            background: linear-gradient(105deg, #dc2626, #ef4444);
            padding: 11px 24px;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: -0.01em;
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            box-shadow: 0 8px 18px -8px rgba(220, 38, 38, 0.5);
            border: 2px solid transparent;
        }

        .navbar a:hover {
            background: linear-gradient(105deg, #b91c1c, #dc2626);
            transform: translateY(-2px);
            box-shadow: 0 16px 26px -10px rgba(220, 38, 38, 0.6);
        }

        /* ---------- CONTAINER ---------- */
        .container {
            max-width: 820px;
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

        /* ---------- FORM CARD ---------- */
        .form-box {
            background: var(--card-bg);
            padding: 48px 48px 44px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255, 255, 255, 0.6);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.35s ease;
        }

        .form-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #1e3a8a);
            opacity: 0.85;
        }

        .form-box:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        /* ---------- HEADINGS ---------- */
        h1 {
            font-size: 2.1rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #0b2b5c 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 8px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 1.05rem;
            margin-bottom: 34px;
            font-weight: 400;
            letter-spacing: -0.01em;
        }

        /* ---------- FORM ELEMENTS ---------- */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--brand-dark);
            letter-spacing: -0.01em;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 15px 18px;
            margin-bottom: 24px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            transition: all 0.2s ease;
            color: var(--text-dark);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            -webkit-appearance: none;
            appearance: none;
        }

        input::placeholder,
        textarea::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        input:hover,
        textarea:hover,
        select:hover {
            border-color: #cbd5e1;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            background: #ffffff;
        }

        textarea {
            height: 150px;
            resize: vertical;
            min-height: 120px;
            line-height: 1.6;
        }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 18px center;
            background-size: 16px;
            padding-right: 48px;
            cursor: pointer;
        }

        /* ---------- BUTTON ---------- */
        button {
            width: 100%;
            padding: 17px;
            border: none;
            border-radius: 60px;
            background: linear-gradient(105deg, #1e3a8a, #2563eb);
            color: white;
            font-size: 1.05rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            letter-spacing: -0.01em;
            box-shadow: 0 12px 24px -8px rgba(37, 99, 235, 0.4);
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
            margin-top: 4px;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(105deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        button:hover {
            background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
            transform: translateY(-3px);
            box-shadow: 0 22px 30px -12px rgba(37, 99, 235, 0.5);
        }

        button:hover::after {
            transform: translateX(100%);
        }

        button:active {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px -10px rgba(37, 99, 235, 0.5);
        }

        /* ---------- MESSAGES ---------- */
        .message {
            padding: 16px 18px;
            margin-bottom: 26px;
            border-radius: var(--radius-sm);
            text-align: center;
            font-weight: 500;
            font-size: 0.98rem;
            border-left: 5px solid transparent;
            letter-spacing: -0.01em;
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

        .success {
            background: #f0fdf4;
            color: #166534;
            border-left-color: #22c55e;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.06);
        }

        .error {
            background: #fef2f2;
            color: #991b1b;
            border-left-color: #dc2626;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.06);
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

            .navbar a {
                padding: 9px 18px;
                font-size: 0.9rem;
            }

            .container {
                padding: 32px 20px 48px;
            }

            .form-box {
                padding: 36px 28px 32px;
            }

            h1 {
                font-size: 1.75rem;
            }

            .subtitle {
                font-size: 1rem;
                margin-bottom: 28px;
            }

            input,
            textarea,
            select {
                padding: 14px 16px;
                margin-bottom: 20px;
                font-size: 0.98rem;
            }

            textarea {
                height: 130px;
            }

            button {
                padding: 15px;
                font-size: 1rem;
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

            .navbar a {
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .container {
                padding: 24px 16px 40px;
            }

            .form-box {
                padding: 28px 20px 26px;
                border-radius: 20px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 0.95rem;
                margin-bottom: 24px;
            }

            label {
                font-size: 0.9rem;
            }

            input,
            textarea,
            select {
                padding: 13px 14px;
                margin-bottom: 18px;
                font-size: 0.95rem;
            }

            textarea {
                height: 120px;
            }
        }

        /* focus outline for accessibility */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        textarea:focus-visible,
        select:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
        }

    </style>

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">

    <div class="logo">
        IT Help Desk
    </div>

    <a href="logout.php">
        Logout
    </a>

</nav>

<!-- MAIN CONTENT -->
<div class="container">

    <a href="dashboard.php" class="back">
        Back to Dashboard
    </a>

    <div class="form-box">

        <h1>Submit IT support ticket</h1>

        <p class="subtitle">
            Describe the technical problem you are experiencing and our team will get back to you.
        </p>

        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label for="ticket_title">Issue title</label>

            <input
                type="text"
                id="ticket_title"
                name="ticket_title"
                placeholder="Example: Computer cannot connect to Wi-Fi"
                required
            >

            <label for="ticket_category">Category</label>

            <select id="ticket_category" name="ticket_category" required>

                <option value="">
                    Select issue category
                </option>

                <option value="Hardware">
                    Hardware
                </option>

                <option value="Software">
                    Software
                </option>

                <option value="Network">
                    Network
                </option>

                <option value="Printer">
                    Printer
                </option>

                <option value="Account/Login">
                    Account / Login
                </option>

                <option value="Other">
                    Other
                </option>

            </select>

            <label for="ticket_priority">Priority</label>

            <select id="ticket_priority" name="ticket_priority">

                <option value="Low">
                    Low
                </option>

                <option value="Medium" selected>
                    Medium
                </option>

                <option value="High">
                    High
                </option>

                <option value="Urgent">
                    Urgent
                </option>

            </select>

            <label for="ticket_description">Describe the problem</label>

            <textarea
                id="ticket_description"
                name="ticket_description"
                placeholder="Explain the problem in detail — what happened, when it started, and any error messages you saw..."
                required
            ></textarea>

            <button type="submit">
                Submit ticket
            </button>

        </form>

    </div>

</div>

</body>

</html>