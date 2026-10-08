<?php
declare(strict_types=1);
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$error = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkCsrf((string)($_POST['csrf'] ?? ''))) {
        $error = 'Neplatný požadavek, zkuste to znovu.';
    } else {
        $current = trim((string)($_POST['current'] ?? ''));
        $new1 = trim((string)($_POST['new1'] ?? ''));
        $new2 = trim((string)($_POST['new2'] ?? ''));
        $hash = setting('admin_password_hash', ADMIN_PASSWORD_HASH);

        if (!password_verify($current, $hash)) {
            $error = 'Současné heslo není správně.';
        } elseif (strlen($new1) < 6) {
            $error = 'Nové heslo musí mít alespoň 6 znaků.';
        } elseif ($new1 !== $new2) {
            $error = 'Nová hesla se neshodují.';
        } else {
            saveSetting('admin_password_hash', password_hash($new1, PASSWORD_BCRYPT));
            $ok = true;
        }
    }
}
$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Změna hesla | Administrace</title>
<style>
  :root{--brown:#6b4a30;}
  *{box-sizing:border-box;}
  body{font-family:"Open Sans",Arial,sans-serif;background:#f4f1ee;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;}
  .box{background:#fff;border-radius:14px;padding:36px;width:100%;max-width:380px;box-shadow:0 10px 30px rgba(0,0,0,.08);}
  h1{font-size:1.25rem;margin:0 0 20px;}
  label{display:block;font-size:.85rem;margin-bottom:6px;color:#444;font-weight:600;}
  input{width:100%;padding:12px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:1rem;margin-bottom:16px;}
  input:focus{outline:none;border-color:var(--brown);}
  button{width:100%;padding:13px;background:var(--brown);color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;}
  .err{background:#fdecea;color:#b3261e;padding:10px 12px;border-radius:8px;font-size:.87rem;margin-bottom:16px;}
  .ok{background:#e7f5ea;color:#1e7d34;padding:10px 12px;border-radius:8px;font-size:.87rem;margin-bottom:16px;}
  a.back{display:block;text-align:center;margin-top:16px;color:#888;font-size:.85rem;text-decoration:none;}
</style>
</head>
<body>
  <form class="box" method="post" autocomplete="off">
    <h1>Změna hesla do administrace</h1>
    <?php if ($error): ?><div class="err"><?= out($error) ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="ok">Heslo bylo úspěšně změněno.</div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= out($csrf) ?>">
    <label>Současné heslo</label>
    <input type="password" name="current" required autocomplete="current-password" autocapitalize="off" autocorrect="off" spellcheck="false">
    <label>Nové heslo</label>
    <input type="password" name="new1" required autocomplete="new-password" autocapitalize="off" autocorrect="off" spellcheck="false">
    <label>Nové heslo znovu</label>
    <input type="password" name="new2" required autocomplete="new-password" autocapitalize="off" autocorrect="off" spellcheck="false">
    <button type="submit">Uložit nové heslo</button>
    <a class="back" href="../index.php">&larr; zpět na web</a>
  </form>
</body>
</html>
