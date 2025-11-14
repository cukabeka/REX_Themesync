<?php

/**
 * Manager for module matching and pairing
 * Inspired by D2U Helper ModuleManager
 */
class rex_themesync_match_module_manager {
    /** @var array<rex_themesync_match_module> Match modules */
    private $match_modules = [];
    
    /** @var rex_themesync_source Repository source */
    private $repo = null;
    
    /** @var rex_addon Addon instance */
    private $addon = null;
    
    /**
     * Constructor
     * 
     * @param rex_themesync_source $repo Repository source
     * @param rex_addon $addon Addon instance
     */
    public function __construct(&$repo, $addon) {
        $this->repo = $repo;
        $this->addon = $addon;
        
        // Load modules from repository
        $this->loadModulesFromRepo();
    }
    
    /**
     * Load modules from repository
     */
    private function loadModulesFromRepo() {
        $modules = $this->repo->listModules();
        
        foreach ($modules as $module) {
            $config = $module->getConfig();
            $name = $config && isset($config['name']) ? $config['name'] : $module->getName();
            $version = $config && isset($config['version']) ? $config['version'] : 'unknown';
            
            $match_module = new rex_themesync_match_module(
                $module->getName(),
                $name,
                $version,
                $this->repo
            );
            
            $this->match_modules[] = $match_module;
        }
    }
    
    /**
     * Get all match modules
     * 
     * @return array<rex_themesync_match_module>
     */
    public function getModules() {
        return $this->match_modules;
    }
    
    /**
     * Handle form actions
     * 
     * @param string $module_folder Module folder name
     * @param string $function Action to perform
     * @param int $paired_module_id REDAXO module ID to pair with
     */
    public function doActions($module_folder, $function, $paired_module_id) {
        foreach ($this->match_modules as $module) {
            if ($module->getFolderName() === $module_folder) {
                if ($function === 'autoupdate') {
                    if ($module->isAutoupdateActivated()) {
                        $module->disableAutoupdate();
                        echo rex_view::success($module->getName() . ': Autoupdate deaktiviert');
                    } else {
                        $module->activateAutoupdate();
                        echo rex_view::success($module->getName() . ': Autoupdate aktiviert');
                    }
                } elseif ($function === 'unlink') {
                    $module->unlink();
                    echo rex_view::success($module->getName() . ': Verknüpfung aufgehoben');
                } else {
                    // Install or update
                    $success = $module->install($paired_module_id);
                    if ($success) {
                        rex_delete_cache();
                        echo rex_view::success($module->getName() . ': Erfolgreich installiert');
                    } else {
                        echo rex_view::error($module->getName() . ': Installation fehlgeschlagen');
                    }
                }
                break;
            }
        }
    }
    
    /**
     * Perform autoupdate for all modules with autoupdate activated
     */
    public function autoupdate() {
        foreach ($this->match_modules as $module) {
            if ($module->isAutoupdateActivated() && $module->isUpdateNeeded()) {
                $module->install();
            }
        }
        rex_delete_cache();
    }
    
    /**
     * Get REDAXO modules
     * 
     * @param bool $unpaired_only Only unpaired modules
     * @return array<int, string> Module ID => Module name
     */
    public static function getRexModules($unpaired_only = false) {
        $rex_modules = [];
        
        $sql = rex_sql::factory();
        $query = 'SELECT id, name FROM ' . rex::getTablePrefix() . 'module ';
        
        if ($unpaired_only) {
            $query .= 'WHERE `attributes` NOT LIKE \'%"themesync_key"%\' OR `attributes` IS NULL ';
        }
        
        $query .= 'ORDER BY name';
        
        $sql->setQuery($query);
        
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $rex_modules[(int) $sql->getValue('id')] = $sql->getValue('name');
            $sql->next();
        }
        
        return $rex_modules;
    }
    
    /**
     * Get paired modules
     * 
     * @return array<int, array> Module ID => ['themesync_key' => ..., 'version' => ...]
     */
    public static function getModulePairs() {
        $paired_modules = [];
        
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id, attributes FROM ' . rex::getTablePrefix() . 'module '
            . 'WHERE `attributes` LIKE \'%"themesync_key"%\'');
        
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $attributes = json_decode($sql->getValue('attributes'), true);
            if (is_array($attributes) && isset($attributes['themesync_key'])) {
                $paired_modules[(int) $sql->getValue('id')] = [
                    'themesync_key' => $attributes['themesync_key'],
                    'version' => $attributes['themesync_version'] ?? 'unknown',
                    'autoupdate' => $attributes['themesync_autoupdate'] ?? false,
                ];
            }
            $sql->next();
        }
        
        return $paired_modules;
    }
    
    /**
     * Display module manager list
     */
    public function showManagerList() {
        $rex_modules = self::getRexModules();
        $unpaired_rex_modules = self::getRexModules(true);
        
        // Remove newly paired modules from unpaired list
        $installed_module = rex_request('module_folder', 'string');
        if ($installed_module !== '') {
            foreach ($unpaired_rex_modules as $rex_id => $name) {
                if (strpos($name, $installed_module) !== false) {
                    unset($unpaired_rex_modules[$rex_id]);
                }
            }
        }
        
        ?>
        <form action="<?= rex_url::currentBackendPage() ?>" method="post">
            <section class="rex-page-section">
                <div class="panel panel-default">
                    <header class="panel-heading">
                        <div class="panel-title">Module verwalten</div>
                    </header>
                    
                    <?= rex_view::info('<strong>Repository:</strong> ' . $this->repo->getRepoInfo()) ?>
                    
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="rex-table-id">Key</th>
                                <th>Modul (Repository)</th>
                                <th>Version</th>
                                <th>REDAXO Modul</th>
                                <th>Autoupdate</th>
                                <th class="rex-table-action">Funktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->match_modules as $module): ?>
                                <?php
                                $config = $module->getConfig();
                                $hasGitInfo = $config && isset($config['git']);
                                ?>
                                <tr>
                                    <td class="rex-table-id">
                                        <?php if ($module->getKey()): ?>
                                            <span class="label label-info"><?= htmlspecialchars($module->getKey()) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($module->getName()) ?></strong>
                                        <br/>
                                        <small class="text-muted">
                                            <code><?= htmlspecialchars($module->getFolderName()) ?></code>
                                            <?php if ($config && isset($config['description'])): ?>
                                                <br/><?= htmlspecialchars($config['description']) ?>
                                            <?php endif; ?>
                                            <?php if ($hasGitInfo): ?>
                                                <br/>
                                                <i class="fa fa-code-fork"></i> <?= htmlspecialchars($config['git']['branch']) ?>
                                                @ <?= htmlspecialchars(substr($config['git']['commit'], 0, 7)) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($module->getVersion()): ?>
                                            <span class="label label-default">v<?= htmlspecialchars($module->getVersion()) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($module->getRedaxoId() === 0): ?>
                                            <?php
                                            $select = new rex_select();
                                            $select->addOption('Neu anlegen', 0);
                                            $select->addArrayOptions($unpaired_rex_modules);
                                            $select->setName('pair_' . $module->getFolderName());
                                            $select->setAttribute('class', 'form-control');
                                            $select->setSelected(0);
                                            echo $select->get();
                                            ?>
                                        <?php else: ?>
                                            <a href="<?= rex_url::currentBackendPage(['function' => 'unlink', 'module_folder' => $module->getFolderName()]) ?>" 
                                               title="Verknüpfung aufheben">
                                                <i class="rex-icon fa-chain-broken"></i>
                                                <?= htmlspecialchars($rex_modules[$module->getRedaxoId()] ?? 'ID: ' . $module->getRedaxoId()) ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($module->isInstalled()): ?>
                                            <a href="<?= rex_url::currentBackendPage(['function' => 'autoupdate', 'module_folder' => $module->getFolderName()]) ?>">
                                                <i class="rex-icon <?= $module->isAutoupdateActivated() ? 'rex-icon-package-is-activated' : 'rex-icon-package-not-activated' ?>"></i>
                                                <?= $module->isAutoupdateActivated() ? 'Deaktivieren' : 'Aktivieren' ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="rex-table-action">
                                        <button type="submit" name="module_folder" class="btn btn-save" value="<?= htmlspecialchars($module->getFolderName()) ?>">
                                            <?php if (!$module->isInstalled()): ?>
                                                <i class="rex-icon rex-icon-package-not-installed"></i> Installieren
                                            <?php elseif ($module->isUpdateNeeded()): ?>
                                                <i class="rex-icon rex-icon-package-is-installed"></i> Aktualisieren
                                            <?php else: ?>
                                                <i class="rex-icon rex-icon-package-is-activated"></i> Neu installieren
                                            <?php endif; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </form>
        <?php
    }
}
