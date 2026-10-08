<?php
declare(strict_types=1);
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$csrf = $_POST['csrf'] ?? $_GET['csrf'] ?? '';
if (!checkCsrf((string)$csrf)) {
    jsonOut(['ok' => false, 'error' => 'Neplatný požadavek (obnovte stránku).'], 400);
}

// Povolené tabulky a jejich editovatelná pole (ochrana proti zápisu kamkoliv)
const TABLE_FIELDS = [
    'services'   => ['icon', 'title', 'text', 'tags'],
    'usp_items'  => ['icon', 'title', 'text'],
    'steps'      => ['title', 'text'],
    'reviews'    => ['stars', 'text', 'author', 'location'],
    'faqs'       => ['question', 'answer', 'is_open'],
    'stats'      => ['value', 'suffix', 'label'],
    'gallery'    => ['title', 'subtitle', 'category'],
];

const NEW_ITEM_DEFAULTS = [
    'services'  => ['icon' => 'i-tools',  'title' => 'Nová služba', 'text' => 'Popis nové služby.', 'tags' => ''],
    'usp_items' => ['icon' => 'i-shield', 'title' => 'Nová výhoda', 'text' => 'Popis výhody.'],
    'steps'     => ['title' => 'Nový krok', 'text' => 'Popis kroku.'],
    'reviews'   => ['stars' => 5, 'text' => 'Text recenze…', 'author' => 'Jméno', 'location' => 'Město'],
    'faqs'      => ['question' => 'Nová otázka', 'answer' => 'Odpověď na otázku.', 'is_open' => 0],
    'stats'     => ['value' => '0', 'suffix' => '', 'label' => 'Popisek', 'is_number' => 1],
];

try {
    switch ($action) {

        // ---------- ulož textové pole nastavení (settings) ----------
        case 'save_text': {
            $key = (string)($_POST['key'] ?? '');
            $value = (string)($_POST['value'] ?? '');
            if ($key === '') throw new RuntimeException('Chybí klíč pole.');
            saveSetting($key, sanitizeRich($value));
            jsonOut(['ok' => true]);
        }

        // ---------- ulož pole položky v repeatable tabulce ----------
        case 'save_field': {
            $table = (string)($_POST['table'] ?? '');
            $field = (string)($_POST['field'] ?? '');
            $id = (int)($_POST['id'] ?? 0);
            $value = (string)($_POST['value'] ?? '');

            if (!isset(TABLE_FIELDS[$table]) || !in_array($field, TABLE_FIELDS[$table], true) || $id <= 0) {
                throw new RuntimeException('Neplatné pole.');
            }
            if ($field === 'is_open' || $field === 'is_number' || $field === 'stars') {
                $value = (string)max(0, (int)$value);
            } else {
                $value = sanitizeRich($value);
            }
            $stmt = db()->prepare("UPDATE `$table` SET `$field` = :v WHERE id = :id");
            $stmt->execute(['v' => $value, 'id' => $id]);
            jsonOut(['ok' => true]);
        }

        // ---------- přidat novou položku do repeatable tabulky ----------
        case 'add_item': {
            $table = (string)($_POST['table'] ?? '');
            if (!isset(NEW_ITEM_DEFAULTS[$table])) throw new RuntimeException('Neplatná tabulka.');

            $max = (int)db()->query("SELECT COALESCE(MAX(sort_order),0) FROM `$table`")->fetchColumn();
            $defaults = NEW_ITEM_DEFAULTS[$table];
            $cols = array_keys($defaults);
            $cols[] = 'sort_order';
            $vals = array_values($defaults);
            $vals[] = $max + 10;

            $placeholders = implode(',', array_fill(0, count($cols), '?'));
            $colList = '`' . implode('`,`', $cols) . '`';
            $stmt = db()->prepare("INSERT INTO `$table` ($colList) VALUES ($placeholders)");
            $stmt->execute($vals);
            $id = (int)db()->lastInsertId();

            $row = db()->prepare("SELECT * FROM `$table` WHERE id = ?");
            $row->execute([$id]);
            jsonOut(['ok' => true, 'item' => $row->fetch()]);
        }

        // ---------- smazat položku z repeatable tabulky ----------
        case 'delete_item': {
            $table = (string)($_POST['table'] ?? '');
            $id = (int)($_POST['id'] ?? 0);
            if (!isset(NEW_ITEM_DEFAULTS[$table]) || $id <= 0) throw new RuntimeException('Neplatný požadavek.');
            $stmt = db()->prepare("DELETE FROM `$table` WHERE id = ?");
            $stmt->execute([$id]);
            jsonOut(['ok' => true]);
        }

        // ---------- zapnout/vypnout viditelnost sekce ----------
        case 'toggle_section': {
            $key = (string)($_POST['key'] ?? '');
            $visible = (int)($_POST['visible'] ?? 1) ? 1 : 0;
            $stmt = db()->prepare('UPDATE sections SET visible = ? WHERE `key` = ?');
            $stmt->execute([$visible, $key]);
            jsonOut(['ok' => true]);
        }

        // ---------- nahrát obrázek pro pole nastavení (hero pozadí, foto o nás…) ----------
        case 'upload_setting_image': {
            $key = (string)($_POST['key'] ?? '');
            if ($key === '' || empty($_FILES['file'])) throw new RuntimeException('Chybí soubor.');
            $filename = handleUpload($_FILES['file'], 'image');
            saveSetting($key, $filename);
            jsonOut(['ok' => true, 'url' => uploadUrl('image') . $filename]);
        }

        // ---------- přidat jednu nebo více referencí do galerie (obrázky i videa najednou) ----------
        case 'gallery_add': {
            if (empty($_FILES['files']) || empty($_FILES['files']['name'][0])) {
                throw new RuntimeException('Vyberte alespoň jeden soubor.');
            }
            $category = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($_POST['category'] ?? 'ostatni'))) ?: 'ostatni';
            $title = sanitizeRich((string)($_POST['title'] ?? ''));
            $subtitle = sanitizeRich((string)($_POST['subtitle'] ?? ''));

            $files = $_FILES['files'];
            $count = count($files['name']);
            $min = (int)db()->query('SELECT COALESCE(MIN(sort_order),100) FROM gallery')->fetchColumn();

            $addedItems = [];
            $errors = [];

            for ($i = 0; $i < $count; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
                $single = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                $kind = guessGalleryKind($single['name']);
                try {
                    $filename = handleUpload($single, $kind);
                } catch (RuntimeException $e) {
                    $errors[] = $single['name'] . ': ' . $e->getMessage();
                    continue;
                }
                // pořadí zachováno tak, jak byly soubory vybrány — první vybraný skončí hned za "+"
                $sort = $min - 10 * ($count - $i);
                $stmt = db()->prepare(
                    'INSERT INTO gallery (type, filename, category, title, subtitle, size, sort_order)
                     VALUES (:type, :filename, :category, :title, :subtitle, "normal", :sort)'
                );
                $stmt->execute([
                    'type' => $kind, 'filename' => $filename, 'category' => $category,
                    'title' => $title, 'subtitle' => $subtitle, 'sort' => $sort,
                ]);
                $addedItems[] = $filename;
            }

            if (!$addedItems) {
                jsonOut(['ok' => false, 'error' => $errors ? implode(' | ', $errors) : 'Nic se nenahrálo.'], 400);
            }
            jsonOut(['ok' => true, 'added' => count($addedItems), 'errors' => $errors]);
        }

        // ---------- smazat referenci z galerie ----------
        case 'gallery_delete': {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new RuntimeException('Neplatná položka.');
            $row = db()->prepare('SELECT filename, type FROM gallery WHERE id = ?');
            $row->execute([$id]);
            $item = $row->fetch();
            db()->prepare('DELETE FROM gallery WHERE id = ?')->execute([$id]);
            if ($item) {
                $path = uploadDir($item['type']) . $item['filename'];
                // mazat jen soubory z uploads (ne z výchozí sady img/) — bezpečnostní pojistka
                if (str_starts_with(realpath($path) ?: '', realpath(__DIR__ . '/../uploads') ?: '~~')) {
                    @unlink($path);
                }
            }
            jsonOut(['ok' => true]);
        }

        default:
            throw new RuntimeException('Neznámá akce.');
    }
} catch (Throwable $e) {
    jsonOut(['ok' => false, 'error' => $e->getMessage()], 400);
}
