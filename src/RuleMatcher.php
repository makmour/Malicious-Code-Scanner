<?php
declare(strict_types=1);

namespace MCS;

final class RuleMatcher
{
    /** @var array<int,array{id:string,pattern:string,severity:string,tags?:array,hint?:string}> */
    private array $rules;

    /**
     * @param array{id:string,pattern:string,severity:string,tags?:array,hint?:string}[] $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return array<int,array{rule_id:string,severity:string,tags:array,offset:int,match:string,hint?:string}>
     */
    public function match(string $content, string $path): array
    {
        $findings = [];
        foreach ($this->rules as $r) {
            $isMedia = Util::hasMediaExtension($path);
            if (($r['id'] ?? '') === 'upload.php_in_media' && !$isMedia) {
                continue;
            }
            if (@preg_match('/' . $r['pattern'] . '/mi', $content, $m, PREG_OFFSET_CAPTURE)) {
                if (!empty($m)) {
                    $findings[] = [
                        'rule_id'  => $r['id'],
                        'severity' => $r['severity'],
                        'tags'     => $r['tags'] ?? [],
                        'offset'   => $m[0][1] ?? 0,
                        'match'    => substr($content, (int)($m[0][1] ?? 0), 160),
                        'hint'     => $r['hint'] ?? null
                    ];
                }
            }
        }
        return $findings;
    }

    public static function fromJsonFile(string $file): self
    {
        if (!is_file($file)) {
            throw new \RuntimeException("Rules file not found: {$file}");
        }
        $data = json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        return new self($data['rules'] ?? []);
    }

    /** @param string[] $files */
    public static function fromMultipleJsonFiles(array $files): self
    {
        $all = [];
        foreach ($files as $file) {
            if (!is_file($file)) {
                throw new \RuntimeException("Rules file not found: {$file}");
            }
            $data = json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            foreach (($data['rules'] ?? []) as $r) {
                $all[] = $r;
            }
        }
        return new self($all);
    }
}
