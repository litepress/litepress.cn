var $ = jQuery.noConflict();
$(".bbp_dropdown").addClass("form-select");
$(function () {
    $(".dropdown-divider + .bbp-body .bbp-reply-content.heti").before("<section class=\"section-toc wp-card\"><header class=\"d-flex align-items-center\">\n" +
        "        <div class=\"me-2 wp-icon\">\n" +
        "            <i class=\"fas fa-clipboard-list-check fa-fw\" style=\"\"></i></div>\n" +
        "        <span>文章目录</span></header><nav class=\"js-toc\"></nav></section>")
$(".heti h2").each(function () {
    let text = $(this).text();
    $(this).attr("id", text);
});
    if ($(".js-toc").length > 0) {
        tocbot.init({
            // Where to render the table of contents.
            tocSelector: '.js-toc',
            // Where to grab the headings to build the table of contents.
            contentSelector: '.bbp-reply-content.heti ',
            // Which headings to grab inside of the contentSelector element.
            headingSelector: 'h1, h2, h3',
            // For headings inside relative or absolute positioned containers within content.
            hasInnerContainers: true,
            scrollSmooth: true,
            scrollSmoothOffset: -90,
            headingsOffset: 150
        });
    }
    $(".bbpress .js-toc").each(function () {
    if ($(this).is(":empty") || $(".toc-list-item").length < 3 ) {
        $(this).parent().remove();
    }
    });
});
$(".operation-sticker .menu > a").click(function () {
    $(this).toggleClass("active");
});
$(".wp-favorite").click(function () {
    const strUrl = location.href;
    const arrUrl = strUrl.split("/");
    const id = arrUrl[arrUrl.length - 1];

    $.ajax({
        url: location.href + "?bbp-ajax=true",
        type: 'post',
        data: {
            "action": "favorite",
            "id": id,
            "type": "post",
            "nonce": $(".favorite-toggle").attr("data-bbp-nonce")
        },
        success: function (success) {
            console.log(success);
            const length = $(success.content).find("a").text();
            if (success.success === false) {
                alert(success.content);
                $('.wp-favorite').removeClass("active");
            }
            if ('取消收藏' === length) {
                $('.wp-favorite span').html("已收藏");
            } else {
                $('.wp-favorite span').html("收藏");
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
});

if ('取消收藏' === $('#favorite-toggle a').html()) {
    $('.wp-favorite').addClass("active");
    $('.wp-favorite span').html("已收藏");
} else {
    $('.wp-favorite').removeClass("active");
    $('.wp-favorite span').html("收藏");
}

$("#bbp_forum_id option:nth-of-type(2)").prop("selected", 'selected');


$(".wp-subscribe").click(function () {
    const strUrl = location.href;
    const arrUrl = strUrl.split("/");
    const id = arrUrl[arrUrl.length - 1];

    $.ajax({
        url: location.href + "?bbp-ajax=true",
        type: 'post',
        data: {
            "action": "subscription",
            "id": id,
            "type": 'post',
            "nonce": $(".subscription-toggle").attr("data-bbp-nonce")
        },
        success: function (success) {
            sc = success.content;
            const reg = /[\u4e00-\u9fa5]+/g;
            let newData = sc.match(reg);
            if ('退订' === newData[0]) {
                $('.wp-subscribe span').html("已关注");
            } else {
                $('.wp-subscribe span').html("关注");
            }
            console.log(success);
            console.log(newData);
        },
        error: function (error) {
            console.log(error);
        }
    });
});

if ('退订' === $('.subscription-toggle').html()) {
    $('.wp-subscribe').addClass("active");
    $('.wp-subscribe span').html("已关注");
} else {
    $('.wp-subscribe').removeClass("active");
    $('.wp-subscribe span').html("关注");
}


const navBar = $(".operation-sticker");
if (navBar.length > 0){
var navToTop = navBar.offset().top - 20
}
$(document).on('scroll', function () {
    const scrollDistance = $(document).scrollTop();
    if (scrollDistance > navToTop) {
        navBar.addClass("fixed");
    } else {
        navBar.removeClass("fixed");
    }
})

const $root = $('html, body');
$('.wp-replies').click(function () {
    const href = $.attr(this, 'href');
    $root.animate({
        scrollTop: $(href).offset().top - 100
    }, 800, function () {
        window.location.hash = href;
    });
    return false;
});
$(".inner-comment-lists").each(function () {
    let height = $(this).height();
    // console.log(height);
    if (height > "500") {
        $(this).addClass("unfold").append("<div class='unfold-btn unfold-bg'><i class=\"fad fa-chevron-circle-down\"></i>展开更多</div>");
    }
});

$('.unfold-btn').click(function () {
    let text = $(this).text();
    $(".inner-comment-lists").toggleClass("unfold");
    $(this).toggleClass("unfold-bg");
    if (text === "展开更多") {
        $(this).html("<i class=\"fad fa-chevron-circle-up\"></i>点击收缩");
    } else {
        $(this).html("<i class=\"fad fa-chevron-circle-down\"></i>展开更多");
    }
});
$(" .bbp-reply-content.heti img").each(function () {
    let src = $(this).attr("src");
    $(this).wrap("<a class=\"item\" href='" + src + "' ></a>")

});
$(function () {
    lightGallery(document.getElementById('lightgallery'), {
        selector: '.item',
    });
});