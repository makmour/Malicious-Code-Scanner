<?php
declare(strict_types=1);

namespace MCS\WPCLI;

use MCS\Scanner;
use MCS\Reporter;
use MCS\RuleMatcher;

final class Command
{
    /**
     * ## OPTIONS
     *
     * [--report=<json|sarif>]
     * : Output format.
     *
     * [--out=<path>]
     * : Write report to file.
     *
     * [--size-limit=<bytes>]
     * : Skip files bigger than this.
     *
     * [--quarantine=<dir>]
     * : Quarantine folder (optional).
     *
     * ## EXAMPLES
     *     wp malcode scan --report=json --out=/tmp/report.json --quarantine=/tmp/quarantine
     *
     * @when after_wp_load
     */
    public function scan(array $args, array $assoc_args): void
    {
        $root = ABSPATH;
        $reportFmt = $assoc_args['report'] ?? 'json';
        $out = $assoc_args['out'] ?? null;
        $sizeLimit = isset($assoc_args['size-limit']) ? (int)$assoc_args['size-limit'] : 10*1024*1024;
        $quarantine = $assoc_args['quarantine'] ?? null;

        $rules = [
            dirname(__DIR__, 2) . '/rules/core.json',
            dirname(__DIR__, 2) . '/rules/wordpress.json'
        ];

        $scanner = new Scanner(
            RuleMatcher::fromMultipleJsonFiles($rules),
            ['php','phtml','php5','inc'],
            ['wp-content/cache','wp-content/languages','wp-content/upgrade','node_modules','vendor','.git'],
            $sizeLimit,
            true,
            $quarantine
        );

        $findings = $scanner->scan($root);
        $report = (new Reporter($reportFmt))->render($findings, [
            'scanned_path' => $root,
            'timestamp' => gmdate('c'),
            'wp' => true
        ]);

        if ($out) {
            file_put_contents($out, $report . PHP_EOL);
            \WP_CLI::success("Report written to {$out}");
        } else {
            \WP_CLI::line($report);
        }

        if (!empty($findings)) {
            \WP_CLI::halt(1);
        }
    }
}
