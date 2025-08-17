<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use MCS\Scanner;
use MCS\RuleMatcher;

final class ScannerTest extends TestCase
{
    public function testFindsEvalBase64(): void
    {
        $rules = new RuleMatcher([
            ['id'=>'obf.base64_eval','pattern'=>'eval\\s*\\(\\s*base64_decode\\s*\\(','severity'=>'high']
        ]);

        $tmp = sys_get_temp_dir() . '/mcs-tmp-' . uniqid();
        mkdir($tmp);
        $file = $tmp . '/bad.php';
        file_put_contents($file, '<?php ' . "eval(base64_decode('ZWNobyAnYmFkJw=='));");

        $scanner = new Scanner($rules, ['php'], [], 1024*1024);
        $findings = $scanner->scan($tmp);

        $this->assertNotEmpty($findings);
        $this->assertSame('obf.base64_eval', $findings[0]['rule_id']);

        unlink($file);
        rmdir($tmp);
    }
}
