<?php

session_start();

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_email = trim($_POST["user_email"]);
    $user_password = $_POST["user_password"];

    if (empty($user_email) || empty($user_password)) {

        $message = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT * FROM users WHERE user_email = ?"
        );

        $stmt->execute([$user_email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($user_password, $user["user_password"])) {

    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["user_name"] = $user["user_name"];
    $_SESSION["user_email"] = $user["user_email"];
    $_SESSION["user_role"] = $user["user_role"];

    if ($_SESSION["user_role"] === "admin") {

        header("Location: admin/dashboard.php");
        exit;

    } else {

        header("Location: dashboard.php");
        exit;

    }

} else {

    $message = "Invalid email or password.";

}
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login · IT Help Desk</title>

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
            justify-content: center;
            align-items: center;
            padding: 40px 20px;

            /* subtle background pattern */
            background-image: radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.03) 0%, transparent 45%),
                              radial-gradient(circle at 85% 80%, rgba(30, 58, 138, 0.03) 0%, transparent 45%);
            background-size: cover;
        }

        /* ---------- CONTAINER ---------- */
        .container {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            position: relative;
        }

        /* ---------- LOGIN CARD ---------- */
        .login-box {
            background: var(--card-bg);
            padding: 52px 44px 44px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(2px);
            transition: transform 0.3s ease, box-shadow 0.35s ease;
            position: relative;
            overflow: hidden;
        }

        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #1e3a8a);
            opacity: 0.85;
        }

        .login-box:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        /* ---------- HEADINGS ---------- */
        h1 {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #0b2b5c 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 1.05rem;
            margin-bottom: 32px;
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

        input {
            width: 100%;
            padding: 15px 18px;
            margin-bottom: 22px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            transition: all 0.2s ease;
            color: var(--text-dark);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        input:hover {
            border-color: #cbd5e1;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            background: #ffffff;
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

        /* ---------- ERROR MESSAGE ---------- */
        .error {
            background: #fef2f2;
            color: #991b1b;
            padding: 16px 18px;
            margin-bottom: 26px;
            border-radius: var(--radius-sm);
            text-align: center;
            font-weight: 500;
            font-size: 0.98rem;
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

        /* ---------- LINKS ---------- */
        .register-link {
            text-align: center;
            margin-top: 28px;
            color: var(--text-muted);
            font-size: 0.98rem;
            font-weight: 400;
        }

        .register-link a {
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease, text-decoration 0.2s;
            border-bottom: 1.5px solid transparent;
            padding-bottom: 1px;
        }

        .register-link a:hover {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .back {
            text-align: center;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #eef2f6;
        }

        .back a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back a:hover {
            color: var(--brand-primary);
        }

        .back a::before {
            content: "←";
            font-size: 1.1rem;
            transition: transform 0.2s ease;
        }

        .back a:hover::before {
            transform: translateX(-3px);
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 560px) {
            .login-box {
                padding: 40px 24px 36px;
            }

            h1 {
                font-size: 1.9rem;
            }

            .subtitle {
                font-size: 1rem;
                margin-bottom: 26px;
            }

            input {
                padding: 14px 16px;
                margin-bottom: 18px;
                font-size: 0.98rem;
            }

            button {
                padding: 15px;
                font-size: 1rem;
            }

            .error {
                padding: 14px 16px;
                font-size: 0.94rem;
            }
        }

        @media (max-width: 400px) {
            body {
                padding: 24px 16px;
            }

            .login-box {
                padding: 32px 18px 28px;
            }

            h1 {
                font-size: 1.7rem;
            }

            label {
                font-size: 0.9rem;
            }
        }

        /* focus outline for accessibility */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="login-box">

        <h1>Welcome back</h1>

        <p class="subtitle">
            Login to your IT Help Desk account
        </p>

        <?php if (!empty($message)): ?>

            <div class="error">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label for="user_email">Email address</label>

            <input
                type="email"
                id="user_email"
                name="user_email"
                placeholder="you@example.com"
                required
                autocomplete="email"
            >

            <label for="user_password">Password</label>

            <input
                type="password"
                id="user_password"
                name="user_password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            >

            <button type="submit">
                Login
            </button>

        </form>

        <div class="register-link">
            Don't have an account?
            <a href="register.php">Create account</a>
        </div>

        <div class="back">
            <a href="index.php">Back to home</a>
        </div>

    </div>

</div>

</body>

</html>