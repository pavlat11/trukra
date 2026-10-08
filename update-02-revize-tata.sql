-- =========================================================
-- ÚPRAVY OBSAHU podle revize webu (Revize_web_táta.docx)
-- Spusťte tento soubor na SVOJÍ existující databázi
-- (phpMyAdmin -> vaše databáze -> záložka "SQL" -> vložit -> Provést).
-- Nic nemaže ani neresetuje, jen upravuje pár konkrétních textů.
-- Bezpečné spustit i opakovaně.
-- =========================================================

-- 1) Rok založení dílny: 1985 -> 1993
UPDATE settings SET value = 'Rodinná truhlárna od roku 1993' WHERE `key` = 'hero_eyebrow';
UPDATE settings SET value = '1993' WHERE `key` = 'about_badge_year';

-- 2) Hero text (úvod) — přidány "ložnice, postele"
UPDATE settings SET value = 'Navrhujeme, vyrábíme a montujeme kuchyně, ložnice, postele, vestavěné skříně, schody, sauny i atypický nábytek. Poctivé řemeslo a osobní přístup — po celé České republice.'
WHERE `key` = 'hero_text';

-- 3) Text v sekci "O nás" — přidáno "ložnic, koupelen,"
UPDATE settings SET value = 'Jsme rodinná truhlárna z Budislavi u Tábora. Za tu dobu prošly naší dílnou stovky kuchyní, ložnic, koupelen, skříní i schodišť — a téměř ke každé zakázce se po letech vracíme na servis. To je pro nás nejlepší vysvědčení.'
WHERE `key` = 'about_text1';

-- 4) Služba "Kuchyně na míru" — smazán štítek "kování Blum" (dělá i Hettich a další, podle přání zákazníka)
UPDATE services SET tags = 'masiv i dýha, osazení spotřebičů' WHERE title = 'Kuchyně na míru';

-- 5) Služba "Dřevěné schody" — smazán štítek "ocel i sklo"
UPDATE services SET tags = 'dub jasan buk, protiskluz' WHERE title = 'Dřevěné schody';

-- 6) Služba "Nábytek na míru" — přidány ložnice a postele
UPDATE services
SET text = 'Stoly, komody, knihovny, ložnice, postele, pracovny i dětské pokoje. Navrhneme kus, který v žádném obchodě nekoupíte.',
    tags = 'masivní stoly, postele, kanceláře, doplňky'
WHERE title = 'Nábytek na míru';

-- 7) Služba "Montáž a servis" — přidána vrata
UPDATE services
SET text = 'Pergoly, přístřešky, vrata, dřevěné obklady a lehká tesařina. Po montáži zůstáváme k dispozici pro servis i úpravy.',
    tags = 'pergoly, vrata, obklady, záruční servis'
WHERE title = 'Montáž a servis';
