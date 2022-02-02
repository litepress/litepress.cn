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


    var projectsearch = $(".search-form input[type=search]");
    var url = $(location).attr('href'); //获取url地址
    var url_noparm = location.protocol + '//' + location.host + location.pathname;
    var url_noparm4 = url_noparm.split("/").splice(0, 4).join("/");
    if (url.indexOf("plugins") >= 0) {
        $(projectsearch).attr("placeholder", "搜索插件……");
    } else if (url.indexOf("docs") >= 0) {
        $(projectsearch).attr("placeholder", "搜索文档……");
    } else if (url.indexOf("themes") >= 0) {
        $(projectsearch).attr("placeholder", "搜索主题……");
    } else if (url.indexOf("wordpress") >= 0) {
        $(projectsearch).attr("placeholder", "搜索WordPress核心……");
    } else if (url.indexOf("mini_programs") >= 0) {
        $(projectsearch).attr("placeholder", "搜索小程序……");
    } else if (url === "price-desc") {
    }
    $("#projects-filter").on("input", function () {

        var projectval = $(projectsearch).val();

        $(projectsearch).keydown(function (event) {
            if (event.keyCode === 13) {
                $(location).prop('href', url_noparm4 + "/?s=" + projectval);
            }
        })

    });

    $(function () {
        var headerval = $(".wp-nav .header-search input").val();
        $(projectsearch).val(headerval);
        $(".wp-nav .header-search input").val("");
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

