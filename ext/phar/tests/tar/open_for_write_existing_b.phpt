--TEST--
Phar: fopen a .phar for writing (existing file) tar-based
--SKIPIF--
<?php if (!extension_loaded("phar")) die("skip"); ?>
--INI--
phar.readonly=0
phar.require_hash=0
--FILE--
<?php

$fname = dirname(__FILE__) . '/' . basename(__FILE__, '.php') . '.phar.tar';
$alias = 'phar://' . $fname;

$phar = new Phar($fname);
$phar->setStub("<?php __HALT_COMPILER(); ?>");

$files = array();

$files['a.php'] = '<?php echo "This is a\n"; ?>';
$files['b.php'] = '<?php echo "This is b\n"; ?>';
$files['b/c.php'] = '<?php echo "This is b/c\n"; ?>';

foreach ($files as $n => $file) {
	$phar[$n] = $file;
}

$phar->stopBuffering();
ini_set('phar.readonly', 1);

function err_handler($errno, $errstr, $errfile, $errline) {
	echo "Catchable fatal error: $errstr in $errfile on line $errline\n";
}

set_error_handler("err_handler", E_RECOVERABLE_ERROR);

$fp = fopen($alias . '/b/c.php', 'wb');
fwrite($fp, (binary)'extra');
fclose($fp);

include $alias . '/b/c.php';

?>

===DONE===
--CLEAN--
<?php unlink(dirname(__FILE__) . '/' . basename(__FILE__, '.clean.php') . '.phar.tar'); ?>
--EXPECTF--
Warning: fopen(phar://%sopen_for_write_existing_b.phar.tar/b/c.php): failed to open stream: phar error: write operations disabled by INI setting in %sopen_for_write_existing_b.php on line %d

Warning: fwrite() expects parameter 1 to be resource, boolean given in %sopen_for_write_existing_b.php on line %d

Warning: fclose(): supplied argument is not a valid stream resource in %sopen_for_write_existing_b.php on line %d
This is b/c

===DONE===
