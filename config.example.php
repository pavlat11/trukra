<?php
declare(strict_types=1);
/**
 * KONFIGURACE — Truhlářství Kratochvíl
 *
 * Tento soubor je jen VZOR. Zkopírujte ho jako config.php a tam vyplňte
 * údaje k vaší MySQL databázi (dostanete je od hostingu).
 * Skutečný config.php se do Gitu nenahrává (je v .gitignore).
 */

// ---- Přístup k databázi ----
const DB_HOST = 'localhost';
const DB_NAME = 'trukra';
const DB_USER = 'trukra';
const DB_PASS = 'zmente_heslo';
const DB_CHARSET = 'utf8mb4';

// ---- Administrace ----
// Výchozí heslo je: trukra2026
// Po prvním přihlášení si ho ZMĚŇTE v administraci (tlačítko "Změnit heslo").
const ADMIN_PASSWORD_HASH = '$2b$10$9kI.mMNBrTkrsMhS/D.IKuB0dybF2dIP10szamKSOLWcYBgvS8EM6';

// ---- Nahrávání souborů ----
const UPLOAD_MAX_IMAGE_MB = 8;   // max. velikost obrázku v MB
const UPLOAD_MAX_VIDEO_MB = 60;  // max. velikost videa v MB

// ---- Ostatní ----
date_default_timezone_set('Europe/Prague');
error_reporting(E_ALL);
ini_set('display_errors', '0'); // v provozu nezobrazovat chyby návštěvníkům
