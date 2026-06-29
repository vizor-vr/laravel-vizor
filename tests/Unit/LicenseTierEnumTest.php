<?php

use Vizor\Laravel\Support\LicenseTierEnum;

describe('LicenseTierEnum', function () {
    it('has exactly 4 tier cases', function () {
        expect(LicenseTierEnum::cases())->toHaveCount(4);
    });

    it('contains free, starter, pro, and enterprise tiers', function () {
        $values = array_map(fn (LicenseTierEnum $t) => $t->value, LicenseTierEnum::cases());

        expect($values)->toContain('free');
        expect($values)->toContain('starter');
        expect($values)->toContain('pro');
        expect($values)->toContain('enterprise');
    });

    it('creates enum from valid string value via from()', function () {
        expect(LicenseTierEnum::from('free'))->toBe(LicenseTierEnum::FREE);
        expect(LicenseTierEnum::from('starter'))->toBe(LicenseTierEnum::STARTER);
        expect(LicenseTierEnum::from('pro'))->toBe(LicenseTierEnum::PRO);
        expect(LicenseTierEnum::from('enterprise'))->toBe(LicenseTierEnum::ENTERPRISE);
    });

    it('returns null for invalid value via tryFrom()', function () {
        expect(LicenseTierEnum::tryFrom('ultra'))->toBeNull();
        expect(LicenseTierEnum::tryFrom(''))->toBeNull();
    });

    it('returns human-readable labels', function () {
        expect(LicenseTierEnum::FREE->label())->toBe('Free');
        expect(LicenseTierEnum::STARTER->label())->toBe('Starter');
        expect(LicenseTierEnum::PRO->label())->toBe('Pro');
        expect(LicenseTierEnum::ENTERPRISE->label())->toBe('Enterprise');
    });

    it('returns features array with expected keys for each tier', function () {
        $expectedKeys = [
            'watermark', 'mono_only', 'analytics', 'custom_branding',
            'api_access', 'collaborative', 'annotations', 'webxr',
        ];

        foreach (LicenseTierEnum::cases() as $tier) {
            $features = $tier->features();

            expect($features)->toBeArray();
            expect(array_keys($features))->toEqual($expectedKeys);
        }
    });

    it('has correct feature flags for Free tier', function () {
        $features = LicenseTierEnum::FREE->features();

        expect($features['watermark'])->toBeTrue();
        expect($features['mono_only'])->toBeTrue();
        expect($features['analytics'])->toBeFalse();
        expect($features['custom_branding'])->toBeFalse();
        expect($features['api_access'])->toBeFalse();
        expect($features['collaborative'])->toBeFalse();
        expect($features['annotations'])->toBeFalse();
        expect($features['webxr'])->toBeFalse();
    });

    it('has all features enabled for Enterprise tier except watermark and mono_only', function () {
        $features = LicenseTierEnum::ENTERPRISE->features();

        expect($features['watermark'])->toBeFalse();
        expect($features['mono_only'])->toBeFalse();
        expect($features['analytics'])->toBeTrue();
        expect($features['custom_branding'])->toBeTrue();
        expect($features['api_access'])->toBeTrue();
        expect($features['collaborative'])->toBeTrue();
        expect($features['annotations'])->toBeTrue();
        expect($features['webxr'])->toBeTrue();
    });

    it('has progressive feature unlocking from Starter to Pro', function () {
        $starter = LicenseTierEnum::STARTER->features();
        $pro = LicenseTierEnum::PRO->features();

        // Starter has no custom_branding or collaborative
        expect($starter['custom_branding'])->toBeFalse();
        expect($starter['collaborative'])->toBeFalse();

        // Pro has both
        expect($pro['custom_branding'])->toBeTrue();
        expect($pro['collaborative'])->toBeTrue();
    });
});
