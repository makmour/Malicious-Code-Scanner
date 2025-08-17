<?php
// scripts/build-phar.php
declare(strict_types=1);

$root = dirname(__DIR__);
$buildDir = $root . '/build';
@mkdir($buildDir, 0775, true);

$pharPath = $buildDir . '/malcode-scan.phar';
if (file_exists($pharPath)) {
    unlink($pharPath);
}

$phar = new Phar($pharPath, 0, 'malcode-scan.phar');
$phar->startBuffering();

// include source, rules, bin bootstrap (as stub)
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iter as $file) {
    /** @var SplFileInfo $file */
    $path = $file->getPathname();
    $rel  = str_replace($root . DIRECTORY_SEPARATOR, '', $path);

    // exclude vendor (we installed --no-dev, so include vendor for runtime deps), but keep vendor/
    // exclude dotfiles, .git, tests, build, .github
    if (preg_match('#^(?:\.git|tests|build|\.github)(/|$)#', $rel)) continue;
    if (is_dir($path)) continue;

    // we DO include vendor/ and src/ and rules/ and bin/malcode-scan
    if (!preg_match('#^(src|rules|vendor|bin/malcode-scan|bin/wp-malcode\.php|composer\.json)$#', $rel)) continue;

    $phar->addFile($path, $rel);
}

// set stub so the phar is executable: `php malcode-scan.phar ...`
$stub = <<<'PHP'
#!/usr/bin/env php
<?php
Phar::mapPhar('malcode-scan.phar');
require 'phar://malcode-scan.phar/vendor/autoload.php';

// Reuse the same CLI entrypoint bundled inside the phar
require 'phar://malcode-scan.phar/bin/malcode-scan';

__HALT_COMPILER();
PHP;

$phar->setStub($stub);
$phar->stopBuffering();

chmod($pharPath, 0755);
echo "Built PHAR at {$pharPath}\n";
