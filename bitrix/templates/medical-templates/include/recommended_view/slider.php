<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arResult = $arParams['DATA'] ?? [];
if (empty($arResult['ITEMS'])) {
	return;
}
?>
<div class="goods">
    <ul class="goods__slider">
        <?php foreach ($arResult['ITEMS'] as $arItem):
			$price = priceDiscount($arItem['ID']);
			$articlsValues = gnkmedRecommendedArticlsValues($arItem);
			$previewSrc = gnkmedRecommendedPreviewSrc($arItem);
			?>
            <li class="goods__item">
                <div class="goods__item_wrapper">
                    <div class="goods__img">
                        <a href="<?=htmlspecialcharsbx($arItem['DETAIL_PAGE_URL'])?>">
							<?php if ($previewSrc): ?>
                            <img src="<?=htmlspecialcharsbx($previewSrc)?>" alt="<?=htmlspecialcharsbx($arItem['NAME'])?>">
							<?php endif; ?>
                        </a>
                    </div>
                    <a href="<?=htmlspecialcharsbx($arItem['DETAIL_PAGE_URL'])?>" class="goods__name">
                        <?=htmlspecialcharsbx(trim($arItem['NAME']))?>

                        <?php if (!empty($arItem['DESCRIPTION'])): ?>
                            <br/>
                            <span class="goods__name__desc"><?=$arItem['DESCRIPTION']?></span>
                        <?php endif; ?>
                    </a>
                    <div class="goods__info">
                        <div class="goods__prices">
                            <div class="goods__price"><?=$price['DISCOUNT_PRICE']?></div>

                            <div class="goods__counter">
                                <div class="goods__counter_subtract">-</div>
                                <input type="text" class="goods__counter_input" id="goods__counter_input_<?=(int)$arItem['ID']?>" value="1" readonly>
                                <div class="goods__counter_add">+</div>
                            </div>
                            <span data-text="за штуку"><?=($arItem['JS_HIDE'] === 'N') ? 'за штуку' : '' ?></span>
                        </div>
                        <?php if (count($articlsValues) > 1): ?>
                            <a href="javascript:void(0)" class="goods__basket icon-basket" onclick="$('#more_option_<?=(int)$arItem['ID']?>').bPopup({zIndex:1000});"></a>
                        <?php else: ?>
                            <?php if (count($articlsValues) === 1): ?>
                            <input type="hidden" name="article" value="<?=htmlspecialcharsbx($articlsValues[0])?>">
                            <?php endif; ?>
                            <a href="javascript:void(0)" class="goods__basket icon-basket" onclick="addToBasket2(<?=(int)$arItem['ID']?>, $('#goods__counter_input_<?=(int)$arItem['ID']?>').val(),this);"></a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
