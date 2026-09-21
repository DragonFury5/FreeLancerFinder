<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = t('login.err_email');
    if ($password === '')                           $errors['password'] = t('login.err_password');

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => (int)$user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];
            header('Location: profile.php');
            exit;
        }
        $errors['general'] = t('login.err_invalid');
    }
}

$pageTitle = t('login.title') . ' — FLF';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <div class="auth-card">
    <h1><?= e(t('login.title')) ?></h1>
    <p class="auth-sub"><?= e(t('login.sub')) ?></p>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="field">
        <label for="email"><?= e(t('login.email')) ?></label>
        <input class="input" id="email" name="email" type="email" value="<?= e($email) ?>" required autofocus>
        <?php if (!empty($errors['email'])): ?><div class="err"><?= e($errors['email']) ?></div><?php endif; ?>
      </div>

      <div class="field">
        <label for="password"><?= e(t('login.password')) ?></label>
        <input class="input" id="password" name="password" type="password" required>
        <?php if (!empty($errors['password'])): ?><div class="err"><?= e($errors['password']) ?></div><?php endif; ?>
      </div>

      <button class="btn btn-primary btn-block btn-lg" type="submit"><?= e(t('login.submit')) ?></button>
    </form>

    <p class="auth-foot">
      <?= e(t('login.no_account')) ?>
      <a href="register.php"><?= e(t('login.create')) ?></a>
    </p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>