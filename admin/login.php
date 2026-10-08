<?php
declare(strict_types=1);
require_once __DIR__ . '/../inc/functions.php';

$error = '';

if (isAdmin()) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim((string)($_POST['password'] ?? ''));
    // Heslo může být buď v config.php (ADMIN_PASSWORD_HASH), nebo po změně v DB (settings.admin_password_hash)
    $hash = setting('admin_password_hash', ADMIN_PASSWORD_HASH);
    if ($password !== '' && password_verify($password, $hash)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: ../index.php');
        exit;
    }
    $error = 'Nesprávné heslo.';
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Přihlášení do administrace | Truhlářství Kratochvíl</title>
<style>
  :root{--brown:#6b4a30;--ink:#2a2a2a;}
  *{box-sizing:border-box;}
  body{font-family:"Open Sans",Arial,sans-serif;background:#1f1b17;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;}
  .box{background:#fff;border-radius:14px;padding:40px 36px;width:100%;max-width:360px;box-shadow:0 20px 60px rgba(0,0,0,.4);}
  h1{font-size:1.3rem;margin:0 0 4px;color:var(--ink);}
  p.sub{color:#777;margin:0 0 24px;font-size:.9rem;}
  label{display:block;font-size:.85rem;margin-bottom:6px;color:#444;font-weight:600;}
  #password{width:100%;padding:12px 44px 12px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:1rem;margin-bottom:18px;box-sizing:border-box;font-family:inherit;}
  #password:focus{outline:none;border-color:var(--brown);}
  .pw-wrap{position:relative;}
  .pw-toggle{position:absolute;right:10px;top:11px;background:none;border:none;color:#888;font-size:.78rem;font-weight:700;cursor:pointer;padding:6px;width:auto;}
  .pw-toggle:hover{background:none;color:var(--brown);}
  button{width:100%;padding:13px;background:var(--brown);color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;}
  button:hover{background:#553a25;}
  .err{background:#fdecea;color:#b3261e;padding:10px 12px;border-radius:8px;font-size:.87rem;margin-bottom:16px;}
  a.back{display:block;text-align:center;margin-top:18px;color:#888;font-size:.85rem;text-decoration:none;}
</style>
</head>
<body>
  <form class="box" method="post" autocomplete="off">
    <h1>Administrace webu</h1>
    <p class="sub">Truhlářství Kratochvíl</p>
    <?php if ($error): ?><div class="err"><?= out($error) ?></div><?php endif; ?>
    <label for="password">Heslo</label>
    <div class="pw-wrap">
      <input type="password" id="password" name="password" required autofocus
             autocomplete="current-password" autocapitalize="off" autocorrect="off" spellcheck="false">
      <button type="button" class="pw-toggle" id="pwToggle">ZOBRAZIT</button>
    </div>
    <button type="submit">Přihlásit se</button>
    <a class="back" href="../index.php">&larr; zpět na web</a>
  </form>
  <script>
    document.getElementById('pwToggle').addEventListener('click', function () {
      var f = document.getElementById('password');
      var showing = f.type === 'text';
      f.type = showing ? 'password' : 'text';
      this.textContent = showing ? 'ZOBRAZIT' : 'SKRÝT';
    });
  </script>
</body>
</html>
