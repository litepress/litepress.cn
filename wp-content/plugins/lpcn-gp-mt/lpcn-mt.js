const lpcnMt = require("@khalyomede/translate");
jQuery(document).ready(function ($) {
    $(".auto-translate").click(function () {
        const textarea = $(this).parent().prev();
        textarea.val("翻译中，请等待1秒左右……");

        const main = async () => {
            let originals = $(this).closest(".panel-content").find(".original_raw").text();
            const textarea = $(this).closest(".textareas.active").find("textarea");

            originals = originals.replaceAll('#', '%23').replaceAll('?', '%3F').replaceAll('&', '%26');

            try {
                let translation = await lpcnMt(originals, {from: "en", to: "zh-cn"});

                translation = translation.replaceAll('%23', '#').replaceAll('%3F', '?').replaceAll('%26', '&');

                textarea.val(translation);
            } catch (err) {
                textarea.val('接口异常，请联系管理员处理。');
            }

        };

        main();
    })
})
