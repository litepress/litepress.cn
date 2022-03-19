const lpcnMt = require("@khalyomede/translate");
jQuery(document).ready(function ($) {
    $(".auto-translate").click(function () {
        const textarea = $(this).parent().prev();
        textarea.val("翻译中，请等待1秒左右……");

        const main = async () => {
            const originals = $(this).closest(".panel-content").find(".original_raw").text();
            const textarea = $(this).parent().prev();

            try {
                const translation = await lpcnMt(originals, {from: "en", to: "zh-cn"});

                textarea.val(translation);
            } catch (err) {
                textarea.val('接口异常，请联系管理员处理。');
            }

        };

        main();
    })
})
