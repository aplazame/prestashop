<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

// object module ($this) available
function upgrade_module_3_0_0($object)
{
    $logsDirectory = _PS_MODULE_DIR_ . 'aplazame/logs';
    if (is_dir($logsDirectory)) {
        removeDirectory($logsDirectory);
    }

    $this->unregisterHook('displayAdminProductsListBefore');

    return false;
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
