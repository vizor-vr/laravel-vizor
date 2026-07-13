<?php

use Vizor\Laravel\Support\FormatEnum;

describe('FormatEnum', function () {
    it('has exactly 19 format cases', function () {
        expect(FormatEnum::cases())->toHaveCount(19);
    });

    it('contains all expected enum cases', function () {
        $expectedCases = [
            'MONO_360', 'MONO_FLAT',
            'STEREO_180_LR', 'STEREO_180_TB',
            'STEREO_180_LR_SPHERICAL', 'STEREO_180_TB_SPHERICAL',
            'STEREO_360_LR', 'STEREO_360_TB',
            'STEREO_FLAT_LR', 'STEREO_FLAT_LR_SQUARE',
            'STEREO_FLAT_TB', 'STEREO_FLAT_TB_SQUARE',
            'MONO_CUBEMAP', 'STEREO_CUBEMAP',
            'MONO_EAC', 'STEREO_EAC_TB',
            'MONO_FISHEYE', 'STEREO_FISHEYE_LR',
            'CARDBOARD_PHOTO',
        ];

        $actualValues = array_map(fn (FormatEnum $f) => $f->value, FormatEnum::cases());

        foreach ($expectedCases as $expected) {
            expect($actualValues)->toContain($expected);
        }
    });

    it('creates enum from valid string value via from()', function () {
        $format = FormatEnum::from('MONO_360');

        expect($format)->toBe(FormatEnum::MONO_360);
        expect($format->value)->toBe('MONO_360');
    });

    it('returns null for invalid value via tryFrom()', function () {
        $format = FormatEnum::tryFrom('INVALID_FORMAT');

        expect($format)->toBeNull();
    });

    it('returns human-readable labels for each format', function () {
        expect(FormatEnum::MONO_360->label())->toBe('Mono 360');
        expect(FormatEnum::STEREO_180_LR->label())->toBe('Stereo 180 Side-by-Side');
        expect(FormatEnum::STEREO_180_LR_SPHERICAL->label())->toBe('VR180 Side-by-Side');
        expect(FormatEnum::CARDBOARD_PHOTO->label())->toBe('Cardboard Photo');
        expect(FormatEnum::MONO_CUBEMAP->label())->toBe('Mono Cubemap');
        expect(FormatEnum::MONO_EAC->label())->toBe('Mono EAC');
        expect(FormatEnum::MONO_FISHEYE->label())->toBe('Mono Fisheye');
    });

    it('returns complete labels() array with 19 entries', function () {
        $labels = FormatEnum::labels();

        expect($labels)->toBeArray()
            ->toHaveCount(19);
        expect($labels['MONO_360'])->toBe('Mono 360');
        expect($labels['CARDBOARD_PHOTO'])->toBe('Cardboard Photo');
    });

    it('returns true from supportsVideo() for all formats except CARDBOARD_PHOTO', function () {
        foreach (FormatEnum::cases() as $format) {
            if ($format === FormatEnum::CARDBOARD_PHOTO) {
                expect($format->supportsVideo())->toBeFalse('CARDBOARD_PHOTO should not support video');
            } else {
                expect($format->supportsVideo())->toBeTrue("{$format->value} should support video");
            }
        }
    });

    it('returns false from supportsImage() only for the 4 STEREO_FLAT variants', function () {
        $noImage = [
            FormatEnum::STEREO_FLAT_LR,
            FormatEnum::STEREO_FLAT_LR_SQUARE,
            FormatEnum::STEREO_FLAT_TB,
            FormatEnum::STEREO_FLAT_TB_SQUARE,
        ];

        foreach (FormatEnum::cases() as $format) {
            if (in_array($format, $noImage, true)) {
                expect($format->supportsImage())->toBeFalse("{$format->value} should not support image");
            } else {
                expect($format->supportsImage())->toBeTrue("{$format->value} should support image");
            }
        }
    });

    it('returns true from isFlat() for MONO_FLAT and all STEREO_FLAT variants', function () {
        $flatFormats = [
            FormatEnum::MONO_FLAT,
            FormatEnum::STEREO_FLAT_LR,
            FormatEnum::STEREO_FLAT_LR_SQUARE,
            FormatEnum::STEREO_FLAT_TB,
            FormatEnum::STEREO_FLAT_TB_SQUARE,
        ];

        foreach (FormatEnum::cases() as $format) {
            if (in_array($format, $flatFormats, true)) {
                expect($format->isFlat())->toBeTrue("{$format->value} should be flat");
            } else {
                expect($format->isFlat())->toBeFalse("{$format->value} should not be flat");
            }
        }
    });

    it('has backed enum values that match @vizor-vr/player format strings exactly', function () {
        // Each enum case name must equal its backing string value
        foreach (FormatEnum::cases() as $format) {
            expect($format->value)->toBe($format->name,
                "Enum case {$format->name} value should be '{$format->name}' but got '{$format->value}'"
            );
        }
    });

    it('supports standard backed enum serialization', function () {
        $serialized = FormatEnum::STEREO_360_TB->value;
        $deserialized = FormatEnum::from($serialized);

        expect($deserialized)->toBe(FormatEnum::STEREO_360_TB);
    });
});
