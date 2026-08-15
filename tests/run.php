<?php
require __DIR__ . '/../src/Security/SecretRedactor.php';
require __DIR__ . '/../src/Security/SqlValidator.php';
use AI_Agent\Security\SecretRedactor;
use AI_Agent\Security\SqlValidator;
function check($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); } echo "PASS: {$message}\n"; }
check(strpos(SecretRedactor::redact('api_key=sk-abcdefghijklmnopqrstuvwxyz'), '[REDACTED]') !== false, 'redacts API keys');
$sql = new SqlValidator(); $sql->read('SELECT * FROM wp_posts LIMIT 1'); check(true, 'allows SELECT');
try { $sql->read('DELETE FROM wp_posts'); check(false, 'rejects mutation in read mode'); } catch (RuntimeException $e) { check(true, 'rejects mutation in read mode'); }
try { $sql->mutation('DROP DATABASE wordpress'); check(false, 'rejects DROP DATABASE'); } catch (RuntimeException $e) { check(true, 'rejects DROP DATABASE'); }
try { $sql->read('SELECT 1; SELECT 2'); check(false, 'rejects multiple statements'); } catch (RuntimeException $e) { check(true, 'rejects multiple statements'); }
echo "All tests passed.\n";
