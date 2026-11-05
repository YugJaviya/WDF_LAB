<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function save_record(string $kind, array $record): bool
{
    $schemas = [
        'registrations' => ['name', 'email', 'mobile', 'course', 'year', 'gender', 'password_hash', 'submitted_at'],
        'contacts' => ['name', 'email', 'subject', 'message', 'submitted_at'],
    ];
    if (!isset($schemas[$kind])) {
        return false;
    }

    $storage = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';
    if (!is_dir($storage) && !mkdir($storage, 0750, true) && !is_dir($storage)) {
        return false;
    }

    $lockPath = $storage . DIRECTORY_SEPARATOR . $kind . '.lock';
    $lock = fopen($lockPath, 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        if (is_resource($lock)) {
            fclose($lock);
        }
        return false;
    }

    $jsonPath = $storage . DIRECTORY_SEPARATOR . $kind . '.json';
    $csvPath = $storage . DIRECTORY_SEPARATOR . $kind . '.csv';
    $existing = [];
    if (is_file($jsonPath)) {
        $contents = file_get_contents($jsonPath);
        if ($contents === false) {
            flock($lock, LOCK_UN);
            fclose($lock);
            return false;
        }
        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            flock($lock, LOCK_UN);
            fclose($lock);
            return false;
        }
        if (!is_array($decoded)) {
            flock($lock, LOCK_UN);
            fclose($lock);
            return false;
        }
        $existing = $decoded;
    }

    $record = array_intersect_key($record, array_flip($schemas[$kind]));
    try {
        $updated = json_encode(array_merge($existing, [$record]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        flock($lock, LOCK_UN);
        fclose($lock);
        return false;
    }
    $temporary = tempnam($storage, $kind . '-');
    $jsonWritten = $temporary !== false && file_put_contents($temporary, $updated, LOCK_EX) !== false;
    if (!$jsonWritten || !rename($temporary, $jsonPath)) {
        if ($temporary !== false && is_file($temporary)) {
            unlink($temporary);
        }
        flock($lock, LOCK_UN);
        fclose($lock);
        return false;
    }

    $newCsv = !is_file($csvPath) || filesize($csvPath) === 0;
    $csv = fopen($csvPath, 'a');
    $csvWritten = $csv !== false && flock($csv, LOCK_EX);
    if ($csvWritten) {
        if ($newCsv) {
            $csvWritten = fputcsv($csv, $schemas[$kind]) !== false;
        }
        if ($csvWritten) {
            $row = [];
            foreach ($schemas[$kind] as $column) {
                $row[] = (string)($record[$column] ?? '');
            }
            $csvWritten = fputcsv($csv, $row) !== false && fflush($csv);
        }
        flock($csv, LOCK_UN);
        fclose($csv);
    } elseif (is_resource($csv)) {
        fclose($csv);
    }

    flock($lock, LOCK_UN);
    fclose($lock);
    return $csvWritten;
}

function page_start(string $title): void
{
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . ' - Student Portal</title>';
    echo '<link rel="stylesheet" href="style.css"><style>';
    echo 'main.server-page{max-width:760px;margin:36px auto;padding:24px;background:#fff;border-radius:14px;box-shadow:0 8px 28px rgba(20,50,70,.12)}';
    echo '.server-page h2{margin-top:0}.server-alert{padding:14px 16px;border-radius:8px;margin:16px 0}';
    echo '.server-success{background:#e8f6ed;color:#14532d;border:1px solid #a7d8b5}.server-error{background:#fff0f0;color:#8b1e1e;border:1px solid #edb5b5}';
    echo '.server-page li{margin:6px 0}.server-links{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}.server-links a{display:inline-block;padding:10px 14px;border-radius:7px;background:#176b87;color:#fff;text-decoration:none}.server-links a:focus,.server-links a:hover{background:#104e64}';
    echo '</style></head><body><main class="server-page">';
}

function page_end(): void
{
    echo '</main></body></html>';
}