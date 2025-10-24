<?php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Healthcare Portal – Welcome</title>
  <link rel="stylesheet" href="/healthcare/style.css" />


  <!-- Page-specific styles -->
  <style>
    :root{
      --primary:#2a5d84;
      --muted:#667085;
      --bg:#f4f6f9;
      --card:#ffffff;
      --radius:14px;
      --shadow:0 10px 30px rgba(0,0,0,.08);
    }
  
    .lp-nav{position:sticky;top:0;z-index:50;background:var(--card);box-shadow:0 2px 12px rgba(0,0,0,.06)}
    .lp-nav__inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:14px 20px}
    .brand{font-weight:800;color:var(--primary);letter-spacing:.2px}
    .lp-links a{color:#1f2937;text-decoration:none;margin-left:16px}
    .lp-links a:hover{text-decoration:underline}


    /* Hero */
    .hero-wrap{background:linear-gradient(0deg, rgba(42,93,132,.75), rgba(42,93,132,.75)), url('https://images.unsplash.com/photo-1584982751601-97dcc096659c?q=80&w=1600&auto=format&fit=crop') center/cover no-repeat; color:#fff;}
    .hero{max-width:1100px;margin:0 auto;padding:80px 20px 70px}
    .hero h1{font-size:40px;line-height:1.15;margin:0 0 12px}
    .hero p{max-width:680px;font-size:18px;opacity:.95;margin-bottom:26px}
    .cta-row a{display:inline-block;background:#fff;color:#15415b;border-radius:10px;padding:12px 18px;text-decoration:none;margin-right:10px;box-shadow:0 6px 18px rgba(0,0,0,.15)}
    .cta-row a:hover{filter:brightness(.95)}


    /* Feature cards */
    .section{max-width:1100px;margin:32px auto;padding:0 20px}
    .kicker{color:var(--muted);font-size:14px;text-transform:uppercase;letter-spacing:.14em;margin-bottom:6px}
    .h2{font-size:28px;margin-bottom:8px;color:#111827}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
    .card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px}
    .card h3{margin:4px 0 6px;color:#0f172a}
    .card p{color:#4b5563;font-size:14.5px}


    /* Gallery */
    .gallery{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin-top:16px}
    .gallery img{width:100%;height:160px;object-fit:cover;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.06)}


    /* Testimonial */
    .quote{background:var(--card);box-shadow:var(--shadow);border-radius:var(--radius);padding:22px;font-style:italic;color:#334155}


    /* Footer */
    .lp-footer{margin:40px 0 24px;color:#6b7280;text-align:center}


    /* Utility */
    .btn{display:inline-block;background:var(--primary);color:#fff;padding:10px 16px;border-radius:10px;text-decoration:none}
    .btn:hover{filter:brightness(.95)}
  </style>
</head>
<body style="background:var(--bg)">


<!-- NAV -->
<div class="lp-nav">
  <div class="lp-nav__inner">
    <div class="brand">Healthcare Portal</div>
    <div class="lp-links">
      <a href="index.php">Home</a>
      <?php if ($isLoggedIn): ?>
        <a href="<?= $role==='doctor' ? 'doctor/view_records.php' : 'patient/view_records.php' ?>">Dashboard</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Create Account</a>
      <?php endif; ?>
    </div>
  </div>
</div>


<div class="hero-wrap">
  <div class="hero">
    <h1>Your health data, beautifully organized.</h1>
    <p>Securely access visit summaries, manage profile details, and (for doctors) review patient records, all in one streamlined portal.</p>
    <div class="cta-row">
      <?php if (!$isLoggedIn): ?>
        <a href="login.php">Login</a>
        <a href="register.php">Create Account</a>
      <?php else: ?>
        <a href="<?= $role==='doctor' ? 'doctor/view_records.php' : 'patient/view_records.php' ?>">Go to Dashboard</a>
      <?php endif; ?>
    </div>
  </div>
</div>


  <!-- FEATURES -->
  <section class="section">
    <div class="kicker">Why choose us</div>
    <h2 class="h2">Fast, secure, and easy to use</h2>
    <div class="grid">
      <div class="card">
        <h3>Secure by default</h3>
        <p>Follows best practices; use hashed passwords and role-based access. Sessions keep you safely signed in.</p>
      </div>
      <div class="card">
        <h3>Patient-first design</h3>
        <p>Simple dashboards that surface what matters: visits, contacts, and health history.</p>
      </div>
      <div class="card">
        <h3>Doctor tools</h3>
        <p>Quickly view patients, add visit notes, and generate reports to share.</p>
      </div>
      <div class="card">
        <h3>Anywhere access</h3>
        <p>Runs locally on XAMPP and can be deployed to any PHP host later.</p>
      </div>
    </div>
  </section>


  <!-- GALLERY-->
  <section class="section">
    <div class="kicker">A quick look</div>
    <h2 class="h2">Clean, modern, accessible</h2>
    <div class="gallery">
      <img alt="Clinic hallway" src="https://img-new.cgtrader.com/items/1889561/c9ec1a266b/hospital-hallway-3-max-3d-model-c9ec1a266b.webp" />
      <img alt="Doctor workstation" src="https://images.unsplash.com/photo-1586773860418-d37222d8fce3?q=80&w=1200&auto=format&fit=crop" />
      <img alt="Tablet with records" src="https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?q=80&w=1200&auto=format&fit=crop" />
      <img alt="Medical team" src="https://www.99mgmt.com/hs-fs/hubfs/iStock-998313080.jpg?width=2121&name=iStock-998313080.jpg" />
    </div>
  </section>


   
  <section class="section">
    <div class="quote">
      “I can find what I need in seconds. The new portal saves us time every day.” — Dr. Daniels
    </div>
    <div style="margin-top:16px">
      <?php if (!$isLoggedIn): ?>
        <a class="btn" href="/healthcare/register.php">Create your account</a>
        <a class="btn" href="/healthcare/index.php" style="margin-left:8px;background:#0f3a57;">I already have an account</a>
      <?php else: ?>
        
        <a class="btn" href="<?= $role==='doctor' ? '/healthcare/doctor/view_patient.php' : '/healthcare/patient/view_records.php' ?>">
          Open my dashboard
        </a>
      <?php endif; ?>
    </div>
  </section>


  <div class="lp-footer">© <?= date('Y') ?> Healthcare Portal</div>
</body>
</html>
