<?php
echo 'abc';

$dataDir = rex_path::addonData('themesync');
$repoConfig = $dataDir.'repo.yml';
if (!file_exists($repoConfig)) {
    $config = '# REPO CONFIG
# use localfolder as repo (subdir in data/addons/themesync):
#classname: "rex_themesync_repo_localfilesystem"
#repo: "repo/"

# ftp repo
classname: "rex_themesync_repo_ftp"
host: ""
user: ""
pass: ""
dir: ""
';
    rex_file::put($repoConfig, $config);
} else {
    echo 'yeeesss';
}