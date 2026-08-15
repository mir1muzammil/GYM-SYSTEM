<?php
session_start();
require_once 'user_db.php';
require_once 'user_auth.php';

$page_title = 'Exercises';
$uid = $_SESSION['user_id'];

$filter = trim($_GET['muscle'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = [];
if ($filter) $where[] = "MuscleGroup = '" . $conn->real_escape_string($filter) . "'";
if ($search) $where[] = "ExerciseName LIKE '%" . $conn->real_escape_string($search) . "%'";
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$exercises = $conn->query("SELECT * FROM Exercise $where_sql ORDER BY MuscleGroup, ExerciseName");
$muscles = $conn->query("SELECT DISTINCT MuscleGroup FROM Exercise WHERE MuscleGroup != '' ORDER BY MuscleGroup");

require_once 'user_header.php';
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <h1>EXERCISES</h1>
      <p>Browse our complete exercise library</p>
    </div>
  </div>
</div>

<!-- Filters -->
<form method="GET" style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
  <input type="text" name="q" class="form-control" placeholder="🔍 Search exercises..." value="<?= htmlspecialchars($search) ?>" style="max-width:260px;">
  <select name="muscle" class="form-control" style="width:auto;" onchange="this.form.submit()">
    <option value="">All Muscle Groups</option>
    <?php while ($m = $muscles->fetch_assoc()): ?>
      <option value="<?= htmlspecialchars($m['MuscleGroup']) ?>" <?= $filter===$m['MuscleGroup']?'selected':'' ?>><?= htmlspecialchars($m['MuscleGroup']) ?></option>
    <?php endwhile; ?>
  </select>
  <?php if ($filter || $search): ?>
    <a href="exercises.php" class="btn btn-outline btn-sm">Clear</a>
  <?php endif; ?>
</form>

<?php
// Group by muscle
$grouped = [];
$exercises_data = [];
while ($e = $exercises->fetch_assoc()) { $exercises_data[] = $e; }
foreach ($exercises_data as $e) {
  $grouped[$e['MuscleGroup'] ?: 'Other'][] = $e;
}

if (empty($grouped)):
?>
<div class="card" style="text-align:center;padding:3rem;">
  <p class="text-muted">No exercises found matching your search.</p>
</div>
<?php else: ?>

<?php foreach ($grouped as $muscle => $exs): ?>
<div style="margin-bottom:2rem;">
  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
    <span style="font-family:var(--font-display);font-size:1.4rem;letter-spacing:1px;color:var(--lime);"><?= strtoupper($muscle) ?></span>
    <span class="badge badge-gray"><?= count($exs) ?> exercises</span>
  </div>
  <div class="exercise-grid">
    <?php foreach ($exs as $e): ?>
    <div class="exercise-card">
      <div class="exercise-muscle"><?= htmlspecialchars($e['MuscleGroup']) ?></div>
      <div class="exercise-name"><?= htmlspecialchars($e['ExerciseName']) ?></div>
      <div class="exercise-meta">
        <div class="exercise-meta-item">
          <div class="val"><?= $e['Repetitions'] ?: '—' ?></div>
          <div class="lbl">Reps</div>
        </div>
        <div class="exercise-meta-item">
          <div class="val"><?= $e['Sets'] ?: '—' ?></div>
          <div class="lbl">Sets</div>
        </div>
        <div class="exercise-meta-item">
          <div class="val"><?= $e['EquipmentNeeded'] ? '⚙️' : '🤸' ?></div>
          <div class="lbl"><?= $e['EquipmentNeeded'] ? 'Equipment' : 'Bodyweight' ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php require_once 'user_footer.php'; ?>