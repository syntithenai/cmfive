<?php


$modules['admin'] = array(
    'active' => true,
    'path' => 'system/modules',
    'topmenu' => true,
    'audit_ignore' => array("index"),
    'database_backup' => true,
);

// please override in the global config.php

$modules['admin']['printing']['command']['unix'] = 'lpr $filename';
$modules['admin']['printing']['command']['windows'] = '/Path/to/SumatraPDF.exe -print-to $printername $filename';