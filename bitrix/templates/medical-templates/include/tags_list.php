<?php
if(!$arParams['SECTION']['UF_TAGS_ACTIVE'])
    return false;

global $APPLICATION;
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/tags-list.css');
?>

<div class="tags-list">
    <div class="subcategories">
        <div class="tags-scroll">
            <ul class="tag-slider sub-links-2">
                <? foreach ($arParams['SECTION']['UF_TAGS_LIST'] as $tags):
                    $arTags = explode('@', $tags, 2);
                    $tagHref = isset($arTags[1]) ? trim($arTags[1]) : '';
                    if ($tagHref !== '' && preg_match('#^(.*?/search/\?q=)([^&]*)(.*)$#u', $tagHref, $tagM)) {
                        $tagHref = $tagM[1] . rawurlencode(rawurldecode(str_replace('+', ' ', $tagM[2]))) . $tagM[3];
                    }
                    ?>
                <li><a href="<?=htmlspecialcharsbx($tagHref)?>"><?=htmlspecialcharsbx($arTags[0])?></a></li>
                <? endforeach; ?>
            </ul>
        </div>
        <div class="navi">
            <button type="button" class="tags-toggle open">Показать все</button>
            <button type="button" class="tags-toggle close" hidden>Свернуть</button>
        </div>
    </div>
</div>

<script>
(function ($) {
    $(function () {
        var $scroll = $(".tags-list .tags-scroll");
        var $list = $(".tags-list .sub-links-2");
        var $open = $(".tags-list .open");
        var $close = $(".tags-list .close");

        $open.on("click", function () {
            $open.prop("hidden", true);
            $close.prop("hidden", false);
            $list.addClass("is-open");
            $scroll.addClass("is-expanded");
        });

        $close.on("click", function () {
            $close.prop("hidden", true);
            $open.prop("hidden", false);
            $list.removeClass("is-open");
            $scroll.removeClass("is-expanded");
            $scroll.find(".sub-links-2").scrollLeft(0);
        });
    });
})(jQuery);
</script>
