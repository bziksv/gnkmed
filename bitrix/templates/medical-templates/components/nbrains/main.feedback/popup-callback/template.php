<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/helpers.php';
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>
<div class="mfeedback-p" id="callback" data-params-hash="<?=htmlspecialcharsbx($arResult['PARAMS_HASH'])?>">

	<span class="button b-close"><span>&times;</span></span>

	<div class="mfeedback-p-head" data-text="Заказать звонок"></div>


<?if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $v)
		ShowError($v);
}

if(strlen($arResult["OK_MESSAGE"]) > 0):?>

	<div class="mf-ok-text"><?=$arResult["OK_MESSAGE"]?></div>

<? else: ?>

	<form action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">

    <?=bitrix_sessid_post()?>

	<? foreach($arResult['USER_FIELD'] as $field):?>

		<?if($field['PROPERTY_TYPE'] == "S" and !$field["USER_TYPE"]):?>
		<div class="mf-name">
			<? if($field['CODE'] == "URL"):?>
				<input type="hidden" name="URL" value="<?=$_SERVER['SERVER_NAME'].$APPLICATION->GetCurPage();?>">
			<?else:?>
				<input type="text" placeholder="<?=$field['NAME']?><?=($field['IS_REQUIRED'] == "Y") ? "*" : ""?>" name="<?=$field['CODE']?>" value="<?=$arResult[$field['CODE']]?>">
			<?endif;?>
		</div>
		<? else: ?>
		<div class="mf-name">
			<textarea name="<?=$field['CODE']?>" rows="10" placeholder="<?=$field['NAME']?><?=($field['IS_REQUIRED'] == "Y") ? "*" : ""?>"><?=$arResult[$field['CODE']]?></textarea>
		</div>
		<? endif; ?>

	<? endforeach; ?>

    <?if($arParams["USE_CAPTCHA"] == "Y"):?>
        <div class="mf-name mf-captcha">
            <div class="mf-text"><?=GetMessage("MFT_CAPTCHA")?></div>
            <input type="hidden" name="captcha_sid" value="<?=htmlspecialcharsbx($arResult["capCode"])?>">
            <img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["capCode"])?>" width="180" height="40" alt="CAPTCHA">
            <div class="mf-text"><?=GetMessage("MFT_CAPTCHA_CODE")?><span class="mf-req">*</span></div>
            <input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off">
        </div>
    <?endif;?>

	<div class="mf-name">

<div>
	<label style="color: #66797f;">
  <input type="checkbox" id="callback-consent" name="callback-consent">
				<span>Нажимая на эту кнопку, я даю свое <a target="_blank" href="<?=htmlspecialcharsbx(gnkmedLegalUrl('consent'))?>">согласие на обработку персональных данных</a> и соглашаюсь с условиями <a target="_blank" href="<?=htmlspecialcharsbx(gnkmedLegalUrl('policy'))?>">политики обработки персональных данных</a>.</span>
</label>
	<div class="mf-consent-error" style="display:none;color:#a94442;margin-top:8px;">
		Необходимо дать согласие на обработку персональных данных
	</div>
</div>


	</div>
		
	<div class="mfeedback-p-footer">
		<input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>">
		<input type="submit" name="submit" class="subscribe__btn" value="<?=GetMessage("MFT_SUBMIT")?>">
	</div>


</form>

<? endif; ?>

</div>

