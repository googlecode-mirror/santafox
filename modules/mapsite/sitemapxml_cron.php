<?php
$webRoot= dirname(dirname(dirname(__FILE__)))."/";
chdir($webRoot);
require_once $webRoot."ini.php";
require_once $webRoot."include/kernel.class.php";
require_once $webRoot."modules/mapsite/mapsite.class.php";
require_once $webRoot."admin/manager_modules.class.php";
$kernel = new kernel(PREFIX);
$kernel->priv_module_for_action_set('mapsite1');
$mapsite = new mapsite();
$mapsite->pub_create_sitemapxml();