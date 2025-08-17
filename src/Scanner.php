<?php
declare(strict_types=1);

namespace MCS;

final class Scanner
{
    private RuleMatcher $matcher;
    /** @var string[] */
    private array $extensions;
    /** @var string[] */
    private array $excludes;
    private int $sizeLimit;
    private bool $progress;
    private ?string $quarantineDir = null;

    /**
     * @param string[] $extensions
     * @param string[] $excludes
     */
    public function __construct(RuleMatcher $matcher, array $extensions, array $excludes, int $sizeLimit, bool $progress = false, ?string $quarantineDir = null)
    {
        $this->matcher   = $matcher;
        $this->extensions = $extensions;
        $this->excludes   = $excludes;
        $this->sizeLimit  = $sizeLimit;
        $this->progress   = $progress;
        $this->quarantineDir = $quarantineDir ? rtrim($quarantineDir, DIRECTORY_SEPARATOR) : null;
    }

    public static function fromDefaults(string $rulesFile, array $extensions, array $excludes, int $sizeLimit, bool $progress = false, ?string $quarantineDir = null): self
    {
        return new self(RuleMatcher::fromJsonFile($rulesFile), $extensions, $excludes, $sizeLimit, $progress, $quarantineDir);
    }

    /** @param string[] $rulesFiles */
    public static function fromDefaultsMultiple(array $rulesFiles, array $extensions, array $excludes, int $sizeLimit, bool $progress = false, ?string $quarantineDir = null): self
    {
        return new self(RuleMatcher::fromMultipleJsonFiles($rulesFiles), $extensions, $excludes, $sizeLimit, $progress, $quarantineDir);
    }

    /**
     * @return array<int,array{path:string,rule_id:string,severity:string,tags:array,offset:int,match:string,hint?:string,size:int,mtime:int,hash:string,entropy:array,quarantined_to?:string}>
     */
    public function scan(string $root): array
    {
        $root = rtrim($root, DIRECTORY_SEPARATOR);
        if (!is_dir($root)) {
            throw new \RuntimeException("Not a directory: {$root}");
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        $findings = [];
        foreach ($it as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            $path = $fileInfo->getPathname();

            if (is_link($path)) {
                continue;
            }
            if (Util::isExcluded($path, $this->excludes)) {
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, $this->extensions, true) && !Util::hasMediaExtension($path)) {
                continue;
            }

            $size = $fileInfo->getSize();
            if ($size === false || $size > $this->sizeLimit) {
                continue;
            }

            $content = @file_get_contents($path);
            if ($content === false) {
                continue;
            }

            $entropyHits = Entropy::findSuspiciousStrings($content);
            $matches = $this->matcher->match($content, $path);
            $candidateFindings = [];

            foreach ($matches as $m) {
                $candidateFindings[] = [
                    'path'    => $path,
                    'rule_id' => $m['rule_id'],
                    'severity'=> $m['severity'],
                    'tags'    => $m['tags'],
                    'offset'  => $m['offset'],
                    'match'   => $m['match'],
                    'hint'    => $m['hint'] ?? null,
                    'size'    => (int)$size,
                    'mtime'   => (int)($fileInfo->getMTime() ?: time()),
                    'hash'    => hash('sha1', $content),
                    'entropy' => array_map(fn($e) => ['entropy'=>$e['entropy'], 'length'=>$e['length'], 'sample'=>$e['value']], array_slice($entropyHits, 0, 3))
                ];
            }

            if (!empty($candidateFindings) && $this->quarantineDir) {
                @mkdir($this->quarantineDir, 0775, true);
                $qpath = $this->quarantineDir . DIRECTORY_SEPARATOR . basename($path) . '.' . substr(sha1($path . microtime(true)), 0, 8);
                if (@copy($path, $qpath)) {
                    @file_put_contents($path, "<?php /** quarantined by MCS at ".gmdate('c')." */\n");
                    foreach ($candidateFindings as &$cf) {
                        $cf['quarantined_to'] = $qpath;
                    }
                }
            }

            $findings = array_merge($findings, $candidateFindings);

            if ($this->progress) {
                fwrite(STDERR, ".");
            }
        }
        if ($this->progress) {
            fwrite(STDERR, "\n");
        }
        return $findings;
    }
}
