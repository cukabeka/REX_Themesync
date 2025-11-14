# Themesync - Module & Templates Synchronisation

Themesync synchronisiert REDAXO-Module und Templates zwischen lokaler Datenbank und einem externen Repository (FTP, lokales Dateisystem, etc.).

## 🎯 Features

### Multidirektionale Synchronisation
- **Download**: Module/Templates aus Repository in REDAXO laden
- **Upload**: Module/Templates von REDAXO ins Repository speichern
- **Matching**: Automatische Zuordnung zwischen lokalen und Remote-Modulen

### Repository-Modi
1. **Lokales Dateisystem** (empfohlen)
   - GitHub Installer kompatibel
   - Theme-Addon kompatibel
   - Automatische Pfad-Erkennung

2. **FTP**
   - Remote-Server Synchronisation
   - Flexibles Directory-Mapping

### Struktur-Kompatibilität
- **Theme-Addon**: `theme/modules/`, `theme/templates/`
- **GitHub Installer**: `assets/modules/`, `assets/templates/`
- **Legacy**: `redaxo/data/addons/themesync/repo/`

## 📁 Repository-Struktur

### Lokales Dateisystem
```
theme/                           # (Theme-Addon Struktur, bevorzugt)
├── modules/
│   ├── 01-text-block/
│   │   ├── config.yml
│   │   ├── input.php
│   │   ├── output.php
│   │   ├── README.md (optional)
│   │   └── assets/
│   │       ├── 01-text-block.css
│   │       └── 01-text-block.js
│   └── 02-gallery/
│       ├── config.yml
│       ├── input.php
│       ├── output.php
│       └── assets/
│
└── templates/
    └── main-layout/
        ├── config.yml
        ├── template.php
        ├── README.md (optional)
        └── assets/
```

**Oder alternativ** (GitHub Installer Struktur):
```
assets/
├── modules/
│   ├── 01-text-block/
│   ├── 02-gallery/
│   └── ...
└── templates/
    └── main-layout/
```

### config.yml Format

```yaml
# Module config.yml
title: "01 - Text Block"
description: "Einfacher Text-Modul"
author: "Dein Name"
version: "1.0.0"
redaxo_version: "5.13+"
```

## ⚙️ Konfiguration

1. **Addon installieren**
   ```
   Backend → Addons → Themesync
   ```

2. **Repository konfigurieren**
   Datei: `redaxo/data/addons/themesync/repo.yml`

### Lokales Dateisystem (empfohlen)
```yaml
classname: "rex_themesync_repo_localfilesystem"
repo: "repo/"
```

Das Addon sucht automatisch nach:
1. `/theme/modules/` und `/theme/templates/` (Theme-Addon, bevorzugt)
2. `/assets/modules/` und `/assets/templates/` (GitHub Installer)
3. `/redaxo/data/addons/themesync/repo/` (Legacy)

### FTP-Repository
```yaml
classname: "rex_themesync_repo_ftp"
host: "example.com"
user: "ftp_username"
pass: "ftp_password"
dir: "/"
```

## 🔄 Integration mit GitHub Installer

### Workflow
1. Module im GitHub-Repository erstellen mit numeriertem CSS
2. GitHub Installer → "Neu laden" → Modul installiert
3. Assets landen in `/assets/modules/{key}/`
4. Themesync kann die Module ebenfalls verwalten

### Repository-Beispiel
```
github-repo/
├── modules/
│   └── 01-text-block/
│       ├── config.yml
│       ├── input.php
│       ├── output.php
│       └── assets/
│           ├── 01-text-block.css
│           └── 01-text-block.js
```

## 🧵 Integration mit Theme-Addon

### Struktur
Das Theme-Addon stellt bereit:
- `theme/private/` - Backend-Dateien (PHP, Libs, etc.)
- `theme/public/` - Frontend-Assets
- `theme/modules/` - Modulverwaltung
- `theme/templates/` - Template-Verwaltung

Themesync nutzt automatisch `theme/modules/` und `theme/templates/` wenn vorhanden.

### Asset-Struktur
```
theme/public/rex_bp/
├── 00-vendor/css/
├── 01-base.css
├── 02-{domain}.css
└── 03-modules.css

theme/modules/
├── 01-text-block/
│   └── assets/
│       └── 01-text-block.css
```

## 📋 Verwendung

### Module/Templates Laden
1. Backend → Addons → Themesync → **Modules** oder **Templates**
2. Verfügbare Module/Templates aus Repository
3. **Load** klicken zum Herunterladen
4. Module/Templates werden in REDAXO-Datenbank gespeichert

### Module/Templates Hochladen
1. Backend → Addons → Themesync → **Modules** oder **Templates**
2. REDAXO-Module/Templates auswählen
3. **Upload** klicken zum Hochladen
4. Dateien werden ins Repository geschrieben

### Modul-Matching
1. Backend → Addons → Themesync → **Match Modules**
2. Automatische Zuordnung zwischen Remote und lokalen Modulen
3. Behilft bei Umbenennungen und Neustrukturierungen

## 🎨 Numerierungs-Konzept (für Asset-Organisation)

Module sollten mit Nummern benannt werden für korrekte CSS-Load-Reihenfolge:

```
theme/modules/
├── 01-text-block/
├── 02-gallery/
├── 03-slideshow/
└── 04-slider/

CSS-Ladereihenfolge:
00-vendor/ (Bootstrap, UIKit)
01-base.css (Variablen, Fonts)
02-{domain}.css (Domain-Styles)
01-text-block.css
02-gallery.css
03-slideshow.css
04-slider.css
03-modules.css (kombiniert)
```

## 🔗 Kompatibilität

| Komponente | Kompatibilität | Beschreibung |
|-----------|---------------|-------------|
| GitHub Installer | ✅ Vollständig | Modulstruktur identisch |
| Theme-Addon | ✅ Vollständig | Nutzt `theme/modules/` & `theme/templates/` |
| Assets/Modules | ✅ Vollständig | Alternative GitHub Installer Struktur |
| FTP-Repos | ✅ Vollständig | Historische Unterstützung |

## 📖 Weitere Ressourcen

- **GitHub Installer**: `redaxo/src/addons/github_installer/README.md`
- **Theme-Addon**: `theme/` Verzeichnis
- **REDAXO-Dokumentation**: https://redaxo.org/doku/main
