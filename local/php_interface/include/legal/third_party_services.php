<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Сторонние сервисы и домены, реально используемые на gnkmed.ru (шаблон medical-templates).
 * Единый перечень для всех юридических документов.
 *
 * Шрифты (Open Sans, Roboto), jQuery UI и Slick Carousel размещены локально в
 * /bitrix/templates/medical-templates/vendor/ и не обращаются к внешним CDN.
 */
function gnkmedLegalThirdPartyServices(): array
{
    return [
        [
            'label' => 'сервисы ООО «Яндекс»',
            'operator' => 'ООО «Яндекс» (ИНН 7736207543)',
            'urls' => [
                ['href' => 'https://mc.yandex.ru/', 'text' => 'https://mc.yandex.ru/'],
                ['href' => 'https://api-maps.yandex.ru/', 'text' => 'https://api-maps.yandex.ru/'],
            ],
            'purpose' => 'Яндекс.Метрика — сбор и анализ статистики посещений, действий пользователей и достижения целей; Яндекс.Карты — отображение пунктов выдачи и доставки при оформлении заказа',
            'processor_note' => 'Яндекс.Метрика, Яндекс.Карты',
        ],
        [
            'label' => 'сервис Roistat',
            'operator' => 'ООО «Бизнес-аналитика» (Roistat)',
            'urls' => [
                ['href' => 'https://cloud.roistat.com/', 'text' => 'https://cloud.roistat.com/'],
                ['href' => 'https://roistat.com/', 'text' => 'https://roistat.com/'],
            ],
            'purpose' => 'используется для сквозной аналитики, отслеживания обращений и источников трафика',
            'processor_note' => 'сквозная аналитика и учёт обращений',
        ],
        [
            'label' => 'платформа 1С-Битрикс',
            'operator' => 'ООО «1С-Битрикс»',
            'urls' => [
                ['href' => 'https://bitrix.info/', 'text' => 'https://bitrix.info/'],
                ['href' => 'https://www.1c-bitrix.ru/', 'text' => 'https://www.1c-bitrix.ru/'],
            ],
            'purpose' => 'обеспечивает работу Сайта, корзины, личного кабинета и оформления заказов; техническая аналитика платформы (bitrix.info)',
            'processor_note' => 'платформа сайта, корзина, личный кабинет, оформление заказов',
        ],
        [
            'label' => 'агентство «Прайм»',
            'operator' => 'агентство «Прайм»',
            'urls' => [
                ['href' => 'https://prime-ltd.su/', 'text' => 'https://prime-ltd.su/'],
            ],
            'purpose' => 'SEO-сопровождение, обезличенная аналитика посещаемости',
            'processor_note' => 'SEO-сопровождение, обезличенная аналитика',
        ],
    ];
}

function gnkmedLegalFormatThirdPartyUrlLinks(array $urls): string
{
    $parts = [];
    foreach ($urls as $url) {
        $href = htmlspecialcharsbx($url['href']);
        $text = htmlspecialcharsbx($url['text']);
        $parts[] = '<a href="' . $href . '" target="_blank" rel="noopener">' . $text . '</a>';
    }

    return implode(', ', $parts);
}

function gnkmedLegalRenderThirdPartyUrlList(): void
{
    echo '<ul class="legal-doc__third-party-list">';
    foreach (gnkmedLegalThirdPartyServices() as $service) {
        echo '<li>' . gnkmedLegalFormatThirdPartyUrlLinks($service['urls'])
            . ' — ' . htmlspecialcharsbx($service['label'])
            . '; ' . $service['purpose'] . '.</li>';
    }
    echo '</ul>';
}

function gnkmedLegalRenderThirdPartyProcessorsList(): void
{
    echo '<ul class="legal-doc__third-party-list">';
    foreach (gnkmedLegalThirdPartyServices() as $service) {
        $operator = htmlspecialcharsbx($service['operator']);
        $note = htmlspecialcharsbx($service['processor_note']);
        $urls = gnkmedLegalFormatThirdPartyUrlLinks($service['urls']);
        echo '<li>' . $operator . ' (' . $note . ') — ' . $urls . ';</li>';
    }
    echo '</ul>';
}

function gnkmedLegalThirdPartyServicesSummary(): string
{
    $labels = array_map(static function (array $service): string {
        return $service['label'];
    }, gnkmedLegalThirdPartyServices());

    return implode(', ', $labels);
}

function gnkmedLegalLocalAssetsNote(): string
{
    return 'Шрифты Open Sans и Roboto, библиотека jQuery UI и компонент Slick Carousel размещены на Сайте локально (каталог шаблона vendor/) и не запрашивают внешние CDN.';
}
