<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('sale')) {
    fwrite(STDERR, 'sale module missing\n');
    exit(1);
}

$rs = CSalePaySystem::GetList([], ['ACTIVE' => 'Y']);
while ($r = $rs->Fetch()) {
    $path = $r['LOGOTIP'] ? CFile::GetPath($r['LOGOTIP']) : '';
    $exists = ($path && is_file($_SERVER['DOCUMENT_ROOT'] . $path)) ? 'yes' : 'no';
    echo $r['ID'] . ' | ' . $r['NAME'] . ' | ' . $r['LOGOTIP'] . ' | ' . $path . ' | ' . $exists . PHP_EOL;
}
