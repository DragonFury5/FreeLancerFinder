<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$errors = [];
$old = [
    'name' => '', 'email' => '', 'role' => 'customer',
    'title' => '', 'skill' => '', 'location' => '', 'bio' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim($_POST[$k] ?? $old[$k]);
    }
    $password  = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password_confirm'] ?? '');

    if ($old['name'] === '')                            $errors['name'] = 'Please enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email.';
    if (strlen($password) < 6)                          $errors['password'] = 'Password must be at least 6 characters.';
    if ($password !== $password2)                       $errors['password_confirm'] = 'Passwords do not match.';
    if (!in_array($old['role'], ['customer', 'lancer'], true)) $old['role'] = 'customer';

    if ($old['role'] === 'lancer') {
        if ($old['title'] === '') $errors['title'] = 'Please add a short title (e.g. "UI Designer").';
        if ($old['skill'] === '') $errors['skill'] = 'Please add your main skill or category.';
    }

    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) $errors['email'] = 'That email is already registered.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$old['name'], $old['email'], $hash, $old['role']]);
            $userId = (int)$pdo->lastInsertId();

            if ($old['role'] === 'lancer') {
                $stmt = $pdo->prepare("INSERT INTO lancers (user_id, title, skill, bio, location) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $old['title'], $old['skill'], $old['bio'], $old['location']]);
            }

            $pdo->commit();

            $_SESSION['user'] = [
                'id'    => $userId,
                'name'  => $old['name'],
                'email' => $old['email'],
                'role'  => $old['role'],
            ];

            header('Location: profile.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}

$pageTitle = 'Sign up — FLF';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <div class="auth-card">
    <h1>Create your account</h1>
    <p class="auth-sub">Join FLF as a customer looking for talent, or as a Lancer offering it.</p>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <fieldset>
        <legend>I want to…</legend>
        <div class="role-choice">
          <label class="role-opt">
            <input type="radio" name="role" value="customer" <?= $old['role'] === 'customer' ? 'checked' : '' ?>>
            <span>Hire a Lancer<small>I'm a customer</small></span>
          </label>
          <label class="role-opt">
            <input type="radio" name="role" value="lancer" <?= $old['role'] === 'lancer' ? 'checked' : '' ?>>
            <span>Offer my skills<small>I'm a Lancer</small></span>
          </label>
        </div>
      </fieldset>

      <div class="field">
        <label for="name">Full name</label>
        <input class="input" id="name" name="name" type="text" value="<?= e($old['name']) ?>" required>
        <?php if (!empty($errors['name'])): ?><div class="err"><?= e($errors['name']) ?></div><?php endif; ?>
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input class="input" id="email" name="email" type="email" value="<?= e($old['email']) ?>" required>
        <?php if (!empty($errors['email'])): ?><div class="err"><?= e($errors['email']) ?></div><?php endif; ?>
      </div>

      <div class="field-row">
        <div class="field">
          <label for="password">Password</label>
          <input class="input" id="password" name="password" type="password" required>
          <?php if (!empty($errors['password'])): ?><div class="err"><?= e($errors['password']) ?></div><?php endif; ?>
        </div>
        <div class="field">
          <label for="password_confirm">Confirm password</label>
          <input class="input" id="password_confirm" name="password_confirm" type="password" required>
          <?php if (!empty($errors['password_confirm'])): ?><div class="err"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
        </div>
      </div>

      <div id="lancerFields" hidden>
        <div class="field">
          <label for="title">Title</label>
          <input class="input" id="title" name="title" type="text" placeholder="e.g. UI Designer" value="<?= e($old['title']) ?>">
          <?php if (!empty($errors['title'])): ?><div class="err"><?= e($errors['title']) ?></div><?php endif; ?>
        </div>
        <div class="field">
          <label for="skill">Main skill / category</label>
          <input class="input" id="skill" name="skill" type="text" placeholder="e.g. Design" value="<?= e($old['skill']) ?>">
          <?php if (!empty($errors['skill'])): ?><div class="err"><?= e($errors['skill']) ?></div><?php endif; ?>
        </div>
        <div class="field">
          <label for="location">Location</label>
          <input class="input" id="location" name="location" type="text" placeholder="e.g. Jakarta" value="<?= e($old['location']) ?>">
        </div>
        <div class="field">
          <label for="bio">Short bio</label>
          <textarea class="input" id="bio" name="bio" placeholder="Tell customers what you do and who you help."><?= e($old['bio']) ?></textarea>
        </div>
      </div>

      <button class="btn btn-primary btn-block btn-lg" type="submit">Create account</button>
    </form>

    <p class="auth-foot">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>