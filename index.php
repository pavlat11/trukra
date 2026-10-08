<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/functions.php';

$admin = isAdmin();
$icons = allowedIcons();

$services = db()->query('SELECT * FROM services ORDER BY sort_order')->fetchAll();
$usp      = db()->query('SELECT * FROM usp_items ORDER BY sort_order')->fetchAll();
$stats    = db()->query('SELECT * FROM stats ORDER BY sort_order')->fetchAll();
$steps    = db()->query('SELECT * FROM steps ORDER BY sort_order')->fetchAll();
$reviews  = db()->query('SELECT * FROM reviews ORDER BY sort_order')->fetchAll();
$faqs     = db()->query('SELECT * FROM faqs ORDER BY sort_order')->fetchAll();
$gallery  = db()->query('SELECT * FROM gallery ORDER BY sort_order')->fetchAll();

$categories = [];
foreach ($gallery as $g) { $categories[$g['category']] = true; }
$catLabels = ['kuchyne' => 'Kuchyně', 'skrine' => 'Skříně', 'schody' => 'Schody', 'dilna' => 'Z dílny', 'sauna' => 'Sauny', 'ostatni' => 'Ostatní'];

/* ---- SEO / analytika ---- */
$canonicalUrl = rtrim(setting('canonical_url', 'https://www.trukra.cz/'), '/') . '/';
$robotsIndex  = setting('robots_index', '1') !== '0';
$ogImageUrl   = $canonicalUrl . settingImageUrl(setting('og_image', 'og.jpg'));
$gaId         = preg_replace('/[^A-Za-z0-9\-]/', '', setting('ga_measurement_id', ''));

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'HomeAndConstructionBusiness',
    'name' => setting('company_name'),
    'image' => $ogImageUrl,
    'url' => $canonicalUrl,
    'telephone' => setting('phone'),
    'email' => setting('email'),
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => setting('address'), 'addressCountry' => 'CZ'],
    'openingHours' => setting('hours'),
    'sameAs' => array_values(array_filter([setting('facebook_url'), setting('instagram_url')])),
];
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= out(setting('site_title')) ?></title>
<meta name="description" content="<?= out(setting('site_description')) ?>">
<?php if (setting('seo_keywords') !== ''): ?><meta name="keywords" content="<?= out(setting('seo_keywords')) ?>"><?php endif; ?>
<meta name="robots" content="<?= $robotsIndex ? 'index, follow' : 'noindex, nofollow' ?>">
<meta name="theme-color" content="#353535">
<link rel="canonical" href="<?= out($canonicalUrl) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= out($canonicalUrl) ?>">
<meta property="og:title" content="<?= out(setting('company_name')) ?> – nábytek na míru">
<meta property="og:description" content="<?= out(setting('site_description')) ?>">
<meta property="og:image" content="<?= out($ogImageUrl) ?>">
<meta property="og:locale" content="cs_CZ">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= out(setting('company_name')) ?> – nábytek na míru">
<meta name="twitter:description" content="<?= out(setting('site_description')) ?>">
<meta name="twitter:image" content="<?= out($ogImageUrl) ?>">
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php if ($gaId !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= out($gaId) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= out($gaId) ?>');
</script>
<?php endif; ?>
<link rel="icon" href="img/favicon.svg" type="image/svg+xml">
<link rel="preload" as="image" href="<?= out(settingImageUrl(setting('hero_bg_image'))) ?>" media="(min-width: 768px)">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500;700;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<?php if ($admin): ?><link rel="stylesheet" href="admin/css/admin.css"><?php endif; ?>
</head>
<body<?= $admin ? ' class="is-editing"' : '' ?>>

<a class="skip-link" href="#hlavni">Přeskočit na obsah</a>

<?php if (sectionVisible('announcement') && (setting('announcement_text') !== '' || $admin)): ?>
<!-- ══════════ UPOZORNĚNÍ ══════════ -->
<div class="announce<?= sectionHiddenForVisitor('announcement') ? ' adm-section-off' : '' ?>" id="announceBar" data-section="announcement" data-msg-key="<?= out(md5(setting('announcement_text'))) ?>">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Lišta s upozorněním (zobrazí se úplně nahoře na webu)
    <label class="adm-switch"><input type="checkbox" data-section-key="announcement" <?= sectionRawVisible('announcement') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap announce__in">
    <span class="announce__t"<?= admAttr('announcement_text') ?>><?= setting('announcement_text') ?: ($admin ? 'Klikněte sem a napište upozornění pro návštěvníky (např. dovolená, akce, změna otevírací doby)…' : '') ?></span>
    <button type="button" class="announce__x" id="announceClose" aria-label="Zavřít upozornění">&times;</button>
  </div>
</div>
<?php endif; ?>

<!-- ══════════ TOPBAR ══════════ -->
<div class="topbar">
  <div class="wrap topbar__in">
    <span class="tb"><svg class="ico" aria-hidden="true"><use href="#i-pin"></use></svg><?= out(setting('address')) ?></span>
    <span class="tb tb--opt"><svg class="ico" aria-hidden="true"><use href="#i-clock"></use></svg><?= out(setting('hours')) ?></span>
    <span class="tb__gap"></span>
    <a class="tb" href="<?= out(telHref(setting('phone'))) ?>"><svg class="ico" aria-hidden="true"><use href="#i-phone"></use></svg><?= out(setting('phone')) ?></a>
    <a class="tb tb--opt" href="mailto:<?= out(setting('email')) ?>"><svg class="ico" aria-hidden="true"><use href="#i-mail"></use></svg><?= out(setting('email')) ?></a>
  </div>
</div>

<!-- ══════════ HLAVIČKA ══════════ -->
<header class="hdr" id="hdr">
  <div class="wrap hdr__in">
    <a class="brand" href="#uvod" aria-label="<?= out(setting('company_name')) ?> – na úvod">
      <img src="img/logo.svg" alt="<?= out(setting('company_name')) ?>" width="150" height="118">
    </a>

    <nav class="nav" aria-label="Hlavní navigace">
      <a href="#uvod" class="nav__l">Úvod</a>
      <a href="#sluzby" class="nav__l">Služby</a>
      <a href="#o-nas" class="nav__l">O nás</a>
      <a href="#reference" class="nav__l">Reference</a>
      <a href="#proces" class="nav__l">Postup</a>
      <a href="#kontakt" class="nav__l">Kontakt</a>
    </nav>

    <div class="hdr__side">
      <div class="hsoc">
        <a href="<?= out(setting('facebook_url')) ?>" target="_blank" rel="noopener" aria-label="Sledujte nás na Facebooku" title="Facebook">
          <svg aria-hidden="true"><use href="#i-fb"></use></svg>
        </a>
        <a href="<?= out(setting('instagram_url')) ?>" target="_blank" rel="noopener" aria-label="Sledujte nás na Instagramu" title="Instagram">
          <svg aria-hidden="true"><use href="#i-ig"></use></svg>
        </a>
      </div>

      <a href="<?= out(telHref(setting('phone'))) ?>" class="hdr__tel">
        <svg class="ico" aria-hidden="true"><use href="#i-phone"></use></svg><span><?= out(setting('phone')) ?></span>
      </a>
      <a href="#poptavka" class="btn btn--primary btn--sm hdr__cta">Poptávka</a>
      <button class="burger" id="burger" type="button" aria-label="Otevřít menu" aria-expanded="false" aria-controls="menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<!-- ══════════ MOBILNÍ MENU ══════════ -->
<div class="scrim" id="scrim"></div>

<aside class="menu" id="menu" aria-label="Mobilní navigace">
  <div class="menu__top">
    <img src="img/logo.svg" alt="<?= out(setting('company_name')) ?>" width="122" height="96">
    <button class="menu__x" id="menuClose" type="button" aria-label="Zavřít menu">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
    </button>
  </div>

  <nav class="menu__nav" aria-label="Navigace v menu">
    <a href="#uvod">Úvod</a>
    <a href="#sluzby">Služby</a>
    <a href="#o-nas">O nás</a>
    <a href="#reference">Reference</a>
    <a href="#proces">Jak to probíhá</a>
    <a href="#faq">Časté dotazy</a>
    <a href="#kontakt">Kontakt</a>
  </nav>

  <div class="menu__soc">
    <span class="menu__soc-t">Sledujte nás</span>
    <div class="msoc">
      <a href="<?= out(setting('facebook_url')) ?>" target="_blank" rel="noopener"><svg aria-hidden="true"><use href="#i-fb"></use></svg>Facebook</a>
      <a href="<?= out(setting('instagram_url')) ?>" target="_blank" rel="noopener"><svg aria-hidden="true"><use href="#i-ig"></use></svg>Instagram</a>
    </div>
  </div>

  <a href="#poptavka" class="btn btn--primary btn--block menu__cta">Nezávazná poptávka</a>

  <div class="menu__foot">
    <a href="<?= out(telHref(setting('phone'))) ?>"><svg class="ico" aria-hidden="true"><use href="#i-phone"></use></svg><?= out(setting('phone')) ?></a>
    <a href="mailto:<?= out(setting('email')) ?>"><svg class="ico" aria-hidden="true"><use href="#i-mail"></use></svg><?= out(setting('email')) ?></a>
    <span><svg class="ico" aria-hidden="true"><use href="#i-pin"></use></svg><?= out(setting('address')) ?></span>
  </div>
</aside>

<main id="hlavni">

<?php if (sectionVisible('hero')): ?>
<!-- ══════════ HERO ══════════ -->
<section class="hero<?= sectionHiddenForVisitor('hero') ? ' adm-section-off' : '' ?>" id="uvod" data-section="hero">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: Úvod (hero) — nelze doporučit trvale skrýt, je to první, co návštěvník uvidí
    <label class="adm-switch"><input type="checkbox" data-section-key="hero" <?= sectionRawVisible('hero') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>

  <div class="hero__bg adm-img-wrap">
    <picture>
      <source media="(max-width: 767px)" srcset="<?= out(settingImageUrl(setting('hero_bg_image_mobile'))) ?>">
      <img src="<?= out(settingImageUrl(setting('hero_bg_image'))) ?>" alt="Kuchyně na míru z masivního dubu od <?= out(setting('company_name')) ?>" fetchpriority="high" decoding="async">
    </picture>
    <?php if ($admin): ?><button type="button" class="adm-img-btn" data-img-key="hero_bg_image">✎ Změnit fotku pozadí</button><?php endif; ?>
  </div>

  <div class="wrap hero__in">
    <p class="eyebrow eyebrow--lt reveal"<?= admAttr('hero_eyebrow', true) ?>><?= setting('hero_eyebrow') ?></p>
    <h1 class="hero__t reveal"<?= admAttr('hero_title') ?>><?= setting('hero_title') ?></h1>
    <p class="hero__p reveal"<?= admAttr('hero_text') ?>><?= setting('hero_text') ?></p>
    <div class="hero__cta reveal">
      <a href="#poptavka" class="btn btn--primary btn--lg"<?= admAttr('hero_cta1', true) ?>><?= setting('hero_cta1') ?></a>
      <a href="#reference" class="btn btn--ghost btn--lg"<?= admAttr('hero_cta2', true) ?>><?= setting('hero_cta2') ?></a>
    </div>
    <ul class="hero__b reveal">
      <li><svg class="ico" aria-hidden="true"><use href="#i-check"></use></svg><span<?= admAttr('hero_bullet1', true) ?>><?= setting('hero_bullet1') ?></span></li>
      <li><svg class="ico" aria-hidden="true"><use href="#i-check"></use></svg><span<?= admAttr('hero_bullet2', true) ?>><?= setting('hero_bullet2') ?></span></li>
      <li><svg class="ico" aria-hidden="true"><use href="#i-check"></use></svg><span<?= admAttr('hero_bullet3', true) ?>><?= setting('hero_bullet3') ?></span></li>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if (sectionVisible('cisla')): ?>
<!-- ══════════ ČÍSLA ══════════ -->
<section class="stats<?= sectionHiddenForVisitor('cisla') ? ' adm-section-off' : '' ?>" data-section="cisla">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: Čísla / statistiky
    <label class="adm-switch"><input type="checkbox" data-section-key="cisla" <?= sectionRawVisible('cisla') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap stats__g">
    <?php foreach ($stats as $s): ?>
    <div class="stat reveal" style="position:relative">
      <?php if ($admin): ?><button class="adm-del-btn" data-del-table="stats" data-del-id="<?= (int)$s['id'] ?>" title="Smazat">×</button><?php endif; ?>
      <strong class="stat__n">
        <?php if ($s['is_number']): ?>
          <b data-count="<?= (int)$s['value'] ?>"<?= admAttrField('stats', $s['id'], 'value', true) ?>>0</b>
        <?php else: ?>
          <b<?= admAttrField('stats', $s['id'], 'value', true) ?>><?= out($s['value']) ?></b>
        <?php endif; ?>
        <i<?= admAttrField('stats', $s['id'], 'suffix', true) ?>><?= out($s['suffix']) ?></i>
      </strong>
      <span class="stat__l"<?= admAttrField('stats', $s['id'], 'label', true) ?>><?= out($s['label']) ?></span>
    </div>
    <?php endforeach; ?>
    <?php if ($admin): ?>
    <div class="stat reveal adm-add-tile" data-add-table="stats" style="min-height:80px"><span class="plus">+</span>Přidat číslo</div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if (sectionVisible('sluzby')): ?>
<!-- ══════════ SLUŽBY ══════════ -->
<section class="sec<?= sectionHiddenForVisitor('sluzby') ? ' adm-section-off' : '' ?>" id="sluzby" data-section="sluzby">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: Služby
    <label class="adm-switch"><input type="checkbox" data-section-key="sluzby" <?= sectionRawVisible('sluzby') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap">
    <header class="head reveal">
      <p class="eyebrow"<?= admAttr('sluzby_eyebrow', true) ?>><?= setting('sluzby_eyebrow') ?></p>
      <h2<?= admAttr('sluzby_title', true) ?>><?= setting('sluzby_title') ?></h2>
      <p class="head__p"<?= admAttr('sluzby_text') ?>><?= setting('sluzby_text') ?></p>
    </header>

    <div class="cards">
      <?php foreach ($services as $sv): $tags = array_filter(array_map('trim', explode(',', $sv['tags']))); ?>
      <article class="card reveal" style="position:relative">
        <?php if ($admin): ?><button class="adm-del-btn" data-del-table="services" data-del-id="<?= (int)$sv['id'] ?>" title="Smazat">×</button><?php endif; ?>
        <span class="card__i"><svg aria-hidden="true"><use href="#<?= out($sv['icon']) ?>"></use></svg></span>
        <?php if ($admin): ?>
        <select class="adm-icon-select" data-table="services" data-id="<?= (int)$sv['id'] ?>">
          <?php foreach ($icons as $ik => $lbl): ?><option value="<?= out($ik) ?>" <?= $ik === $sv['icon'] ? 'selected' : '' ?>><?= out($lbl) ?></option><?php endforeach; ?>
        </select>
        <?php endif; ?>
        <h3<?= admAttrField('services', $sv['id'], 'title', true) ?>><?= $sv['title'] ?></h3>
        <p<?= admAttrField('services', $sv['id'], 'text') ?>><?= $sv['text'] ?></p>
        <ul class="tags"<?= admAttrField('services', $sv['id'], 'tags') ?>><?php foreach ($tags as $t): ?><li><?= out($t) ?></li><?php endforeach; ?></ul>
      </article>
      <?php endforeach; ?>
      <?php if ($admin): ?>
      <div class="card reveal adm-add-tile" data-add-table="services"><span class="plus">+</span>Přidat službu</div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (sectionVisible('o-nas')): ?>
<!-- ══════════ O NÁS ══════════ -->
<section class="sec sec--alt<?= sectionHiddenForVisitor('o-nas') ? ' adm-section-off' : '' ?>" id="o-nas" data-section="o-nas">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: O nás
    <label class="adm-switch"><input type="checkbox" data-section-key="o-nas" <?= sectionRawVisible('o-nas') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap about">
    <div class="about__m reveal adm-img-wrap">
      <picture>
        <source media="(max-width: 767px)" srcset="<?= out(settingImageUrl(setting('about_image_mobile'))) ?>">
        <img src="<?= out(settingImageUrl(setting('about_image'))) ?>" alt="Truhlář hoblující dubové prkno v dílně" loading="lazy" decoding="async" width="900" height="1125">
      </picture>
      <?php if ($admin): ?><button type="button" class="adm-img-btn" data-img-key="about_image">✎ Změnit fotku</button><?php endif; ?>
      <div class="about__s"><strong<?= admAttr('about_badge_year', true) ?>><?= setting('about_badge_year') ?></strong><span<?= admAttr('about_badge_label', true) ?>><?= setting('about_badge_label') ?></span></div>
    </div>

    <div class="about__t">
      <p class="eyebrow reveal"<?= admAttr('about_eyebrow', true) ?>><?= setting('about_eyebrow') ?></p>
      <h2 class="reveal"<?= admAttr('about_title', true) ?>><?= setting('about_title') ?></h2>
      <p class="reveal"<?= admAttr('about_text1') ?>><?= setting('about_text1') ?></p>
      <p class="reveal"<?= admAttr('about_text2') ?>><?= setting('about_text2') ?></p>

      <div class="usp reveal">
        <?php foreach ($usp as $u): ?>
        <div class="usp__i" style="position:relative">
          <?php if ($admin): ?><button class="adm-del-btn" data-del-table="usp_items" data-del-id="<?= (int)$u['id'] ?>" title="Smazat">×</button><?php endif; ?>
          <svg class="ico" aria-hidden="true"><use href="#<?= out($u['icon']) ?>"></use></svg>
          <div>
            <strong<?= admAttrField('usp_items', $u['id'], 'title', true) ?>><?= $u['title'] ?></strong>
            <span<?= admAttrField('usp_items', $u['id'], 'text', true) ?>><?= $u['text'] ?></span>
          </div>
          <?php if ($admin): ?>
          <select class="adm-icon-select" data-table="usp_items" data-id="<?= (int)$u['id'] ?>">
            <?php foreach ($icons as $ik => $lbl): ?><option value="<?= out($ik) ?>" <?= $ik === $u['icon'] ? 'selected' : '' ?>><?= out($lbl) ?></option><?php endforeach; ?>
          </select>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if ($admin): ?>
        <div class="usp__i adm-add-tile" data-add-table="usp_items" style="min-height:60px"><span class="plus">+</span>Přidat výhodu</div>
        <?php endif; ?>
      </div>

      <a href="#poptavka" class="btn btn--primary reveal"<?= admAttr('about_cta', true) ?>><?= setting('about_cta') ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (sectionVisible('reference')): ?>
<!-- ══════════ REFERENCE ══════════ -->
<section class="sec<?= sectionHiddenForVisitor('reference') ? ' adm-section-off' : '' ?>" id="reference" data-section="reference">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: Reference
    <label class="adm-switch"><input type="checkbox" data-section-key="reference" <?= sectionRawVisible('reference') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap">
    <header class="head reveal">
      <p class="eyebrow"<?= admAttr('reference_eyebrow', true) ?>><?= setting('reference_eyebrow') ?></p>
      <h2<?= admAttr('reference_title', true) ?>><?= setting('reference_title') ?></h2>
      <p class="head__p"<?= admAttr('reference_text') ?>><?= setting('reference_text') ?></p>
    </header>

    <div class="filters reveal">
      <button class="filter is-on" type="button" data-f="*">Vše</button>
      <?php foreach (array_keys($categories) as $cat): ?>
      <button class="filter" type="button" data-f="<?= out($cat) ?>"><?= out($catLabels[$cat] ?? ucfirst($cat)) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="gal" id="gal">
      <?php if ($admin): ?>
      <div class="gal__add" id="galAddTile"><span class="plus">+</span>Přidat referenci</div>
      <?php endif; ?>

      <?php foreach ($gallery as $g): ?>
      <figure class="gal__i<?= $g['size'] === 'tall' ? ' gal__i--tall' : ($g['size'] === 'wide' ? ' gal__i--wide' : '') ?> reveal" data-cat="<?= out($g['category']) ?>" data-type="<?= out($g['type']) ?>" tabindex="0">
        <?php if ($admin): ?><button class="gal__del adm-del-btn" data-id="<?= (int)$g['id'] ?>" title="Smazat">×</button><?php endif; ?>
        <?php if ($g['type'] === 'video'): ?>
          <video src="<?= out(galleryUrl($g)) ?>" muted playsinline preload="metadata"></video>
        <?php else: ?>
          <img src="<?= out(galleryUrl($g)) ?>" alt="<?= out($g['title']) ?>" loading="lazy" decoding="async">
        <?php endif; ?>
        <figcaption>
          <strong<?= admAttrField('gallery', $g['id'], 'title', true) ?>><?= $g['title'] ?></strong>
          <span<?= admAttrField('gallery', $g['id'], 'subtitle', true) ?>><?= $g['subtitle'] ?></span>
        </figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (sectionVisible('proces')): ?>
<!-- ══════════ POSTUP ══════════ -->
<section class="sec sec--dark<?= sectionHiddenForVisitor('proces') ? ' adm-section-off' : '' ?>" id="proces" data-section="proces">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: Jak spolupráce probíhá
    <label class="adm-switch"><input type="checkbox" data-section-key="proces" <?= sectionRawVisible('proces') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap">
    <header class="head reveal">
      <p class="eyebrow eyebrow--lt"<?= admAttr('proces_eyebrow', true) ?>><?= setting('proces_eyebrow') ?></p>
      <h2<?= admAttr('proces_title', true) ?>><?= setting('proces_title') ?></h2>
      <p class="head__p"<?= admAttr('proces_text') ?>><?= setting('proces_text') ?></p>
    </header>

    <ol class="steps">
      <?php foreach ($steps as $i => $st): ?>
      <li class="step reveal" style="position:relative">
        <?php if ($admin): ?><button class="adm-del-btn" data-del-table="steps" data-del-id="<?= (int)$st['id'] ?>" title="Smazat">×</button><?php endif; ?>
        <span class="step__n"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <h3<?= admAttrField('steps', $st['id'], 'title', true) ?>><?= $st['title'] ?></h3>
        <p<?= admAttrField('steps', $st['id'], 'text') ?>><?= $st['text'] ?></p>
      </li>
      <?php endforeach; ?>
      <?php if ($admin): ?>
      <li class="step reveal adm-add-tile" data-add-table="steps"><span class="plus">+</span>Přidat krok</li>
      <?php endif; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php if (sectionVisible('recenze')): ?>
<!-- ══════════ RECENZE ══════════ -->
<section class="sec sec--alt<?= sectionHiddenForVisitor('recenze') ? ' adm-section-off' : '' ?>" data-section="recenze">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: Recenze klientů
    <label class="adm-switch"><input type="checkbox" data-section-key="recenze" <?= sectionRawVisible('recenze') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap">
    <header class="head reveal">
      <p class="eyebrow"<?= admAttr('recenze_eyebrow', true) ?>><?= setting('recenze_eyebrow') ?></p>
      <h2<?= admAttr('recenze_title', true) ?>><?= setting('recenze_title') ?></h2>
    </header>
    <div class="quotes">
      <?php foreach ($reviews as $r): ?>
      <blockquote class="quote reveal" style="position:relative">
        <?php if ($admin): ?><button class="adm-del-btn" data-del-table="reviews" data-del-id="<?= (int)$r['id'] ?>" title="Smazat">×</button><?php endif; ?>
        <div class="stars" aria-label="Hodnocení <?= (int)$r['stars'] ?> z 5"<?= $admin ? ' data-stars-id="' . (int)$r['id'] . '"' : '' ?>>
          <?php for ($n = 1; $n <= 5; $n++): ?><span class="star <?= $n <= $r['stars'] ? 'on' : '' ?>" data-v="<?= $n ?>"><?= $n <= $r['stars'] ? '★' : ($admin ? '☆' : '') ?></span><?php endfor; ?>
        </div>
        <p<?= admAttrField('reviews', $r['id'], 'text') ?>><?= $r['text'] ?></p>
        <footer><strong<?= admAttrField('reviews', $r['id'], 'author', true) ?>><?= $r['author'] ?></strong><span<?= admAttrField('reviews', $r['id'], 'location', true) ?>><?= $r['location'] ?></span></footer>
      </blockquote>
      <?php endforeach; ?>
      <?php if ($admin): ?>
      <div class="quote reveal adm-add-tile" data-add-table="reviews"><span class="plus">+</span>Přidat recenzi</div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (sectionVisible('faq')): ?>
<!-- ══════════ FAQ ══════════ -->
<section class="sec<?= sectionHiddenForVisitor('faq') ? ' adm-section-off' : '' ?>" id="faq" data-section="faq">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: Časté dotazy
    <label class="adm-switch"><input type="checkbox" data-section-key="faq" <?= sectionRawVisible('faq') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap faq__w">
    <header class="head head--l reveal">
      <p class="eyebrow"<?= admAttr('faq_eyebrow', true) ?>><?= setting('faq_eyebrow') ?></p>
      <h2<?= admAttr('faq_title', true) ?>><?= setting('faq_title') ?></h2>
      <p class="head__p"<?= admAttr('faq_text', true) ?>><?= setting('faq_text') ?> <a href="<?= out(telHref(setting('phone'))) ?>"><?= out(setting('phone')) ?></a>.</p>
    </header>

    <div class="faq reveal">
      <?php foreach ($faqs as $f): ?>
      <details class="faq__i" <?= $f['is_open'] ? 'open' : '' ?> style="position:relative">
        <summary<?= admAttrField('faqs', $f['id'], 'question', true) ?>><?= $f['question'] ?></summary>
        <div class="faq__a"><p<?= admAttrField('faqs', $f['id'], 'answer') ?>><?= $f['answer'] ?></p></div>
        <?php if ($admin): ?><button class="adm-del-btn" data-del-table="faqs" data-del-id="<?= (int)$f['id'] ?>" title="Smazat" style="top:17px;left:10px;right:auto">×</button><?php endif; ?>
      </details>
      <?php endforeach; ?>
      <?php if ($admin): ?>
      <div class="faq__i adm-add-tile" data-add-table="faqs" style="min-height:60px"><span class="plus">+</span>Přidat otázku</div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (sectionVisible('poptavka')): ?>
<!-- ══════════ POPTÁVKA ══════════ -->
<section class="sec sec--dark<?= sectionHiddenForVisitor('poptavka') ? ' adm-section-off' : '' ?>" id="poptavka" data-section="poptavka">
  <?php if ($admin): ?>
  <div class="adm-sec-bar">Sekce: Poptávkový formulář
    <label class="adm-switch"><input type="checkbox" data-section-key="poptavka" <?= sectionRawVisible('poptavka') ? 'checked' : '' ?>><span class="track"></span></label>
  </div>
  <?php endif; ?>
  <div class="wrap contact">
    <div class="contact__i" id="kontakt">
      <p class="eyebrow eyebrow--lt reveal"<?= admAttr('poptavka_eyebrow', true) ?>><?= setting('poptavka_eyebrow') ?></p>
      <h2 class="reveal"<?= admAttr('poptavka_title', true) ?>><?= setting('poptavka_title') ?></h2>
      <p class="reveal"<?= admAttr('poptavka_text') ?>><?= setting('poptavka_text') ?></p>

      <ul class="clist reveal">
        <li<?= admAttr('poptavka_bullet1', true) ?>><?= setting('poptavka_bullet1') ?></li>
        <li<?= admAttr('poptavka_bullet2', true) ?>><?= setting('poptavka_bullet2') ?></li>
        <li<?= admAttr('poptavka_bullet3', true) ?>><?= setting('poptavka_bullet3') ?></li>
      </ul>

      <ul class="clist reveal">
        <li><span class="ci"><svg aria-hidden="true"><use href="#i-phone"></use></svg></span><div><strong>Telefon</strong><a href="<?= out(telHref(setting('phone'))) ?>"<?= admAttr('phone', true) ?>><?= out(setting('phone')) ?></a></div></li>
        <li><span class="ci"><svg aria-hidden="true"><use href="#i-mail"></use></svg></span><div><strong>E-mail</strong><a href="mailto:<?= out(setting('email')) ?>"<?= admAttr('email', true) ?>><?= out(setting('email')) ?></a></div></li>
        <li><span class="ci"><svg aria-hidden="true"><use href="#i-pin"></use></svg></span><div><strong>Dílna</strong><a href="https://maps.google.com/?q=<?= urlencode(setting('address')) ?>" target="_blank" rel="noopener"<?= admAttr('address', true) ?>><?= out(setting('address')) ?></a></div></li>
        <li><span class="ci"><svg aria-hidden="true"><use href="#i-clock"></use></svg></span><div><strong>Otevírací doba</strong><span<?= admAttr('hours', true) ?>><?= out(setting('hours')) ?></span></div></li>
      </ul>

      <div class="soc reveal">
        <a href="<?= out(setting('facebook_url')) ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg aria-hidden="true"><use href="#i-fb"></use></svg></a>
        <a href="<?= out(setting('instagram_url')) ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg aria-hidden="true"><use href="#i-ig"></use></svg></a>
      </div>
    </div>

    <form class="form reveal" id="form" action="send.php" method="post" novalidate>
      <div class="form__r">
        <label class="fld"><span>Jméno a příjmení <i>*</i></span><input type="text" name="jmeno" required autocomplete="name" placeholder="Jan Novák"></label>
        <label class="fld"><span>Telefon <i>*</i></span><input type="tel" name="telefon" required autocomplete="tel" placeholder="+420 777 123 456"></label>
      </div>
      <div class="form__r">
        <label class="fld"><span>E-mail</span><input type="email" name="email" autocomplete="email" placeholder="jan@email.cz"></label>
        <label class="fld"><span>Mám zájem o</span>
          <select name="sluzba">
            <?php foreach ($services as $sv): ?><option><?= out($sv['title']) ?></option><?php endforeach; ?>
            <option>Něco jiného</option>
          </select>
        </label>
      </div>
      <label class="fld"><span>Popis projektu <i>*</i></span><textarea name="zprava" rows="5" required placeholder="Rozměry, představa o materiálu, termín…"></textarea></label>

      <label class="chk"><input type="checkbox" name="souhlas" required><span>Souhlasím se zpracováním osobních údajů pro účely vyřízení poptávky.</span></label>
      <input type="text" name="web" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

      <button type="submit" class="btn btn--primary btn--lg btn--block">Odeslat poptávku</button>
      <p class="form__n">Odpovídáme zpravidla do 24 hodin. Nebo rovnou volejte <a href="<?= out(telHref(setting('phone'))) ?>"><?= out(setting('phone')) ?></a>.</p>
      <p class="form__m" id="formMsg" role="status" aria-live="polite"></p>
    </form>
  </div>
</section>
<?php endif; ?>

</main>

<!-- ══════════ PATIČKA ══════════ -->
<footer class="ftr">
  <div class="wrap ftr__g">
    <div>
      <img class="ftr__logo" src="img/logo-full-light.svg" alt="<?= out(setting('company_name')) ?>" width="170" height="162" loading="lazy">
      <p class="ftr__a"<?= admAttr('footer_text') ?>><?= setting('footer_text') ?></p>
      <div class="soc soc--sm">
        <a href="<?= out(setting('facebook_url')) ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg aria-hidden="true"><use href="#i-fb"></use></svg></a>
        <a href="<?= out(setting('instagram_url')) ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg aria-hidden="true"><use href="#i-ig"></use></svg></a>
      </div>
    </div>

    <nav aria-label="Služby">
      <h3>Služby</h3>
      <?php foreach ($services as $sv): ?><a href="#sluzby"><?= out($sv['title']) ?></a><?php endforeach; ?>
    </nav>

    <nav aria-label="Rychlé odkazy">
      <h3>Rychlé odkazy</h3>
      <a href="#o-nas">O nás</a>
      <a href="#reference">Reference</a>
      <a href="#proces">Jak to probíhá</a>
      <a href="#faq">Časté dotazy</a>
      <a href="#poptavka">Poptávka</a>
    </nav>

    <div>
      <h3>Kontakt</h3>
      <p class="ftr__c">
        <?= out(setting('address')) ?><br><br>
        <a href="<?= out(telHref(setting('phone'))) ?>"><?= out(setting('phone')) ?></a><br>
        <a href="mailto:<?= out(setting('email')) ?>"><?= out(setting('email')) ?></a>
      </p>
    </div>
  </div>

  <div class="wrap ftr__b">
    <span>© <span id="rok">2026</span> <?= out(setting('company_name')) ?>. Všechna práva vyhrazena.</span>
    <span<?= admAttr('footer_note', true) ?>><?= setting('footer_note') ?></span>
  </div>
</footer>

<a href="#uvod" class="totop" id="totop" aria-label="Nahoru"><svg aria-hidden="true"><use href="#i-up"></use></svg></a>

<!-- ══════════ LIGHTBOX ══════════ -->
<div class="lb" id="lb" aria-hidden="true">
  <button class="lb__x" id="lbClose" type="button" aria-label="Zavřít">&times;</button>
  <button class="lb__n lb__n--p" id="lbPrev" type="button" aria-label="Předchozí">&#8249;</button>
  <img id="lbImg" src="" alt="">
  <video id="lbVideo" controls playsinline style="display:none"></video>
  <button class="lb__n lb__n--x" id="lbNext" type="button" aria-label="Další">&#8250;</button>
  <p class="lb__c" id="lbCap"></p>
</div>

<?php if ($admin): ?>
<!-- ══════════ MODÁLNÍ OKNO: PŘIDAT REFERENCI ══════════ -->
<div class="adm-modal-scrim" id="galModal">
  <div class="adm-modal">
    <h3>Nové reference</h3>
    <label>Kategorie (platí pro všechny vybrané soubory)</label>
    <select id="galCategory">
      <?php foreach ($catLabels as $k => $lbl): ?><option value="<?= out($k) ?>"><?= out($lbl) ?></option><?php endforeach; ?>
    </select>
    <label>Nadpis (nepovinné, lze doplnit/upravit i po nahrání)</label>
    <input type="text" id="galTitle">
    <label>Podtitul (nepovinné)</label>
    <input type="text" id="galSubtitle">
    <label>Soubory — jde vybrat víc fotek i videí najednou</label>
    <input type="file" id="galFile" accept="image/*,video/mp4,video/webm,video/quicktime" multiple>
    <div class="row">
      <button type="button" class="btn-cancel">Zrušit</button>
      <button type="button" class="btn-ok">Nahrát a přidat</button>
    </div>
  </div>
</div>

<!-- ══════════ PLOVOUCÍ ADMIN PANEL ══════════ -->
<div class="adm-bar" id="admBar">
  <button type="button" class="adm-bar__toggle" id="admBarToggle" aria-label="Otevřít administraci" aria-expanded="false">
    <span class="dot"></span>
  </button>
  <div class="adm-bar__panel" id="admBarPanel">
    <span class="adm-bar__label"><span class="dot"></span> Administrace</span>
    <button type="button" id="admModeBtn">✏️ Úpravy: zapnuty</button>
    <a href="admin/seo">SEO a Analytics</a>
    <a href="admin/heslo">Změnit heslo</a>
    <a href="admin/logout">Odhlásit</a>
  </div>
</div>
<script>window.ADM = { csrf: <?= json_encode(csrfToken()) ?> };</script>
<script src="admin/js/admin.js" defer></script>
<?php endif; ?>

<!-- ══════════ IKONY ══════════ -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
<symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11Z" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.6" fill="none" stroke="currentColor" stroke-width="1.7"/></symbol>
<symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5.2l3.3 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></symbol>
<symbol id="i-phone" viewBox="0 0 24 24"><path d="M6.6 3.5h3l1.5 4-2 1.4a12 12 0 0 0 6 6l1.4-2 4 1.5v3c0 1-.9 1.8-1.9 1.7C11 18.6 5.4 13 4.9 5.4A1.8 1.8 0 0 1 6.6 3.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></symbol>
<symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="5.5" width="18" height="13" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m3.8 7 8.2 6 8.2-6" fill="none" stroke="currentColor" stroke-width="1.7"/></symbol>
<symbol id="i-check" viewBox="0 0 24 24"><path d="m4.5 12.5 4.5 4.5L19.5 6.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
<symbol id="i-up" viewBox="0 0 24 24"><path d="M12 19V5m0 0-6 6m6-6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
<symbol id="i-kitchen" viewBox="0 0 24 24"><rect x="3" y="3.5" width="18" height="17" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3 10h18M12 3.5v17M6.5 7h2m7 0h2M6.5 14h2m7 0h2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
<symbol id="i-wardrobe" viewBox="0 0 24 24"><rect x="4" y="2.8" width="16" height="18.4" rx="1.6" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 2.8v18.4M10 11.5h-.6m5.2 0h.6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
<symbol id="i-stairs" viewBox="0 0 24 24"><path d="M3 20h4v-4h4v-4h4V8h4V4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" stroke-linecap="round"/><path d="M3 20h18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></symbol>
<symbol id="i-sauna" viewBox="0 0 24 24"><path d="M4 20h16M6 20V9l6-4 6 4v11" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9.5 16.5c0-1.5 1.5-2 1.5-3.5m3 3.5c0-1.5 1.5-2 1.5-3.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
<symbol id="i-table" viewBox="0 0 24 24"><path d="M2.5 8.5h19M5 8.5V19m14-10.5V19M2.5 8.5 5 5h14l2.5 3.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round"/></symbol>
<symbol id="i-tools" viewBox="0 0 24 24"><path d="M14.5 3.5a4 4 0 0 0 5.2 5.2L9.9 18.5a2.4 2.4 0 0 1-3.4-3.4L14.5 3.5Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m5 5 3 3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
<symbol id="i-ruler" viewBox="0 0 24 24"><rect x="2.5" y="8" width="19" height="8" rx="1.6" transform="rotate(-8 12 12)" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M7 9.5v2.2M11 9v2.6M15 8.4v2.2M19 8v2.6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
<symbol id="i-wood" viewBox="0 0 24 24"><ellipse cx="12" cy="12" rx="9" ry="8" fill="none" stroke="currentColor" stroke-width="1.6"/><ellipse cx="12" cy="12" rx="5.5" ry="4.6" fill="none" stroke="currentColor" stroke-width="1.4"/><ellipse cx="12" cy="12" rx="2" ry="1.6" fill="none" stroke="currentColor" stroke-width="1.4"/></symbol>
<symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3.5" y="5" width="17" height="15" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3.5 10h17M8 3.5V6m8-2.5V6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></symbol>
<symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3 5 6v5.5c0 4.4 3 7.7 7 9.5 4-1.8 7-5.1 7-9.5V6l-7-3Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m9 12 2.2 2.2L15.5 10" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></symbol>
<symbol id="i-fb" viewBox="0 0 24 24"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5h1.65V3.6c-.3 0-1.3-.13-2.45-.13-2.42 0-4.07 1.48-4.07 4.19V9.9H7.5V13h2.68v8h3.32Z" fill="currentColor"/></symbol>
<symbol id="i-ig" viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="17" cy="7" r="1.2" fill="currentColor"/></symbol>
</svg>

<script src="js/main.js" defer></script>
</body>
</html>
