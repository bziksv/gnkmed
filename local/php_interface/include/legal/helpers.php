<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once __DIR__ . '/third_party_services.php';

function gnkmedLegalConfig(): array
{
    static $config = null;

    if ($config === null) {
        $config = include __DIR__ . '/config.php';
    }

    return $config;
}

function gnkmedLegalUrl(string $key): string
{
    return gnkmedLegalConfig()['urls'][$key] ?? '';
}

function gnkmedLegalPublicUrl(string $key): string
{
    $site = rtrim((string) (gnkmedLegalConfig()['operator']['site'] ?? 'https://gnkmed.ru/'), '/');
    $path = gnkmedLegalUrl($key);

    return $path !== '' ? $site . $path : '';
}

function gnkmedLegalEmail(): string
{
    return (string) (gnkmedLegalConfig()['operator']['email'] ?? '');
}

function gnkmedLegalEmailMailto(): string
{
    $email = htmlspecialcharsbx(gnkmedLegalEmail());

    return '<a href="mailto:' . $email . '">' . $email . '</a>';
}
