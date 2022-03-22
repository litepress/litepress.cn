var $ = jQuery.noConflict();
var UMTCaptcha = {"captcha_appid": "2032867318"};/*腾讯验证码ID*/
const wp_ajax_url = "/wp-admin/admin-ajax.php";

$("li.menu-item-has-children > a").attr("data-bs-toggle", "dropdown");
$(".ant-btn").on("change", "input[type='file']", function () {
    var filePath = $(this).val();
    //filePath.indexOf("jpg")!=-1 || filePath.indexOf("png")!=-1
    if (filePath.length > 0) {
        $(".fileerrorTip").html("").hide();
        const arr = filePath.split('\\');
        const fileName = arr[arr.length - 1];
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
                } else {
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
const header_search_keyword = $.Request("keyword");
const header_search_s = $.Request("s");
if (header_search_keyword != null) {
    $(".search-form input").val(decodeURIComponent(header_search_keyword))
} else if (header_search_s != null) {
    $(".search-form input").val(decodeURIComponent(header_search_s))
}


$(function () {
    $(".order").before("<tr class='sep-row'></tr>");
    $(".api-manager-domains td").attr('colspan', '5');
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('.tooltip-show').tooltip('show');
    $("tr.editor").each(function () {
        if ($(this).is(':visible')) {
            $(this).find(".textareas").addClass("active");
        }
    });
    $(".textareas").each(function () {
        $(this).addClass("active");
    })
});


/*代码高亮+代码行数+复制*/
hljs.highlightAll();
$(".heti pre").each(function () {
    $(this).wrap("<section class=\"wp-code\"></section>")
});

$(function () {
    $(".wp-code code").each(function () {
        $(this).html("<ul><li>" + $(this).html().replace(/\n/g, "\n</li><li>") + "\n</li></ul>");
    });
    const numLi = $(".wp-code .hljs ul > li").length;

    for (let i = 0; i < numLi; i++) {
        $(".wp-code .hljs ul > li").eq(i).wrap('<li  id="L' + (i + 1) + '" ></div>');
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
    const o = this;
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
    let menu_a = $(this).find("a").attr("href");
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
    /*申请第三方项目ajax*/
    $(".trusteeship_form").on('click', '.btn-primary', function () {

        $.ajax({
            url: "/translate/wp-json/gp/v1/projects/new",
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
                if (s.message !== undefined) {
                    alert_success(s.message)
                    setTimeout(function () {
                        window.location.href = s.project_url; // 你的url地址
                    }, 500);

                } else {
                    alert_danger(s.error)
                }

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
    if ($(".form-control:invalid").length > 0) {
        $(".needs-validation").addClass("was-validated")
        $(this).siblings(".invalid-feedback").show();
    } else {
        $(".needs-validation").removeClass("was-validated")
        $(this).siblings(".invalid-feedback").hide();
    }
}


/*失去焦点和发生改变表单验证*/
/*$(".form-control").on("blur change", function () {
    form_validation()
});*/
$(".form-control").on("blur change", function () {
    if ($(this).is(":invalid")) {
        $(this).parent().addClass("was-validated")
        $(this).siblings(".invalid-feedback").show();
    } else {
        $(this).parent().removeClass("was-validated")
        $(this).siblings(".invalid-feedback").hide();
    }

});
$("#sign-up-password2").on("keyup blur change", function () {
    if ($("#sign-up-password").val() !== $(this).val()) {
        $(this).attr("class", "form-control is-invalid").siblings(".invalid-feedback").show()
    } else {
        $(this).attr("class", "form-control is-valid")
    }

})
$(".toggle-password").on("click", function () {
    $(this).siblings(".form-control").toggleAttrVal('type', "password", "text");
    $(this).find("i").toggleClass("fa-eye-slash fa-eye")
    return false;
})

// jquery toggle whole attribute
$.fn.toggleAttr = function (attr, val) {
    var test = $(this).attr(attr);
    if (test) {
        // if attrib exists with ANY value, still remove it
        $(this).removeAttr(attr);
    } else {
        $(this).attr(attr, val);
    }
    return this;
};

// jquery toggle just the attribute value
$.fn.toggleAttrVal = function (attr, val1, val2) {
    var test = $(this).attr(attr);
    if (test === val1) {
        $(this).attr(attr, val2);
        return this;
    }
    if (test === val2) {
        $(this).attr(attr, val1);
        return this;
    }
    // default to val1 if neither
    $(this).attr(attr, val1);
    return this;
};

/*升级表单*/
const $lp_apply_modal = $("#lp-apply-Modal");
$("#lp-apply-button").on("click", function () {
    form_validation()
    const site = $("#lp-apply-site").val();
    if ($(this).parent().find(".form-control:invalid").length === 0) {
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
                        alert_success(s.msg)
                    } else {
                        alert_danger(s.msg)
                    }
                },
                //调用出错执行的函数
                error: function () {
                    $lp_apply_modal.modal('hide');
                    alert_danger("请求失败，请检查本地网络！")
                    console.log('错误')
                }
            });
        })

    }
    $(".verify-btn").find("a").text("开始验证").end().find(".spinner-border").addClass("hide")
});
/*回退表单*/
$("#lp-exit-button").on("click", function () {
    form_validation()
    const site = $("#lp-exit-site").val();
    if ($(this).parent().find(".form-control:invalid").length === 0) {
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
                        alert_success(s.msg)
                    } else {
                        alert_danger(s.msg)
                    }

                },
                //调用出错执行的函数
                error: function () {
                    $lp_apply_modal.modal('hide');
                    alert_danger("请求失败，请检查本地网络！")
                }
            });
        })
    }
    $(".verify-btn").find("a").text("开始验证").end().find(".spinner-border").addClass("hide")
});


/*wangeditor编辑器配置*/
const E = window.wangEditor; // 全局变量
const editorConfig = {MENU_CONF: {}}
editorConfig.placeholder = '点击开始写作……'
editorConfig.MENU_CONF['uploadImage'] = {
    server: '/wp-json/upload_image/v1/upload',
    fieldName: 'upload_img_file',
    // 小于该值就插入 base64 格式（而不上传），默认为 0
    base64LimitSize: 5 * 1024, // 5kb
    allowedFileTypes: ['image/*'],
    // 上传错误，或者触发 timeout 超时
    onError(file, err, res) {
        console.log(`${file.name} 上传出错`, err, res)
    },
}


if ($("#editor-container").length > 0) {
    editorConfig.onChange = function () {
        document.getElementById('comment').value = editor.getHtml()
    }
    const editor = E.createEditor({
        selector: '#editor-container',
        config: editorConfig,
        mode: 'default'
    })

    const toolbarConfig = {
        toolbarKeys: [
            "emotion",
            {
                key: 'group-code', // 必填，要以 group 开头
                title: '代码', // 必填
                iconSvg: "<svg viewBox=\"0 0 1280 1024\"><path d=\"M832 736l96 96 320-320L928 192l-96 96 224 224zM448 288l-96-96L32 512l320 320 96-96-224-224zM701.312 150.528l69.472 18.944-192 704.032-69.472-18.944 192-704.032z\"></path></svg>", // 可选
                menuKeys: ["code", "codeBlock",] // 下级菜单 key ，必填
            },
            {
                "key": "group-image",
                "title": "图片",
                "iconSvg": "<svg viewBox=\"0 0 1024 1024\"><path d=\"M959.877 128l0.123 0.123v767.775l-0.123 0.122H64.102l-0.122-0.122V128.123l0.122-0.123h895.775zM960 64H64C28.795 64 0 92.795 0 128v768c0 35.205 28.795 64 64 64h896c35.205 0 64-28.795 64-64V128c0-35.205-28.795-64-64-64zM832 288.01c0 53.023-42.988 96.01-96.01 96.01s-96.01-42.987-96.01-96.01S682.967 192 735.99 192 832 234.988 832 288.01zM896 832H128V704l224.01-384 256 320h64l224.01-192z\"></path></svg>",
                "menuKeys": [
                    "insertImage",
                    "uploadImage"
                ]
            },
            "insertLink",
            "|",
            "bold",
            "underline",
            "italic",
            "through",
            "color",
            "clearStyle",
            "|",
            "bulletedList",
            "numberedList",
            "todo",
            "|",
            {
                "key": "group-more-style",
                "title": "更多",
                "iconSvg": "<svg viewBox=\"0 0 1024 1024\"><path d=\"M204.8 505.6m-76.8 0a76.8 76.8 0 1 0 153.6 0 76.8 76.8 0 1 0-153.6 0Z\"></path><path d=\"M505.6 505.6m-76.8 0a76.8 76.8 0 1 0 153.6 0 76.8 76.8 0 1 0-153.6 0Z\"></path><path d=\"M806.4 505.6m-76.8 0a76.8 76.8 0 1 0 153.6 0 76.8 76.8 0 1 0-153.6 0Z\"></path></svg>",
                "menuKeys": [
                    "bgColor",
                    "sup",
                    "sub",
                    "insertVideo",
                    "insertTable",
                    "divider"
                ]
            },
            "|",
            "undo",
            "redo",
            "|",
            "fullScreen",
        ],
    }
    const toolbar = E.createToolbar({
        editor,
        selector: '#editor-toolbar',
        config: toolbarConfig,
        mode: 'simple'
    })

}

if ($("#bbp-editor-container").length > 0) {
    editorConfig.onChange = function () {
        $('.wp-editor-area').val(editor.getHtml())
    }
    var editor = E.createEditor({
        selector: '#bbp-editor-container',
        config: editorConfig,
        mode: 'default',

    })

    const toolbarConfig = {
        toolbarKeys: [
            "emotion",
            {
                key: 'group-code', // 必填，要以 group 开头
                title: '代码', // 必填
                iconSvg: "<svg viewBox=\"0 0 1280 1024\"><path d=\"M832 736l96 96 320-320L928 192l-96 96 224 224zM448 288l-96-96L32 512l320 320 96-96-224-224zM701.312 150.528l69.472 18.944-192 704.032-69.472-18.944 192-704.032z\"></path></svg>", // 可选
                menuKeys: ["code", "codeBlock",] // 下级菜单 key ，必填
            },
            {
                "key": "group-image",
                "title": "图片",
                "iconSvg": "<svg viewBox=\"0 0 1024 1024\"><path d=\"M959.877 128l0.123 0.123v767.775l-0.123 0.122H64.102l-0.122-0.122V128.123l0.122-0.123h895.775zM960 64H64C28.795 64 0 92.795 0 128v768c0 35.205 28.795 64 64 64h896c35.205 0 64-28.795 64-64V128c0-35.205-28.795-64-64-64zM832 288.01c0 53.023-42.988 96.01-96.01 96.01s-96.01-42.987-96.01-96.01S682.967 192 735.99 192 832 234.988 832 288.01zM896 832H128V704l224.01-384 256 320h64l224.01-192z\"></path></svg>",
                "menuKeys": [
                    "insertImage",
                    "uploadImage"
                ]
            },
            "insertLink",
            "|",
            "bold",
            "underline",
            "italic",
            "through",
            "color",
            "clearStyle",
            "|",
            "bulletedList",
            "numberedList",
            "todo",
            "|",
            {
                "key": "group-more-style",
                "title": "更多",
                "iconSvg": "<svg viewBox=\"0 0 1024 1024\"><path d=\"M204.8 505.6m-76.8 0a76.8 76.8 0 1 0 153.6 0 76.8 76.8 0 1 0-153.6 0Z\"></path><path d=\"M505.6 505.6m-76.8 0a76.8 76.8 0 1 0 153.6 0 76.8 76.8 0 1 0-153.6 0Z\"></path><path d=\"M806.4 505.6m-76.8 0a76.8 76.8 0 1 0 153.6 0 76.8 76.8 0 1 0-153.6 0Z\"></path></svg>",
                "menuKeys": [
                    "bgColor",
                    "sup",
                    "sub",
                    "insertVideo",
                    "insertTable",
                    "divider"
                ]
            },
            "|",
            "undo",
            "redo",
            "|",
            "fullScreen",
        ],
    }
    const toolbar = E.createToolbar({
        editor,
        selector: '#bbp-editor-toolbar',
        config: toolbarConfig,
        mode: 'simple'
    })
    editor.on('fullScreen', () => {
        $("body").addClass("overflow-hidden")
    })
    editor.on('unFullScreen', () => {
        $("body").removeClass("overflow-hidden")
    })
    /*话题编辑同步内容*/
    $(function () {
        const bbp_content = $(".topic-edit .bbp_topic_content_hide").html();
        const pre_class = $(".topic-edit .bbp_topic_content_hide pre").attr("class");
        if (pre_class !== undefined) {
            const pre_class_1 = pre_class.replace(/^\s*/, "");
            const C1 = bbp_content.replace("tabindex=\"0\"", "")
            const C4 = C1.replaceAll(pre_class, pre_class_1)
            editor.dangerouslyInsertHtml(C4)
        } else {
            editor.dangerouslyInsertHtml(bbp_content)
        }

    })
    /*评论编辑同步内容*/
    $(function () {
        const bbp_content = $(".reply-edit .bbp_topic_content_hide").html();
        const pre_class = $(".reply-edit .bbp_topic_content_hide pre").attr("class");
        if (pre_class !== undefined) {
            const pre_class_1 = pre_class.replace(/^\s*/, "");
            const C1 = bbp_content.replace("tabindex=\"0\"", "")
            const C4 = C1.replaceAll(pre_class, pre_class_1)
            editor.dangerouslyInsertHtml(C4)
        } else {
            editor.dangerouslyInsertHtml(bbp_content)
        }

    })
}
if ($("#reply-title a").length > 0) {
    const text = $("#reply-title a").text();
    $('.w-e-text-placeholder').text('回复 ' + text + ' :')
} else {
    $(".cancel-comment-reply-link").hide()
}
/*搜索占位符*/
const projectsearch = $(".search-form input[type=search]");
const url = $(location).attr('href'); //获取url地址
const url_noparm = location.protocol + '//' + location.host + location.pathname;
const url_noparm4 = url_noparm.split("/").splice(0, 4).join("/");
if (url.indexOf("plugins") >= 0) {
    projectsearch.attr("placeholder", "搜索插件……");
} else if (url.indexOf("docs") >= 0) {
    projectsearch.attr("placeholder", "搜索文档……");
} else if (url.indexOf("themes") >= 0) {
    projectsearch.attr("placeholder", "搜索主题……");
} else if (url.indexOf("cores") >= 0) {
    projectsearch.attr("placeholder", "搜索核心……");
} else if (url.indexOf("mini-app") >= 0) {
    projectsearch.attr("placeholder", "搜索小程序……");
} else if (url.indexOf("others") >= 0) {
    projectsearch.attr("placeholder", "搜索第三方……");
}
/*点击区域外隐藏通知*/
$(document).on("click", function (e) {
    if ($(e.target).closest(".um-notification-live-feed").length === 0) {
        $(".um-notification-live-feed").hide();
    }
});

/*手机号登录注册*/
$(".input-smscode").on("click", function () {
    $("#sms-code").focus()
})
$("#sms-code").on("keyup", function () {
    const $this = $(this)
    const $this_form = $("#form-sign-up")
    const mobile = $this_form.find("#mobile")
    const sms_code = $this_form.find("#sms-code").val()
    const mobile_val = mobile.val()
    if (sms_code.length === 4) {

                $.ajax({
                    /*手机号登录*/
                    type: "POST",
                    url: "/auth/wp-json/lpcn/user/login-by-mobile",
                    data: {
                        'mobile': mobile_val,
                        'sms_code': sms_code,
                    },
                    datatype: "json",
                    //在请求之前调用的函数
                    beforeSend: function () {

                    },
                    //成功返回之后调用的函数
                    success: function (s) {
                        alert_success(s.message)
                        $this.closest(".modal").modal('hide');
                        window.location.reload()
                    },
                    //调用出错执行的函数
                    error: function (e) {
                        /*console.log(e)*/
                         if (e.responseJSON.status===2) {
                            alert_danger(e.responseJSON.message+"，即将跳转到注册引导")
                            window.location.href = "/auth/user/bind?type=mobile&?token="+e.responseJSON.data.token
                        }
                         else {
                            alert_danger(e.responseJSON.message)
                        }

                    }
                });

    }
})

$(".send-sms-code").on("click", function () {
    const $this = $(this)
    const $this_form = $this.closest("form")
    const mobile = $this_form.find("#mobile")
    const agree = $this_form.find("#form-sign-up-agree")
    const mobile_val = mobile.val()
    const t_ticket = $this_form.find(".tcaptcha-ticket")
    const t_randstr = $this_form.find(".tcaptcha-randstr")


    if ($this_form.find(".form-control:invalid,.form-check-input:invalid").length === 0) {

        const lp_captcha = new TencentCaptcha(UMTCaptcha.captcha_appid, function (res) {
            if (res.ret === 0) {
                /*滑块通过*/
                t_ticket.val(res.ticket)
                t_randstr.val(res.randstr)
                const ticket = t_ticket.val()
                const randstr = t_randstr.val()


                let count = 60;
                const resend = setInterval(function () {
                    count--;
                    if (count > 0) {
                        $this.html("重新发送 " + count + "s").addClass("disabled");
                        $.cookie("captcha", count, {path: '/', expires: (1 / 86400) * count});
                    } else {
                        clearInterval(resend);
                        $this.html("重新发送").toggleClass("disabled");
                    }
                }, 1000);

                $.ajax({
                    /*发送验证码*/
                    type: "POST",
                    xhrFields: {withCredentials: true},
                    url: "/auth/wp-json/lpcn/user/send_sms_code",
                    data: {
                        'mobile': mobile_val,
                        "tcaptcha-ticket": ticket,
                        "tcaptcha-randstr": randstr,
                    },
                    datatype: "json",
                    //在请求之前调用的函数
                    beforeSend: function () {
                        btn_load($this)
                    },
                    //成功返回之后调用的函数
                    success: function (s) {
                        alert_success(s.message)
                        $this_form.find("section.hide").show().siblings().hide()
                        $("#sms-code").focus()
                        console.log(s)
                    },
                    //调用出错执行的函数
                    error: function (e) {
                        alert_danger(e.responseJSON.message)
                    },
                    complete:function () {
                        btn_load_remove($this)
                    }
                });

            } else {
                alert_danger("图形验证失败，请重试！")
            }
        });
        // 显示验证码
        lp_captcha.show();
    } else {
        $(mobile).parent().addClass("was-validated")
        $(agree).parent().addClass("was-validated")
    }


})

/*密码登录*/


$("#form-sign-in").on("click", "[data-type='submit']", function () {
    form_validation()
    const $this = $(this);
    const $this_form = $this.closest("form");
    const username = $this_form.find(".username").val()
    const password = $this_form.find(".password").val()
    const t_ticket = $this_form.find(".tcaptcha-ticket")
    const t_randstr = $this_form.find(".tcaptcha-randstr")

    if ($(this).parent().find(".form-control:invalid").length === 0) {
        const lp_captcha = new TencentCaptcha(UMTCaptcha.captcha_appid, function (res) {
            if (res.ret === 0) {
                t_ticket.val(res.ticket)
                t_randstr.val(res.randstr)
                const ticket = t_ticket.val()
                const randstr = t_randstr.val()

                $.ajax({
                    type: "POST",
                    xhrFields: {withCredentials: true},
                    url: "/auth/wp-json/lpcn/user/login",
                    data: {
                        'username': username,
                        'password': password,
                        "tcaptcha-ticket": ticket,
                        "tcaptcha-randstr": randstr,
                    },
                    datatype: "json",
                    //在请求之前调用的函数
                    beforeSend: function () {
                        btn_load($this)
                    },
                    //成功返回之后调用的函数
                    success: function (s) {
                        if (s.code === 1) {
                            alert_danger(s.error)

                        } else {
                            $this.closest(".modal").modal('hide');
                            alert_success(s.message)
                            window.location.reload()
                        }
                    },
                    //调用出错执行的函数
                    error: function (e) {
                        alert_danger(e.responseJSON.message)
                    },
                    complete:function () {
                        btn_load_remove($this)
                    }
                });


            } else {
                alert_danger("图形验证失败，请重试！")
            }
        });
        // 显示验证码
        lp_captcha.show();
    }

})


/*绑定旧账号*/
$("#bd-old-account").on("click", "[data-type='submit']", function () {
    const $this = $(this);
    const $this_form = $this.closest("form");
    const username = $this_form.find(".username").val()
    const password = $this_form.find(".password").val()
    const t_ticket = $this_form.find(".tcaptcha-ticket")
    const t_randstr = $this_form.find(".tcaptcha-randstr")
    const token = $.Request("token");

    if ($this_form.find(".form-control:invalid").length === 0) {
    const lp_captcha = new TencentCaptcha(UMTCaptcha.captcha_appid, function (res) {
        if (res.ret === 0) {
            /*滑块通过*/
            t_ticket.val(res.ticket)
            t_randstr.val(res.randstr)
            const ticket = t_ticket.val()
            const randstr = t_randstr.val()
            $.ajax({
                type: "POST",
                url: "/auth/wp-json/lpcn/user/login",
                data: {
                    'username': username,
                    'password': password,
                    "tcaptcha-ticket": ticket,
                    "tcaptcha-randstr": randstr,
                    "token": token
                },
                datatype: "json",
                //在请求之前调用的函数
                beforeSend: function () {
                    btn_load($this)
                },
                //成功返回之后调用的函数
                success: function (s) {
                    alert_success("与登录账号成功绑定,即将跳转到资料页")
                    window.location.href = "/auth/user"
                },
                //调用出错执行的函数
                error: function (e) {
                    alert_danger(e.responseJSON.message)
                },
                complete:function () {
                    btn_load_remove($this)
                }
            });
        }
        else {
            alert_danger("图形验证失败，请重试！")
        }
    });
        // 显示验证码
        lp_captcha.show();
    }
    })

/*绑定新用户*/
$("#bd-new-account").on("click", "[data-type='submit']", function () {
    const $this = $(this);
    const $this_form = $this.closest("form");
    const token = $.Request("token");


                /*滑块通过*/
                $.ajax({
                    type: "POST",
                    url: "/auth/wp-json/lpcn/user/register",
                    data: {
                        "token": token
                    },
                    datatype: "json",
                    //在请求之前调用的函数
                    beforeSend: function () {
                        btn_load($this)
                    },
                    //成功返回之后调用的函数
                    success: function (s) {
                        window.location.href = "/auth/user"
                    },
                    //调用出错执行的函数
                    error: function (e) {
                        alert_danger(e.responseJSON.message)
                    },
                    complete:function () {
                        btn_load_remove($this)
                    }
                });




})

/*! jquery.cookie v1.4.1 | MIT */
!function (a) {
    "function" == typeof define && define.amd ? define(["jquery"], a) : "object" == typeof exports ? a(require("jquery")) : a(jQuery)
}(function (a) {
    function b(a) {
        return h.raw ? a : encodeURIComponent(a)
    }

    function c(a) {
        return h.raw ? a : decodeURIComponent(a)
    }

    function d(a) {
        return b(h.json ? JSON.stringify(a) : String(a))
    }

    function e(a) {
        0 === a.indexOf('"') && (a = a.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, "\\"));
        try {
            return a = decodeURIComponent(a.replace(g, " ")), h.json ? JSON.parse(a) : a
        } catch (b) {
        }
    }

    function f(b, c) {
        var d = h.raw ? b : e(b);
        return a.isFunction(c) ? c(d) : d
    }

    var g = /\+/g, h = a.cookie = function (e, g, i) {
        if (void 0 !== g && !a.isFunction(g)) {
            if (i = a.extend({}, h.defaults, i), "number" == typeof i.expires) {
                var j = i.expires, k = i.expires = new Date;
                k.setTime(+k + 864e5 * j)
            }
            return document.cookie = [b(e), "=", d(g), i.expires ? "; expires=" + i.expires.toUTCString() : "", i.path ? "; path=" + i.path : "", i.domain ? "; domain=" + i.domain : "", i.secure ? "; secure" : ""].join("")
        }
        for (var l = e ? void 0 : {}, m = document.cookie ? document.cookie.split("; ") : [], n = 0, o = m.length; o > n; n++) {
            var p = m[n].split("="), q = c(p.shift()), r = p.join("=");
            if (e && e === q) {
                l = f(r, g);
                break
            }
            e || void 0 === (r = f(r)) || (l[q] = r)
        }
        return l
    };
    h.defaults = {}, a.removeCookie = function (b, c) {
        return void 0 === a.cookie(b) ? !1 : (a.cookie(b, "", a.extend({}, c, {expires: -1})), !a.cookie(b))
    }
});







/*快捷登录打开小窗口*/
function openWin(url) {
    childWindow = window.open(url, 'Login', 'height=720, width=630, top=30%,left=30%, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=no, status=no');
}

function closeChildWindowAndBindUser(token) {
    childWindow.close();

    window.location.href = "/auth/user/bind?type=qq&token=" + token;
}

function closeChildWindow() {
    childWindow.close();

    location.reload();
}

$(".social-item a").click(function () {
    const url = $(this).attr("href");
    openWin(url)
    return false;
})

/*封装通知*/
function alert_danger(html) {
    const T = $("#liveToast")
    T.find(".danger").show().siblings().hide().closest(".d-flex").attr("class", "d-flex alert-danger")
    T.toast('show').find("span").html(html)
}

function alert_success(html) {
    const T = $("#liveToast")
    T.find(".success").show().siblings().hide().closest(".d-flex").attr("class", "d-flex alert-success")
    T.toast('show').find("span").html(html)
}

/*封装加载按钮*/
function btn_load($this){
    const a = $($this)
    const b = $($this).text()
    const c = "<span class=\"spinner-border spinner-border-sm me-2\" role=\"status\" aria-hidden=\"true\"></span>"
    a.html(c + b)
}
function btn_load_remove($this){
    const a = $($this)
    const b = $($this).text()
    a.html(b)
}


/*翻译跳转*/
let fanyi_seach_val = $(".search-form input").val()
$(".menu-item-translate a").attr("href", '/translate/projects/plugins/?s=' + fanyi_seach_val)
$(".menu-item-manual  a").attr("href", '/translate/projects/plugins/?s=' + fanyi_seach_val)


$('.btn-sign-up .send-sms-code').on('click', function () {

    /*仿刷新：检测是否存在cookie*/
    if ($.cookie("captcha")) {
        let count = $.cookie("captcha");
        $(".send-sms-code").html("重新发送 " + count + "s").addClass("disabled");
        const resend = setInterval(function () {
            count--;
            if (count > 0) {
                $(".send-sms-code").html("重新发送 " + count + "s").addClass("disabled");
                $.cookie("captcha", count, {path: '/', expires: (1 / 86400) * count});
            } else {
                clearInterval(resend);
                $(".send-sms-code").html("重新发送").toggleClass("disabled");
            }
        }, 1000);
    }

    return false;
})

const smsCodeInput = document.querySelector('.input-smscode-value');
opts = typeof opts == 'undefined' ? {} : opts;
opts.maxLength = typeof opts.maxLength == 'undefined' ? 4 : opts.maxLength;
const inputSmscodeCloseButton = document.querySelector('.input-smscode-button-close');

function smsCodeInput_onkeyup(e) {
    const tmpCodes = smsCodeInput.value.split('');
    const smsCodeBox = document.querySelector('.input-smscode');
    let tmphtml = '';
    smsCodeBox.innerHTML = tmphtml;
    let i = 0;
    [].forEach.call(tmpCodes, function (code) {
        tmphtml += '<li>' + code + '</li>';
        i++;
    });
    while (i < opts.maxLength) {
        tmphtml += '<li></li>';
        i++;
    }

    smsCodeBox.innerHTML = tmphtml ? tmphtml : '<li></li><li></li><li></li><li></li>';
}

function smsCodeInput_onblur(e) {
    smsCodeInput.focus();
}

function smsCodeInput_setfocus() {
    smsCodeInput.focus();
}

document.addEventListener('keyup', smsCodeInput_onkeyup, false);

