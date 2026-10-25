"use strict";
$(function() {
    function refreshLightSliders(selector) {
        $(selector).each(function() {
            var slider = $(this).data("lightSlider");
            if (slider && typeof slider.refresh === "function") {
                slider.refresh();
            }
        });
    }

    function debounce(fn, wait) {
        var timer;
        return function() {
            var context = this,
                args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() {
                fn.apply(context, args);
            }, wait);
        };
    }

    $(".slider__wrapper").lightSlider({
        adaptiveHeight: !1,
        item: 1,
        auto:true,
        loop:true,
        pause:5000,
        slideMargin: 0,
        enableDrag: false,
        prevHtml: '<i class="icon-arrow_prev"></i>',
        nextHtml: '<i class="icon-arrow_next"></i>'
    }), $(".full-slider__wrapper").lightSlider({
        adaptiveHeight: !0,
        item: 1,
        slideMargin: 10,
        pager: !1,
        enableDrag: !1,
        prevHtml: '<i class="icon-arrow_prev"></i>',
        nextHtml: '<i class="icon-arrow_next"></i>',
        responsive: [{
            breakpoint: 768,
            settings: {
                item: 1,
                slideMargin: 0
            }
        }]
    }), $(".goods__slider").lightSlider({
        adaptiveHeight: !1,
        item: 4,
        loop:true,
        slideMargin: 0,
        pager: !1,
        enableDrag: !1,
        prevHtml: '<i class="icon-arrow_prev"></i>',
        nextHtml: '<i class="icon-arrow_next"></i>',
        responsive: [{
            breakpoint: 991,
            settings: {
                item: 3
            }
        }, {
            breakpoint: 768,
            settings: {
                item: 2
            }
        }, {
            breakpoint: 480,
            settings: {
                item: 1
            }
        }]
    }), $(".cart__slider").lightSlider({
        prevHtml: '<i class="icon-arrow_prev"></i>',
        nextHtml: '<i class="icon-arrow_next"></i>',
        gallery: true,
        enableDrag: false,
        item: 1,
        thumbItem: 5,
        thumbMargin: 10,
        slideMargin: 0,
        onSliderLoad: function(el) {
            el.lightGallery({
                selector: '.cart__slider .lslide',
            });
        },
        responsive: [{
            breakpoint: 991,
            settings: {
                thumbMargin: 8,
                thumbItem: 3
            }
        }, {
            breakpoint: 768,
            settings: {
                thumbItem: 4
            }
        }]
    }), $(".iselect").select2(), $(".catalog__submenu_toggle").click(function(e) {
        e.preventDefault(), $(this).toggleClass("active").siblings(".catalog__submenu").stop().slideToggle()
    }), $(".goods__counter_add").click(function(e) {
        e.preventDefault();
        var t = $(this),
            i = t.siblings(".goods__counter_input"),
            a = i.val();
        console.log(a), i.val(++a)
    }), $(".goods__counter_subtract").click(function(e) {
        e.preventDefault();
        var t = $(this),
            i = t.siblings(".goods__counter_input"),
            a = i.val();
        a > 1 && i.val(--a)
    }), $(".toggle__menu").click(function() {
        // Class toggle — slideToggle breaks on flex .nav at 768–900px (stuck height)
        return $(this).toggleClass("on"), !1
    }), $(".header__contact_mobile").click(function() {
        $(this).toggleClass("active"), $(".header__contact").toggleClass("active")
    }), $(".tabs").each(function() {
        $(this).find(".tabs__nav").first().addClass("active")
    }), $(".tabs__nav").click(function(e) {
        e.preventDefault(), $(this).addClass("active").siblings(".tabs__nav").removeClass("active")
    });

    $('[data-text]').each(function(i, el){
        let self = $(el);
        self.html(self.data('text'));
    });

    // Recalculate product/full sliders after layout settles (fixes empty slides on ~320px)
    var refreshHomeSliders = function() {
        refreshLightSliders(".goods__slider, .full-slider__wrapper");
    };
    $(window).on("load", function() {
        refreshHomeSliders();
        setTimeout(refreshHomeSliders, 100);
    });
    $(window).on("resize orientationchange", debounce(refreshHomeSliders, 200));
    setTimeout(refreshHomeSliders, 0);
});
