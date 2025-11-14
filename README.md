# Themesync

## Konfiguration

Nach dem Installieren die `repo.yml` im `data`-Ordner des Addons bearbeiten.

### FTP-Repository

    classname: 'rex_themesync_repo_ftp'
    host: '...'
    user: '...'
    pass: '...'
    dir: '/pfad/zu/repo/root/'

### Repository in lokalem Ordner

    classname: 'rex_themesync_repo_localfilesystem'
    repo: '/pfad/zu/repo/root/'
