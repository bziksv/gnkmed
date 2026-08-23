<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arResult = $arParams['DATA'] ?? [];
if (empty($arResult['ITEMS'])) {
	return;
}
?>
<div class="goods__list">
    <?php foreach ($arResult['ITEMS'] as $item):
		$price = priceDiscount($item['ID']);
		$articlsValues = gnkmedRecommendedArticlsValues($item);
		$previewSrc = gnkmedRecommendedPreviewSrc($item);
		?>
        <div class="goods__item">
            <div class="goods__item_wrapper">
                <?php if (!empty($item['PRICES']['BASE']['DISCOUNT_DIFF_PERCENT'])): ?>
                    <div class="goods__alert">-<?=$item['PRICES']['BASE']['DISCOUNT_DIFF_PERCENT']?>%</div>
                <?php endif; ?>

                <?php if (!empty($item['PROPERTIES']['BADGE']['VALUE'])): ?>
                    <div class="goods__badge">
                        <div class="badge" style="background-color: <?=htmlspecialcharsbx($item['PROPERTIES']['BADGE']['DESCRIPTION'])?>">
                            <span><?=htmlspecialcharsbx($item['PROPERTIES']['BADGE']['VALUE'])?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="goods__img">
                    <a href="<?=htmlspecialcharsbx($item['DETAIL_PAGE_URL'])?>">
						<?php if ($previewSrc): ?>
                        <img src="<?=htmlspecialcharsbx($previewSrc)?>" alt="<?=htmlspecialcharsbx($item['NAME'])?>">
						<?php endif; ?>
                    </a>
                </div>

                <a href="<?=htmlspecialcharsbx($item['DETAIL_PAGE_URL'])?>" class="goods__name">
                    <?=htmlspecialcharsbx($item['NAME'])?>
                    <?php if (!empty($item['DESCRIPTION'])): ?>
                        <br/>
                        <span class="goods__name__desc"><?=$item['DESCRIPTION']?></span>
                    <?php endif; ?>
                </a>

                <div class="goods__info">
                    <div class="goods__prices">
                        <div class="goods__price"><?=$price['DISCOUNT_PRICE']?></div>

                        <div class="goods__counter">
                            <div class="goods__counter_subtract">-</div>
                            <input type="text" class="goods__counter_input" id="goods__counter_input_<?=(int)$item['ID']?>" value="1" readonly>
                            <div class="goods__counter_add">+</div>
                        </div>
                        <span data-text="за штуку"><?=($item['JS_HIDE'] === 'N') ? 'за штуку' : '' ?></span>
                    </div>
                    <?php if (count($articlsValues) > 1): ?>
                        <a href="javascript:void(0)" class="goods__buy_thumbs" onclick="$('#more_option_<?=(int)$item['ID']?>').bPopup({zIndex:1000});" data-text="Купить"><?=($item['JS_HIDE'] === 'N') ? 'Купить' : '' ?></a>
                    <?php else: ?>
                        <?php if (count($articlsValues) === 1): ?>
                        <input type="hidden" name="article" value="<?=htmlspecialcharsbx($articlsValues[0])?>">
                        <?php endif; ?>
                        <a href="javascript:void(0)" class="goods__buy_thumbs" onclick="addToBasket2(<?=(int)$item['ID']?>, $('#goods__counter_input_<?=(int)$item['ID']?>').val(),this);" data-text="Купить"><?=($item['JS_HIDE'] === 'N') ? 'Купить' : '' ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
