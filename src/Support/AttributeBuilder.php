<?php

namespace Vizor\Laravel\Support;

use Illuminate\View\Component;

/**
 * Converts component props to HTML attribute strings for Vizor custom elements.
 *
 * Handles camelCase -> kebab-case, boolean attrs (true = present, false = omitted),
 * null omission, and special character escaping.
 */
final class AttributeBuilder
{
    /**
     * Props-to-attribute name mapping (camelCase PHP -> kebab-case HTML).
     *
     * @var array<string, string>
     */
    private const PROP_MAP = [
        'src' => 'src',
        'format' => 'format',
        'title' => 'title',
        'poster' => 'poster',
        'author' => 'author',
        'muted' => 'muted',
        'loop' => 'loop',
        'controls' => 'controls',
        'autoplay' => 'autoplay',
        'apiKey' => 'api-key',
        'licenseKey' => 'license-key',
        'primaryColor' => 'primary-color',
        'contentId' => 'content-id',
        'apiEndpoint' => 'api-endpoint',
        'controlsBehavior' => 'controls-behavior',
        'hideControls' => 'hide-controls',
        'preload' => 'preload',
        'startProbeId' => 'start-probe-id',
        'probeId' => 'probe-id',
        'loopPlaylist' => 'loop-playlist',
        'panel' => 'panel',
        'lat' => 'lat',
        'lon' => 'lon',
        'timeStart' => 'time-start',
        'timeEnd' => 'time-end',
        'sortOrder' => 'sort-order',
        'icon' => 'icon',
        'to' => 'to',
    ];

    /**
     * Build an HTML attribute string from a component's public properties.
     *
     * @param  array<string, mixed>  $props
     */
    public static function build(array $props): string
    {
        $attrs = [];

        foreach ($props as $key => $value) {
            $attrName = self::PROP_MAP[$key] ?? self::camelToKebab($key);

            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                if ($value) {
                    $attrs[] = $attrName;
                }

                continue;
            }

            if ($value instanceof FormatEnum) {
                $attrs[] = sprintf('%s="%s"', $attrName, $value->value);

                continue;
            }

            if (is_array($value)) {
                $attrs[] = sprintf('%s="%s"', $attrName, e(json_encode($value)));

                continue;
            }

            if (is_int($value) || is_float($value)) {
                $attrs[] = sprintf('%s="%s"', $attrName, $value);

                continue;
            }

            $attrs[] = sprintf('%s="%s"', $attrName, e((string) $value));
        }

        return implode(' ', $attrs);
    }

    /**
     * Build an attribute string from a Blade component instance.
     */
    public static function fromComponent(Component $component): string
    {
        $props = [];
        $reflection = new \ReflectionClass($component);

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $param) {
            $name = $param->getName();
            if (property_exists($component, $name)) {
                $props[$name] = $component->{$name};
            }
        }

        return self::build($props);
    }

    /**
     * Convert a camelCase string to kebab-case.
     */
    public static function camelToKebab(string $value): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '-$0', $value));
    }
}
