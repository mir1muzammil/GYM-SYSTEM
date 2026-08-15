<?php
// Shared header for authenticated user pages
// Expects $page_title and $active_page to be set before include
$initials = strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1));
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title ?? 'Dashboard') ?> — Fitness Club</title>
<link rel="stylesheet" href="style.css">
<?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>

<nav class="navbar">
  <a href="dashboard.php" class="nav-logo">FITNESS<span>CLUB</span></a>
  <div class="nav-links">
    <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="my_schedule.php" class="<?= $current === 'my_schedule.php' ? 'active' : '' ?>">My Schedule</a>
    <a href="exercises.php" class="<?= $current === 'exercises.php' ? 'active' : '' ?>">Exercises</a>
    <a href="trainers.php" class="<?= $current === 'trainers.php' ? 'active' : '' ?>">Trainers</a>
    <a href="my_payments.php" class="<?= $current === 'my_payments.php' ? 'active' : '' ?>">Payments</a>
  </div>
  <div class="nav-right">
    <a href="profile.php" class="nav-user">
      <div class="avatar"><?= $initials ?></div>
      <?= htmlspecialchars($_SESSION['user_name'] ?? 'Member') ?>
    </a>
  </div>
</nav>

<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">Overview</div>
    <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="my_attendance.php" class="<?= $current === 'my_attendance.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
      Attendance
    </a>

    <div class="sidebar-section">Training</div>
    <a href="my_schedule.php" class="<?= $current === 'my_schedule.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      My Schedule
    </a>
    <a href="exercises.php" class="<?= $current === 'exercises.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4v16M18 4v16M3 8h3M18 8h3M3 16h3M18 16h3M6 12h12"/></svg>
      Exercises
    </a>
    <a href="trainers.php" class="<?= $current === 'trainers.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M3 21v-2a7 7 0 0114 0v2"/></svg>
      Trainers
    </a>

    <div class="sidebar-section">Finance</div>
    <a href="my_payments.php" class="<?= $current === 'my_payments.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      Payments
    </a>
    <a href="membership.php" class="<?= $current === 'membership.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
      Membership
    </a>

    <div class="sidebar-section">Account</div>
    <a href="profile.php" class="<?= $current === 'profile.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Profile
    </a>
    <a href="user_logout.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sign Out
    </a>
  </aside>

  <main class="main-content">