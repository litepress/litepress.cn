<?php
/**
 * 手机扫脸页面模板
 *
 * @package WP_REAL_PERSON_VERIFY
 */
?>

<script type="text/javascript" src="https://cn-shanghai-aliyun-cloudauth.oss-cn-shanghai.aliyuncs.com/web_sdk_js/jsvm_all.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    let meta_info = getMetaInfo();
    (function($){
        $.getUrlParam = function(name) {
            var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
            var r = window.location.search.substr(1).match(reg);
            if (r!=null) return unescape(r[2]);
            return null;
        }
    })(jQuery);

   var task_id = $.getUrlParam('task_id');
    $.ajax({
        url: "/wp-json/wprpv/v1/init-aliyun-face-verify-task",
        type: "POST",
        dataType:"JSON",

        data: {
            meta_info: JSON.stringify(meta_info),
            task_id:task_id,
        },
        beforeSend: function() {},
        error: function() {
            console.log(data);
        },
        complete: function() {
        },
        success: function(data) {
            console.log(data);
            $(location).prop('href', data.data.certify_url)
        }
    });

</script>
