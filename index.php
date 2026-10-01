<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IT Help Desk · Professional support</title>

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
      --radius-md: 14px;
      --radius-lg: 28px;
      --radius-sm: 10px;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
      background: #fafcff;
      color: var(--text-dark);
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* ---------- SHARED LAYOUT ---------- */
    .container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 32px;
    }

    /* ---------- NAVBAR (refined) ---------- */
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
      font-size: 1.7rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      display: flex;
      align-items: center;
      gap: 8px;
      color: white;
    }

    .logo::before {
      content: "🛡️";
      font-size: 1.9rem;
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
      font-size: 1rem;
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

    /* ---------- HERO SECTION (elevated) ---------- */
    .hero {
      padding: 80px 32px 60px;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      background: radial-gradient(circle at 10% 30%, rgba(59, 130, 246, 0.03) 0%, transparent 50%),
                  radial-gradient(circle at 90% 70%, rgba(30, 58, 138, 0.04) 0%, transparent 50%),
                  #ffffff;
      position: relative;
    }

    .hero-content {
      max-width: 820px;
      margin: 0 auto;
      padding: 20px 10px;
    }

    .hero h1 {
      font-size: 3.8rem;
      font-weight: 700;
      line-height: 1.2;
      letter-spacing: -0.03em;
      background: linear-gradient(135deg, #0b2b5c 0%, #2563eb 100%);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      margin-bottom: 24px;
      text-shadow: 0 2px 10px rgba(37, 99, 235, 0.08);
    }

    .hero p {
      font-size: 1.25rem;
      color: #334155;
      line-height: 1.7;
      margin-bottom: 40px;
      max-width: 640px;
      margin-left: auto;
      margin-right: auto;
      font-weight: 400;
    }

    /* ---------- BUTTONS (polished) ---------- */
    .buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 18px;
      justify-content: center;
    }

    .buttons a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      font-weight: 600;
      padding: 16px 36px;
      border-radius: 60px;
      font-size: 1.05rem;
      transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
      letter-spacing: -0.01em;
      box-shadow: 0 8px 18px -6px rgba(0, 0, 0, 0.1);
      border: 2px solid transparent;
      min-width: 180px;
    }

    .btn-primary {
      background: linear-gradient(105deg, #1e3a8a, #2563eb);
      color: white;
      border: 2px solid transparent;
      box-shadow: 0 12px 24px -10px rgba(37, 99, 235, 0.4);
    }

    .btn-primary:hover {
      background: linear-gradient(105deg, #1e3a8a, #1d4ed8);
      transform: translateY(-3px);
      box-shadow: 0 22px 30px -12px rgba(37, 99, 235, 0.5);
    }

    .btn-secondary {
      background: transparent;
      border: 2px solid #1e3a8a;
      color: #1e3a8a;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    }

    .btn-secondary:hover {
      background: #1e3a8a;
      color: white;
      transform: translateY(-3px);
      box-shadow: 0 20px 28px -12px rgba(30, 58, 138, 0.35);
    }

    /* ---------- FEATURES (cards with depth) ---------- */
    .features {
      display: flex;
      justify-content: center;
      gap: 32px;
      padding: 60px 32px 80px;
      flex-wrap: wrap;
      background: #ffffff;
      position: relative;
    }

    .feature {
      background: var(--card-bg);
      padding: 36px 28px 32px;
      width: 320px;
      text-align: center;
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
      transition: all 0.3s ease;
      border: 1px solid #e9eef6;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      flex: 1 1 280px;
      max-width: 360px;
    }

    .feature:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-md);
      border-color: #cbdffc;
      background: #ffffff;
    }

    /* Add icons via pseudo-elements with better emoji presentation */
    .feature h3 {
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      color: var(--brand-dark);
      margin: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
    }

    .feature h3::before {
      font-size: 2.6rem;
      line-height: 1;
      display: block;
      margin-bottom: 4px;
      filter: drop-shadow(0 6px 8px rgba(0, 0, 0, 0.06));
    }

    .feature:nth-child(1) h3::before {
      content: "🎫";
    }

    .feature:nth-child(2) h3::before {
      content: "📊";
    }

    .feature:nth-child(3) h3::before {
      content: "🛠️";
    }

    .feature p {
      color: var(--text-muted);
      line-height: 1.7;
      font-size: 1.02rem;
      margin: 0;
      max-width: 260px;
      margin-left: auto;
      margin-right: auto;
    }

    /* ---------- DIVIDER / SEPARATOR (subtle) ---------- */
    .section-divider {
      width: 100%;
      height: 1px;
      background: linear-gradient(90deg, transparent, #d9e2ef, transparent);
      margin: 0 auto;
      max-width: 1100px;
    }

    /* ---------- FOOTER (deep & professional) ---------- */
    footer {
      background: #0b1f3b;
      color: #ccd9f0;
      text-align: center;
      padding: 32px 20px;
      margin-top: auto;
      font-size: 0.95rem;
      letter-spacing: 0.01em;
      border-top: 1px solid #1e3a8a;
      box-shadow: 0 -8px 20px -12px rgba(0, 0, 0, 0.2);
    }

    footer p {
      opacity: 0.9;
      font-weight: 400;
    }

    /* ---------- RESPONSIVE ---------- */
    @media (max-width: 900px) {
      .hero h1 {
        font-size: 3rem;
      }

      .hero p {
        font-size: 1.15rem;
      }
    }

    @media (max-width: 700px) {
      .navbar {
        padding: 14px 20px;
        flex-direction: column;
        align-items: flex-start;
      }

      .nav-links {
        width: 100%;
        justify-content: flex-start;
      }

      .nav-links a {
        padding: 8px 16px;
        font-size: 0.95rem;
      }

      .hero {
        padding: 60px 20px 40px;
      }

      .hero h1 {
        font-size: 2.5rem;
      }

      .hero p {
        font-size: 1.05rem;
        margin-bottom: 30px;
      }

      .buttons a {
        padding: 14px 24px;
        min-width: 150px;
        font-size: 1rem;
      }

      .features {
        padding: 40px 20px 60px;
        gap: 22px;
      }

      .feature {
        padding: 30px 22px;
        width: 100%;
        max-width: 100%;
      }

      .feature h3 {
        font-size: 1.4rem;
      }
    }

    @media (max-width: 480px) {
      .logo {
        font-size: 1.4rem;
      }

      .logo::before {
        font-size: 1.6rem;
      }

      .hero h1 {
        font-size: 2rem;
      }

      .hero p {
        font-size: 1rem;
      }

      .buttons {
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
      }

      .buttons a {
        width: 100%;
        justify-content: center;
      }

      .navbar {
        padding: 12px 16px;
      }

      .nav-links a {
        padding: 6px 14px;
        font-size: 0.9rem;
      }
    }

    /* additional subtle enhancements */
    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #2563eb, #60a5fa, #2563eb);
      opacity: 0.7;
    }

    /* smooth focus outlines for accessibility */
    a:focus-visible {
      outline: 3px solid #3b82f6;
      outline-offset: 3px;
      border-radius: 40px;
    }

    .feature {
      transition: transform 0.25s ease, box-shadow 0.35s ease, border-color 0.2s;
    }
  </style>
</head>

<body>

  <!-- NAVBAR with refined branding -->
  <nav class="navbar">
    <div class="logo">IT Help Desk</div>
    <div class="nav-links">
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="hero">
    <div class="hero-content">
      <h1>IT support made simple</h1>
      <p>
        Report technical issues, track your support requests,
        and get assistance from the IT support team — all in one place.
      </p>
      <div class="buttons">
        <a href="register.php" class="btn-primary">Get started</a>
        <a href="login.php" class="btn-secondary">Login</a>
      </div>
    </div>
  </section>

  <!-- subtle divider -->
  <div class="section-divider"></div>

  <!-- FEATURES SECTION -->
  <section class="features">
    <div class="feature">
      <h3>Submit tickets</h3>
      <p>
        Easily report hardware, software, network and other
        technical problems with our streamlined form.
      </p>
    </div>

    <div class="feature">
      <h3>Track issues</h3>
      <p>
        Follow the status of your support requests from
        submission to resolution with real‑time updates.
      </p>
    </div>

    <div class="feature">
      <h3>IT support</h3>
      <p>
        IT administrators can review, prioritise and manage
        reported technical issues efficiently.
      </p>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <p>&copy; 2026 IT Help Desk System — professional support, simplified.</p>
  </footer>

</body>
</html>