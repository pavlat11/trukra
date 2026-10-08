<?php
declare(strict_types=1);
/**
 * Zpracování poptávkového formuláře – Truhlářství Kratochvíl
 * Vrací JSON: {"ok":true} nebo {"ok":false,"error":"..."}
 * Adresa příjemce se bere z nastavení v administraci (pole "E-mail" v sekci Kontakt).
 */
require_once __DIR__ . '/inc/functions.php';
header('Content-Type: application/json; charset=utf-8');

// MUSÍ být adresa na vlastní doméně, jinak ji poštovní server může odmítnout jako podvrženou.
const ODESILATEL = 'web@trukra.cz';

function fail(string $m, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $m], JSON_UNESCAPED_UNICODE);
    exit;
}
function clean(string $v): string {
    return trim(str_replace(["\r", "\n", "%0a", "%0d"], ' ', strip_tags($v)));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Neplatný požadavek', 405);
if (!empty($_POST['web'] ?? '')) { echo json_encode(['ok' => true]); exit; } // honeypot

$prijemce = setting('email', 'info@example.cz');

$jmeno   = clean((string)($_POST['jmeno'] ?? ''));
$telefon = clean((string)($_POST['telefon'] ?? ''));
$email   = clean((string)($_POST['email'] ?? ''));
$sluzba  = clean((string)($_POST['sluzba'] ?? ''));
$zprava  = trim(strip_tags((string)($_POST['zprava'] ?? '')));

if ($jmeno === '' || $telefon === '' || $zprava === '') fail('Vyplňte prosím povinná pole.');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Neplatný e-mail.');

$predmet = '=?UTF-8?B?' . base64_encode('Nová poptávka z webu – ' . ($sluzba ?: 'obecná')) . '?=';

$telo  = "Nová poptávka z webu\n";
$telo .= str_repeat('-', 40) . "\n";
$telo .= "Jméno:   {$jmeno}\n";
$telo .= "Telefon: {$telefon}\n";
$telo .= "E-mail:  " . ($email ?: '—') . "\n";
$telo .= "Zájem o: " . ($sluzba ?: '—') . "\n\n";
$telo .= "Zpráva:\n{$zprava}\n\n";
$telo .= str_repeat('-', 40) . "\n";
$telo .= 'Odesláno: ' . date('j.n.Y H:i') . ' | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . "\n";

$hlavicky  = 'From: Web <' . ODESILATEL . ">\r\n";
$hlavicky .= 'Reply-To: ' . ($email !== '' ? $email : ODESILATEL) . "\r\n";
$hlavicky .= "Content-Type: text/plain; charset=UTF-8\r\n";
$hlavicky .= "MIME-Version: 1.0\r\n";

if (!@mail($prijemce, $predmet, $telo, $hlavicky)) fail('Zprávu se nepodařilo odeslat.', 500);

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
