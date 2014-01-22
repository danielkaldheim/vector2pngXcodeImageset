<?php
/**
 *
 *	createjson.php
 *	Created on 22.1.2014.
 *
 *	@author Daniel Rufus Kaldheim <daniel@idrift.no>
 *	@copyright 2012 - 2014 iDrift Web AS
 *	@version 1.0.0
 *
 */

define('DS', DIRECTORY_SEPARATOR);
global $ignore; $ignore = array('..', '.', '.DS_Store', '.git', '.svn', '.localized', 'Thumbs.db', '');

function open($path) {
	$dirs = array();
	foreach (scandir($path) as $file) {
		if (in_array($file, $GLOBALS['ignore'])) {
			continue;
		}
		if (is_dir($path . DS . $file)) {
			$dirs[] = realpath($path . DS . $file);
			$dirs = array_merge($dirs, open($path . DS . $file));
		}
	}
	return $dirs;
}

$options = getopt("d:", array("dir:"));
$optdir = (($options['d']) ? $options['d'] : $options['dir']);
$path = (($optdir) ? $optdir : dirname(__FILE__));

echo "Open ".realpath($path).PHP_EOL;

$dirs = open($path);

$countDirs = 0;
$countImages = 0;
foreach ($dirs as $dir) {
	if (end(explode('.', $dir)) == 'imageset') {
		echo "\tScans: ".end(explode(DS, $dir)).PHP_EOL;
		$files = scandir($dir);
		$json = array();

		foreach ($files as $file) {

			if (!in_array($file, $GLOBALS['ignore']) && in_array(end(explode(".", $file)), array('jpg', 'jpeg', 'png'))) {

				echo "\t\t{$file}".PHP_EOL;

				$images = array();
				$images['idiom'] = 'universal';
				$images['scale']  = "1x";
				if (strrpos($file, '@2x')) {
					$images['scale']  = "2x";
				}
				$images['filename'] = $file;
				$countImages++;
				$json['images'][] = $images;
			}

		}

		$json['info'] = array('version' => 1, 'author' => 'Daniel Rufus Kaldheim');
		if (file_exists($dir . DS . 'Contents.json')) {
			unlink($dir . DS . 'Contents.json');
		}
		echo "\t".((@file_put_contents($dir . DS . 'Contents.json', json_encode($json, JSON_PRETTY_PRINT))) ? 'Created new Contents.json file' : '').PHP_EOL;
		$countDirs++;
	}
}

echo "Made json for {$countDirs} directories and {$countImages} images".PHP_EOL;
exit();

/* End of file createjson.php */

?>
