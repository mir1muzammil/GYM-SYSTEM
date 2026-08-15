<aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel">
      <div class="pull-left image">
        <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <form action="#" method="get" class="sidebar-form">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search...">
        <span class="input-group-btn">
          <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
        </span>
      </div>
    </form>

    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MAIN NAVIGATION</li>

      <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
        <a href="index.php">
          <i class="fa fa-dashboard"></i> <span>Dashboard</span>
        </a>
      </li>

      <li class="header">MEMBER MANAGEMENT</li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'members_manage') !== false) ? 'active' : ''; ?>">
        <a href="members_manage.php">
          <i class="fa fa-users"></i> <span>Members</span>
        </a>
      </li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'membership_manage') !== false) ? 'active' : ''; ?>">
        <a href="membership_manage.php">
          <i class="fa fa-id-card"></i> <span>Membership Types</span>
        </a>
      </li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'attendance_manage') !== false) ? 'active' : ''; ?>">
        <a href="attendance_manage.php">
          <i class="fa fa-calendar-check-o"></i> <span>Attendance</span>
        </a>
      </li>

      <li class="header">TRAINING</li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'trainers_manage') !== false) ? 'active' : ''; ?>">
        <a href="trainers_manage.php">
          <i class="fa fa-user-plus"></i> <span>Trainers</span>
        </a>
      </li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'workout_plans_manage') !== false) ? 'active' : ''; ?>">
        <a href="workout_plans_manage.php">
          <i class="fa fa-clipboard"></i> <span>Workout Plans</span>
        </a>
      </li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'exercises_manage') !== false) ? 'active' : ''; ?>">
        <a href="exercises_manage.php">
          <i class="fa fa-heartbeat"></i> <span>Exercises</span>
        </a>
      </li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'schedules_manage') !== false) ? 'active' : ''; ?>">
        <a href="schedules_manage.php">
          <i class="fa fa-calendar"></i> <span>Workout Schedules</span>
        </a>
      </li>

      <li class="header">FINANCE & RESOURCES</li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'payments_manage') !== false) ? 'active' : ''; ?>">
        <a href="payments_manage.php">
          <i class="fa fa-money"></i> <span>Payments</span>
        </a>
      </li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'payments_manage') !== false) ? 'active' : ''; ?>">
        <a href="payment_requests_manage.php">
          <i class="fa fa-money"></i> <span>Payments Request</span>
        </a>
      </li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'equipment_manage') !== false) ? 'active' : ''; ?>">
        <a href="equipment_manage.php">
          <i class="fa fa-cogs"></i> <span>Equipment</span>
        </a>
      </li>

      <li class="<?php echo (strpos($_SERVER['PHP_SELF'], 'staff_manage') !== false) ? 'active' : ''; ?>">
        <a href="staff_manage.php">
          <i class="fa fa-user-secret"></i> <span>Staff</span>
        </a>
      </li>

      <li class="header">SYSTEM</li>

      <li>
        <a href="logout.php">
          <i class="fa fa-sign-out"></i> <span>Logout</span>
        </a>
      </li>
    </ul>
  </section>
</aside>