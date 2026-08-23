<?php
if (!$arParams['SECTION']['UF_RECOMMENDED_LIST']) {
	return false;
}

if (!function_exists('gnkmedRecommendedArticlsValues')) {
	function gnkmedRecommendedArticlsValues(array $item): array
	{
		$value = null;
		if (!empty($item['PROPERTIES']['ARTICLS']['VALUE'])) {
			$value = $item['PROPERTIES']['ARTICLS']['VALUE'];
		} elseif (!empty($item['ARTICLS']['VALUE'])) {
			$value = $item['ARTICLS']['VALUE'];
		}

		if ($value === null || $value === '' || $value === false) {
			return [];
		}

		return is_array($value) ? $value : [$value];
	}
}

if (!function_exists('gnkmedRecommendedPreviewSrc')) {
	function gnkmedRecommendedPreviewSrc(array $item): string
	{
		if (!empty($item['PREVIEW_PICTURE']['SRC'])) {
			return (string)$item['PREVIEW_PICTURE']['SRC'];
		}
		if (!empty($item['PREVIEW_PICTURE']['src'])) {
			return (string)$item['PREVIEW_PICTURE']['src'];
		}
		if (is_string($item['PREVIEW_PICTURE']) && $item['PREVIEW_PICTURE'] !== '') {
			return $item['PREVIEW_PICTURE'];
		}

		return '';
	}
}

if (!function_exists('gnkmedRecommendedBuildItemPicture')) {
	function gnkmedRecommendedBuildItemPicture($fileId): array
	{
		if (!$fileId) {
			return ['SRC' => ''];
		}

		$resized = CFile::ResizeImageGet(
			$fileId,
			['width' => 400, 'height' => 400],
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		if (!$resized || empty($resized['src'])) {
			return ['SRC' => ''];
		}

		return ['SRC' => $resized['src']];
	}
}
?>
<hr class="hr">

<style>
    .goods__name__desc{
        font-weight: 400;
        font-size: smaller;
    }
</style>

<?php foreach ($arParams['SECTION']['UF_RECOMMENDED_LIST'] as $p):
	$arSlider = explode('@', $p, 3);
	$title = $arSlider[0];
	$jProduct = json_decode($arSlider[1], true);
	$viewTemplate = trim((string)($arSlider[2] ?? ''));

	$arResult = ['ITEMS' => []];

	if ($jProduct && $viewTemplate) {
		$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL', 'PROPERTY_*'];
		$arFilter = ['IBLOCK_ID' => $arParams['SECTION']['IBLOCK_ID'], 'ID' => array_keys($jProduct)];
		$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
		while ($ob = $res->GetNextElement()) {
			$arFields = $ob->GetFields();
			$arProps = $ob->GetProperties();
			$itemId = (int)$arFields['ID'];

			$arResult['ITEMS'][$itemId] = $arFields;
			$arResult['ITEMS'][$itemId]['PREVIEW_PICTURE'] = gnkmedRecommendedBuildItemPicture($arFields['PREVIEW_PICTURE']);
			$arResult['ITEMS'][$itemId]['PROPERTIES'] = $arProps;
			$arResult['ITEMS'][$itemId]['NAME'] = ($jProduct[$itemId][0]) ?: $arFields['NAME'];
			$arResult['ITEMS'][$itemId]['DESCRIPTION'] = $jProduct[$itemId][1];
			$arResult['ITEMS'][$itemId]['JS_HIDE'] = (isset($jProduct[$itemId][2]) && $jProduct[$itemId][2] === 'Y') ? 'Y' : 'N';

			if (CModule::IncludeModule('catalog')) {
				global $USER;
				$optimalPrice = CCatalogProduct::GetOptimalPrice(
					$itemId,
					1,
					$USER->GetUserGroupArray(),
					'N'
				);
				if (!empty($optimalPrice['RESULT_PRICE'])) {
					$resultPrice = $optimalPrice['RESULT_PRICE'];
					$arResult['ITEMS'][$itemId]['PRICES']['BASE']['VALUE'] = $resultPrice['BASE_PRICE'];
					$arResult['ITEMS'][$itemId]['PRICES']['BASE']['DISCOUNT_VALUE'] = $resultPrice['DISCOUNT_PRICE'];
					$arResult['ITEMS'][$itemId]['PRICES']['BASE']['DISCOUNT_DIFF_PERCENT'] = $resultPrice['PERCENT'];
				}
			}
		}
	}

	if (empty($arResult['ITEMS'])) {
		continue;
	}
	?>
<div class="title"><?=htmlspecialcharsbx($title);?></div>

	<?php
	$APPLICATION->IncludeFile(
		SITE_TEMPLATE_PATH.'/include/recommended_view/'.$viewTemplate.'.php',
		['DATA' => $arResult],
		[
			'MODE' => 'php',
			'NAME' => '',
			'TEMPLATE' => '',
			'SHOW_BORDER' => false,
		]
	);
	?>

<?php endforeach; ?>
