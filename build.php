<?php

// Get real path for our folder
$rootPath = __DIR__."/src/goo1-nimbuscloud/";

$str = file_get_contents(__DIR__."/src/goo1-nimbuscloud/goo1-nimbuscloud.php");
if (!preg_match("@Version: (?P<v>[0-9\.]+)@mi", $str, $m2)) die("Version nicht gefunden".PHP_EOL);
$m = $m2;
$g = explode(".", $m["v"]);
$g[2]++;
$m["v"]=implode(".", $g);
$str = str_replace("Version: ".$m2["v"], "Version: ".$m["v"], $str);
file_put_contents(__DIR__."/src/goo1-nimbuscloud/goo1-nimbuscloud.php", $str);
echo("Version: ".$m2["v"]." => ".$m["v"].PHP_EOL);

// Initialize archive object
$zip = new ZipArchive();
$zip->open('dist/goo1-nimbuscloud.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = str_replace(DIRECTORY_SEPARATOR, "/", substr($filePath, strlen($rootPath)));

        // Add current file to archive
        $zip->addFile($filePath, "goo1-nimbuscloud/".$relativePath);
    }
}

// Zip archive will be created only after closing object
$zip->close();



$version = $m["v"];

$json = json_decode(file_get_contents(__DIR__."/dist/updater.json"), true);

$json["version"] = $version;
$json["last_updated"] = date("Y-m-d H:i:s");

file_put_contents(__DIR__."/dist/updater.json", json_encode($json, JSON_PRETTY_PRINT));
echo("fertig".PHP_EOL.PHP_EOL);