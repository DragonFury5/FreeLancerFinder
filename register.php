<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$errors = [];
$old = [
    'name' => '', 'email' => '', 'role' => 'customer',
    'title' => '', 'skill' => '', 'location' => '', 'bio' => '',
    'category' => 'Others',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim($_POST[$k] ?? $old[$k]);
    }
    $password  = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password_confirm'] ?? '');

    if ($old['name'] === '')                              $errors['name'] = t('register.err_name');
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))$errors['email'] = t('register.err_email');
    if (strlen($password) < 6)                            $errors['password'] = t('register.err_password');
    if ($password !== $password2)                         $errors['password_confirm'] = t('register.err_pw_match');
    if (!in_array($old['role'], ['customer', 'lancer'], true)) $old['role'] = 'customer';


      if ($old['role'] === 'lancer') {
        if ($old['title'] === '') $errors['title'] = t('register.err_title');
        if ($old['skill'] === '') $errors['skill'] = t('register.err_skill');
        if (!in_array($old['category'], ['IT', 'Service', 'Others'], true)) {
            $errors['category'] = t('register.err_category');
        }
    }

    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) $errors['email'] = t('register.err_email_used');
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

                        $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$old['name'], $old['email'], $hash, $old['role']]);
            $userId = (int)$pdo->lastInsertId();

            if ($old['role'] === 'lancer') {
                $stmt = $pdo->prepare(
                    "INSERT INTO lancers (user_id, title, skill, category, bio, location)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $userId,
                    $old['title'],
                    $old['skill'],
                    $old['category'],
                    $old['bio'],
                    $old['location'],
                ]);
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
            $errors['general'] = t('register.err_general');
        }
    }
}

$pageTitle = t('register.title') . ' — FLF';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <div class="auth-card">
    <h1><?= e(t('register.title')) ?></h1>
    <p class="auth-sub"><?= e(t('register.sub')) ?></p>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <fieldset>
        <legend><?= e(t('register.role_legend')) ?></legend>
        <div class="role-choice">
          <label class="role-opt">
            <input type="radio" name="role" value="customer" <?= $old['role'] === 'customer' ? 'checked' : '' ?>>
            <span><?= e(t('register.role_cust')) ?><small><?= e(t('register.role_cust_s')) ?></small></span>
          </label>
          <label class="role-opt">
            <input type="radio" name="role" value="lancer" <?= $old['role'] === 'lancer' ? 'checked' : '' ?>>
            <span><?= e(t('register.role_lanc')) ?><small><?= e(t('register.role_lanc_s')) ?></small></span>
          </label>
        </div>
      </fieldset>

      <div class="field">
        <label for="name"><?= e(t('register.name')) ?></label>
        <input class="input" id="name" name="name" type="text" value="<?= e($old['name']) ?>" required>
        <?php if (!empty($errors['name'])): ?><div class="err"><?= e($errors['name']) ?></div><?php endif; ?>
      </div>

      <div class="field">
        <label for="email"><?= e(t('register.email')) ?></label>
        <input class="input" id="email" name="email" type="email" value="<?= e($old['email']) ?>" required>
        <?php if (!empty($errors['email'])): ?><div class="err"><?= e($errors['email']) ?></div><?php endif; ?>
      </div>

      <div class="field-row">
        <div class="field">
          <label for="password"><?= e(t('register.password')) ?></label>
          <input class="input" id="password" name="password" type="password" required>
          <?php if (!empty($errors['password'])): ?><div class="err"><?= e($errors['password']) ?></div><?php endif; ?>
        </div>
        <div class="field">
          <label for="password_confirm"><?= e(t('register.password_c')) ?></label>
          <input class="input" id="password_confirm" name="password_confirm" type="password" required>
          <?php if (!empty($errors['password_confirm'])): ?><div class="err"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
        </div>
      </div>

        <div class="field">
          <label for="category"><?= e(t('register.category')) ?></label>
          <select class="input" id="category" name="category">
            <option value="IT"      <?= $old['category'] === 'IT'      ? 'selected' : '' ?>>IT</option>
            <option value="Service" <?= $old['category'] === 'Service' ? 'selected' : '' ?>><?= e(t('browse.filter_svc')) ?></option>
            <option value="Others"  <?= $old['category'] === 'Others'  ? 'selected' : '' ?>><?= e(t('browse.filter_other')) ?></option>
          </select>
          <p class="hint"><?= e(t('register.category_hint')) ?></p>
          <?php if (!empty($errors['category'])): ?>
            <div class="err"><?= e($errors['category']) ?></div>
          <?php endif; ?>
        </div>

      <button class="btn btn-primary btn-block btn-lg" type="submit"><?= e(t('register.submit')) ?></button>
    </form>

    <p class="auth-foot">
      <?= e(t('register.have')) ?>
      <a href="login.php"><?= e(t('register.login_link')) ?></a>
    </p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>