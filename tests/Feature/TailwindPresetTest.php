<?php

use Vizor\Laravel\Tailwind\VizorPreset;

// ──────────────────────────── Structure ────────────────────────────

it('config returns an array with theme.extend key', function () {
    $config = VizorPreset::config();

    expect($config)->toBeArray();
    expect($config)->toHaveKey('theme');
    expect($config['theme'])->toHaveKey('extend');
});

// ──────────────────────────── Colors ────────────────────────────

it('includes vizor-primary color', function () {
    $config = VizorPreset::config();

    expect($config['theme']['extend']['colors'])->toHaveKey('vizor-primary');
    expect($config['theme']['extend']['colors']['vizor-primary'])->toContain('#f43f5e');
});

// ──────────────────────────── Aspect Ratios ────────────────────────────

it('includes vizor aspect ratio (16/9)', function () {
    $config = VizorPreset::config();

    expect($config['theme']['extend']['aspectRatio'])->toHaveKey('vizor');
    expect($config['theme']['extend']['aspectRatio']['vizor'])->toBe('16 / 9');
});

// ──────────────────────────── Max Width ────────────────────────────

it('includes vizor-4k max width (3840px)', function () {
    $config = VizorPreset::config();

    expect($config['theme']['extend']['maxWidth'])->toHaveKey('vizor-4k');
    expect($config['theme']['extend']['maxWidth']['vizor-4k'])->toBe('3840px');
});

// ──────────────────────────── Screens ────────────────────────────

it('includes vizor breakpoint screens', function () {
    $config = VizorPreset::config();
    $screens = $config['theme']['extend']['screens'];

    expect($screens)->toHaveKey('vizor-sm');
    expect($screens)->toHaveKey('vizor-md');
    expect($screens)->toHaveKey('vizor-lg');
    expect($screens)->toHaveKey('vizor-xl');

    expect($screens['vizor-sm'])->toBe('480px');
    expect($screens['vizor-md'])->toBe('768px');
    expect($screens['vizor-lg'])->toBe('1024px');
    expect($screens['vizor-xl'])->toBe('1440px');
});

// ──────────────────────────── Preset File ────────────────────────────

it('tailwind.preset.js file exists in the package root', function () {
    $packageRoot = dirname(__DIR__, 2);
    $presetPath = $packageRoot.'/tailwind.preset.js';

    expect(file_exists($presetPath))->toBeTrue();
});
