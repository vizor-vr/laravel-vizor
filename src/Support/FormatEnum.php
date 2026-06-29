<?php

namespace Vizor\Laravel\Support;

/**
 * All 19 projection formats supported by the Vizor VR player.
 * Values match the @vizor-vr/player VzFormat constants exactly.
 */
enum FormatEnum: string
{
    // Mono
    case MONO_360 = 'MONO_360';
    case MONO_FLAT = 'MONO_FLAT';

    // 180 Stereo Equirectangular
    case STEREO_180_LR = 'STEREO_180_LR';
    case STEREO_180_TB = 'STEREO_180_TB';

    // 180 Stereo Spherical (VR180)
    case STEREO_180_LR_SPHERICAL = 'STEREO_180_LR_SPHERICAL';
    case STEREO_180_TB_SPHERICAL = 'STEREO_180_TB_SPHERICAL';

    // 360 Stereo Equirectangular
    case STEREO_360_LR = 'STEREO_360_LR';
    case STEREO_360_TB = 'STEREO_360_TB';

    // Flat Stereo (3D movies)
    case STEREO_FLAT_LR = 'STEREO_FLAT_LR';
    case STEREO_FLAT_LR_SQUARE = 'STEREO_FLAT_LR_SQUARE';
    case STEREO_FLAT_TB = 'STEREO_FLAT_TB';
    case STEREO_FLAT_TB_SQUARE = 'STEREO_FLAT_TB_SQUARE';

    // Cubemap
    case MONO_CUBEMAP = 'MONO_CUBEMAP';
    case STEREO_CUBEMAP = 'STEREO_CUBEMAP';

    // Equi-Angular Cubemap (EAC)
    case MONO_EAC = 'MONO_EAC';
    case STEREO_EAC_TB = 'STEREO_EAC_TB';

    // Fisheye
    case MONO_FISHEYE = 'MONO_FISHEYE';
    case STEREO_FISHEYE_LR = 'STEREO_FISHEYE_LR';

    // Special
    case CARDBOARD_PHOTO = 'CARDBOARD_PHOTO';

    /**
     * Human-readable label for each format.
     */
    public function label(): string
    {
        return match ($this) {
            self::MONO_360 => 'Mono 360',
            self::MONO_FLAT => 'Mono Flat',
            self::STEREO_180_LR => 'Stereo 180 Side-by-Side',
            self::STEREO_180_TB => 'Stereo 180 Top-Bottom',
            self::STEREO_180_LR_SPHERICAL => 'VR180 Side-by-Side',
            self::STEREO_180_TB_SPHERICAL => 'VR180 Top-Bottom',
            self::STEREO_360_LR => 'Stereo 360 Side-by-Side',
            self::STEREO_360_TB => 'Stereo 360 Top-Bottom',
            self::STEREO_FLAT_LR => 'Stereo Flat Side-by-Side',
            self::STEREO_FLAT_LR_SQUARE => 'Stereo Flat SBS Square',
            self::STEREO_FLAT_TB => 'Stereo Flat Top-Bottom',
            self::STEREO_FLAT_TB_SQUARE => 'Stereo Flat TB Square',
            self::MONO_CUBEMAP => 'Mono Cubemap',
            self::STEREO_CUBEMAP => 'Stereo Cubemap',
            self::MONO_EAC => 'Mono EAC',
            self::STEREO_EAC_TB => 'Stereo EAC Top-Bottom',
            self::MONO_FISHEYE => 'Mono Fisheye',
            self::STEREO_FISHEYE_LR => 'Stereo Fisheye Side-by-Side',
            self::CARDBOARD_PHOTO => 'Cardboard Photo',
        };
    }

    /**
     * Get all format labels as an associative array.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];
        foreach (self::cases() as $format) {
            $labels[$format->value] = $format->label();
        }

        return $labels;
    }

    /**
     * Whether this format supports video content.
     */
    public function supportsVideo(): bool
    {
        return $this !== self::CARDBOARD_PHOTO;
    }

    /**
     * Whether this format supports image content.
     */
    public function supportsImage(): bool
    {
        return ! in_array($this, [
            self::STEREO_FLAT_LR,
            self::STEREO_FLAT_LR_SQUARE,
            self::STEREO_FLAT_TB,
            self::STEREO_FLAT_TB_SQUARE,
        ], true);
    }

    /**
     * Whether this format uses a flat (non-panoramic) projection.
     */
    public function isFlat(): bool
    {
        return in_array($this, [
            self::MONO_FLAT,
            self::STEREO_FLAT_LR,
            self::STEREO_FLAT_LR_SQUARE,
            self::STEREO_FLAT_TB,
            self::STEREO_FLAT_TB_SQUARE,
        ], true);
    }
}
