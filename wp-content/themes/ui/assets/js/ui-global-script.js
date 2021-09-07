var $ = jQuery.noConflict();
$("li.menu-item-has-children > a").attr("data-bs-toggle", "dropdown");
$(".ant-btn").on("change", "input[type='file']", function () {
    var filePath = $(this).val();
    //filePath.indexOf("jpg")!=-1 || filePath.indexOf("png")!=-1
    if (filePath.length > 0) {
        $(".fileerrorTip").html("").hide();
        var arr = filePath.split('\\');
        var fileName = arr[arr.length - 1];
        $(".showFileName").html(fileName);
    } else {
        $(".showFileName").html("");
        $(".fileerrorTip").html("您未上传文件，或者您上传文件类型有误！").show();
        return false
    }
});
const searchinput = $(".header-search input");
$(searchinput).keyup(function () {
    search()
});

function search() {
    var content = "";
    const formData = new FormData();
    var key = $(searchinput).val()
    formData.append("action", "lava_ajax_search_query");
    formData.append("search_term", key);
    $.ajax({
        url: "/wp-admin/admin-ajax.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function (data) {
            $(".header-search").addClass("ajax-loading")
        },
        success: function (data) {
            if (data.length > 0) {
                $.each(data, function (wp, val) {
                    content += val.value;
                    /*console.log(val.value);*/
                });
                $(".header-search").removeClass("ajax-loading");
                $("#showDiv").slideDown().html(content);
            }

            $(document).click(function (e) {
                const $target = $(e.target);
                if (!$target.is('#showDiv *')) {
                    $('#showDiv').slideUp();
                }
            });

        },
        error: function () {
            console.log("error！");
        }
    });
}

$(function () {
    $(".order").before("<tr class='sep-row'></tr>");
    $(".api-manager-domains td").attr('colspan', '5');
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('.tooltip-show').tooltip('show');
    /* $(window).scroll(function(){
         if($(window).scrollTop()>0){
             $(".wp-nav").css({"position":"fixed","top":0,"z-index":"1001","width":"100%"});

         }else{
             $(".wp-nav").css({"position":"static"});
         }
     });*/
});

/*$(".mce-tinymce").ready(function () {
    QTags.addButton('按钮', '按钮', '<a class="btn btn-primary" href="http://修改URL">', '</a>');
    QTags.addButton('加粗', '加粗', '<strong>', '</strong>');
    QTags.addButton('p', 'p', '<p>', '</p>');
    QTags.addButton('hr', 'hr', '<hr>', '');
});*/


hljs.highlightAll();
$(".heti pre").each(function () {
    $(this).wrap("<section class=\"wp-code\"></section>")
});


$(".wp-code pre").after(" <button class=\"btn-clipboard\" " + " style=\"display: none\">复制\n" + "</button>");


$(".wp-code").hover(function () {
    $(this).find(".btn-clipboard").css("display", "block")
}, function () {
    $(this).find(".btn-clipboard").css("display", "none")
});

$(".wp-code code").each(function () {
    $(this).html("<ul><li>" + $(this).html().replace(/\n/g, "\n</li><li>") + "\n</li></ul>");
});
$(function () {
var numLi = $(".wp-code .hljs ul li").length;

for (var i = 0; i < numLi; i++) {
    $(".wp-code .hljs ul li").eq(i).wrap('<div  id="L'+ (i + 1) +'" ></div>');
}
})
const n = $(".btn-clipboard");
n.click(function () {
    $(this).text("已复制");
    var o = this;
    setTimeout(function () {
        $(o).text("复制"),
            window.getSelection().removeAllRanges()
    }, 1500)
});
new ClipboardJS('.wp-code > pre + .btn-clipboard', {
    target: function (trigger) {
        return trigger.previousElementSibling;
    }
});
new ClipboardJS('.enlighter-origin + .btn-clipboard', {
    target: function (trigger) {
        return trigger.previousElementSibling.previousElementSibling;
    }
});
$("#site-header .menu-item").each(function () {
    menu_a = $(this).find("a").attr("href");
    pathname = $(location).attr('pathname');
    if (pathname.indexOf(menu_a) > -1 && pathname !== "/") {
        $(this).addClass("current-menu-item").siblings().removeClass('current-menu-item');
    }
    $(this).on('click', function () {
        $(this).addClass("current-menu-item").siblings().removeClass('current-menu-item');
    })
});

/*$(".um-profile-photo a.um-profile-photo-img img").attr({
    "data-bs-toggle" : "tooltip",
    "data-bs-placement" : "bottom",
    "data-bs-html" : "true",
    "title" : "你可以在<a href=\"https://cravatar.cn/\" target=\"_blank\">Cravatar</a>上更改你的头像"
}).addClass("tooltip-show");*/
//下拉框查询组件点击查询栏时不关闭下拉框
$("body").on('click', '[data-stopPropagation]', function (e) {
    e.stopPropagation();
});

if ($(window).width() > 991) {

    $("#site-header .menu-item-has-children > .nav-link").removeAttr("data-bs-toggle");


} else if ($(window).width() < 991) {

    $("#site-header .menu-item-has-children > .nav-link").attr("data-bs-toggle", "dropdown");
    $("#site-header  .nav-link").attr("data-stopPropagation", "true");
} else {
    $("#site-header .nav-link").removeAttr("data-bs-toggle");
}
$(window).resize(function () {
    if ($(window).width() > 991) {
        $("#site-header .menu-item-has-children > .nav-link").removeAttr("data-bs-toggle");
    } else if ($(window).width() < 991) {
        $("#site-header .menu-item-has-children > .nav-link").attr("data-bs-toggle", "dropdown");
        $("#site-header  .nav-link").attr("data-stopPropagation", "true");
    } else {
        $("#site-header .menu-item-has-children > .nav-link").removeAttr("data-bs-toggle");
    }
});

$(".action.edit").click(function(){
    $(this).parent().parent().next().find(".textareas").addClass("active");
    $(this).parent().parent().next().siblings().find(".textareas").removeClass("active")


})

