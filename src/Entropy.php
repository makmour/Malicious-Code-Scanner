<?php
declare(strict_types=1);

namespace MCS;

final class Entropy
{
    public static function shannon(string $s): float
    {
        if ($s === '') return 0.0;
        $len = strlen($s);
        $freq = [];
        for ($i = 0; $i < $len; $i++) {
            $c = $s[$i];
            $freq[$c] = ($freq[$c] ?? 0) + 1;
        }
        $h = 0.0;
        foreach ($freq as $count) {
            $p = $count / $len;
            $h -= $p * log($p, 2);
        }
        return $h;
    }

    public static function findSuspiciousStrings(string $content, int $minLen = 64, float $threshold = 4.5): array
    {
        preg_match_all('/[A-Za-z0-9\/\+\=\_\-]{' . $minLen . ',}/', $content, $m);
        $hits = [];
        foreach ($m[0] as $str) {
            $h = self::shannon($str);
            if ($h >= $threshold) {
                $hits[] = ['value' => substr($str, 0, 200), 'entropy' => $h, 'length' => strlen($str)];
            }
        }
        return $hits;
    }
}
