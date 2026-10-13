<?php
class CNigesCookiesAcceptPublic
{
	/**
	 * Inject cookie notice before </body> (not after </html>).
	 * Must not call ob_start() — OnEndBufferContent runs inside a buffer handler.
	 */
	public static function OnEndBufferContent(&$content)
	{
		if (!is_string($content) || $content === '') {
			return;
		}

		if (!CModule::IncludeModule(cookiesaccept_MODULE_ID)) {
			return;
		}

		if (COption::GetOptionString(cookiesaccept_MODULE_ID, 'ACTIVE', 'N', SITE_ID) !== 'Y') {
			return;
		}

		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) {
			return;
		}

		if (defined('PUBLIC_AJAX_MODE') && PUBLIC_AJAX_MODE === true) {
			return;
		}
		if (isset($_REQUEST['ajax']) && (string)$_REQUEST['ajax'] !== '') {
			return;
		}
		if (isset($_REQUEST['bxajaxid']) && (string)$_REQUEST['bxajaxid'] !== '') {
			return;
		}

		if (strpos($content, 'id="nca-cookiesaccept-line"') !== false) {
			return;
		}

		$banner = self::renderBannerHtml();
		if ($banner === '') {
			return;
		}

		$pos = strripos($content, '</body>');
		if ($pos !== false) {
			$content = substr($content, 0, $pos) . $banner . substr($content, $pos);
			return;
		}

		$pos = strripos($content, '</html>');
		if ($pos !== false) {
			$content = substr($content, 0, $pos) . $banner . substr($content, $pos);
		}
	}

	/**
	 * Build banner markup + CSS link without IncludeComponent / output buffering.
	 */
	public static function renderBannerHtml()
	{
		if (!CModule::IncludeModule(cookiesaccept_MODULE_ID) || !class_exists('CNigesCookiesAcceptHelper')) {
			return '';
		}

		$arResult = CNigesCookiesAcceptHelper::loadSettings(SITE_ID);

		if ($arResult['TEXTBTN'] === '') {
			$arResult['TEXTBTN'] = 'Принять';
		}

		if ($arResult['MAINTEXT'] === '') {
			$arResult['MAINTEXT'] = CNigesCookiesAcceptHelper::sanitizeHtml(
				'Данный веб-сайт использует cookie-файлы в целях предоставления вам лучшего пользовательского опыта на нашем сайте. Продолжая использовать данный сайт, вы соглашаетесь с использованием нами cookie-файлов.'
			);
		}

		$arResult['COOKIE_NAME'] = CNigesCookiesAcceptHelper::getCookieName($arResult['TEXTVER']);
		$arResult['OPACITY'] = round(((int)$arResult['BTNOPACITY']) / 100, 2);

		$cookieName = htmlspecialcharsbx($arResult['COOKIE_NAME']);
		$textVer = (int)$arResult['TEXTVER'];
		$padding = (int)$arResult['PADDINGSIZE'];
		$opacity = htmlspecialcharsbx((string)$arResult['OPACITY']);
		$zIndex = (int)$arResult['ZINDEX'];
		$topOffset = (int)$arResult['TOP'];
		$styleId = (int)$arResult['SETSTYLE'];
		$position = htmlspecialcharsbx($arResult['POSITION']);
		$animation = htmlspecialcharsbx($arResult['ANIMATION']);
		$btnText = htmlspecialcharsbx($arResult['TEXTBTN']);
		$mainText = $arResult['MAINTEXT'];

		$classes = array(
			'nca-cookiesaccept-line',
			'style-'.$styleId,
			'nca-position-'.$position,
			'nca-animation-'.$animation,
		);
		if ($arResult['HIDE_MOBILE'] === 'Y') {
			$classes[] = 'nca-hidden-mobile';
		}
		if ($arResult['HIDE_PC'] === 'Y') {
			$classes[] = 'nca-hidden-pc';
		}
		if ($arResult['TOPORBOTTOM'] === '1') {
			$classes[] = 'nca-place-top';
		} else {
			$classes[] = 'nca-place-bottom';
		}

		$styleVars = sprintf(
			'--nca-padding:%dpx;--nca-opacity:%s;--nca-zindex:%d;--nca-top:%d%%;',
			$padding,
			$opacity,
			$zIndex,
			$topOffset
		);

		$cssHref = '/bitrix/components/niges/cookiesaccept/templates/.default/style.css';
		$classAttr = htmlspecialcharsbx(implode(' ', $classes));
		$styleAttr = htmlspecialcharsbx($styleVars);

		return
			'<link rel="stylesheet" href="'.htmlspecialcharsbx($cssHref).'">'."\n"
			.'<div'
			.' id="nca-cookiesaccept-line"'
			.' class="'.$classAttr.'"'
			.' style="'.$styleAttr.'"'
			.' data-cookie-name="'.$cookieName.'"'
			.' data-textver="'.$textVer.'"'
			.' hidden'
			.' role="dialog"'
			.' aria-live="polite"'
			.'>'."\n"
			.'	<div class="nca-bar" id="nca-bar">'."\n"
			.'		<div class="nca-cookiesaccept-line-text">'.$mainText.'</div>'."\n"
			.'		<div class="nca-cookiesaccept-line-actions">'."\n"
			.'			<button type="button" id="nca-cookiesaccept-line-accept-btn" class="nca-accept-btn">'
			.$btnText
			.'</button>'."\n"
			.'		</div>'."\n"
			.'	</div>'."\n"
			.'</div>'."\n"
			.'<script>'."\n"
			."document.addEventListener('DOMContentLoaded', function() {\n"
			."    (function () {\n"
			."        if (window !== window.top) { return; }\n"
			."        var root = document.getElementById('nca-cookiesaccept-line');\n"
			."        if (!root) { return; }\n"
			."        var cookieName = root.getAttribute('data-cookie-name') || '';\n"
			."        if (!cookieName) { return; }\n"
			."        function readCookie(name) {\n"
			."            var parts = ('; ' + document.cookie).split('; ' + name + '=');\n"
			."            if (parts.length === 2) { return parts.pop().split(';').shift(); }\n"
			."            return '';\n"
			."        }\n"
			."        function writeAcceptCookie(name) {\n"
			."            var maxAge = 60 * 60 * 24 * 400;\n"
			."            var parts = [name + '=Y', 'path=/', 'max-age=' + maxAge, 'SameSite=Lax'];\n"
			."            if (window.location.protocol === 'https:') { parts.push('Secure'); }\n"
			."            document.cookie = parts.join('; ');\n"
			."        }\n"
			."        function hideBanner() {\n"
			."            if (root && root.parentNode) { root.parentNode.removeChild(root); }\n"
			."        }\n"
			."        if (readCookie(cookieName) === 'Y') { hideBanner(); return; }\n"
			."        root.hidden = false;\n"
			."        var btn = document.getElementById('nca-cookiesaccept-line-accept-btn');\n"
			."        if (btn) {\n"
			."            btn.addEventListener('click', function () {\n"
			."                writeAcceptCookie(cookieName);\n"
			."                hideBanner();\n"
			."            });\n"
			."        }\n"
			."    })();\n"
			."});\n"
			.'</script>'."\n";
	}

	/**
	 * Legacy hook — kept for module upgrade safety; no-op when buffer hook is active.
	 */
	public static function OnEpilog()
	{
	}
}
