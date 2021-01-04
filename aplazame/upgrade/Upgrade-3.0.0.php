<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2021 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_0_0(Aplazame $module)
{
    $logsDirectory = _PS_MODULE_DIR_ . 'aplazame/logs';
    if (is_dir($logsDirectory)) {
        removeDirectory($logsDirectory);
    }

    $module->unregisterHook('displayAdminProductsListBefore');

    return true;
}

/**
 * @param string $dir
 */
function removeDirectory($dir)
{
    foreach (scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) {
            continue;
        }
        if (is_dir("$dir/$file")) {
            removeDirectory("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    rmdir($dir);
}
