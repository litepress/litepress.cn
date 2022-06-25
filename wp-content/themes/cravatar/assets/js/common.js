var $ = jQuery.noConflict();
$("li.menu-item-has-children > a").attr("data-bs-toggle", "dropdown");
$(function () {
    var $img = $(".cropper-view");
    var $avatarSave = $('.avatar-save');
    var $modal = $("#avatar-modal");
    var trigger = $(".avater_field");
    var $alert = $('.alert');
    var $model_alert = $('.avatar-body .alert');
    var cropper;
    var options = {
        aspectRatio: 1,
        preview: '.avatar-preview',
        crop: function (e) {
            trigger.attr("data-coord", Math.round(e.detail.x) + ',' + Math.round(e.detail.y) + ',' + Math.round(e.detail.width) + ',' + Math.round(e.detail.height))
        },
        viewMode: 2,
    };

// Tooltip
    $('[data-bs-toggle="tooltip"]').tooltip();
    $modal.on('shown.bs.modal', function () {
        $img.cropper(options);
    }).on('hidden.bs.modal', function () {
        $img.cropper("destroy");
        cropper = null;
        $('#avatarInput').val("")
    });


    $("#avatarInput").on("change", function (e) {
        var uploadedImageURL = getObjectURL(this.files[0]);


        $img.attr("src", uploadedImageURL);
        upload_image();

        $modal.modal('show');


        $(".avatar-btns button").on('click', function () {
            const $this = $(this);
            const data = $this.data();
            const this_method = $(this).attr("data-method");
            const this_option = $(this).attr("data-option");
            const this_second_option = $(this).attr("data-second-option");


            switch (data.method) {
                case 'rotate':
                    break;

                case 'getCroppedCanvas':
                    break;
            }

            result = $img.cropper(this_method, this_option, this_second_option);


            switch (data.method) {
                case 'rotate':
                    break;
                case 'scaleX':
                case 'scaleY':
                    $this.attr('data-option', -this_option);
                    break;
                case 'getCroppedCanvas':
                    break;
                case 'destroy':
                    $img.attr('src', "");
                    $(".avatar-input").val("");
                    break;
            }

        });

        $avatarSave.on('click', function () {
            resize_image()
        });

        // Keyboard
        $(document.body).on('keydown', function (e) {
            if (e.target !== this || !$img.data('cropper') || this.scrollTop > 300) {
                return;
            }

            switch (e.which) {
                case 37:
                    e.preventDefault();
                    $img.cropper('move', -1, 0);
                    break;

                case 38:
                    e.preventDefault();
                    $img.cropper('move', 0, -1);
                    break;

                case 39:
                    e.preventDefault();
                    $img.cropper('move', 1, 0);
                    break;

                case 40:
                    e.preventDefault();
                    $img.cropper('move', 0, 1);
                    break;
            }
        });

// Import image
        var $inputImage = $('#avatarInput');

        if (URL) {
            $inputImage.change(function () {
                var files = this.files;
                var file;

                if (!$img.data('cropper')) {
                    return;
                }

                if (files && files.length) {
                    file = files[0];

                    if (/^image\/\w+$/.test(file.type)) {
                        uploadedImageName = file.name;
                        uploadedImageType = file.type;

                        if (uploadedImageURL) {
                            URL.revokeObjectURL(uploadedImageURL);
                        }

                        uploadedImageURL = URL.createObjectURL(file);
                        $img.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                        $inputImage.val('');
                    } else {
                        window.alert('Please choose an image file.');
                    }
                }
            });
        } else {
            $inputImage.prop('disabled', true).parent().addClass('disabled');
        }
    })


//建立一個可存取到該file的url
    function getObjectURL(file) {
        var url = null;
        if (window.createObjectURL != undefined) { // basic
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) {
            // mozilla(firefox)
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) {
            // webkit or chrome
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }


    function upload_image() {
        var formData = new FormData();
        formData.append('profile_photo', $('#avatarInput').get(0).files[0]);
        formData.append('action', 'um_imageupload');
        formData.append('key', trigger.data('key'));
        formData.append('set_id', trigger.data('set_id'));
        formData.append('set_mode', trigger.data('set_mode'));
        formData.append('_wpnonce', trigger.data('nonce'));
        formData.append('timestamp', trigger.data('timestamp'));
        formData.append('user_id', trigger.data('user_id'));
        $.ajax({
            url: wp.ajax.settings.url,
            type: "POST",
            multiple: false,
            data: formData,
            processData: false,
            contentType: false,

            success: function (s) {
                console.log(s);
                console.log(s.data.error);
                if (s.data.error) {
                    $alert.show().attr("class",'alert-fixed alert-warning').html("<i class=\"fad fa-times-circle\"></i> " + s.data.error);
                } else {
                    $alert.hide();
                    var src =  s.data[0].url;
                    var Newsrc = src.replace("litepress.cn/cravatar", "cravatar.cn");
                    $(".cropper-view").attr({
                        "src" : Newsrc,
                        "data-src" : src
                    });
                }
            },
            error: function (e) {
                console.log(e)
                $alert.show().attr("class",'alert alert-warning').html("<i class=\"fad fa-times-circle\"></i> " + "限制2M以内文件，请重新选择");
            },
        });
    }

    function resize_image() {
        var src = $('.cropper-view').attr('data-src');
        $modal.modal('hide');
        $.ajax({
            url: wp.ajax.settings.url,
            type: "POST",
            multiple: false,
            dataType: "json",
            data: {
                action: "um_resize_image",
                src: src + '?' + trigger.data('timestamp'),
                coord: trigger.data('coord'),
                user_id: trigger.data('user_id'),
                key: trigger.data('key'),
                set_id: 5,
                set_mode: trigger.data('set_mode'),
                nonce: trigger.data('resize-nonce')
            },

            success: function (s) {
                console.log(s);
                if (s.success === false) {
                    /*$alert.show().attr("class",'alert alert-warning').text(s.data);*/
                }else {
                    $(".avatar-view img").attr("src",s.data.image.source_url);
                    $alert.show().attr("class",'alert-fixed alert-success').text('上传成功,两秒后刷新当前页面');
                    setTimeout(function(){
                        window.location.reload();//刷新当前页面.
                    },2000)
                }
            },
            error: function (e) {
                console.log()
                $alert.show().attr("class",'alert-fixed alert-warning').text(e);
            },
        });
    }

    $(".email_list li").on("click",function(){
        $(this).addClass("selected").siblings().removeClass("selected")
    })


    $(".remove_email").on('click', function () {
        const remove_email = $(this).parent().parent().parent().parent().parent().siblings("#wpemail").text();
        const nonce = $(this).parent().parent().parent().parent().parent().siblings("input[name=nonce]").val();
        const user_id = trigger.data('user_id');
        $.ajax({
            url: wp.ajax.settings.url,
            type: "POST",
            dataType: "json",
            data: {
                action: "delete_email",
                email:remove_email,
                _wpnonce:nonce,
                user_id:user_id,
            },
            success: function (s) {
                console.log(s);
                $alert.show().attr("class",'alert-fixed alert-success').html("<i class=\"fad fa-check-circle\"></i> " + s.data.message);
                $(".email.selected").remove();
            },
            error: function (e) {}
        })
    })
/*    $(window).on('scroll load',  function(){
        $navbar_sticky = $('.home #site-header');
        // console.log($(window).scrollTop())

        if ( $(window).scrollTop() >= 83 ){

            //要想使用jQuery slideDown动画,在使用动画之前display只能是none
            $navbar_sticky.slideDown(200)
            //在middle盒子滑下后给其添加fixed类，让其固定定位
            $navbar_sticky.addClass('navbar-sticky').removeClass("transparent")

            //当给.middle添加fixed，.middle就脱离了文档流，下面的内容就一下顶上去了
            /!*$('.bottom').css('margin-top','69px')*!/

        }
        else{//滚动值小于198px
            if ( $(window).scrollTop() < 82 ){
                $navbar_sticky.addClass("transparent").removeClass('navbar-sticky');
            }
        }
    })*/

    const endVal = $("#counter").attr("data-to");
    const c = new countUp.CountUp("counter", endVal)
    c.start()


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




});
