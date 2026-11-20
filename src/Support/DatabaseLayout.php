<?php

namespace Xgenious\Installer\Support;

/**
 * Resolves the PostgreSQL tenant layout selected in the installer and hands
 * it to the product's install callback.
 *
 * The installer package stays product-agnostic: it only knows the layout key
 * (for example "per_tenant" or "pooled"). Writing app-specific .env values,
 * creating extra databases, and running migrations is the callback's job.
 */
class DatabaseLayout
{
    /**
     * @return array<string, string> layout key => human label
     */
    public static function options(): array
    {
        return (array) config('installer.database_layouts', []);
    }

    public static function resolve(?string $layout): string
    {
        $options = self::options();

        if ($options === []) {
            return 'per_tenant';
        }

        $layout = (string) $layout;

        if (array_key_exists($layout, $options)) {
            return $layout;
        }

        $default = (string) config('installer.database_layout', '');

        if (array_key_exists($default, $options)) {
            return $default;
        }

        return (string) array_key_first($options);
    }

    /**
     * Invoke the configured product callback.
     *
     * Contract: public function apply(string $layout, array $db): array
     * returning ['ok' => bool, 'message' => string].
     *
     * @param  array{driver?: string, host?: string, port?: int|string, name?: string, username?: string, password?: string}  $db
     * @return array{ok: bool, message: string}
     */
    public static function apply(?string $layout, array $db): array
    {
        if (self::options() === []) {
            return ['ok' => true, 'message' => ''];
        }

        $layout = self::resolve($layout);
        $callback = config('installer.install_callback');

        if (! is_string($callback) || ! class_exists($callback)) {
            return ['ok' => true, 'message' => ''];
        }

        $handler = app($callback);

        if (! is_object($handler) || ! method_exists($handler, 'apply')) {
            return ['ok' => true, 'message' => ''];
        }

        $result = $handler->apply($layout, $db);

        if (! is_array($result)) {
            return ['ok' => true, 'message' => ''];
        }

        return [
            'ok' => (bool) ($result['ok'] ?? true),
            'message' => (string) ($result['message'] ?? ''),
        ];
    }
}
