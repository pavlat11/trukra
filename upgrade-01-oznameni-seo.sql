-- =========================================================
-- DOPLNĚK pro už existující databázi (pokud jste schema.sql
-- naimportovali dřív, než přibylo upozornění a SEO/Analytics).
-- Pokud zakládáte databázi úplně nově, tento soubor NEPOUŽÍVEJTE
-- — všechno potřebné už je v hlavním schema.sql.
--
-- Spuštění je bezpečné i opakovaně, nic nepřepíše existující data.
-- =========================================================

INSERT IGNORE INTO sections (`key`, label, visible, sort_order) VALUES
('announcement', 'Lišta s upozorněním (nahoře na webu)', 0, 5);

INSERT IGNORE INTO settings (`key`, `value`) VALUES
('announcement_text', ''),
('seo_keywords', ''),
('canonical_url', 'https://www.trukra.cz/'),
('robots_index', '1'),
('og_image', 'og.jpg'),
('ga_measurement_id', '');
