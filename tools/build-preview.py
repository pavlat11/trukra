#!/usr/bin/env python3
"""
Vytvoří statický náhled webu pro GitHub Pages.

PHP web se vykreslí s dočasnou SQLite databází naplněnou ze schema.sql
(výchozí obsah webu) a výsledek se uloží do složky _site/.
Nejde o skutečný provoz: administrace, ukládání změn a formulář tam nefungují.
"""
import os, shutil, sqlite3, subprocess, sys, time, urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BUILD = os.path.join(ROOT, "_build")
SITE = os.path.join(ROOT, "_site")
PORT = 8123

SKIP = {".git", ".github", "_build", "_site", "tools", "uploads"}

DB_SHIM = """<?php
declare(strict_types=1);
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../preview.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}
"""

DDL = """
CREATE TABLE settings(key TEXT PRIMARY KEY, value TEXT);
CREATE TABLE sections(key TEXT PRIMARY KEY, label TEXT, visible INT, sort_order INT);
CREATE TABLE stats(id INTEGER PRIMARY KEY AUTOINCREMENT, value TEXT, suffix TEXT, label TEXT, is_number INT DEFAULT 1, sort_order INT);
CREATE TABLE services(id INTEGER PRIMARY KEY AUTOINCREMENT, icon TEXT, title TEXT, text TEXT, tags TEXT, sort_order INT);
CREATE TABLE usp_items(id INTEGER PRIMARY KEY AUTOINCREMENT, icon TEXT, title TEXT, text TEXT, sort_order INT);
CREATE TABLE steps(id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, text TEXT, sort_order INT);
CREATE TABLE reviews(id INTEGER PRIMARY KEY AUTOINCREMENT, stars INT, text TEXT, author TEXT, location TEXT, sort_order INT);
CREATE TABLE faqs(id INTEGER PRIMARY KEY AUTOINCREMENT, question TEXT, answer TEXT, is_open INT DEFAULT 0, sort_order INT);
CREATE TABLE gallery(id INTEGER PRIMARY KEY AUTOINCREMENT, type TEXT, filename TEXT, category TEXT, title TEXT, subtitle TEXT, size TEXT, sort_order INT, created_at TEXT);
"""


def copy_project():
    shutil.rmtree(BUILD, ignore_errors=True)
    os.makedirs(BUILD)
    for name in os.listdir(ROOT):
        if name in SKIP:
            continue
        src = os.path.join(ROOT, name)
        dst = os.path.join(BUILD, name)
        if os.path.isdir(src):
            shutil.copytree(src, dst)
        else:
            shutil.copy2(src, dst)
    with open(os.path.join(BUILD, "inc", "db.php"), "w", encoding="utf-8") as f:
        f.write(DB_SHIM)
    shutil.copy2(os.path.join(BUILD, "config.example.php"), os.path.join(BUILD, "config.php"))


def build_database():
    with open(os.path.join(ROOT, "schema.sql"), encoding="utf-8") as f:
        src = f.read()
    inserts = src[src.index("INSERT INTO"):]
    inserts = "\n".join(l for l in inserts.splitlines() if not l.strip().startswith("--"))
    con = sqlite3.connect(os.path.join(BUILD, "preview.sqlite"))
    con.executescript(DDL)
    con.executescript(inserts)
    con.commit()
    con.close()


def render():
    server = subprocess.Popen(
        ["php", "-S", f"127.0.0.1:{PORT}", "-t", BUILD],
        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
    )
    try:
        html = None
        for _ in range(40):
            try:
                with urllib.request.urlopen(f"http://127.0.0.1:{PORT}/index.php", timeout=5) as r:
                    html = r.read().decode("utf-8")
                break
            except Exception:
                time.sleep(0.25)
        if html is None:
            sys.exit("Nepodařilo se vykreslit stránku (PHP server neodpovídá).")
        return html
    finally:
        server.terminate()
        server.wait()


def main():
    copy_project()
    build_database()
    html = render()
    if "Chyba připojení" in html or "Fatal error" in html or "<html" not in html:
        sys.exit("Vykreslení skončilo chybou:\n" + html[:500])

    # Statický náhled: formulář nemá kam odeslat
    html = html.replace('action="send.php"', 'action="#" onsubmit="return false"')

    shutil.rmtree(SITE, ignore_errors=True)
    os.makedirs(SITE)
    with open(os.path.join(SITE, "index.html"), "w", encoding="utf-8") as f:
        f.write(html)
    for d in ("css", "js", "img"):
        shutil.copytree(os.path.join(BUILD, d), os.path.join(SITE, d))
    open(os.path.join(SITE, ".nojekyll"), "w").close()
    shutil.rmtree(BUILD, ignore_errors=True)
    print(f"Hotovo: {os.path.join(SITE, 'index.html')} ({len(html)//1024} kB)")


if __name__ == "__main__":
    main()
