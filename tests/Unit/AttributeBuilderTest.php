<?php

use Vizor\Laravel\Support\AttributeBuilder;
use Vizor\Laravel\Support\FormatEnum;

describe('AttributeBuilder', function () {
    describe('build()', function () {
        it('returns empty string for empty array', function () {
            expect(AttributeBuilder::build([]))->toBe('');
        });

        it('maps camelCase keys to kebab-case via PROP_MAP', function () {
            $result = AttributeBuilder::build(['apiKey' => 'abc123']);

            expect($result)->toBe('api-key="abc123"');
        });

        it('maps additional camelCase keys correctly', function () {
            $result = AttributeBuilder::build(['primaryColor' => '#ff0000']);

            expect($result)->toBe('primary-color="#ff0000"');
        });

        it('renders boolean true as standalone attribute', function () {
            $result = AttributeBuilder::build(['muted' => true]);

            expect($result)->toBe('muted');
        });

        it('omits boolean false attributes entirely', function () {
            $result = AttributeBuilder::build(['muted' => false]);

            expect($result)->toBe('');
        });

        it('omits null values entirely', function () {
            $result = AttributeBuilder::build(['src' => null]);

            expect($result)->toBe('');
        });

        it('renders string values in quotes with HTML escaping', function () {
            $result = AttributeBuilder::build(['src' => 'https://example.com/video.mp4']);

            expect($result)->toBe('src="https://example.com/video.mp4"');
        });

        it('renders integer values in quotes', function () {
            $result = AttributeBuilder::build(['lat' => 45]);

            expect($result)->toBe('lat="45"');
        });

        it('renders float values in quotes', function () {
            $result = AttributeBuilder::build(['lon' => 90.5]);

            expect($result)->toBe('lon="90.5"');
        });

        it('renders FormatEnum as its backing value string', function () {
            $result = AttributeBuilder::build(['format' => FormatEnum::MONO_360]);

            expect($result)->toBe('format="MONO_360"');
        });

        it('renders arrays as JSON-encoded strings', function () {
            $result = AttributeBuilder::build(['panel' => ['a', 'b']]);

            expect($result)->toContain('panel="');
            expect($result)->toContain('[');
        });

        it('handles empty string values', function () {
            $result = AttributeBuilder::build(['title' => '']);

            expect($result)->toBe('title=""');
        });

        it('escapes HTML special characters in string values', function () {
            $result = AttributeBuilder::build(['title' => 'A & B <script>"quoted"</script>']);

            expect($result)->not->toContain('<script>');
            expect($result)->toContain('&amp;');
            expect($result)->toContain('&lt;');
            expect($result)->toContain('&gt;');
            expect($result)->toContain('&quot;');
        });

        it('combines multiple attributes separated by spaces', function () {
            $result = AttributeBuilder::build([
                'src' => 'video.mp4',
                'muted' => true,
                'loop' => true,
            ]);

            expect($result)->toBe('src="video.mp4" muted loop');
        });

        it('filters out null and false from mixed props', function () {
            $result = AttributeBuilder::build([
                'src' => 'video.mp4',
                'poster' => null,
                'autoplay' => false,
                'muted' => true,
            ]);

            expect($result)->toBe('src="video.mp4" muted');
            expect($result)->not->toContain('poster');
            expect($result)->not->toContain('autoplay');
        });
    });

    describe('camelToKebab()', function () {
        it('converts apiKey to api-key', function () {
            expect(AttributeBuilder::camelToKebab('apiKey'))->toBe('api-key');
        });

        it('converts primaryColor to primary-color', function () {
            expect(AttributeBuilder::camelToKebab('primaryColor'))->toBe('primary-color');
        });

        it('converts contentId to content-id', function () {
            expect(AttributeBuilder::camelToKebab('contentId'))->toBe('content-id');
        });

        it('leaves lowercase strings unchanged', function () {
            expect(AttributeBuilder::camelToKebab('muted'))->toBe('muted');
        });

        it('converts controlsBehavior to controls-behavior', function () {
            expect(AttributeBuilder::camelToKebab('controlsBehavior'))->toBe('controls-behavior');
        });
    });
});
