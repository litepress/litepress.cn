var $ = jQuery.noConflict();
/*排序*/
$(".plugin-search input[type=search]").attr("autocomplete", "off");
$(function () {
    $(".f-sort a").each(function () {
        var url = Url.queryString("orderby");
        var rel = $(this).attr('rel');
        if (url === rel) {
            $(this).addClass("curr").siblings('a').removeClass('curr');
        } else if (url === undefined) {
            $(".f-sort a:first").addClass("curr");
        }
        $(this).on('click', function () {
            $(this).addClass("curr").siblings('a').removeClass('curr');
        })
    });


    jQuery('#hide-help-notice').click(function () {
        jQuery.ajax({url: '/getting-started/hide-notice/'});
        jQuery('#help-notice').fadeOut(1000);
        return false;
    });
    $(".user-rating a").click(function () {
        $(".reviews_tab").addClass("active").siblings("li").removeClass("active");
        $(".woocommerce-Tabs-panel--reviews").show().siblings(".wc-tab").hide();
        return 0;
    })


    $(".gallery-top img,.entry-title img").each(function () {
        let src = $(this).attr("src");
        let size_medium = $(this).hasClass("size-medium");
        if (size_medium) {
            before = '-' + src.split("-").splice(-1, 1).join("");
            after = '.' + src.split(".").splice(-1, 1).join("");
            unzip = src.replace(before, after);
            $(this).wrap("<a class=\"item\" href='" + unzip + "'></a>")
        } else {
            $(this).wrap("<a class=\"item\" href='" + src + "' ></a>")
        }
    });
    lightGallery(document.getElementById('primary'), {

        selector: '.item',

    });


});

