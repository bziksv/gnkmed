<?php
//IBlock catalog id
define("IBLOCK_CATALOG","33");

AddEventHandler("main", "OnPageStart", "gnkmedDisableCaptcha");
AddEventHandler("main", "OnBeforeUserLogin", "gnkmedDisableLoginCaptcha");
AddEventHandler("main", "OnPageStart", "gnkmedDisableCompositeForForms");
AddEventHandler('main', 'OnEndBufferContent', 'gnkmedCookiesAcceptBeforeBody');
AddEventHandler('main', 'OnEndBufferContent', 'gnkmedSanitizeBufferContent');

function gnkmedCookiesAcceptBeforeBody(&$content)
{
	if (!CModule::IncludeModule('niges.cookiesaccept') || !class_exists('CNigesCookiesAcceptPublic'))
	{
		return;
	}

	CNigesCookiesAcceptPublic::OnEndBufferContent($content);
}


function gnkmedSanitizeBufferContent(&$content)
{
	if (!is_string($content) || $content === '' || (defined('ADMIN_SECTION') && ADMIN_SECTION === true))
	{
		return;
	}

	// Only safe global fixes — do not run full markup rewrite on the whole page shell
	if (stripos($content, 'noindex') !== false)
	{
		$content = preg_replace('/<\s*noindex\s*>/i', '<!--noindex-->', $content);
		$content = preg_replace('/<\s*\/\s*noindex\s*>/i', '<!--/noindex-->', $content);
	}

	if (stripos($content, '<img') !== false)
	{
		$content = preg_replace_callback(
			'/<img\b[^>]*>/i',
			static function ($m) {
				$tag = $m[0];
				$tag = preg_replace('#alt="([^"]*?)\s+src="#i', 'alt="$1" src="', $tag);
				$tag = preg_replace("#alt='([^']*?)\s+src='#i", "alt='$1' src='", $tag);
				$tag = preg_replace('#;class="\s*loading="#i', '" loading="', $tag);
				$tag = preg_replace('#;(class|loading)="#i', '" $1="', $tag);
				return $tag;
			},
			$content
		);
	}
}

function gnkmedDisableCompositeForForms()
{
	if ((!empty($_REQUEST['success']) && is_string($_REQUEST['success']))
		|| (!empty($_POST['submit']) && $_SERVER['REQUEST_METHOD'] === 'POST'))
	{
		if (!defined('BX_COMPOSITE_DISABLED'))
		{
			define('BX_COMPOSITE_DISABLED', true);
		}
	}
}

function gnkmedDisableCaptcha()
{
	if (COption::GetOptionString("main", "captcha_registration", "N") !== "N")
	{
		COption::SetOptionString("main", "captcha_registration", "N");
	}
	if (COption::GetOptionString("main", "captcha_restoring_password", "N") !== "N")
	{
		COption::SetOptionString("main", "captcha_restoring_password", "N");
	}

	if (COption::GetOptionString("main", "gnkmed_forum_captcha_disabled", "N") !== "Y")
	{
		if (CModule::IncludeModule("forum"))
		{
			$forumRes = CForumNew::GetList(array(), array());
			while ($forum = $forumRes->Fetch())
			{
				if ($forum["USE_CAPTCHA"] === "Y")
				{
					CForumNew::Update($forum["ID"], array("USE_CAPTCHA" => "N"));
				}
			}
		}
		COption::SetOptionString("main", "gnkmed_forum_captcha_disabled", "Y");
	}

	$kernelSession = \Bitrix\Main\Application::getInstance()->getKernelSession();
	unset($kernelSession["BX_LOGIN_NEED_CAPTCHA"]);
}

function gnkmedDisableLoginCaptcha(&$arParams)
{
	if (empty($arParams["LOGIN"]))
	{
		return;
	}

	$kernelSession = \Bitrix\Main\Application::getInstance()->getKernelSession();
	unset($kernelSession["BX_LOGIN_NEED_CAPTCHA"]);

	$rsUser = CUser::GetList(
		"LOGIN",
		"DESC",
		array(
			"LOGIN_EQUAL_EXACT" => $arParams["LOGIN"],
			"EXTERNAL_AUTH_ID" => "",
		),
		array("FIELDS" => array("ID", "LOGIN_ATTEMPTS"))
	);
	if ($arUser = $rsUser->Fetch() && (int)$arUser["LOGIN_ATTEMPTS"] > 0)
	{
		$user = new CUser();
		$user->Update($arUser["ID"], array("LOGIN_ATTEMPTS" => 0));
	}
}

AddEventHandler("main", "OnBeforeEndBufferContent", "OnBeforeEndBufferContent", 100500);
function OnBeforeEndBufferContent()
{
            $arPanelButtons = &$GLOBALS['APPLICATION']->arPanelButtons;
            foreach ($arPanelButtons as &$arItemPanel) {
                if ($arItemPanel['ICON'] == 'bx-panel-site-template-icon') {

                    if (isset($arItemPanel['MENU']) && is_array($arItemPanel['MENU'])) {

                        $arItemPanel['MENU'][] = array(
                            'TEXT' => "Цветовые схемы",
                            'MENU' => array(
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_1')", 'TEXT' => "schemes_1"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_2')", 'TEXT' => "schemes_2"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_3')", 'TEXT' => "schemes_3"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_4')", 'TEXT' => "schemes_4"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_5')", 'TEXT' => "schemes_5"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_6')", 'TEXT' => "schemes_6"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_7')", 'TEXT' => "schemes_7"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_8')", 'TEXT' => "schemes_8"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_9')", 'TEXT' => "schemes_9"),
                                array('ACTION' => "jsUtils.Redirect([], '/?THEME=schemes_10')", 'TEXT' => "schemes_10"),
                            ),
                        );
                    }
                }
            }
}
if($_REQUEST['THEME']){
    COption::SetOptionString("main","color_theme",$_REQUEST['THEME']);
}





function priceDiscount($id){
    global $USER;
    $ar_res_price = CCatalogProduct::GetOptimalPrice($id, 1, $USER->GetUserGroupArray(), 'N');
	$ar_res_price['DISCOUNT_PRICE'] = ($ar_res_price['DISCOUNT_PRICE']) ? CurrencyFormat($ar_res_price['DISCOUNT_PRICE'], $ar_res_price["RESULT_PRICE"]["CURRENCY"]) : "Цена по запросу";
	
	return $ar_res_price;
}

/**
 * Fix common invalid markup in iblock HTML (noindex, duplicate attrs, stray quotes).
 */
function gnkmedSanitizeHtmlMarkup($html)
{
	$html = (string)$html;
	if ($html === '')
	{
		return $html;
	}

	if (stripos($html, 'noindex') !== false)
	{
		$html = preg_replace('/<\s*noindex\s*>/i', '<!--noindex-->', $html);
		$html = preg_replace('/<\s*\/\s*noindex\s*>/i', '<!--/noindex-->', $html);
	}

	// Repair broken <img> attributes from visual editor paste
	if (stripos($html, '<img') !== false)
	{
		$html = preg_replace_callback(
			'/<img\b[^>]*>/iu',
			static function ($m) {
				$tag = $m[0];
				$tag = preg_replace('#alt="([^"]*?)\s+src="#iu', 'alt="$1" src="', $tag);
				$tag = preg_replace("#alt='([^']*?)\s+src='#iu", "alt='$1' src='", $tag);
				$tag = preg_replace('#;class="\s*loading="#i', '" loading="', $tag);
				$tag = preg_replace('#;(class|loading)="#i', '" $1="', $tag);
				return $tag;
			},
			$html
		);
	}

	// Floating quote between attributes: src="..." " align="right"
	// Do NOT match the legitimate closing quote of values that end with a space: alt="text " src=
	$html = preg_replace('/(?<=["\'])\s+"\s+(?=[a-zA-Z_:][-a-zA-Z0-9_:.]*\s*=)/', ' ', $html);
	$html = preg_replace('/(?<=["\'])\s+"\s*>/', '>', $html);

	// <br> cannot be a direct child of <ul>/<ol>
	$html = preg_replace('/(<(?:ul|ol)\b[^>]*>)\s*(?:<br\s*\/?>\s*)+/i', '$1', $html);
	$html = preg_replace('/(?:<br\s*\/?>\s*)+(<\/(?:ul|ol)>)/i', '$1', $html);
	$html = preg_replace('/<\/li>\s*<br\s*\/?>\s*(?=<li\b)/i', '</li>', $html);

	// Drop obsolete align on lists
	$html = preg_replace('/<(ul|ol)\b([^>]*?)\salign\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '<$1$2', $html);

	// Duplicate alt on <img> — keep the first
	if (stripos($html, '<img') !== false)
	{
		$html = preg_replace_callback(
			'/<img\b([^>]*)>/i',
			static function ($m) {
				$attrs = $m[1];
				if (preg_match_all('/\salt\s*=\s*("|\')(.*?)\1/i', $attrs) < 2
					&& preg_match_all('/\salt\s*=\s*[^\s>]+/i', $attrs) < 2)
				{
					return '<img' . $attrs . '>';
				}

				$seen = false;
				$attrs = preg_replace_callback(
					'/\s+alt\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i',
					static function ($a) use (&$seen) {
						if ($seen)
						{
							return '';
						}
						$seen = true;
						return $a[0];
					},
					$attrs
				);

				return '<img' . $attrs . '>';
			},
			$html
		);
	}

	// Orphan </p> and empty <p></p>
	$html = preg_replace('#<p>\s*</p>#i', '', $html);
	$html = gnkmedDropOrphanCloseTags($html, 'p');
	$html = gnkmedDropOrphanCloseTags($html, 'span');

	// Close unclosed list tags at end of fragment
	$html = gnkmedCloseUnclosedLists($html);

	// Close unclosed <span> before block-level tags (li/ul/h2/div/…)
	$html = gnkmedCloseSpansBeforeBlocks($html);

	return $html;
}

/**
 * Append missing </li>/</ul>/</ol> for content fragments from the visual editor.
 */
function gnkmedCloseUnclosedLists($html)
{
	if ($html === '' || (stripos($html, '<ul') === false && stripos($html, '<ol') === false))
	{
		return $html;
	}

	$stack = [];
	if (!preg_match_all('/<\/?(?:ul|ol|li)\b[^>]*>/i', $html, $matches, PREG_SET_ORDER))
	{
		return $html;
	}

	foreach ($matches as $m)
	{
		$tag = strtolower($m[0]);
		$isClose = isset($tag[1]) && $tag[1] === '/';
		if (preg_match('/<\/?(ul|ol|li)\b/i', $m[0], $tm))
		{
			$name = strtolower($tm[1]);
		}
		else
		{
			continue;
		}

		if ($isClose)
		{
			// pop until matching open
			for ($i = count($stack) - 1; $i >= 0; $i--)
			{
				if ($stack[$i] === $name)
				{
					array_splice($stack, $i, 1);
					break;
				}
			}
		}
		else
		{
			$stack[] = $name;
		}
	}

	if (!$stack)
	{
		return $html;
	}

	$closeMap = ['li' => '</li>', 'ul' => '</ul>', 'ol' => '</ol>'];
	$suffix = '';
	for ($i = count($stack) - 1; $i >= 0; $i--)
	{
		$suffix .= $closeMap[$stack[$i]] ?? '';
	}

	return $html . $suffix;
}

/**
 * Remove closing tags that have no matching open tag.
 */
function gnkmedDropOrphanCloseTags($html, $tag)
{
	$tag = preg_quote($tag, '/');
	$depth = 0;

	return (string)preg_replace_callback(
		'/<(\/?)' . $tag . '\b[^>]*>/i',
		static function ($m) use (&$depth) {
			$isClose = ($m[1] === '/');
			if ($isClose)
			{
				if ($depth <= 0)
				{
					return '';
				}
				$depth--;
				return $m[0];
			}

			$depth++;
			return $m[0];
		},
		$html
	);
}

/**
 * Insert </span> before block tags while a span is still open.
 */
function gnkmedCloseSpansBeforeBlocks($html)
{
	$spanDepth = 0;
	$pattern = '/<\/?span\b[^>]*>|<\/?(?:ul|ol|li|h[1-6]|div|table|thead|tbody|tr|td|th|p|section|article|header|footer|nav|aside)\b[^>]*>/i';

	return (string)preg_replace_callback(
		$pattern,
		static function ($m) use (&$spanDepth) {
			$tag = $m[0];

			if (preg_match('/^<span\b/i', $tag))
			{
				$spanDepth++;
				return $tag;
			}

			if (preg_match('/^<\/span>/i', $tag))
			{
				if ($spanDepth > 0)
				{
					$spanDepth--;
				}
				return $tag;
			}

			$prefix = '';
			while ($spanDepth > 0)
			{
				$prefix .= '</span>';
				$spanDepth--;
			}

			return $prefix . $tag;
		},
		$html
	);
}

/**
 * Fix DETAIL_TEXT heading outline after page H1: no level skips in document order.
 * Sibling headings with the same original level stay siblings (H2→H4×N → H2→H3×N).
 */
function gnkmedNormalizeDetailHeadings($html)
{
	$html = gnkmedSanitizeHtmlMarkup((string)$html);
	if ($html === '' || !preg_match('/<h[1-6]\b/i', $html))
	{
		return $html;
	}

	$stack = [['orig' => 0, 'out' => 1]]; // implied page H1
	$closeLevels = [];

	return (string)preg_replace_callback(
		'/<\/?h([1-6])(\s[^>]*)?>/i',
		static function ($m) use (&$stack, &$closeLevels) {
			$level = (int)$m[1];
			$isClose = (isset($m[0][1]) && $m[0][1] === '/');

			if ($isClose)
			{
				$out = array_pop($closeLevels);
				if ($out === null)
				{
					// Orphan closing heading in content — drop it
					return '';
				}

				return '</h' . $out . '>';
			}

			while ($stack && $stack[count($stack) - 1]['orig'] >= $level)
			{
				array_pop($stack);
			}
			if (!$stack)
			{
				$stack[] = ['orig' => 0, 'out' => 1];
			}

			$parentOut = (int)$stack[count($stack) - 1]['out'];
			$out = min($level, $parentOut + 1);
			if ($out < 1)
			{
				$out = 1;
			}
			elseif ($out > 6)
			{
				$out = 6;
			}

			$stack[] = ['orig' => $level, 'out' => $out];
			$closeLevels[] = $out;

			$attrs = isset($m[2]) ? $m[2] : '';

			return '<h' . $out . $attrs . '>';
		},
		$html
	);
}

function EditData ($DATA){
    $MES = array(
        "01" => "Января",
        "02" => "Февраля",
        "03" => "Марта",
        "04" => "Апреля",
        "05" => "Мая",
        "06" => "Июня",
        "07" => "Июля",
        "08" => "Августа",
        "09" => "Сентября",
        "10" => "Октября",
        "11" => "Ноября",
        "12" => "Декабря"
    );

    $arData = explode(".", $DATA);
    $d = ($arData[0] < 10) ? substr($arData[0], 1) : $arData[0];
    $newData = $d." ".$MES[$arData[1]]." ".$arData[2];
    return $newData;
}


AddEventHandler("sale", "OnOrderNewSendEmail", "bxModifySaleMails");

function bxModifySaleMails($orderID, &$eventName, &$arFields)
{
  $arOrder = CSaleOrder::GetByID($orderID);
  $order_props = CSaleOrderPropsValue::GetOrderProps($orderID);

  $phone="";
  $delivery="";
  while ($arProps = $order_props->Fetch())
  {
    if ($arProps["CODE"] == "PHONE")
    {
       $phone = htmlspecialchars($arProps["VALUE"]);
    }
    if ($arProps["CODE"] == "ADDRESS")
    {
        $delivery = htmlspecialchars($arProps["VALUE"]);
    }
  }

  if(CModule::IncludeModule("sale") && CModule::IncludeModule("iblock"))
    {
        $strOrderList = "";
        $dbBasketItems = CSaleBasket::GetList(
            array("NAME" => "ASC"),
            array("ORDER_ID" => $orderID),
            false,
            false,
            array("PRODUCT_ID", "ID", "NAME", "QUANTITY", "PRICE", "CURRENCY")
        );
        while ($arProps = $dbBasketItems->Fetch())
        {
            $db_res_props = CSaleBasket::GetPropsList(array(),array("BASKET_ID" => $arProps['ID'],"CODE" => "CML2_ARTICLE"));
            if ($ar_res_props = $db_res_props->Fetch())
            {
                $arProps['ARTICLE'] = trim($ar_res_props['VALUE']);
            }else
                unset($arProps['ARTICLE']);

            $strOrderList .= "<tr><td style='text-align: left;padding: 5px 0;'>".$arProps['NAME']." (".$arProps['ARTICLE'].")</td><td style='padding: 5px 10px;'>".$arProps['QUANTITY']."</td><td style='padding: 5px 0;'>".CurrencyFormat($arProps['PRICE'], $arProps['CURRENCY'])."</td><tr>";
        }
    $arFields["ORDER_LIST_TABLE"] = $strOrderList;
  }

  $arFields["PHONE"] =  $phone;
  $arFields["DELIVERY"] =  $delivery;
  $arFields["USER_DESCRIPTION"] =  $arOrder['USER_DESCRIPTION'];
  if($_COOKIE['roistat_visit'])
    $arFields["ROI_VISIT"] = $_COOKIE['roistat_visit'];
}

AddEventHandler("sale", "OnOrderStatusSendEmail", "bxModifySaleStatusSendEmail");
function bxModifySaleStatusSendEmail($orderID, &$eventName, &$arFields, $status){

    $arOrder = CSaleOrder::GetByID($orderID);
    $order_props = CSaleOrderPropsValue::GetOrderProps($orderID);

    $phone="";
    $delivery="";
    while ($arProps = $order_props->Fetch())
    {
        if ($arProps["CODE"] == "PHONE")
        {
            $phone = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "ADDRESS")
        {
            $delivery = htmlspecialchars($arProps["VALUE"]);
        }
    }

    if(CModule::IncludeModule("sale") && CModule::IncludeModule("iblock"))
    {
        $strOrderList = "";
        $dbBasketItems = CSaleBasket::GetList(
            array("NAME" => "ASC"),
            array("ORDER_ID" => $orderID),
            false,
            false,
            array("PRODUCT_ID", "ID", "NAME", "QUANTITY", "PRICE", "CURRENCY")
        );
        $sum = 0;
        while ($arProps = $dbBasketItems->Fetch())
        {
            $db_res_props = CSaleBasket::GetPropsList(array(),array("BASKET_ID" => $arProps['ID'],"CODE" => "CML2_ARTICLE"));
            if ($ar_res_props = $db_res_props->Fetch())
            {
                $arProps['ARTICLE'] = trim($ar_res_props['VALUE']);
            }else
                unset($arProps['ARTICLE']);

            $sum += $arProps['PRICE']*$arProps['QUANTITY'];
            $strOrderList .= "<tr><td style='text-align: left;padding: 5px 0;'>".$arProps['NAME']." (".$arProps['ARTICLE'].")</td><td style='padding: 5px 10px;'>".$arProps['QUANTITY']."</td><td style='padding: 5px 0;'>".CurrencyFormat($arProps['PRICE'], $arProps['CURRENCY'])."</td><tr>";
        }
        $arFields["ORDER_LIST_TABLE"] = $strOrderList;
        $arFields["PRICE"] = CurrencyFormat($sum,"RUB");
    }
    $arFields["PHONE"] =  $phone;
    $arFields["ORDER_USER"] =  $arOrder['USER_NAME'].' '.$arOrder['USER_LAST_NAME'];
    $arFields["DELIVERY"] =  $delivery;
}

AddEventHandler("sale", "OnOrderPaySendEmail", "bxModifySaleStatusPaySendEmail");
function bxModifySaleStatusPaySendEmail($orderID, &$eventName, &$arFields){

    $arOrder = CSaleOrder::GetByID($orderID);
    $order_props = CSaleOrderPropsValue::GetOrderProps($orderID);

    $phone="";
    $delivery="";
    while ($arProps = $order_props->Fetch())
    {
        if ($arProps["CODE"] == "PHONE")
        {
            $phone = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "ADDRESS")
        {
            $delivery = htmlspecialchars($arProps["VALUE"]);
        }
    }

    if(CModule::IncludeModule("sale") && CModule::IncludeModule("iblock"))
    {
        $strOrderList = "";
        $dbBasketItems = CSaleBasket::GetList(
            array("NAME" => "ASC"),
            array("ORDER_ID" => $orderID),
            false,
            false,
            array("PRODUCT_ID", "ID", "NAME", "QUANTITY", "PRICE", "CURRENCY")
        );
        $sum = 0;
        while ($arProps = $dbBasketItems->Fetch())
        {
            $db_res_props = CSaleBasket::GetPropsList(array(),array("BASKET_ID" => $arProps['ID'],"CODE" => "CML2_ARTICLE"));
            if ($ar_res_props = $db_res_props->Fetch())
            {
                $arProps['ARTICLE'] = trim($ar_res_props['VALUE']);
            }else
                unset($arProps['ARTICLE']);

            $sum += $arProps['PRICE']*$arProps['QUANTITY'];
            $strOrderList .= "<tr><td style='text-align: left;padding: 5px 0;'>".$arProps['NAME']." (".$arProps['ARTICLE'].")</td><td style='padding: 5px 10px;'>".$arProps['QUANTITY']."</td><td style='padding: 5px 0;'>".CurrencyFormat($arProps['PRICE'], $arProps['CURRENCY'])."</td><tr>";
        }
        $arFields["ORDER_LIST_TABLE"] = $strOrderList;
        $arFields["PRICE"] = CurrencyFormat($sum,"RUB");
    }
    $arFields["PHONE"] =  $phone;
    $arFields["ORDER_USER"] =  $arOrder['USER_NAME'].' '.$arOrder['USER_LAST_NAME'];
    $arFields["DELIVERY"] =  $delivery;
}

AddEventHandler('main', 'OnBeforeEventAdd', array('MyClassTrack', 'OnTrack'));

class MyClassTrack
{
    static function OnTrack(&$event, &$lid, &$arFields, &$message_id) {
        if ($event == 'SALE_ORDER_TRACKING_NUMBER') {
            $orderID = $arFields['ORDER_REAL_ID'];
            $arOrder = CSaleOrder::GetByID($orderID);
            $order_props = CSaleOrderPropsValue::GetOrderProps($orderID);

            $phone="";
            $delivery="";
            while ($arProps = $order_props->Fetch())
            {
                if ($arProps["CODE"] == "PHONE")
                {
                    $phone = htmlspecialchars($arProps["VALUE"]);
                }
                if ($arProps["CODE"] == "ADDRESS")
                {
                    $delivery = htmlspecialchars($arProps["VALUE"]);
                }
            }

            if(CModule::IncludeModule("sale") && CModule::IncludeModule("iblock"))
            {
                $strOrderList = "";
                $dbBasketItems = CSaleBasket::GetList(
                    array("NAME" => "ASC"),
                    array("ORDER_ID" => $orderID),
                    false,
                    false,
                    array("PRODUCT_ID", "ID", "NAME", "QUANTITY", "PRICE", "CURRENCY")
                );
                $sum = 0;
                while ($arProps = $dbBasketItems->Fetch())
                {
                    $db_res_props = CSaleBasket::GetPropsList(array(),array("BASKET_ID" => $arProps['ID'],"CODE" => "CML2_ARTICLE"));
                    if ($ar_res_props = $db_res_props->Fetch())
                    {
                        $arProps['ARTICLE'] = trim($ar_res_props['VALUE']);
                    }else
                        unset($arProps['ARTICLE']);

                    $sum += $arProps['PRICE']*$arProps['QUANTITY'];
                    $strOrderList .= "<tr><td style='text-align: left;padding: 5px 0;'>".$arProps['NAME']." (".$arProps['ARTICLE'].")</td><td style='padding: 5px 10px;'>".$arProps['QUANTITY']."</td><td style='padding: 5px 0;'>".CurrencyFormat($arProps['PRICE'], $arProps['CURRENCY'])."</td><tr>";
                }
                $arFields["ORDER_LIST_TABLE"] = $strOrderList;
                $arFields["PRICE"] = CurrencyFormat($sum,"RUB");
            }
            $arFields["PHONE"] =  $phone;
            $arFields["ORDER_USER"] =  $arOrder['USER_NAME'].' '.$arOrder['USER_LAST_NAME'];
            $arFields["DELIVERY"] =  $delivery;
        }
    }
}

// регистрируем обработчик
AddEventHandler("search", "BeforeIndex", "BeforeIndexHandler");
// создаем обработчик события "BeforeIndex"
function BeforeIndexHandler($arFields)
{

    if(!CModule::IncludeModule("iblock")) // подключаем модуль
        return $arFields;
    if($arFields["MODULE_ID"] == "iblock")
    {
        $VALUES = [];
        $db_props = CIBlockElement::GetProperty(                        // Запросим свойства индексируемого элемента
            $arFields["PARAM2"],         // BLOCK_ID индексируемого свойства
            $arFields["ITEM_ID"],          // ID индексируемого свойства
            array("sort" => "asc"),       // Сортировка (можно упустить)
            Array("CODE" => "ARTICLS")); // CODE свойства (в данном случае артикул)
        while ($ar_props = $db_props->Fetch())
            $VALUES[] = $ar_props['VALUE'];

        if(is_array($VALUES) && count($VALUES) > 0)
        $arFields["TITLE"] .= " Артикул: ".implode(', ',$VALUES);   // Добавим свойство в конец заголовка индексируемого элемента
    }

    return $arFields; // вернём изменения
}
