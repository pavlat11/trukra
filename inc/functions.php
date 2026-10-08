<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
   PŘIHLÁŠENÍ / OPRÁVNĚNÍ
   ============================================================ */
function isAdmin(): bool {
    return !empty($_SESSION['admin']);
}

function requireAdmin(): void {
    if (!isAdmin()) {
        http_response_code(403);
        die('Nemáte oprávnění.');
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function checkCsrf(string $token): bool {
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ============================================================
   NASTAVENÍ (klíč -> hodnota) — jednotlivá textová/obrázková pole
   ============================================================ */
function allSettings(): array {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $stmt = db()->query('SELECT `key`, `value` FROM settings');
        foreach ($stmt as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string {
    $all = allSettings();
    return $all[$key] ?? $default;
}

function saveSetting(string $key, string $value): void {
    $stmt = db()->prepare(
        'INSERT INTO settings (`key`, `value`) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE `value` = :v2'
    );
    $stmt->execute(['k' => $key, 'v' => $value, 'v2' => $value]);
}

/* ============================================================
   SEKCE — viditelnost a pořadí
   ============================================================ */
function allSections(): array {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $stmt = db()->query('SELECT * FROM sections ORDER BY sort_order ASC');
        foreach ($stmt as $row) {
            $cache[$row['key']] = $row;
        }
    }
    return $cache;
}

function sectionVisible(string $key): bool {
    $sections = allSections();
    if (!isset($sections[$key])) return true;
    return (bool)$sections[$key]['visible'] || isAdmin();
}

function sectionHiddenForVisitor(string $key): bool {
    $sections = allSections();
    return isset($sections[$key]) && !$sections[$key]['visible'];
}

/* ============================================================
   VÝSTUP TEXTU / HTML — ošetření proti XSS
   ============================================================ */
// Prostý text (jedna řádka) — vždy escapovat
function out(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

// Krátký "rich" text (může obsahovat pár základních značek zadaných přes editaci)
function outRich(string $val): string {
    $allowed = '<b><strong><i><em><br><a><span><u>';
    return strip_tags($val, $allowed);
}

function sanitizeRich(string $html): string {
    $allowed = '<b><strong><i><em><br><a><span><u><p><ul><ol><li>';
    $clean = strip_tags($html, $allowed);
    // odstranit případné on*="" atributy a javascript: odkazy
    $clean = preg_replace('/\son\w+\s*=\s*"[^"]*"/i', '', $clean);
    $clean = preg_replace('/\son\w+\s*=\s*\'[^\']*\'/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*"javascript:[^"]*"/i', 'href="#"', $clean);
    return $clean;
}

/* ============================================================
   ATRIBUTY PRO EDITAČNÍ REŽIM (přidány jen přihlášenému adminovi)
   ============================================================ */
function admAttr(string $key, bool $single = false): string {
    if (!isAdmin()) return '';
    $attr = ' data-editable="true" data-key="' . out($key) . '"';
    if ($single) $attr .= ' data-singleline="1"';
    return $attr;
}

function admAttrField(string $table, $id, string $field, bool $single = false): string {
    if (!isAdmin()) return '';
    $attr = ' data-editable="true" data-table="' . out($table) . '" data-id="' . (int)$id . '" data-field="' . out($field) . '"';
    if ($single) $attr .= ' data-singleline="1"';
    return $attr;
}

function sectionRawVisible(string $key): bool {
    $s = allSections();
    return isset($s[$key]) ? (bool)$s[$key]['visible'] : true;
}

// Výchozí (předinstalované) reference jsou v /img, nově nahrané v /uploads/img nebo /uploads/video
function galleryUrl(array $g): string {
    $type = $g['type'];
    if (is_file(uploadDir($type) . $g['filename'])) {
        return uploadUrl($type) . $g['filename'];
    }
    return 'img/' . $g['filename'];
}

// Totéž pro obrázky uložené v "settings" (hero pozadí, foto O nás, OG obrázek…) —
// pokud byl obrázek nahrán přes administraci, leží v /uploads/img, jinak jde
// o původní soubor v /img.
function settingImageUrl(string $filename): string {
    if ($filename !== '' && is_file(uploadDir('image') . $filename)) {
        return uploadUrl('image') . $filename;
    }
    return 'img/' . $filename;
}

// Podle přípony souboru pozná, jestli jde o obrázek, nebo video
function guessGalleryKind(string $filename): string {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['mp4', 'webm', 'mov'], true) ? 'video' : 'image';
}

function telHref(string $phone): string {
    return 'tel:' . preg_replace('/[^\d+]/', '', $phone);
}

/* ============================================================
   IKONY — povolená sada SVG symbolů, které lze v adminu vybrat
   ============================================================ */
function allowedIcons(): array {
    return [
        'i-kitchen'  => 'Kuchyň',
        'i-wardrobe' => 'Skříň',
        'i-stairs'   => 'Schody',
        'i-sauna'    => 'Sauna',
        'i-table'    => 'Nábytek',
        'i-tools'    => 'Nářadí',
        'i-ruler'    => 'Metr',
        'i-wood'     => 'Dřevo',
        'i-calendar' => 'Kalendář',
        'i-shield'   => 'Štít',
    ];
}

/* ============================================================
   NAHRÁVÁNÍ SOUBORŮ
   ============================================================ */
function uploadDir(string $type): string {
    return __DIR__ . '/../uploads/' . ($type === 'video' ? 'video' : 'img') . '/';
}
function uploadUrl(string $type): string {
    return 'uploads/' . ($type === 'video' ? 'video' : 'img') . '/';
}

/**
 * Zpracuje nahraný soubor z $_FILES['file'] a vrátí nový název souboru.
 * @throws RuntimeException při chybě
 */
function handleUpload(array $file, string $kind): string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Nahrávání souboru selhalo.');
    }

    $imageExt = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml'];
    $videoExt = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'];

    $origName = $file['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if ($kind === 'video') {
        if (!isset($videoExt[$ext])) throw new RuntimeException('Nepovolený formát videa. Použijte MP4, WEBM nebo MOV.');
        $maxBytes = UPLOAD_MAX_VIDEO_MB * 1024 * 1024;
    } else {
        if (!isset($imageExt[$ext])) throw new RuntimeException('Nepovolený formát obrázku. Použijte JPG, PNG, WEBP nebo GIF.');
        $maxBytes = UPLOAD_MAX_IMAGE_MB * 1024 * 1024;
    }

    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('Soubor je příliš velký.');
    }

    $newName = bin2hex(random_bytes(8)) . '-' . date('Ymd-His') . '.' . $ext;
    $dest = uploadDir($kind) . $newName;

    if (!is_dir(uploadDir($kind))) {
        mkdir(uploadDir($kind), 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Soubor se nepodařilo uložit na server.');
    }

    return $newName;
}

/* ============================================================
   JSON odpověď pro AJAX
   ============================================================ */
function jsonOut(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
