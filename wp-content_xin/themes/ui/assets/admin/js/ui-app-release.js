var $ = jQuery.noConflict();
$(function () {
    $(".app_release_tabs>li").click(function () {
        $(this).addClass('active').siblings().removeClass('active');//点击时背景色改变
        var i = $(this).index();//当前元素下标
        $('#box > ul > li').eq(i).show().siblings().hide();
    })
    if (typeof (tinyMCE) == "object") {
        tinymce.init({
            selector: '.wp-tinymce',
            height: 300,
            menubar: false,
            plugins: [],
            mobile: {
                theme: 'mobile'
            },
            toolbar: ' undo redo |  formatselect | bold italic -backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        });
    }
    ;
    data_row = $('#box #46_tab a.button.insert').attr("data-row");
    $("#box  #46_tab a.button.insert").click(function () {
        return $("#box  #46_tab .ui-sortable").append(data_row),
            !1;
    });

    $("#box").on("click", "a.delete", function () {
        return $(this).closest("tr").remove(),
            !1;
    })
    $("#box #product-type").on("change", function () {
        val = $(this).find("option:selected").val();
        group = $("#box ul li.active .item");
        if (val === "external") {
            group.eq(1).show().siblings(".item").hide();
        } else {
            group.eq(0).show().siblings(".item").hide();
        }
    })
    $("#box #app-type").on("change", function () {
        val = $(this).find("option:selected").val();
        if (val === "17") {

            $(".theme-item").show().siblings(".item").hide();
            $(".plugin-sub-cat-item").hide();
            $(".theme-sub-cat-item").show();
        } else if (val === "15") {
            $(".theme-sub-cat-item").hide();
            $(".plugin-sub-cat-item").show();
            $(".app_release_tabs li").show();
        }

    })

    $(function () {
        val = $("#app-type").val();
        if (val === "17") {
            $(".theme-item").show().siblings(".item").hide();
            $(".plugin-sub-cat-item").hide();
            $(".theme-sub-cat-item").show();
        } else if (val === "15") {
            $(".theme-sub-cat-item").hide();
            $(".plugin-sub-cat-item").show();
            $(".app_release_tabs li").show();
        }

    })

});
