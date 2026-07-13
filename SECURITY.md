# Security Policy

## Supported versions

| Version | Supported |
| ------- | --------- |
| 0.2.x   | ✅        |
| < 0.2   | ❌        |

## Reporting a vulnerability

Please **do not** open a public GitHub issue for security problems.

Email **security@utgnetworks.com** with a description of the issue, reproduction
steps, and the affected version. You will receive an acknowledgement within
72 hours and a status update within 14 days. We ask that you give us a
reasonable window to ship a fix before public disclosure.

This package is part of the Vizor VR platform; the org-wide disclosure policy
lives at
[vizor-vr/vizor-vr/SECURITY.md](https://github.com/vizor-vr/vizor-vr/blob/main/SECURITY.md).

## Scope notes

- This package renders `<script>` tags pointing at the pinned Vizor player CDN
  bundle (`config('vizor.player_version')`). It never loads `@latest`; version
  bumps land via reviewed pull requests.
- API keys and license keys configured via `config/vizor.php` are read from
  environment variables. Never commit real keys.
- The `vizor.inject` middleware modifies HTML responses only when
  `vizor.auto_inject` is explicitly enabled.
