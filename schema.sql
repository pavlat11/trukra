-- =========================================================
-- Truhlářství Kratochvíl — databázové schéma + výchozí obsah
-- Naimportujte celý tento soubor do prázdné MySQL databáze.
-- =========================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(100) NOT NULL PRIMARY KEY,
  `value` LONGTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sections (
  `key` VARCHAR(50) NOT NULL PRIMARY KEY,
  `label` VARCHAR(100) NOT NULL,
  `visible` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  value VARCHAR(20) NOT NULL,
  suffix VARCHAR(10) DEFAULT '',
  label VARCHAR(200) NOT NULL,
  is_number TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  icon VARCHAR(50) NOT NULL DEFAULT 'i-tools',
  title VARCHAR(200) NOT NULL,
  text TEXT,
  tags VARCHAR(255) DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usp_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  icon VARCHAR(50) NOT NULL DEFAULT 'i-shield',
  title VARCHAR(200) NOT NULL,
  text VARCHAR(255),
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS steps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  text TEXT,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  stars TINYINT NOT NULL DEFAULT 5,
  text TEXT,
  author VARCHAR(150),
  location VARCHAR(150),
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  answer TEXT,
  is_open TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('image','video') NOT NULL DEFAULT 'image',
  filename VARCHAR(255) NOT NULL,
  category VARCHAR(50) NOT NULL DEFAULT 'ostatni',
  title VARCHAR(200) DEFAULT '',
  subtitle VARCHAR(200) DEFAULT '',
  size VARCHAR(10) NOT NULL DEFAULT 'normal',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- SEKCE (pořadí a viditelnost)
-- =========================================================
INSERT INTO sections (`key`, label, visible, sort_order) VALUES
('announcement', 'Lišta s upozorněním (nahoře na webu)', 0, 5),
('hero',     'Úvod (hero)',        1, 10),
('cisla',    'Čísla / statistiky', 1, 20),
('sluzby',   'Služby',             1, 30),
('o-nas',    'O nás',              1, 40),
('reference','Reference',          1, 50),
('proces',   'Jak spolupráce probíhá', 1, 60),
('recenze',  'Recenze klientů',    1, 70),
('faq',      'Časté dotazy',       1, 80),
('poptavka', 'Poptávkový formulář',1, 90);

-- =========================================================
-- NASTAVENÍ — kontakty a texty jednotlivých sekcí
-- =========================================================
INSERT INTO settings (`key`, `value`) VALUES
('announcement_text', ''),
('seo_keywords', 'truhlářství, nábytek na míru, kuchyně na míru, vestavěné skříně, dřevěné schody, sauny, Tábor, Budislav'),
('canonical_url', 'https://www.trukra.cz/'),
('robots_index', '1'),
('og_image', 'og.jpg'),
('ga_measurement_id', ''),
('site_title', 'Truhlářství Kratochvíl | Nábytek na míru, kuchyně, schody – Budislav'),
('site_description', 'Zakázková truhlářská výroba s více než 40letou tradicí. Kuchyně na míru, vestavěné skříně, dřevěné schody, sauny a nábytek na míru. Návrh, výroba i montáž po celé ČR.'),
('company_name', 'Truhlářství Kratochvíl'),
('phone', '+420 776 594 326'),
('email', 'trukra@seznam.cz'),
('address', 'Budislav 89, okres Tábor'),
('hours', 'Po–Pá 8:00–17:00'),
('facebook_url', 'https://www.facebook.com/trukra/'),
('instagram_url', 'https://www.instagram.com/trukra_nabytek/'),

('hero_eyebrow', 'Rodinná truhlárna od roku 1993'),
('hero_title', 'Nábytek na míru,<br>který přežije generace'),
('hero_text', 'Navrhujeme, vyrábíme a montujeme kuchyně, ložnice, postele, vestavěné skříně, schody, sauny i atypický nábytek. Poctivé řemeslo a osobní přístup — po celé České republice.'),
('hero_cta1', 'Chci cenovou nabídku'),
('hero_cta2', 'Prohlédnout realizace'),
('hero_bullet1', 'Zaměření zdarma'),
('hero_bullet2', 'Návrh před výrobou'),
('hero_bullet3', 'Montáž vlastními lidmi'),
('hero_bg_image', 'hero.jpg'),
('hero_bg_image_mobile', 'hero-mobile.jpg'),

('sluzby_eyebrow', 'Co pro vás vyrobíme'),
('sluzby_title', 'Naše služby'),
('sluzby_text', 'Každá zakázka je originál. Od prvního náčrtu po finální montáž řešíme vše pod jednou střechou — bez subdodavatelů a bez kompromisů.'),

('about_eyebrow', 'Poctivé řemeslo'),
('about_title', 'Se dřevem pracujeme přes 40 let'),
('about_text1', 'Jsme rodinná truhlárna z Budislavi u Tábora. Za tu dobu prošly naší dílnou stovky kuchyní, ložnic, koupelen, skříní i schodišť — a téměř ke každé zakázce se po letech vracíme na servis. To je pro nás nejlepší vysvědčení.'),
('about_text2', 'Nevyrábíme na sklad a nepoužíváme katalogové rozměry. Přijedeme, zaměříme, poradíme s materiálem i kováním podle vašeho rozpočtu a teprve pak začneme kreslit.'),
('about_image', 'dilna-4x5.jpg'),
('about_image_mobile', 'dilna.jpg'),
('about_badge_year', '1993'),
('about_badge_label', 'rok založení dílny'),
('about_cta', 'Domluvit nezávaznou schůzku'),

('reference_eyebrow', 'Vybrané realizace'),
('reference_title', 'Naše reference'),
('reference_text', 'Malý výběr z toho, co jsme letos vyrobili. Kliknutím zobrazíte fotografii ve velkém.'),

('proces_eyebrow', 'Od nápadu k hotovému dílu'),
('proces_title', 'Jak spolupráce probíhá'),
('proces_text', 'Čtyři jasné kroky. Víte přesně, co se děje a kdy se to stane.'),

('recenze_eyebrow', 'Co říkají klienti'),
('recenze_title', 'Spokojení zákazníci'),

('faq_eyebrow', 'Časté dotazy'),
('faq_title', 'Na co se ptáte nejčastěji'),
('faq_text', 'Nenašli jste odpověď? Zavolejte na'),

('poptavka_eyebrow', 'Pojďme na to'),
('poptavka_title', 'Nezávazná cenová nabídka'),
('poptavka_text', 'Napište pár vět o vašem projektu. Ozveme se zpravidla do 24 hodin, poradíme a v případě potřeby přijedeme na zaměření.'),
('poptavka_bullet1', 'Zaměření a konzultace zdarma'),
('poptavka_bullet2', 'Nabídka do 3 pracovních dnů'),
('poptavka_bullet3', 'Odpovídáme zpravidla do 24 hodin'),

('footer_text', 'Rodinná truhlárna z Budislavi. Zakázková výroba nábytku, kuchyní, schodů a saun s více než čtyřicetiletou tradicí.'),
('footer_note', 'Vyrobeno s láskou ke dřevu.');

-- =========================================================
-- STATISTIKY
-- =========================================================
INSERT INTO stats (value, suffix, label, is_number, sort_order) VALUES
('40', '+', 'let praxe se dřevem', 1, 10),
('1000', '+', 'dokončených zakázek', 1, 20),
('100', '%', 'výroba ve vlastní dílně', 1, 30),
('ČR', '', 'působíme po celé republice', 0, 40);

-- =========================================================
-- SLUŽBY
-- =========================================================
INSERT INTO services (icon, title, text, tags, sort_order) VALUES
('i-kitchen', 'Kuchyně na míru', 'Kompletní návrh i výroba kuchyně přesně do vašeho prostoru, včetně kování, spotřebičů a pracovních desek.', 'masiv i dýha, osazení spotřebičů', 10),
('i-wardrobe', 'Vestavěné skříně', 'Maximální využití každého centimetru — šatny, skříně do podkroví i atypické úložné prostory pod schody.', 'posuvné dveře, vnitřní organizéry, LED osvětlení', 20),
('i-stairs', 'Dřevěné schody', 'Masivní schodiště s precizním zpracováním a bezpečným zábradlím. Samonosné, obkladové i na ocelové konstrukci.', 'dub jasan buk, protiskluz', 30),
('i-sauna', 'Sauny a wellness', 'Finské sauny na míru z osiky, termoborovice nebo cedru — do domu, sklepa i na zahradu.', 'lavice na míru, kamna a ovládání, venkovní provedení', 40),
('i-table', 'Nábytek na míru', 'Stoly, komody, knihovny, ložnice, postele, pracovny i dětské pokoje. Navrhneme kus, který v žádném obchodě nekoupíte.', 'masivní stoly, postele, kanceláře, doplňky', 50),
('i-tools', 'Montáž a servis', 'Pergoly, přístřešky, vrata, dřevěné obklady a lehká tesařina. Po montáži zůstáváme k dispozici pro servis i úpravy.', 'pergoly, vrata, obklady, záruční servis', 60);

-- =========================================================
-- USP (v sekci O nás)
-- =========================================================
INSERT INTO usp_items (icon, title, text, sort_order) VALUES
('i-ruler', 'Vlastní zaměření', 'Bez poplatku, včetně konzultace.', 10),
('i-wood', 'Prověřené materiály', 'Evropský masiv a kvalitní kování.', 20),
('i-calendar', 'Dohodnuté termíny', 'Jasný harmonogram, který držíme.', 30),
('i-shield', 'Servis po montáži', 'Seřízení i úpravy i po letech.', 40);

-- =========================================================
-- POSTUP
-- =========================================================
INSERT INTO steps (title, text, sort_order) VALUES
('Konzultace a zaměření', 'Ozvěte se telefonicky nebo formulářem. Přijedeme k vám, zaměříme prostor a probereme představy i rozpočet.', 10),
('Návrh a nabídka', 'Připravíme vizualizaci a položkovou nabídku. Ladíme materiály, barvy a kování, dokud nebudete spokojeni.', 20),
('Výroba v naší dílně', 'Vše vyrábíme sami v Budislavi. Průběžně vás informujeme a hlídáme dohodnutý termín.', 30),
('Montáž a servis', 'Nábytek dovezeme, odborně namontujeme, uklidíme po sobě a zůstáváme k dispozici i po letech.', 40);

-- =========================================================
-- RECENZE
-- =========================================================
INSERT INTO reviews (stars, text, author, location, sort_order) VALUES
(5, 'Kuchyň sedí na milimetr, i tam, kde měly zdi „vlnu“. Termín platil a po montáži bylo uklizeno. Doporučuji.', 'Martina H.', 'Tábor', 10),
(5, 'Schodiště z dubu je srdcem domu. Pan Kratochvíl poradil s materiálem i povrchovou úpravou, výsledek předčil návrh.', 'Jiří P.', 'Soběslav', 20),
(5, 'Vestavěné skříně do podkroví — přesně tam, kde jsme mysleli, že to nejde. Skvělá komunikace od začátku do konce.', 'Rodina Nováků', 'Písek', 30);

-- =========================================================
-- FAQ
-- =========================================================
INSERT INTO faqs (question, answer, is_open, sort_order) VALUES
('Kolik stojí kuchyň na míru?', 'Cena vždy vychází z rozměrů, materiálu a kování. Po zaměření dostanete položkovou nabídku, ve které přesně vidíte, za co platíte — a můžeme ji společně upravit podle rozpočtu.', 1, 10),
('Jak dlouho trvá výroba?', 'U běžné kuchyně nebo vestavěné skříně počítejte zhruba 4–8 týdnů od odsouhlasení návrhu. Přesný termín potvrdíme v nabídce a držíme ho.', 0, 20),
('Jezdíte i mimo Jihočeský kraj?', 'Ano, realizujeme zakázky po celé ČR. U vzdálenějších míst si jen předem domluvíme podmínky dopravy a montáže.', 0, 30),
('Zajistíte i spotřebiče a dřezy?', 'Zajistíme. Poradíme s výběrem, obstaráme je za výhodnější ceny a při montáži rovnou osadíme.', 0, 40),
('Co když budu chtít po letech něco upravit?', 'Ozvěte se. Seřízení pantů, výměna kování nebo doplnění dalšího dílu — servis děláme i u zakázek starých mnoho let.', 0, 50);

-- =========================================================
-- GALERIE / REFERENCE
-- =========================================================
INSERT INTO gallery (type, filename, category, title, subtitle, size, sort_order) VALUES
('image', 'g-kuchyne.jpg', 'kuchyne', 'Kuchyň s dubovým ostrůvkem', 'Kuchyně na míru', 'tall', 10),
('image', 'g-kuchyne-detail.jpg', 'kuchyne', 'Detail ostrůvku a polic', 'Kuchyně na míru', 'normal', 20),
('image', 'g-skrine.jpg', 'skrine', 'Vestavěná skříň do předsíně', 'Vestavěné skříně', 'normal', 30),
('image', 'g-skrine-detail.jpg', 'skrine', 'Otevřená šatna s LED', 'Vestavěné skříně', 'normal', 40),
('image', 'g-schody.jpg', 'schody', 'Dubové schodiště s ocelí', 'Dřevěné schody', 'normal', 50),
('image', 'g-dilna-detail.jpg', 'dilna', 'Ruční opracování masivu', 'Z dílny', 'wide', 60),
('image', 'g-schody-detail.jpg', 'schody', 'Detail masivních stupňů', 'Dřevěné schody', 'normal', 70);
