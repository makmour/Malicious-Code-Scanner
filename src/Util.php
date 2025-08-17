<?php
declare(strict_types=1);

namespace MCS;

final class Util
{
    public static function isExcluded(string $path, array $excludes): bool
    {
        foreach ($excludes as $ex) {
            if ($ex === '') continue;
            if (str_contains($path, DIRECTORY_SEPARATOR . $ex . DIRECTORY_SEPARATOR) ||
                str_ends_with($path, DIRECTORY_SEPARATOR . $ex) ||
                basename($path) === $ex) {
                return true;
            }
        }
        return false;
    }

    public static function hasMediaExtension(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg','jpeg','png','gif','webp','ico','mp4','mov','svg'], true);
    }
}
