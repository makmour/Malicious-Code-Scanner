<?php
declare(strict_types=1);

namespace MCS;

final class Reporter
{
    private string $format; // json|sarif

    public function __construct(string $format = 'json')
    {
        $this->format = $format;
    }

    /**
     * @param array<int,array<string,mixed>> $findings
     * @param array<string,mixed> $meta
     */
    public function render(array $findings, array $meta): string
    {
        if ($this->format === 'json') {
            return json_encode([
                'tool'     => 'malicious-code-scanner',
                'version'  => '0.1.0',
                'meta'     => $meta,
                'findings' => $findings
            ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        }

        if ($this->format === 'sarif') {
            return json_encode([
                'version' => '2.1.0',
                '$schema' => 'https://json.schemastore.org/sarif-2.1.0.json',
                'runs' => [[
                    'tool' => ['driver' => ['name' => 'malicious-code-scanner', 'version' => '0.1.0']],
                    'results' => array_map(function($f) {
                        return [
                            'ruleId' => $f['rule_id'],
                            'level'  => $f['severity'],
                            'message'=> ['text' => substr($f['match'], 0, 200)],
                            'locations' => [[ 'physicalLocation' => ['artifactLocation' => ['uri' => $f['path']]] ]]
                        ];
                    }, $findings)
                ]]
            ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        }

        throw new \InvalidArgumentException("Unknown report format: {$this->format}");
    }
}
