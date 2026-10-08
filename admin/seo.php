<?php
declare(strict_types=1);
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$ok = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkCsrf((string)($_POST['csrf'] ?? ''))) {
        $error = 'Neplatný požadavek, zkuste to znovu.';
    } else {
        try {
            saveSetting('site_title', sanitizeRich(trim((string)($_POST['site_title'] ?? ''))));
            saveSetting('site_description', sanitizeRich(trim((string)($_POST['site_description'] ?? ''))));
            saveSetting('seo_keywords', sanitizeRich(trim((string)($_POST['seo_keywords'] ?? ''))));
            $canonical = trim((string)($_POST['canonical_url'] ?? ''));
            if ($canonical !== '' && !filter_var($canonical, FILTER_VALIDATE_URL)) {
                throw new RuntimeException('Adresa webu (canonical URL) nevypadá jako platná adresa.');
            }
            if ($canonical !== '') saveSetting('canonical_url', $canonical);
            saveSetting('robots_index', !empty($_POST['robots_index']) ? '1' : '0');

            $ga = trim((string)($_POST['ga_measurement_id'] ?? ''));
            if ($ga !== '' && !preg_match('/^[A-Za-z0-9\-]+$/', $ga)) {
                throw new RuntimeException('Měřicí ID smí obsahovat jen písmena, číslice a pomlčky (např. G-XXXXXXXXXX).');
            }
            saveSetting('ga_measurement_id', $ga);

            if (!empty($_FILES['og_image']['name'])) {
                $filename = handleUpload($_FILES['og_image'], 'image');
                saveSetting('og_image', $filename);
            }

            $ok = true;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$csrf = csrfToken();
$ogPreview = settingImageUrl(setting('og_image', 'og.jpg'));
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SEO a Analytics | Administrace</title>
<style>
  :root{--brown:#6b4a30;}
  *{box-sizing:border-box;}
  body{font-family:"Open Sans",Arial,sans-serif;background:#f4f1ee;margin:0;padding:32px 16px;color:#2a2a2a;}
  .box{background:#fff;border-radius:14px;padding:32px;max-width:640px;margin:0 auto;box-shadow:0 10px 30px rgba(0,0,0,.06);}
  h1{font-size:1.4rem;margin:0 0 4px;font-family:"Roboto",sans-serif;}
  p.sub{color:#777;margin:0 0 24px;font-size:.9rem;}
  fieldset{border:1px solid #e6e1da;border-radius:10px;padding:18px 18px 6px;margin-bottom:20px;}
  legend{padding:0 8px;font-weight:700;font-size:.92rem;color:var(--brown);}
  label{display:block;font-size:.85rem;margin-bottom:6px;color:#444;font-weight:600;}
  .hint{font-size:.78rem;color:#888;margin:-8px 0 14px;}
  input[type=text], input[type=url], textarea{width:100%;padding:10px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:.95rem;margin-bottom:14px;font-family:inherit;}
  textarea{resize:vertical;min-height:70px}
  input:focus, textarea:focus{outline:none;border-color:var(--brown);}
  .chk{display:flex;align-items:center;gap:8px;font-weight:600;font-size:.88rem;margin-bottom:16px;}
  .chk input{width:auto;margin:0;}
  img.og-preview{max-width:220px;border-radius:8px;display:block;margin-bottom:10px;border:1px solid #eee;}
  button{padding:12px 20px;background:var(--brown);color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;}
  button:hover{background:#553a25;}
  .err{background:#fdecea;color:#b3261e;padding:10px 12px;border-radius:8px;font-size:.87rem;margin-bottom:16px;}
  .ok{background:#e7f5ea;color:#1e7d34;padding:10px 12px;border-radius:8px;font-size:.87rem;margin-bottom:16px;}
  a.back{display:inline-block;margin-top:16px;color:#888;font-size:.85rem;text-decoration:none;}
</style>
</head>
<body>
  <form class="box" method="post" enctype="multipart/form-data">
    <h1>SEO a Google Analytics</h1>
    <p class="sub">Nastavení, která nejsou vidět přímo na stránce, ale ovlivňují vyhledávače a sdílení.</p>
    <?php if ($error): ?><div class="err"><?= out($error) ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="ok">Uloženo.</div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= out($csrf) ?>">

    <fieldset>
      <legend>Základní SEO</legend>
      <label>Titulek stránky (zobrazí se jako název karty v Google a v záložce prohlížeče)</label>
      <input type="text" name="site_title" value="<?= out(setting('site_title')) ?>" maxlength="180">

      <label>Meta popis (krátký text pod odkazem ve výsledcích vyhledávání)</label>
      <textarea name="site_description" maxlength="300"><?= out(setting('site_description')) ?></textarea>

      <label>Klíčová slova (nepovinné, dnes už mají malý význam, oddělujte čárkou)</label>
      <input type="text" name="seo_keywords" value="<?= out(setting('seo_keywords')) ?>" placeholder="kuchyně na míru, truhlářství, dřevěné schody">

      <label>Hlavní adresa webu (canonical URL)</label>
      <input type="url" name="canonical_url" value="<?= out(setting('canonical_url', 'https://www.trukra.cz/')) ?>" placeholder="https://www.vasedomena.cz/">
      <p class="hint">Používá se i pro sdílené odkazy a strukturovaná data pro Google.</p>

      <label class="chk"><input type="checkbox" name="robots_index" <?= setting('robots_index', '1') !== '0' ? 'checked' : '' ?>> Povolit vyhledávačům indexaci webu</label>
      <p class="hint">Vypněte jen dočasně, např. dokud web ještě není hotový k zveřejnění.</p>
    </fieldset>

    <fieldset>
      <legend>Obrázek pro sdílení (Facebook, WhatsApp…)</legend>
      <img class="og-preview" src="../<?= out($ogPreview) ?>?v=<?= time() ?>" alt="Náhled OG obrázku">
      <label>Nahradit obrázek</label>
      <input type="file" name="og_image" accept="image/*">
      <p class="hint">Doporučený poměr stran cca 1200×630 px.</p>
    </fieldset>

    <fieldset>
      <legend>Google Analytics</legend>
      <label>Měřicí ID (Measurement ID)</label>
      <input type="text" name="ga_measurement_id" value="<?= out(setting('ga_measurement_id')) ?>" placeholder="G-XXXXXXXXXX">
      <p class="hint">Najdete ho v Google Analytics (Nastavení administrátora → Datové streamy → váš web →
        „ID měření“). Vypadá jako G-XXXXXXXXXX. Pole nechte prázdné, pokud analytiku nechcete používat.</p>
    </fieldset>

    <button type="submit">Uložit</button>
    <br>
    <a class="back" href="../index.php">&larr; zpět na web</a>
  </form>
</body>
</html>
