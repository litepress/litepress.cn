// 监听并绑定提交事件
jQuery('#um-submit-btn').closest('form').on('submit', tcaptcha_validate_form);

function tcaptcha_validate_form(e) {
    // 先阻止表单提交
    e.preventDefault();

    // 初始化验证码
    var lp_captcha = new TencentCaptcha(UMTCaptcha.captcha_appid, function (res) {
        if (res.ticket && res.randstr) {
            var $form = jQuery(e.target);
            $form.append('<input type="hidden" name="tcaptcha-ticket" value="' + res.ticket + '">');
            $form.append('<input type="hidden" name="tcaptcha-randstr" value="' + res.randstr + '">');
            $form.off('submit', tcaptcha_validate_form).trigger('submit');
        } else {
            alert('请完成验证！');
            jQuery('#um-submit-btn').removeAttr("disabled");
        }
    });

    // 显示验证码
    lp_captcha.show();
}
