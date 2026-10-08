# Truhlářství Kratochvíl — web s administrací

PHP + MySQL web s editací přímo na stránce (texty, fotky, videa, skrývání sekcí,
reference s hromadným nahráním, lišta s upozorněním, SEO a Google Analytics).

## Rychlá instalace

1. Zkopírujte `config.example.php` jako `config.php` a vyplňte přístup k databázi.
2. Naimportujte `schema.sql` do prázdné MySQL databáze.
3. Nahrajte soubory na hosting; složky `uploads/img` a `uploads/video` musí být zapisovatelné.
4. Přihlaste se na `/admin/` a **hned změňte výchozí heslo** (viz `NAVOD.txt`).

Podrobný návod je v [`NAVOD.txt`](NAVOD.txt).

## Aktualizace existující databáze

- `upgrade-01-oznameni-seo.sql` — doplněk pro DB vytvořenou před přidáním upozornění a SEO.
- `update-02-revize-tata.sql` — úpravy textů podle revize webu.

> `config.php` a nahrané soubory v `uploads/` se do repozitáře záměrně nenahrávají.
