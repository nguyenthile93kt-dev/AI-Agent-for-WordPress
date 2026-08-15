<?php
namespace AI_Agent\Security;
final class SecretRedactor
{
    public static function redact(string $content): string
    {
        $patterns = array(
            '/\b(?:sk|sk-proj|sk-ant|AIza)[-_A-Za-z0-9]{12,}\b/i' => '[REDACTED_API_KEY]',
            '/((?:password|passwd|secret|api[_-]?key|auth[_-]?key)\s*[=:]\s*[\'\"]?)[^\s\'\"]+/i' => '$1[REDACTED]',
            '/-----BEGIN (?:RSA |OPENSSH )?PRIVATE KEY-----.*?-----END (?:RSA |OPENSSH )?PRIVATE KEY-----/s' => '[REDACTED_PRIVATE_KEY]',
        );
        return preg_replace(array_keys($patterns), array_values($patterns), $content);
    }
}
