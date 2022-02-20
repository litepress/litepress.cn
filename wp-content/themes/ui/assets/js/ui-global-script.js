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


(function ($) {
    $.extend({
        Request: function (m) {
            var sValue = location.search.match(new RegExp("[\?\&]" + m + "=([^\&]*)(\&?)", "i"));
            return sValue ? sValue[1] : sValue;
        },
        UrlUpdateParams: function (url, name, value) {
            var r = url;
            if (r != null && r != 'undefined' && r != "") {
                value = encodeURIComponent(value);
                var reg = new RegExp("(^|)" + name + "=([^&]*)(|$)");
                var tmp = name + "=" + value;
                if (url.match(reg) != null) {
                    r = url.replace(eval(reg), tmp);
                }
                else {
                    if (url.match("[\?]")) {
                        r = url + "&" + tmp;
                    } else {
                        r = url + "?" + tmp;
                    }
                }
            }
            return r;
        }

    });
})(jQuery);

/*搜索词跟随*/
const header_search_val = decodeURIComponent($.Request("keyword"));
$(".search-form  .header-search input").val(header_search_val)






$(function () {
    $(".order").before("<tr class='sep-row'></tr>");
    $(".api-manager-domains td").attr('colspan', '5');
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('.tooltip-show').tooltip('show');
    $("tr.editor").each(function (){
        if($(this).is(':visible')){
            $(this).find(".textareas").addClass("active");
        }
    }) ;   $(".textareas").each(function (){
        $(this).addClass("active");
    })
});




/*代码高亮+代码行数+复制*/
document.querySelectorAll('.heti pre code').forEach(el => {
    // then highlight each
    hljs.highlightElement(el);
});
$(".heti pre").each(function () {
    $(this).wrap("<section class=\"wp-code\"></section>")
});
$(".wp-code code").each(function () {
    $(this).html("<ul><li>" + $(this).html().replace(/\n/g, "\n</li><li>") + "\n</li></ul>");
});
$(function () {
    var numLi = $(".wp-code .hljs ul > li").length;

    for (var i = 0; i < numLi; i++) {
        $(".wp-code .hljs ul > li").eq(i).wrap('<li  id="L'+ (i + 1) +'" ></div>');
    }
})

$(".wp-code pre").after(" <button class=\"btn-clipboard\" " + " style=\"display: none\">复制\n" + "</button>");


$(".wp-code").hover(function () {
    $(this).find(".btn-clipboard").css("display", "block")
}, function () {
    $(this).find(".btn-clipboard").css("display", "none")
});

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


/*激活当前页面导航*/
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




$(function () {
    const textdomain = $.Request("textdomain");
    const project_name = $.Request("project_name");
    $("input[name=textdomain]").val(textdomain);
    $("input[name=project_name]").val(project_name);
/*$("li .um-notification-b").click(function () {
    $('.um-notification-live-feed').animate({width:'toggle'},350);

})
    $(".um-notification-i-close").click(function () {

        $('.um-notification-live-feed').animate({width:'toggle'},350);

})*/




/*申请第三方项目ajax*/
    $(".trusteeship_form").on('click', '.btn-primary', function () {

        $.ajax({
            url: "https://litepress.cn/translate/wp-json/gp/v1/projects/new",
            type: "post",
            data: {
                "project_name": $("#tf_project_name").val(),
                "text_domain": $("#tf_project_textdomin").val(),
                "description": $("#tf_project_textarea").val(),
                "icon": $("#tf_project_icon").val(),
            },
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            },
            success: function (s) {
                console.log(s);
                if (s.message !== undefined ) {
                    $(" .toast-body").html("<i class=\"fad fa-check-circle text-success\"></i> " + s.message);
                    setTimeout(function(){
                        window.location.href = s.project_url; // 你的url地址
                    },500);

                } else {
                    $(" .toast-body").html("<i class=\"fad fa-exclamation-circle text-danger\"></i> " + s.error);
                }
                $('#liveToast').toast('show')
            },

        })
        return false;
    })




})


// 如果验证不通过禁止提交表单
// 获取表单验证样式
        var forms = document.querySelectorAll('.needs-validation')
// 循环并禁止提交
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })

/*封装表单验证*/
function form_validation() {
    if ( $(".form-control:invalid").length > 0 ) {
        $(".needs-validation").addClass("was-validated")
    }
    if ($(" .form-control:invalid").length > 0){
        $(this).siblings(".invalid-feedback").show();
    }
    else {
        $(this).siblings(".invalid-feedback").hide();
    }
}


/*失去焦点和发生改变表单验证*/
$(".form-control").on("blur change",function(){
    form_validation()
});


/*升级表单*/
const $lp_apply_modal = $("#lp-apply-Modal");
$("#lp-apply-button").on("click",function(){
    form_validation()
    const site = $("#lp-apply-site").val();
    if ( $(this).parent().find(".form-control:invalid").length === 0 ) {
        $lp_apply_modal.modal('show');
        /*        $(".lp-apply-site").text(site);
                $(".lp-apply-code").text(md5(site));*/
        $("#lp-apply-Modal .modal-body").html(' <ol><li>请在 <code class="lp-apply-site"> ' + site + ' </code> 根目录下建立<code class="lp-apply-file">lp-check.txt</code>文件</li><li>内容为<code class="lp-apply-code">' + md5(site) + '</code></li><li>配置完成后可<a href="' + site + '/lp-check.txt" target="_blank"><span class="text-primary">点此</span></a>测试访问是否正常，访问正常即可点击下面开始验证按钮提交后端验证。</li></ol>')
        $(".verify-btn").on("click", function () {
            var $this = $(this);
            $.ajax({
                type: "POST",
                url: '/wp-json/lp/apply',
                data: {'site': site},
                datatype: "json",
                //在请求之前调用的函数
                beforeSend: function () {
                    console.log('验证中...')
                    $this.find("a").text("验证中...").end().find(".spinner-border").removeClass("hide")
                },
                //成功返回之后调用的函数
                success: function (s) {
                    $lp_apply_modal.modal('hide');
                    if (s.code === 0) {
                        $(" .toast-body").html("<i class=\"fad fa-check-circle text-success\"></i> " + s.msg);
                    } else {
                        $(" .toast-body").html("<i class=\"fad fa-exclamation-circle text-danger\"></i> " + s.msg);
                    }
                    $('#liveToast').toast('show')
                },
                //调用出错执行的函数
                error: function () {
                    $lp_apply_modal.modal('hide');
                    $(" .toast-body").html("<i class=\"fad fa-exclamation-circle text-danger\"></i>  请求失败，请检查本地网络！");
                    $('#liveToast').toast('show')
                    console.log('错误')
                }
            });
        })

    }
    $(".verify-btn").find("a").text("开始验证").end().find(".spinner-border").addClass("hide")
});

$("#lp-exit-button").on("click",function(){
    form_validation()
    const site = $("#lp-exit-site").val();
    if ( $(this).parent().find(".form-control:invalid").length === 0 ) {
        $lp_apply_modal.modal('show');
        /*        $(".lp-apply-site").text(site);
                $(".lp-apply-code").text(md5(site));*/
        $("#lp-apply-Modal .modal-body").html(' <ol><li>请在 <code class="lp-apply-site"> ' + site + ' </code> 根目录下建立<code class="lp-apply-file">lp-check.txt</code>文件</li><li>内容为<code class="lp-apply-code">' + md5(site) + '</code></li><li>配置完成后可<a href="' + site + '/lp-check.txt" target="_blank"><span class="text-primary">点此</span></a>测试访问是否正常，访问正常即可点击下面开始验证按钮提交后端验证。</li></ol>')
        $(".verify-btn").on("click", function () {
            const $this = $(this);
            $.ajax({
                type: "POST",
                url: '/wp-json/lp/exit',
                data: {'site': site},
                datatype: "json",
                //在请求之前调用的函数
                beforeSend: function () {
                    console.log('验证中...')
                    $this.find("a").text("验证中...").end().find(".spinner-border").removeClass("hide")
                },
                //成功返回之后调用的函数
                success: function (s) {
                    $lp_apply_modal.modal('hide');
                    if (s.code === 0) {
                        $(" .toast-body").html("<i class=\"fad fa-check-circle text-success\"></i> " + s.msg);
                    } else {
                        $(" .toast-body").html("<i class=\"fad fa-exclamation-circle text-danger\"></i> " + s.msg);
                    }
                    $('#liveToast').toast('show')

                },
                //调用出错执行的函数
                error: function () {
                    $lp_apply_modal.modal('hide');
                    $(" .toast-body").html("<i class=\"fad fa-exclamation-circle text-danger\"></i>  请求失败，请检查本地网络！");
                    $('#liveToast').toast('show')
                    console.log('错误')
                }
            });
        })
    }
    $(".verify-btn").find("a").text("开始验证").end().find(".spinner-border").addClass("hide")
});