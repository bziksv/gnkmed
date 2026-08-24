$('input[name="PHONE"]').mask('+7 (999) 999 99 99');
$('input[autocomplete="tel"]').mask('+7 (999) 999 99 99');

$('.cart__content .cart__radio').change(function(){
    $('.cart__content > p.article').text("Арт: " + $(this).val());
    $('.cart__content > .cart__price').text($(this).attr('data-price'));
    $('.cart__content > .cart_old_price').text($(this).attr('data-old-price'));
});

$('.cart__content > p.article').text("Арт: " + $('.cart__content .cart__radio:checked').val());

if($('.cart__content .cart__radio:checked').attr('data-price') != 'Цена 0 ₽'){
		$('.cart__content > .cart__price').text($('.cart__content .cart__radio:checked').attr('data-price'));
}

$('.cart__content > .cart_old_price').text($('.cart__content .cart__radio:checked').attr('data-old-price'));


$('.callback-btn').click(function(){
    $('#callback').bPopup({
        zIndex:1000
    });
});



var path = "/bitrix/templates/medical-templates/ajax/";

function replaseBasketTop() {
    $.ajax({
        url: path + 'basket.php',
        type: 'get',
        success: function (data) {
            $('.header__basket').replaceWith(data);
        }
    })
}

function replaseBasketMobileTop() {
    $.ajax({
        url: path + 'basket.mobile.php',
        type: 'get',
        success: function (data) {
            $('.header__basket_mobile').replaceWith(data);
        }
    })
}


function addToBasket2(idel, quantity,el) {

    $art = $(el).closest('.cart__content').find('.cart__radio:checked').val();
    if(!$art)
        $art = $(el).closest('.goods__item').find('input[name="article"]').val();

    $color = $.trim($(el).closest('.cart__content').find('.cart__radio:checked').parent().text());
    if(!$color)
        $color = $(el).closest('.goods__item').find('input[name="color"]').val();

    if($color == undefined)
        $color = 0;

    $href = path + "add.php?id=" + idel + '&quantity=' + quantity + '&art=' + $art + '&color=' + $color;
    $.ajax({
        url: $href,
        type: 'get',
        success: function (data) {
            console.log(data);
            if (data == 'Товар успешно добавлен в корзину') {
                replaseBasketTop();
                replaseBasketMobileTop();
                alertify.success(data);
            } else {
                alertify.error(data);
            }
        }
    });
    return false;
}


$( function() {
    $( ".cart__price.tooltip,.goods__price.tooltip" ).tooltip({
        show: null,
        content: "<noindex>Цена зависит от комплектации прибора и/или наличия на складе. Для уточнения стоимости необходимо отправить запрос по электронной почте (запросить КП),  либо оформить заказ на сайте  и менеджер сам вам перезвонит. Если указанная цена вас не устроит, Вы можете отказаться от товара до момента его оплаты.</noindex>",
        items: "div[class]",
        position: {
            my: "left top",
            at: "left bottom"
        },
        open: function( event, ui ) {
            ui.tooltip.animate({ top: ui.tooltip.position().top + 10 }, "fast" );
        }
    });
} );

window.getRoistatVisitId = function() {
	if (window.roistat && window.roistat.visit) {
		return String(window.roistat.visit);
	}

	if (typeof window.roistatGetCookie === 'function') {
		var cookieVisit = window.roistatGetCookie('roistat_visit');
		if (cookieVisit) {
			return cookieVisit;
		}
	}

	var match = document.cookie.match(/(?:^|;\s*)roistat_visit=([^;]*)/);
	return match ? decodeURIComponent(match[1]) : '';
};

window.applyRoistatEmailSubstitution = function(visitId) {
	if (!visitId) {
		visitId = window.getRoistatVisitId();
	}

	if (!visitId)
		return;

	var host = window.location.hostname;
	if (host === '127.0.0.1' || host === 'localhost') {
		host = 'gnkmed.ru';
	}

	var mail = visitId + '@' + host;
	$('.roi_visit').each(function() {
		$(this).text(mail).attr('href', 'mailto:' + mail);
	});
};

window.roistatVisitCallback = function(visitId) {
	window.applyRoistatEmailSubstitution(visitId);
};

window.onRoistatModuleLoaded = function() {
	window.applyRoistatEmailSubstitution();
};

$(function() {
	[500, 1500, 3000].forEach(function(delay) {
		setTimeout(function() {
			window.applyRoistatEmailSubstitution();
		}, delay);
	});

	window.ensureBitrixSessid = function($form) {
		var $sessid = $form.find('input[name="sessid"]');
		if (!$sessid.length || !$sessid.val()) {
			if (typeof BX !== 'undefined' && BX.bitrix_sessid) {
				if (!$sessid.length) {
					$form.append('<input type="hidden" name="sessid" value="">');
					$sessid = $form.find('input[name="sessid"]');
				}
				$sessid.val(BX.bitrix_sessid());
			}
		}
	};

	window.validateCallbackConsent = function() {
		var $checkbox = $('#callback-consent');
		var $error = $('#callback .mf-consent-error');

		if (!$checkbox.length) {
			return true;
		}

		if (!$checkbox.is(':checked')) {
			$error.show();
			return false;
		}

		$error.hide();
		return true;
	};

	window.handleCallbackFormResult = function() {
		var $callback = $('#callback');
		if (!$callback.length) {
			return;
		}

		var paramsHash = String($callback.data('params-hash') || '');
		var urlParams = new URLSearchParams(window.location.search);
		var success = urlParams.get('success');

		if (success && paramsHash && success === paramsHash) {
			alertify.success('Спасибо, ваше сообщение принято.');
			$callback.bPopup({ zIndex: 1000 });
		} else if ($callback.find('.errortext').length) {
			var errorText = $.trim($callback.find('.errortext').first().text());
			alertify.error(errorText || 'Ошибка отправки формы');
			$callback.bPopup({ zIndex: 1000 });
		} else {
			return;
		}

		if (success) {
			urlParams.delete('success');
			var query = urlParams.toString();
			var newUrl = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
			history.replaceState({}, '', newUrl);
		}
	};

	$(document).on('change', '#callback-consent', function() {
		$('#callback .mf-consent-error').hide();
	});

	$(document).on('submit', '#callback form', function(e) {
		var $form = $(this);
		window.ensureBitrixSessid($form);

		if (!window.validateCallbackConsent()) {
			e.preventDefault();
			return false;
		}
	});

	window.handleCallbackFormResult();
});