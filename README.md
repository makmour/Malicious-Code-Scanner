# Malicious Code Scanner (starter)

A lightweight PHP malware/backdoor scanner with rules, CLI, JSON/SARIF reports, email/webhook notifiers, and WordPress mode.

## Install
```bash
composer install
chmod +x bin/malcode-scan
```

## Usage
```bash
bin/malcode-scan \
  --path=/var/www/site \
  --ext=php,phtml \
  --exclude=vendor,node_modules,wp-content/uploads \
  --size-limit=10485760 \
  --report=json \
  --out=/tmp/mcs-report.json \
  --email=alerts@example.com --smtp-host=smtp.example.com --smtp-user=alerts@example.com --smtp-pass=secret --smtp-port=587 \
  --webhook=https://hooks.slack.com/services/XXX/YYY/ZZZ \
  --rules=rules/core.json \
  --progress --strict
```

### WordPress mode
```bash
bin/malcode-scan --path=/var/www/html --wp-mode --report=json --out=/tmp/report.json --quarantine=/tmp/quarantine
```

### WP-CLI wrapper
```bash
wp --require=bin/wp-malcode.php malcode scan --report=json --out=/tmp/report.json
```

### Exit codes
- 0: clean
- 1: suspicious (when `--strict` is used)
- 2: reserved
