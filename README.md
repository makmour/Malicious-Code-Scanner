# Malicious Code Scanner

![CI](https://github.com/makmour/Malicious-Code-Scanner/actions/workflows/ci.yml/badge.svg?branch=main)
![Release](https://github.com/makmour/Malicious-Code-Scanner/actions/workflows/release.yml/badge.svg?branch=main)

A lightweight PHP malware & backdoor scanner.  
Supports CLI usage, JSON/SARIF reports, entropy heuristics, quarantine mode, WordPress-specific rules, and pluggable notifiers (email, webhooks).

---

## Features
- **CLI tool** (`bin/malcode-scan`) with excludes, size limits, and strict exit codes
- **Rule-based detection** (core + WordPress signatures)
- **Entropy heuristics** for obfuscated strings (base64, gzdeflate, chr chains)
- **Quarantine mode** – isolate flagged files safely
- **Notifiers** – send results via SMTP email or webhook
- **WordPress mode** with WP-CLI command integration
- **JSON/SARIF reports** for CI integration
- **GitHub Actions CI** + PHAR release workflow

---

## Installation

### Composer (recommended for devs)
```bash
git clone https://github.com/makmour/Malicious-Code-Scanner.git
cd Malicious-Code-Scanner
composer install
chmod +x bin/malcode-scan
```

### PHAR (standalone binary)
1. Download the latest [`malcode-scan.phar`](https://github.com/makmour/Malicious-Code-Scanner/releases) from Releases.
2. Run it directly:
   ```bash
   php malcode-scan.phar --path=/var/www/html --report=json --progress
   ```
3. (Optional) Install globally:
   ```bash
   chmod +x malcode-scan.phar
   sudo mv malcode-scan.phar /usr/local/bin/malcode-scan
   malcode-scan --help
   ```

---

## Usage

### Basic scan
```bash
bin/malcode-scan --path=/var/www/html --report=json --progress
```

### With email + webhook alerts
```bash
bin/malcode-scan \
  --path=/var/www/html \
  --report=json \
  --email=alerts@example.com \
  --smtp-host=smtp.example.com --smtp-user=alerts@example.com --smtp-pass=secret --smtp-port=587 \
  --webhook=https://hooks.slack.com/services/XXX/YYY/ZZZ
```

### WordPress mode
```bash
bin/malcode-scan --path=/var/www/html --wp-mode --report=json --out=/tmp/report.json --quarantine=/tmp/quarantine
```

### WP-CLI command
From a WordPress install:
```bash
wp --require=bin/wp-malcode.php malcode scan --report=json --out=/tmp/report.json
```

---

## Options

| Flag            | Description |
|-----------------|-------------|
| `--path`        | Root directory to scan (default: current working dir) |
| `--ext`         | File extensions to include (comma-separated, default: php,php5,phtml,inc) |
| `--exclude`     | Paths to exclude (comma-separated) |
| `--size-limit`  | Max file size in bytes (default: 10 MB) |
| `--report`      | Output format: `json` or `sarif` |
| `--out`         | Write report to file |
| `--progress`    | Show progress dots while scanning |
| `--strict`      | Exit code 1 if suspicious files found (0 otherwise) |
| `--wp-mode`     | Enable WordPress-specific excludes + rules |
| `--quarantine`  | Directory to copy suspicious files into and replace with stubs |
| `--email`       | Send results to an email (requires `--smtp-host` etc.) |
| `--webhook`     | Send results as JSON POST to a webhook URL |

---

## Exit codes
- `0` → Clean (no findings)  
- `1` → Findings detected (when `--strict` is enabled)  
- `2` → Reserved for errors

---

## Development
Run tests:
```bash
composer test
```

Static analysis:
```bash
composer stan
```

Auto-fix coding style:
```bash
composer cs
```

---

## Roadmap
- `--rules-extra=/path.json` to load custom signatures
- `--report=pretty` for human-friendly console output
- Prebuilt Docker image for CI/CD pipelines

---

## License
MIT
