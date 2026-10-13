<?php
class CNigesCookiesAcceptPublic
{
	/**
	 * Inject cookie notice before </body> (not after </html>).
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

		global $APPLICATION;
		ob_start();
		$APPLICATION->IncludeComponent(
			'niges:cookiesaccept',
			'.default',
			array(),
			false,
			array('HIDE_ICONS' => 'Y')
		);
		$banner = ob_get_clean();
		if ($banner === '' || $banner === false) {
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
	 * Legacy hook — kept for module upgrade safety; no-op when buffer hook is active.
	 */
	public static function OnEpilog()
	{
	}
}
