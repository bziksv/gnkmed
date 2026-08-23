<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);

if (!empty($arResult['AUTH']))
{
	$arResult['AUTH']['captcha_registration'] = 'N';
	unset($arResult['AUTH']['capCode']);
}

if (!empty($arResult['JS_DATA']['AUTH']))
{
	$arResult['JS_DATA']['AUTH']['captcha_registration'] = 'N';
	unset($arResult['JS_DATA']['AUTH']['capCode']);
}