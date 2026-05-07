<?php

class InstallController extends Controller {

    public function index(): void {
        $step = (int)$this->request->get('step', 1);
        $checks = $this->systemChecks();
        $this->view('install.wizard', compact('step','checks'), null);
    }

    public function step(int $step): void {
        switch ($step) {
            case 2: $this->testDatabase(); break;
            case 3: $this->runMigrations(); break;
            case 4: $this->createAdmin(); break;
            default: $this->json(['error' => 'Invalid step'], 400);
        }
    }

    private function systemChecks(): array {
        return [
            // Required
            'PHP >= 8.0'         => ['ok' => version_compare(PHP_VERSION, '8.0', '>='), 'required' => true],
            'PDO MySQL'          => ['ok' => extension_loaded('pdo_mysql'),             'required' => true],
            'OpenSSL'            => ['ok' => extension_loaded('openssl'),               'required' => true],
            'Mbstring'           => ['ok' => extension_loaded('mbstring'),              'required' => true],
            'JSON'               => ['ok' => extension_loaded('json'),                  'required' => true],
            'Curl'               => ['ok' => extension_loaded('curl'),                  'required' => true],
            'Storage writable'   => ['ok' => is_writable(STORAGE_PATH),                 'required' => true],
            '.env file'          => ['ok' => file_exists(ROOT_PATH . '/.env'),          'required' => true],
            // Optional (recommended)
            'GD (thumbnails)'    => ['ok' => extension_loaded('gd'),                    'required' => false],
            'Fileinfo (uploads)' => ['ok' => extension_loaded('fileinfo'),              'required' => false],
            'Intl (i18n)'        => ['ok' => extension_loaded('intl'),                  'required' => false],
        ];
    }

    private function testDatabase(): void {
        Database::reset();
        $cfg = require CONFIG_PATH . '/database.php';
        try {
            // First try connecting WITHOUT database name
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};charset={$cfg['charset']}";
            $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            // Try to USE the configured DB; if it doesn't exist, create it
            $dbName = $cfg['database'];
            $exists = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($dbName))->fetch();
            if (!$exists) {
                $pdo->exec("CREATE DATABASE `" . str_replace('`', '', $dbName) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->json(['success' => true, 'message' => "Connected to MySQL — created database `$dbName`."]);
            } else {
                $this->json(['success' => true, 'message' => "Connected to MySQL — database `$dbName` exists."]);
            }
        } catch (Throwable $e) {
            $this->json([
                'error' => "Could not connect to MySQL.\n\nDetails: " . $e->getMessage()
                  . "\n\nConfig used: host={$cfg['host']}, port={$cfg['port']}, db={$cfg['database']}, user={$cfg['username']}",
            ], 500);
        }
    }

    private function runMigrations(): void {
        Database::reset();
        try {
            $migrations = ROOT_PATH . '/database/migrations.sql';
            if (!file_exists($migrations)) throw new RuntimeException('migrations.sql not found');
            $sql = file_get_contents($migrations);
            $statements = $this->splitSqlStatements($sql);
            $pdo = Database::getInstance();
            $count = 0;
            $skipped = 0;
            // MySQL error codes that mean "object already exists" — safe to skip
            $alreadyExists = [1050, 1060, 1061, 1826, 1022];
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '' || str_starts_with($stmt, '--')) continue;
                try {
                    $pdo->exec($stmt);
                    $count++;
                } catch (PDOException $e) {
                    $code = (int)($e->errorInfo[1] ?? 0);
                    if (in_array($code, $alreadyExists, true)) {
                        $skipped++;
                        continue;
                    }
                    throw $e;
                }
            }
            // Seed (ignore duplicate-key errors since seeder may already have data)
            $seeders = ROOT_PATH . '/database/seeders.sql';
            if (file_exists($seeders)) {
                foreach ($this->splitSqlStatements(file_get_contents($seeders)) as $stmt) {
                    $stmt = trim($stmt);
                    if ($stmt === '' || str_starts_with($stmt, '--')) continue;
                    try { $pdo->exec($stmt); } catch (Throwable) {}
                }
            }
            $msg = "Migrated $count statements. Database is ready.";
            if ($skipped > 0) $msg .= " ($skipped already existed, skipped.)";
            $this->json(['success' => true, 'message' => $msg]);
        } catch (Throwable $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function splitSqlStatements(string $sql): array {
        // Strip comment lines starting with -- or # but keep quoted content intact (basic split)
        $sql = preg_replace('/^\s*(--|#).*$/m', '', $sql);
        return preg_split('/;\s*[\r\n]+/m', $sql);
    }

    private function createAdmin(): void {
        $name     = $this->request->post('name');
        $email    = $this->request->post('email');
        $password = $this->request->post('password');
        if (!$name || !$email || !$password) {
            $this->json(['error' => 'All fields required'], 400);
            return;
        }
        try {
            Database::query("UPDATE users SET email = ?, password = ?, name = ? WHERE role = 'super_admin' LIMIT 1",
                [$email, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $name]);
            file_put_contents(ROOT_PATH . '/storage/installed.lock', date('Y-m-d H:i:s'));
            $this->json(['success' => true, 'message' => 'Installation complete!']);
        } catch (Throwable $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
