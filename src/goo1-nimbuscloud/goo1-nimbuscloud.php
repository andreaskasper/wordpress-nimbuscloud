<?php
/**
 * Plugin Name: goo1 Nimbuscloud Plugin
 * Plugin URI: https://github.com/andreaskasper/wordpress-nimbuscloud
 * Description: Connect Nimbuscloud to your Wordpress
 * Author: Andreas Kasper
 * Version: 0.1.9
 * Author URI: https://github.com/andreaskasper/
 * Network: True
 * Text Domain: goo1-nimbuscloud
 */

spl_autoload_register(function ($class_name) {
	if (substr($class_name,0,25) != "plugins\\goo1\\nimbuscloud\\") return false;
	$files = array(
		__DIR__."/classes/".str_replace("\\", DIRECTORY_SEPARATOR,substr($class_name, 25)).".php"
	);
	foreach ($files as $file) {
		if (file_exists($file)) {
			include($file);
			return true;
		}
	}
	die(__DIR__."/classes/".str_replace("\\", DIRECTORY_SEPARATOR,substr($class_name, 24)).".php");
	return false;
});

add_action( "plugins_loaded", function() {
    load_plugin_textdomain( "goo1-nimbuscloud", FALSE, basename( dirname( __FILE__ ) ) . "/languages/" );
});
\plugins\goo1\nimbuscloud\core::init();


if (!class_exists("Puc_v4_Factory")) {
	require_once(__DIR__."/plugin-update-checker/plugin-update-checker.php");
}
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    "https://raw.githubusercontent.com/andreaskasper/wordpress-nimbuscloud/main/dist/updater.json",
    __FILE__, //Full path to the main plugin file or functions.php.
    "goo1-nimbuscloud"
);