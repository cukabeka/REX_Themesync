# Themesync Modernisierungs-Plan

## 📋 Übersicht

Dieses Dokument beschreibt die Modernisierung von Themesync zur Kompatibilität mit:
- GitHub Installer (FriendsOfREDAXO)
- Theme-Addon (verwendet `/theme` statt `/repository`)
- REDAXO 5.15+ Key-Value System für Templates
- Bidirektionale Synchronisation (FTP und Git)

**Aktuelle REDAXO Version:** 5.13.3
**Ziel-Version:** 5.15+ ready (abwärtskompatibel)

---

## 🎯 Kernkonzept

Themesync ermöglicht bidirektionale Synchronisation zwischen:

1. **FTP-Server** (extern)
   - Beliebige Ordnerstruktur auf Remote-Server
   - Nur Download (einseitig)
   - Ideal für Distribution

2. **GitHub Installer** (optional, via github_installer Addon)
   - GitHub Repositories als Quelle
   - Kompatibilität mit FriendsOfREDAXO Standard

3. **Lokales Dateisystem** (lokal)
   - `/theme/modules/` + `/theme/templates/` (primär, Theme-Addon kompatibel)
   - `/redaxo/data/addons/themesync/repository/modules/` + `/templates/` (fallback)
   - Bidirektionale Sync ✅
   - Git-versionierbar

4. **REDAXO Datenbank** (lokal)
   - Aktuelle Module/Templates in der DB
   - Wird mit Dateisystem synchronisiert

---

## 📊 REDAXO 5.13 vs 5.15+ Unterschiede

### REDAXO 5.13 (Aktuell)

**Templates:**
- Identifizierung nur über Namen
- Keine Key-Felder in der Datenbank

**Struktur:**
```
/lib/module/0010_text_bild_video_link/
    ├── input.inc
    ├── output.inc
    ├── config.inc
    └── info.inc
```

**Ansatz für Themesync:**
- Key wird optional aus Ordnernamen extrahiert (z.B. `0010` aus `0010_text_bild_video_link`)
- In `config.yml` gespeichert für zukünftige Kompatibilität
- CSS-Load-Order bleibt über Nummerierung erhalten

### REDAXO 5.15+ (Zukünftig)

**Templates:**
- Key-Feld als zusätzliche eindeutige ID
- Key wird führend für Template-Identifikation

**Struktur (erweitert):**
```
/theme/modules/01-text-bild-video-link/
    ├── input.php
    ├── output.php
    └── config.yml          # NEU: Metadaten inkl. Key
```

**Themesync Enhancement:**
- Key wird aus `config.yml` gelesen
- Falls nicht vorhanden, aus Ordnernamen extrahiert
- Key wird beim Import in REDAXO 5.15+ verwendet

---

## 🔌 Repository-Modi

### 1. FTP-Mode (extern)

**Konfiguration (config):**
```yaml
classname: 'rex_themesync_repo_ftp'
host: 'ftp.example.com'
user: 'username'
pass: 'password'
dir: '/httpdocs/theme/'
```

**Struktur auf FTP-Server:**
```
/theme/
    ├── modules/
    │   ├── 01-text-bild-video-link/
    │   │   ├── input.php
    │   │   ├── output.php
    │   │   └── config.yml
    │   └── 02-bildergalerie/
    │       └── ...
    └── templates/
        ├── 01-standard/
        │   ├── template.php
        │   └── config.yml
        └── 02-fullwidth/
            └── ...
```

**Eigenschaften:**
- ✅ Nur Download (keine bidirektionale Sync)
- ✅ Keine Git-Versionierung
- ✅ Geeignet für externe Distribution
- ❌ Keine Diff-Ansicht möglich

### 2. Lokales Dateisystem (lokal)

**Konfiguration (config):**
```yaml
classname: 'rex_themesync_repo_localfilesystem'
repo: 'theme/'  # relativ zu /redaxo/data/addons/themesync/
# ODER
repo: '/absolute/path/to/theme/'
```

**Theme-Addon Kompatibilität:**
Wenn Theme-Addon installiert, primäre Pfade:
```
/theme/
    ├── modules/
    └── templates/
```

Fallback (ohne Theme-Addon):
```
/redaxo/data/addons/themesync/repository/
    ├── modules/
    └── templates/
```

**Eigenschaften:**
- ✅ Bidirektionale Sync (Download + Upload)
- ✅ Git-Versionierung möglich
- ✅ Diff-Ansicht verfügbar
- ✅ Commit-Historie einsehbar
- ✅ Theme-Addon kompatibel

### 3. GitHub Installer Integration (optional)

**Voraussetzung:**
- `github_installer` Addon installiert

**Konfiguration:**
```yaml
classname: 'rex_themesync_repo_github'
repository: 'FriendsOfREDAXO/modulsammlung'
branch: 'main'
```

**Eigenschaften:**
- ✅ GitHub als Quelle
- ✅ Kompatibel mit FriendsOfREDAXO Standard
- ✅ Versionshistorie über GitHub API
- ✅ Release-Management
- ❌ Kein direkter Upload (nur über GitHub)

---

## 💾 config.yml Struktur

Jedes Modul/Template hat ein `config.yml` mit Metadaten:

### Modul config.yml

```yaml
# Modul-Metadaten
name: "Text, Bild, Video, Link"
key: "01"                           # Optional in 5.13, führend in 5.15+
description: "Universalmodul für Text, Medien und Links"
version: "2.1.0"
author: "Stefan Beyer"

# Git-Informationen (automatisch generiert bei lokalem Repo)
git:
  commit: "abc123def456"            # Letzter Commit Hash
  branch: "main"
  last_update: "2025-11-14T12:00:00+01:00"

# REDAXO-spezifisch
category: "content"                 # Optional: Kategorisierung
dependencies:
  - "mform"                         # Optional: Abhängigkeiten
  - "mblock"

# Dateien
files:
  input: "input.php"
  output: "output.php"
  assets:                           # Optional: Zusätzliche Assets
    - "mediamanager.inc"
    - "styles_scss.inc"
```

### Template config.yml

```yaml
# Template-Metadaten
name: "Standard"
key: "01"                           # Optional in 5.13, führend in 5.15+
description: "Standard-Template mit Sidebar"
version: "1.5.0"
author: "Stefan Beyer"

# Git-Informationen
git:
  commit: "def789ghi012"
  branch: "main"
  last_update: "2025-11-14T12:00:00+01:00"

# Template-spezifisch
ctype: "default"                    # Content-Type
categories:                         # Verfügbar für Kategorien
  - 1
  - 2
modules:                            # Erlaubte Module (optional)
  - "01"
  - "02"

# Dateien
files:
  template: "template.php"
```

---

## 🔄 Workflow-Beispiele

### Szenario 1: Modul von GitHub Installer laden

1. **Themesync öffnen** → Modul-Übersicht
2. **Repository wählen:** GitHub Installer (FriendsOfREDAXO/modulsammlung)
3. **Modul auswählen:** "01-text-bild-video-link"
4. **Aktion:** "Download to Local Filesystem"
   - Lädt Modul nach `/theme/modules/01-text-bild-video-link/`
   - Erstellt `config.yml` mit GitHub-Metadaten
5. **Aktion:** "Import to REDAXO"
   - Erstellt Modul in REDAXO-Datenbank
   - In REDAXO 5.15+: Verwendet Key "01" aus config.yml

**Ergebnis:**
- ✅ Modul in REDAXO verfügbar
- ✅ Modul im lokalen Dateisystem (Git-fähig)
- ✅ Herkunft dokumentiert (GitHub)

### Szenario 2: Lokales Modul editieren und versionieren

1. **REDAXO-Backend:** Modul "01-text-bild-video-link" bearbeiten
2. **Themesync:** Modul-Übersicht
3. **Status:** "⚠️ Local DB changed (not synced)"
4. **Aktion:** "Export to Local Filesystem"
   - Speichert Input/Output nach `/theme/modules/01-text-bild-video-link/`
   - Aktualisiert `config.yml` (Version bump, Timestamp)
5. **Git Workflow:**
   ```bash
   cd /theme
   git add modules/01-text-bild-video-link/
   git commit -m "Update text-bild-video-link module"
   git push
   ```

**Ergebnis:**
- ✅ Änderungen im Git
- ✅ Version erhöht
- ✅ Timestamp aktualisiert

### Szenario 3: Modul von externem FTP-Server laden

1. **Themesync:** Config
2. **FTP-Verbindung konfigurieren:**
   ```yaml
   classname: 'rex_themesync_repo_ftp'
   host: 'ftp.extern.de'
   user: 'username'
   pass: 'password'
   dir: '/www/theme/'
   ```
3. **Themesync:** Modul-Übersicht
4. **Modul auswählen:** "05-newsletter-signup"
5. **Aktion:** "Download to Local Filesystem"
   - Lädt Modul nach `/theme/modules/05-newsletter-signup/`
   - Erstellt `config.yml` (Version "untracked (ftp)")
6. **Aktion:** "Import to REDAXO"

**Ergebnis:**
- ✅ Modul in REDAXO verfügbar
- ✅ Modul im lokalen Dateisystem
- ❌ Keine Git-Historie (von FTP)

### Szenario 4: REDAXO 5.15+ mit Key-Feld

**Voraussetzung:** REDAXO auf 5.15+ aktualisiert

1. **Themesync:** Template-Übersicht
2. **Template:** "01-standard" (Key "01" in config.yml)
3. **Aktion:** "Import to REDAXO"
   - REDAXO 5.15+ erkennt Key-Feld
   - Template wird mit Key "01" angelegt

**Bei erneutem Import:**
- Key "01" wird erkannt
- Bestehendes Template wird aktualisiert (statt Duplikat)

---

## 🚀 Implementations-Roadmap

### Sprint 1: Config.yml & Key-Feld Support (REDAXO 5.13)

**Ziel:** Basis-Infrastruktur für config.yml und optionales Key-Feld

**Tasks:**
- [ ] `config.yml` Parser implementieren
- [ ] Key aus Ordnernamen extrahieren (Regex: `^(\d+)[-_]`)
- [ ] Key in `rex_themesync_item_base` speichern
- [ ] `repo_localfilesystem.php`: config.yml Lesen/Schreiben
- [ ] Git-Informationen auslesen (Commit Hash, Branch, Timestamp)
- [ ] Template-Unterstützung für config.yml

**Deliverables:**
- ✅ config.yml wird gelesen und geschrieben
- ✅ Key wird aus Ordnernamen extrahiert
- ✅ Git-Infos werden in config.yml gespeichert

### Sprint 2: Theme-Addon Pfade & Bidirektionale Sync

**Ziel:** /theme/ Pfade unterstützen, bidirektionale Sync

**Tasks:**
- [ ] Theme-Addon Erkennung implementieren
- [ ] Pfad-Resolver: /theme/ (primär) vs. /repository/ (fallback)
- [ ] `repo_localfilesystem.php`: Upload-Methoden erweitern
- [ ] Match-Modules Style für Templates
- [ ] Diff-Ansicht (DB vs. Filesystem)
- [ ] Timestamp-Vergleich (Server vs. Local)

**Deliverables:**
- ✅ /theme/ Pfade werden unterstützt
- ✅ Upload von DB zu Filesystem funktioniert
- ✅ Templates haben Match-Modules Style Anzeige
- ✅ Diff-Ansicht zeigt Änderungen

### Sprint 3: Testing & Dokumentation

**Ziel:** Vollständige Tests und User-Dokumentation

**Tasks:**
- [ ] Tests für config.yml Parser
- [ ] Tests für Key-Extraktion
- [ ] Tests für Theme-Pfade
- [ ] Tests für bidirektionale Sync
- [ ] User-Dokumentation aktualisieren
- [ ] README.md erweitern
- [ ] Screenshots für UI-Änderungen

**Deliverables:**
- ✅ Alle Features getestet
- ✅ Dokumentation vollständig
- ✅ README aktualisiert

### Sprint 4: REDAXO 5.15+ Migration (Später)

**Ziel:** Vollständige REDAXO 5.15+ Unterstützung mit Key-Feld

**Voraussetzung:** REDAXO System auf 5.15+ aktualisiert

**Tasks:**
- [ ] REDAXO 5.15+ Key-Feld API nutzen
- [ ] Key als führende Identifikation
- [ ] Migration: Name → Key Mapping
- [ ] Rückwärtskompatibilität testen

**Deliverables:**
- ✅ Key-Feld wird in REDAXO 5.15+ verwendet
- ✅ Migration von 5.13 → 5.15+ funktioniert
- ✅ Abwärtskompatibilität erhalten

---

## 📝 Wichtige Design-Entscheidungen

### 1. Nummerierung beibehalten

**Entscheidung:** Numerische Präfixe (01-, 02-, 03-) behalten

**Gründe:**
- CSS-Load-Order bleibt definiert
- Visuelle Sortierung in Dateimanager
- Abwärtskompatibilität zu bestehendem System

**Beispiel:**
```
/theme/modules/
    ├── 01-text-bild-video-link/
    ├── 02-bildergalerie/
    ├── 03-kontaktformular/
    └── 04-newsletter-signup/
```

### 2. config.yml als Single Source of Truth

**Entscheidung:** config.yml speichert alle Metadaten

**Gründe:**
- Git-versionierbar (Text-Format)
- Menschlich lesbar und editierbar
- Maschinell parsebar (YAML)
- Kompatibel mit GitHub Installer Standard

**Inhalt:**
- Name, Key, Version, Autor
- Git-Informationen (Commit, Branch, Timestamp)
- Dependencies, Dateien

### 3. Bidirektionale Sync nur für lokales Dateisystem

**Entscheidung:** FTP nur einseitig (Download), lokales Filesystem bidirektional

**Gründe:**
- FTP-Upload zu unsicher (versehentliches Überschreiben)
- FTP für Distribution (Read-Only)
- Lokales Filesystem für Entwicklung (Read-Write)
- Git-Workflow erfordert lokale Dateien

### 4. Key-Extraktion aus Ordnernamen

**Entscheidung:** Key aus Ordnernamen extrahieren (Regex: `^(\d+)[-_]`)

**Beispiele:**
- `01-text-bild-video-link` → Key: "01"
- `02_bildergalerie` → Key: "02"
- `text-ohne-nummer` → Key: null (kein Präfix)

**Gründe:**
- Abwärtskompatibilität zu REDAXO 5.13
- Vorbereitung für REDAXO 5.15+
- Automatische Migration möglich

### 5. Theme-Addon Kompatibilität

**Entscheidung:** /theme/ als primärer Pfad, /repository/ als Fallback

**Priorisierung:**
1. `/theme/` (wenn Theme-Addon installiert)
2. `/redaxo/data/addons/themesync/repository/` (Fallback)

**Gründe:**
- Theme-Addon ist REDAXO Standard für Theme-Entwicklung
- Kompatibilität mit Entwickler-Workflows
- Flexibilität für verschiedene Setups

---

## 🔧 Technische Details

### Pfad-Resolver Logik

```php
class rex_themesync_path_resolver {
    public static function getModulesPath() {
        // 1. Prüfe Theme-Addon
        if (rex_addon::exists('theme') && rex_addon::get('theme')->isAvailable()) {
            return rex_path::base('theme/modules/');
        }
        
        // 2. Fallback: Themesync Repository
        return rex_path::addonData('themesync', 'repository/modules/');
    }
    
    public static function getTemplatesPath() {
        if (rex_addon::exists('theme') && rex_addon::get('theme')->isAvailable()) {
            return rex_path::base('theme/templates/');
        }
        
        return rex_path::addonData('themesync', 'repository/templates/');
    }
}
```

### Key-Extraktion

```php
class rex_themesync_key_extractor {
    public static function extractKey($folderName) {
        // Regex: Zahlen am Anfang, gefolgt von - oder _
        if (preg_match('/^(\d+)[-_]/', $folderName, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
```

### Git-Informationen

```php
class rex_themesync_git_info {
    public static function getInfo($path) {
        if (!is_dir($path . '/.git')) {
            return null;
        }
        
        return [
            'commit' => exec('git -C ' . escapeshellarg($path) . ' rev-parse HEAD'),
            'branch' => exec('git -C ' . escapeshellarg($path) . ' rev-parse --abbrev-ref HEAD'),
            'last_update' => exec('git -C ' . escapeshellarg($path) . ' log -1 --format=%cI'),
        ];
    }
}
```

---

## 📚 Zusätzliche Dokumentation

### Für Entwickler

- **API-Dokumentation:** siehe `/docs/api/`
- **Code-Struktur:** siehe `/docs/architecture.md`
- **Testing-Guide:** siehe `/docs/testing.md`

### Für Nutzer

- **Installations-Guide:** siehe `README.md`
- **Configuration-Guide:** siehe `/docs/configuration.md`
- **Workflow-Beispiele:** siehe `/docs/workflows.md`

---

## ✅ Zusammenfassung

Diese Modernisierung macht Themesync:

- ✅ Kompatibel mit GitHub Installer Standard
- ✅ Kompatibel mit Theme-Addon (/theme/ Pfade)
- ✅ Bereit für REDAXO 5.15+ (Key-Feld Support)
- ✅ Git-versionierbar (config.yml, Metadaten)
- ✅ Bidirektional sync-fähig (lokales Filesystem)
- ✅ FTP-kompatibel (Download von externen Servern)
- ✅ Abwärtskompatibel (REDAXO 5.13 funktioniert weiter)

**Nächster Schritt:** Sprint 1 starten - Config.yml & Key-Feld Support implementieren
