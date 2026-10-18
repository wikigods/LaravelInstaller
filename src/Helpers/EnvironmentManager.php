<?php

namespace Wikigods\LaravelInstaller\Helpers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnvironmentManager
{
    /**
     * Environment keys managed directly by the wizard, including legacy aliases.
     *
     * @var array
     */
    private $managedEnvironmentKeys = [
        'APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'LOG_LEVEL', 'APP_URL',
        'APP_LOCALE', 'APP_FALLBACK_LOCALE', 'APP_FAKER_LOCALE',
        'APP_MAINTENANCE_DRIVER', 'BCRYPT_ROUNDS', 'LOG_CHANNEL', 'LOG_STACK',
        'LOG_DEPRECATIONS_CHANNEL', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT',
        'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'BROADCAST_CONNECTION',
        'BROADCAST_DRIVER', 'CACHE_STORE', 'CACHE_DRIVER', 'SESSION_DRIVER',
        'SESSION_LIFETIME', 'SESSION_ENCRYPT', 'SESSION_PATH', 'SESSION_DOMAIN',
        'QUEUE_CONNECTION', 'QUEUE_DRIVER', 'FILESYSTEM_DISK', 'MEMCACHED_HOST',
        'REDIS_CLIENT', 'REDIS_HOST', 'REDIS_PASSWORD', 'REDIS_PORT', 'MAIL_MAILER',
        'MAIL_DRIVER', 'MAIL_SCHEME', 'MAIL_ENCRYPTION', 'MAIL_HOST', 'MAIL_PORT',
        'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME',
        'PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_HOST',
        'PUSHER_PORT', 'PUSHER_SCHEME', 'PUSHER_APP_CLUSTER',
    ];

    /**
     * @var string
     */
    private $envPath;

    /**
     * @var string
     */
    private $envExamplePath;

    /**
     * Set the .env and .env.example paths.
     */
    public function __construct()
    {
        $this->envPath = base_path('.env');
        $this->envExamplePath = base_path('.env.example');
    }

    /**
     * Get the content of the .env file.
     *
     * @return string
     */
    public function getEnvContent()
    {
        if (! file_exists($this->envPath)) {
            if (file_exists($this->envExamplePath)) {
                copy($this->envExamplePath, $this->envPath);
            } else {
                touch($this->envPath);
            }
        }

        return file_get_contents($this->envPath);
    }

    /**
     * Get the the .env file path.
     *
     * @return string
     */
    public function getEnvPath()
    {
        return $this->envPath;
    }

    /**
     * Get the the .env.example file path.
     *
     * @return string
     */
    public function getEnvExamplePath()
    {
        return $this->envExamplePath;
    }

    /**
     * Get valid custom environment variable definitions from the installer config.
     *
     * @return array
     */
    public function getCustomVariables()
    {
        $definitions = config('installer.environment.custom_variables', []);
        $variables = [];

        foreach ($definitions as $key => $definition) {
            if (! is_string($key)
                || ! preg_match('/^[A-Z_][A-Z0-9_]*$/', $key)
                || in_array($key, $this->managedEnvironmentKeys, true)
                || ! is_array($definition)) {
                continue;
            }

            $type = $definition['type'] ?? 'text';
            if (! in_array($type, ['text', 'password', 'number', 'boolean'], true)) {
                $definition['type'] = 'text';
            }

            $variables[$key] = $definition;
        }

        return $variables;
    }

    /**
     * Read configured custom values from the current .env file.
     * Password values are returned to the form controller but should not be rendered.
     *
     * @return array
     */
    public function getCustomEnvironmentValues()
    {
        $keys = array_keys($this->getCustomVariables());
        if (! $keys || ! file_exists($this->envPath)) {
            return [];
        }

        $values = [];
        foreach (file($this->envPath, FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^\s*(?:export\s+)?([A-Z_][A-Z0-9_]*)\s*=\s*(.*)\s*$/', $line, $matches)
                && in_array($matches[1], $keys, true)) {
                $values[$matches[1]] = $this->parseEnvValue($matches[2]);
            }
        }

        return $values;
    }

    /**
     * Save the edited content to the .env file.
     *
     * @param  Request  $input
     * @return string
     */
    public function saveFileClassic(Request $input)
    {
        $message = trans('installer_messages.environment.success');

        try {
            file_put_contents($this->envPath, $input->get('envConfig'));
        } catch (Exception $e) {
            $message = trans('installer_messages.environment.errors');
        }

        return $message;
    }

    /**
     * Save the form content to the .env file.
     *
     * @param  Request  $request
     * @return string
     */
    public function saveFileWizard(Request $request)
    {
        $results = trans('installer_messages.environment.success');

        $frameworkVersion = app()->version();
        $broadcastKey = version_compare($frameworkVersion, '11.0', '>=') ? 'BROADCAST_CONNECTION' : 'BROADCAST_DRIVER';
        $cacheKey = version_compare($frameworkVersion, '11.0', '>=') ? 'CACHE_STORE' : 'CACHE_DRIVER';
        $queueKey = version_compare($frameworkVersion, '5.7', '>=') ? 'QUEUE_CONNECTION' : 'QUEUE_DRIVER';
        $mailerKey = version_compare($frameworkVersion, '9.0', '>=') ? 'MAIL_MAILER' : 'MAIL_DRIVER';
        $mailSchemeKey = version_compare($frameworkVersion, '11.0', '>=') ? 'MAIL_SCHEME' : 'MAIL_ENCRYPTION';
        $mailScheme = $request->mail_scheme;

        if (version_compare($frameworkVersion, '11.0', '<')) {
            $mailScheme = $mailScheme === 'smtps' ? 'ssl' : ($mailScheme === 'smtp' ? 'tls' : null);
        }

        $environment = $request->environment === 'other'
            ? $request->environment_custom
            : $request->environment;

        $envFileData = implode("\n", [
            $this->envLine('APP_NAME', $request->app_name),
            $this->envLine('APP_ENV', $environment),
            'APP_KEY=base64:'.base64_encode(Str::random(32)),
            $this->envLine('APP_DEBUG', $request->app_debug),
            $this->envLine('LOG_LEVEL', $request->log_level),
            $this->envLine('APP_URL', $request->app_url),
            $this->envLine('APP_LOCALE', config('app.locale', 'en')),
            $this->envLine('APP_FALLBACK_LOCALE', config('app.fallback_locale', 'en')),
            $this->envLine('APP_FAKER_LOCALE', config('app.faker_locale', 'en_US')),
            'APP_MAINTENANCE_DRIVER=file',
            'BCRYPT_ROUNDS=12',
            'LOG_CHANNEL=stack',
            'LOG_STACK=single',
            'LOG_DEPRECATIONS_CHANNEL=null',
            '',
            $this->envLine('DB_CONNECTION', $request->database_connection),
            $this->envLine('DB_HOST', $request->database_hostname),
            $this->envLine('DB_PORT', $request->database_port),
            $this->envLine('DB_DATABASE', $request->database_name),
            $this->envLine('DB_USERNAME', $request->database_username),
            $this->envLine('DB_PASSWORD', $request->database_password),
            '',
            $this->envLine($broadcastKey, $request->broadcast_connection),
            $this->envLine($cacheKey, $request->cache_store),
            $this->envLine('SESSION_DRIVER', $request->session_driver),
            'SESSION_LIFETIME=120',
            'SESSION_ENCRYPT=false',
            'SESSION_PATH=/',
            'SESSION_DOMAIN=null',
            $this->envLine($queueKey, $request->queue_connection),
            'FILESYSTEM_DISK=local',
            '',
            'MEMCACHED_HOST=127.0.0.1',
            $this->envLine('REDIS_CLIENT', 'phpredis'),
            $this->envLine('REDIS_HOST', $request->redis_hostname),
            $this->envLine('REDIS_PASSWORD', $request->redis_password),
            $this->envLine('REDIS_PORT', $request->redis_port),
            '',
            $this->envLine($mailerKey, $request->mail_mailer),
            $this->envLine($mailSchemeKey, $mailScheme),
            $this->envLine('MAIL_HOST', $request->mail_host),
            $this->envLine('MAIL_PORT', $request->mail_port),
            $this->envLine('MAIL_USERNAME', $request->mail_username),
            $this->envLine('MAIL_PASSWORD', $request->mail_password),
            'MAIL_FROM_ADDRESS="hello@example.com"',
            'MAIL_FROM_NAME="${APP_NAME}"',
            '',
            $this->envLine('PUSHER_APP_ID', $request->pusher_app_id),
            $this->envLine('PUSHER_APP_KEY', $request->pusher_app_key),
            $this->envLine('PUSHER_APP_SECRET', $request->pusher_app_secret),
            'PUSHER_HOST=',
            'PUSHER_PORT=443',
            'PUSHER_SCHEME=https',
            'PUSHER_APP_CLUSTER=mt1',
        ]);

        $customValues = $request->input('custom_env', []);
        $existingCustomValues = $this->getCustomEnvironmentValues();
        $customLines = [];
        $customKeys = [];

        foreach ($this->getCustomVariables() as $key => $definition) {
            $customKeys[] = $key;
            $value = array_key_exists($key, $customValues)
                ? $customValues[$key]
                : ($definition['default'] ?? null);

            if (($definition['type'] ?? 'text') === 'password'
                && ($value === null || $value === '')
                && array_key_exists($key, $existingCustomValues)) {
                $value = $existingCustomValues[$key];
            }

            $customLines[] = $this->envLine($key, $value);
        }

        $preservedLines = $this->getUnmanagedEnvLines($customKeys);
        if ($preservedLines || $customLines) {
            $envFileData .= "\n\n".implode("\n", array_merge($preservedLines, $customLines));
        }

        try {
            file_put_contents($this->envPath, $envFileData);
        } catch (Exception $e) {
            $results = trans('installer_messages.environment.errors');
        }

        return $results;
    }

    /**
     * Format a value as a safely quoted dotenv assignment.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return string
     */
    private function envLine($key, $value)
    {
        if ($value === null || $value === 'null') {
            return $key.'=null';
        }

        $value = str_replace(
            ['\\', '"', '$', "\r", "\n"],
            ['\\\\', '\\"', '\\$', '\\r', '\\n'],
            (string) $value
        );

        return $key.'="'.$value.'"';
    }

    /**
     * Preserve existing environment assignments that the wizard does not manage.
     *
     * @param  array  $customKeys
     * @return array
     */
    private function getUnmanagedEnvLines(array $customKeys)
    {
        if (! file_exists($this->envPath)) {
            return [];
        }

        $managedKeys = array_merge($this->managedEnvironmentKeys, $customKeys);
        $preservedLines = [];

        foreach (file($this->envPath, FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^\s*(?:export\s+)?([A-Z_][A-Z0-9_]*)\s*=/', $line, $matches)
                && ! in_array($matches[1], $managedKeys, true)) {
                $preservedLines[] = $line;
            }
        }

        return $preservedLines;
    }

    /**
     * Decode the simple quoted values written by this wizard.
     *
     * @param  string  $value
     * @return string|null
     */
    private function parseEnvValue($value)
    {
        $value = trim($value);
        if ($value === 'null') {
            return null;
        }

        if (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
            return str_replace(
                ['\\\\', '\\"', '\\$', '\\r', '\\n'],
                ['\\', '"', '$', "\r", "\n"],
                $value
            );
        }

        if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
