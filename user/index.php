<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fitness Club — Forge Your Best Self</title>
<link rel="stylesheet" href="style.css">
<style>
.public-nav { background: transparent; border-bottom: none; }
.public-nav.scrolled { background: rgba(10,10,10,0.95); border-bottom: 1px solid var(--border); }
.membership-section { padding: 6rem 2.5rem; }
.trainers-section { padding: 5rem 2.5rem; background: var(--dark); }
.cta-section {
  padding: 6rem 2.5rem; text-align: center;
  background: linear-gradient(135deg, rgba(200,245,66,0.06) 0%, transparent 50%);
  border-top: 1px solid var(--border);
}
.cta-section h2 { font-family: var(--font-display); font-size: clamp(3rem,7vw,5.5rem); letter-spacing: 3px; margin-bottom: 1.5rem; }
.number-strip {
  display: flex; gap: 0; overflow: hidden;
  border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
}
.number-item {
  flex: 1; padding: 2.5rem; text-align: center;
  border-right: 1px solid var(--border);
}
.number-item:last-child { border-right: none; }
.number-val { font-family: var(--font-display); font-size: 3.5rem; line-height: 1; color: var(--lime); }
.number-lbl { font-size: 0.8rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.5rem; }
footer {
  padding: 2.5rem; text-align: center;
  border-top: 1px solid var(--border);
  color: var(--muted); font-size: 0.875rem;
}
footer a { color: var(--lime); text-decoration: none; }
</style>
</head>
<body>

<nav class="navbar public-nav" id="mainNav">
  <a href="index.php" class="nav-logo">FITNESS<span>CLUB</span></a>
  <div class="nav-links">
    <a href="#memberships">Memberships</a>
    <a href="#trainers">Trainers</a>
    <a href="#about">About</a>
  </div>
  <div class="nav-right">
    <a href="user_login.php" class="btn btn-outline btn-sm">Sign In</a>
    <a href="register.php" class="btn btn-primary btn-sm">Join Now</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-content">
    <div class="hero-eyebrow">⚡ Karachi's Premier Fitness Club</div>
    <h1>FORGE<em>YOUR BEST</em>SELF</h1>
    <p>World-class equipment, expert trainers, and a community that pushes you beyond your limits. Your transformation starts today.</p>
    <div class="hero-actions">
      <a href="register.php" class="btn btn-primary btn-lg">Start Free Today</a>
      <a href="#memberships" class="btn btn-outline btn-lg">View Plans</a>
    </div>
  </div>
</section>

<!-- NUMBERS -->
<div class="number-strip">
  <div class="number-item"><div class="number-val">200+</div><div class="number-lbl">Active Members</div></div>
  <div class="number-item"><div class="number-val">5</div><div class="number-lbl">Expert Trainers</div></div>
  <div class="number-item"><div class="number-val">50+</div><div class="number-lbl">Pieces of Equipment</div></div>
  <div class="number-item"><div class="number-val">5</div><div class="number-lbl">Workout Programs</div></div>
</div>

<!-- FEATURES -->
<section class="features" id="about">
  <div class="section-header">
    <h2>WHY CHOOSE US</h2>
    <p>Everything you need to reach your fitness goals, all in one place.</p>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">🏋️</div>
      <div class="feature-title">Premium Equipment</div>
      <div class="feature-desc">State-of-the-art machines including treadmills, ellipticals, cable machines, and a full free-weights section.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">👊</div>
      <div class="feature-title">Expert Trainers</div>
      <div class="feature-desc">Certified coaches specializing in weight training, yoga, HIIT, CrossFit, boxing, and more.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📊</div>
      <div class="feature-title">Track Your Progress</div>
      <div class="feature-desc">Monitor your attendance, active workout plans, and payment history all from your personal dashboard.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📅</div>
      <div class="feature-title">Flexible Plans</div>
      <div class="feature-desc">Monthly, quarterly, semi-annual, annual, and student memberships to fit your lifestyle and budget.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🧘</div>
      <div class="feature-title">Diverse Programs</div>
      <div class="feature-desc">From beginner strength training to advanced CrossFit — structured programs designed for real results.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🔒</div>
      <div class="feature-title">Safe & Clean</div>
      <div class="feature-desc">Professionally maintained facilities with dedicated cleaning staff and 24/7 security.</div>
    </div>
  </div>
</section>

<!-- MEMBERSHIPS -->
<?php
require_once 'user_db.php';
$memberships = $conn->query("SELECT * FROM Membership ORDER BY Price ASC");
?>
<section class="membership-section" id="memberships">
  <div class="section-header">
    <h2>MEMBERSHIP PLANS</h2>
    <p>Pick the plan that works for you. No hidden fees, no surprises.</p>
  </div>
  <div class="plan-grid" style="max-width:1100px;margin:0 auto;">
    <?php while ($m = $memberships->fetch_assoc()): ?>
    <div class="plan-card">
      <div class="plan-name"><?= htmlspecialchars($m['TypeName']) ?></div>
      <div class="plan-price">Rs. <?= number_format($m['Price']) ?><span> /plan</span></div>
      <div class="plan-duration"><?= $m['DurationMonths'] ?> month<?= $m['DurationMonths'] > 1 ? 's' : '' ?> access</div>
      <a href="register.php?plan=<?= $m['MembershipTypeID'] ?>" class="btn btn-outline btn-sm btn-block mt-3">Get Started</a>
    </div>
    <?php endwhile; ?>
  </div>
</section>

<!-- TRAINERS -->
<?php $trainers = $conn->query("SELECT * FROM Trainer ORDER BY Name LIMIT 4"); ?>
<section class="trainers-section" id="trainers">
  <div class="section-header">
    <h2>MEET OUR TRAINERS</h2>
    <p>World-class coaches ready to guide your journey.</p>
  </div>
  <div class="trainer-grid" style="max-width:1100px;margin:0 auto;">
    <?php while ($t = $trainers->fetch_assoc()): ?>
    <div class="trainer-card">
      <div class="trainer-avatar"><?= strtoupper(substr($t['Name'],0,1)) ?></div>
      <div class="trainer-name"><?= htmlspecialchars($t['Name']) ?></div>
      <div class="trainer-spec"><?= htmlspecialchars($t['Specialization']) ?></div>
      <div class="trainer-contact"><?= htmlspecialchars($t['ContactNumber']) ?></div>
    </div>
    <?php endwhile; ?>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <h2>READY TO START?</h2>
  <p class="text-muted mb-3" style="font-size:1rem;max-width:500px;margin:0 auto 2rem;">Join hundreds of members who have already transformed their lives at Fitness Club.</p>
  <a href="register.php" class="btn btn-primary btn-lg">Create Your Account</a>
</section>

<footer>
  <p>&copy; <?= date('Y') ?> <strong>Fitness Club</strong> — Karachi, Pakistan &nbsp;|&nbsp; <a href="user_login.php">Member Login</a></p>
</footer>

<script>
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 50);
});
</script>
</body>
</html>