# Themesync

Themesync ermöglicht die bidirektionale Synchronisation von REDAXO-Modulen und Templates zwischen:
- FTP-Servern (Download)
- Lokalem Dateisystem (bidirektional, Git-versionierbar)
- REDAXO-Datenbank

## Features

- ✅ **Theme-Addon Kompatibilität**: Nutzt `/theme/` Pfade wenn Theme-Addon verfügbar
- ✅ **Config.yml Support**: Metadaten (Key, Version, Autor, Git-Info) in YAML-Format
- ✅ **Key-Feld Support**: Vorbereitet für REDAXO 5.15+ Template Key-Felder
- ✅ **Git-Integration**: Automatische Extraktion von Commit-Hash, Branch und Timestamp
- ✅ **Flexible Pfade**: Theme-Addon (`/theme/`) oder Repository (`/data/addons/themesync/repository/`)

## Konfiguration

Nach dem Installieren die Konfiguration über das Backend setzen oder `repo.yml` im `data`-Ordner bearbeiten.

### FTP-Repository

Lädt Module/Templates von einem externen FTP-Server (nur Download, keine bidirektionale Sync).

```yaml
classname: 'rex_themesync_repo_ftp'
host: 'ftp.example.com'
user: 'username'
pass: 'password'
dir: '/httpdocs/theme/'
```

### Lokales Dateisystem

Bidirektionale Synchronisation mit lokalem Dateisystem. Unterstützt Git-Versionierung.

**Theme-Addon Modus (empfohlen):**
```yaml
classname: 'rex_themesync_repo_localfilesystem'
use_theme_paths: true
```

Nutzt automatisch:
- `/theme/modules/` für Module
- `/theme/templates/` für Templates

**Repository Modus (Fallback):**
```yaml
classname: 'rex_themesync_repo_localfilesystem'
repo: 'repository/'  # relativ zu /redaxo/data/addons/themesync/
```

Oder mit absolutem Pfad:
```yaml
classname: 'rex_themesync_repo_localfilesystem'
repo: '/absolute/path/to/repository/'
```

## Verzeichnisstruktur

### Mit Theme-Addon

```
/theme/
    ├── modules/
    │   ├── 01-text-bild-video-link/
    │   │   ├── input.php
    │   │   ├── output.php
    │   │   └── config.yml
    │   └── 02-bildergalerie/
    │       ├── input.php
    │       ├── output.php
    │       └── config.yml
    └── templates/
        ├── 01-standard/
        │   ├── template.php
        │   └── config.yml
        └── 02-fullwidth/
            ├── template.php
            └── config.yml
```

### Ohne Theme-Addon (Fallback)

```
/redaxo/data/addons/themesync/repository/
    ├── modules/
    │   └── ...
    └── templates/
        └── ...
```

## Config.yml Format

Jedes Modul/Template kann eine `config.yml` mit Metadaten haben:

### Modul

```yaml
name: "Text, Bild, Video, Link"
key: "01"                           # Numerischer Key (optional in REDAXO 5.13, führend in 5.15+)
description: "Universalmodul für Text, Medien und Links"
version: "2.1.0"
author: "Stefan Beyer"
category: "content"

# Git-Informationen (automatisch generiert)
git:
  commit: "abc123def456"
  branch: "main"
  last_update: "2025-11-14T12:00:00+01:00"

# Dateien
files:
  input: "input.php"
  output: "output.php"
```

### Template

```yaml
name: "Standard"
key: "01"
description: "Standard-Template mit Sidebar"
version: "1.5.0"
author: "Stefan Beyer"

git:
  commit: "def789ghi012"
  branch: "main"
  last_update: "2025-11-14T12:00:00+01:00"

files:
  template: "template.php"
```

## Key-Feld Extraktion

Themesync extrahiert automatisch numerische Keys aus Ordnernamen:

- `01-text-bild-video-link` → Key: `"01"`
- `02_bildergalerie` → Key: `"02"`
- `0010_excel_2_table` → Key: `"0010"`

Diese Keys werden:
1. In `config.yml` gespeichert
2. Für die Sortierung verwendet
3. In REDAXO 5.15+ als Template-Key verwendet (wenn verfügbar)

## Workflow-Beispiele

### 1. Modul bearbeiten und versionieren

1. **REDAXO-Backend**: Modul bearbeiten
2. **Themesync**: "Export to Filesystem"
3. **Git**:
   ```bash
   cd /theme
   git add modules/01-text-bild-video-link/
   git commit -m "Update module"
   git push
   ```

### 2. Modul von FTP laden

1. **Themesync Config**: FTP-Verbindung konfigurieren
2. **Themesync**: Modul auswählen
3. **Aktion**: "Download to Filesystem"
4. **Aktion**: "Import to REDAXO"

## REDAXO 5.15+ Support

Themesync ist vorbereitet für REDAXO 5.15+ Template Key-Felder:

- Key wird aus Ordnernamen extrahiert und in `config.yml` gespeichert
- Bei Import in REDAXO 5.15+ wird Key verwendet (wenn verfügbar)
- Abwärtskompatibel mit REDAXO 5.13

## Weitere Dokumentation

Siehe [THEMESYNC_MODERNIZATION_PLAN.md](THEMESYNC_MODERNIZATION_PLAN.md) für:
- Detaillierte Architektur
- Implementations-Roadmap
- Technische Details
- Workflow-Beispiele

