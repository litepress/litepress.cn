var $ = jQuery.noConflict();
const url = $(location).attr('href');
const url4 = url.split("/").splice(5, 6).join("/");
const urlsearch = $(location).attr('search');
const api_domain = $("link[rel$='https://api.w.org/']").attr('href');
const arr = api_domain.split("/");
const domain = arr.slice(0, arr.length - 2).join('/');
let pathname = window.location.pathname;


/*导航进度*/
if( url4 === "personal" || url4 === "enterprise" ){
    $(".step-nav .item2").addClass("in-progress");
    $(".step-nav .item2 i").addClass("fas").removeClass("far");
}else if( url4 === "complete?passed=no" || url4 === "complete?passed=yes" ){
    $(".step-nav .item").addClass("in-progress");
    $(".step-nav .item i").addClass("fas").removeClass("far");
}



/*封装表单验证*/
function form_validation() {
    if ( $(".form-control:invalid").length > 0 ) {
        $(".needs-validation").addClass("was-validated")
    }
    if ($(".btn-file .form-control:invalid").length > 0){
        $(".btn-file-div + .invalid-feedback").show();
    }
    else {
        $(".btn-file-div + .invalid-feedback").hide();
    }
}

/*封装个人认证*/
function ajax_personal_verify() {
    const realname = $('#realname').val();
    const cert_no = $('#cert_no').val();
    $.ajax({
        url: api_domain + "wprpv/v1/init-local-face-verify-task",
        type: "POST",
        dataType:"JSON",
        data: {
            name:realname,
            cert_no:cert_no,
        },
        beforeSend: function (XMLHttpRequest) {
            XMLHttpRequest.setRequestHeader("X-WP-Nonce", wprpv_rest_api_nonce);
        },
        error:function(error) {
            console.log(error);
            $('.authentication-message').html(error.responseJSON.message);
        },
        complete: function() {
        },
        success: function(data) {
            console.log(data);
            console.log(data.data.url+"?task_id=" + data.data.task_id);
            $("#qrcode").html("");
            $('#qrcode').qrcode(data.data.url+"?task_id="+data.data.task_id);
            $('.authentication-message').html(data.message)
            var  task_id = data.data.task_id;
            localStorage.setItem("local_task_id",task_id);
        }
    });
}


/*封装企业认证*/
function ajax_enterprise_verify() {
    const code = document.getElementById('enterprise_code')
    const name = document.getElementById('enterprise_name')
    const license_img = document.getElementById('enterprise_license_img')
    let data = new FormData()
    data.append('code', code.value)
    data.append('name', name.value)
    data.append('license_img', license_img.files[0])
    $.ajax({
        url: api_domain + "wprpv/v1/enterprise-verify",
        type: "POST",
        dataType:"JSON",
        contentType: false,
        processData: false,
        data: data,
        beforeSend: function (XMLHttpRequest) {
            XMLHttpRequest.setRequestHeader("X-WP-Nonce", wprpv_rest_api_nonce);
        },
        error:function(error) {
            console.log(error);
            $('#enterprise-btn-alert .modal-body').html(error.responseJSON.message);
            $('#enterprise-btn-alert').modal('show');
        },
        complete: function() {
        },
        success: function(data) {
            console.log(data);
            $("#qrcode").html("");
            $('.authentication-message').html(data.message)
            if( data.data.passed === true){
                $(location).prop('href', domain + "/real-person-verify/complete?passed=yes")
            }
            else {
                $(location).prop('href', domain + "/real-person-verify/complete?passed=no")
            }

        }
    });
}

/*封装个人认证查询*/
function Personal_authentication_query(){
    $.ajax({
        url: api_domain + "wprpv/v1/describe-face-verify-task",
        type: "GET",
        dataType:"JSON",
        data: {
            task_id:localStorage.getItem("local_task_id"),
        },
        beforeSend: function (XMLHttpRequest) {
            XMLHttpRequest.setRequestHeader("X-WP-Nonce", wprpv_rest_api_nonce);
        },
        error:function(error) {
            if( error.responseJSON.message.length !== 0 ) {
                $("#qrcode").html("");
                $('.authentication-message').html(error.responseJSON.message);
            }
            /*console.log(error.responseJSON.message);*/
        },
        complete: function() {
        },
        success: function(data) {
            $("#qrcode").html("");
            $('.authentication-message').html(data.message);
            if( data.data.passed === true && pathname.indexOf("real-person-verify/personal") >= 0 ){
                $(location).prop('href', domain + "/real-person-verify/complete?passed=yes")
            }
            else if(data.data.passed === true && pathname.indexOf("real-person-verify/enterprise") >= 0){
                ajax_enterprise_verify();
            }
            else {
                $(location).prop('href', domain + "/real-person-verify/complete?passed=no")
            }
        }
    });
}

/*失去焦点和发生改变表单验证*/
$(".form-control").on("blur change",function(){
    form_validation()
});

/*个人认证按钮*/
$("#ali-authentication-btn").click(function(){
    form_validation()
    ajax_personal_verify()
    if( realname.length === 0 || cert_no .length === 0 ){
        $('#qrcode').html("");
    }
    if ($(".form-control").length === $(".form-control:valid").length) {
        $("#ali-authentication-modal").modal('show');
        $(".modal-footer .btn").click(function(){
            Personal_authentication_query()
        })
    }
});

/*企业认证按钮*/
$("#ali-authentication-enterprise-btn").click(function(){
    form_validation()
    if ($(".form-control").length === $(".form-control:valid").length) {
        $("#ali-authentication-modal").modal('show');
        ajax_enterprise_verify();
    }
});




/*倒计时*/
if(  urlsearch === "?passed=yes"){
    function jump(count) {
        window.setTimeout(function() {
            count--;
            if (count > 0) {
                $('.step-complete #num').text(count);
                jump(count);
            } else {
                location.href = $('.step-complete-href').attr('href');
            }
        }, 1000);//1秒
    }
    $(function() {
        jump(3);
    });}





/*表单图片上传预览*/
+function($){const isIE=window.navigator.appName=="Microsoft Internet Explorer";const Fileinput=function(element,options){this.$element=$(element);this.$input=this.$element.find(":file");if(this.$input.length===0){return}this.name=this.$input.attr("name")||options.name;this.$hidden=this.$element.find('input[type=hidden][name="'+this.name+'"]');if(this.$hidden.length===0){this.$hidden=$('<input type="hidden">').insertBefore(this.$input)}this.$preview=this.$element.find(".fileinput-preview");var height=this.$preview.css("height");if(this.$preview.css("display")!=="inline"&&height!=="0px"&&height!=="none"){this.$preview.css("line-height",height)}this.original={exists:this.$element.hasClass("fileinput-exists"),preview:this.$preview.html(),hiddenVal:this.$hidden.val()};this.listen()};Fileinput.prototype.listen=function(){this.$input.on("change.bs.fileinput",$.proxy(this.change,this));$(this.$input[0].form).on("reset.bs.fileinput",$.proxy(this.reset,this));this.$element.find('[data-trigger="fileinput"]').on("click.bs.fileinput",$.proxy(this.trigger,this));this.$element.find('[data-dismiss="fileinput"]').on("click.bs.fileinput",$.proxy(this.clear,this))};Fileinput.prototype.change=function(e){var files=e.target.files===undefined?(e.target&&e.target.value?[{name:e.target.value.replace(/^.+\\/,"")}]:[]):e.target.files;e.stopPropagation();if(files.length===0){this.clear();return}this.$hidden.val("");this.$hidden.attr("name","");this.$input.attr("name",this.name);var file=files[0];if(this.$preview.length>0&&(typeof file.type!=="undefined"?file.type.match(/^image\/(gif|png|jpeg)$/):file.name.match(/\.(gif|png|jpe?g)$/i))&&typeof FileReader!=="undefined"){var reader=new FileReader();var preview=this.$preview;var element=this.$element;reader.onload=function(re){var $img=$("<img>");$img[0].src=re.target.result;files[0].result=re.target.result;element.find(".fileinput-filename").text(file.name);if(preview.css("max-height")!="none"){$img.css("max-height",parseInt(preview.css("max-height"),10)-parseInt(preview.css("padding-top"),10)-parseInt(preview.css("padding-bottom"),10)-parseInt(preview.css("border-top"),10)-parseInt(preview.css("border-bottom"),10))}preview.html($img);element.addClass("fileinput-exists").removeClass("fileinput-new");element.trigger("change.bs.fileinput",files)};reader.readAsDataURL(file)}else{this.$element.find(".fileinput-filename").text(file.name);this.$preview.text(file.name);this.$element.addClass("fileinput-exists").removeClass("fileinput-new");this.$element.trigger("change.bs.fileinput")}};Fileinput.prototype.clear=function(e){if(e){e.preventDefault()}this.$hidden.val("");this.$hidden.attr("name",this.name);this.$input.attr("name","");if(isIE){var inputClone=this.$input.clone(true);this.$input.after(inputClone);this.$input.remove();this.$input=inputClone}else{this.$input.val("")}this.$preview.html("");this.$element.find(".fileinput-filename").text("");this.$element.addClass("fileinput-new").removeClass("fileinput-exists");if(e!==undefined){this.$input.trigger("change");this.$element.trigger("clear.bs.fileinput")}};Fileinput.prototype.reset=function(){this.clear();this.$hidden.val(this.original.hiddenVal);this.$preview.html(this.original.preview);this.$element.find(".fileinput-filename").text("");if(this.original.exists){this.$element.addClass("fileinput-exists").removeClass("fileinput-new")}else{this.$element.addClass("fileinput-new").removeClass("fileinput-exists")}this.$element.trigger("reset.bs.fileinput")};Fileinput.prototype.trigger=function(e){this.$input.trigger("click");e.preventDefault()};var old=$.fn.fileinput;$.fn.fileinput=function(options){return this.each(function(){var $this=$(this),data=$this.data("bs.fileinput");if(!data){$this.data("bs.fileinput",(data=new Fileinput(this,options)))}if(typeof options=="string"){data[options]()}})};$.fn.fileinput.Constructor=Fileinput;$.fn.fileinput.noConflict=function(){$.fn.fileinput=old;return this};$(document).on("click.fileinput.data-api",'[data-provides="fileinput"]',function(e){var $this=$(this);if($this.data("bs.fileinput")){return}$this.fileinput($this.data());var $target=$(e.target).closest('[data-dismiss="fileinput"],[data-trigger="fileinput"]');if($target.length>0){e.preventDefault();$target.trigger("click.bs.fileinput")}})}(window.jQuery);

/*表单存储插件*/
!function(a){function b(a){return"[id="+a.attr("id")+"][name="+a.attr("name")+"]"}a.fn.sisyphus=function(c){var d=a.map(this,function(c){return b(a(c))}).join(),e=Sisyphus.getInstance(d);return e.protect(this,c),e};var c={};c.isAvailable=function(){if("object"==typeof a.jStorage)return!0;try{return localStorage.getItem}catch(b){return!1}},c.set=function(b,c){if("object"==typeof a.jStorage)a.jStorage.set(b,c+"");else try{localStorage.setItem(b,c+"")}catch(d){}},c.get=function(b){if("object"==typeof a.jStorage){var c=a.jStorage.get(b);return c?c.toString():c}return localStorage.getItem(b)},c.remove=function(b){"object"==typeof a.jStorage?a.jStorage.deleteKey(b):localStorage.removeItem(b)},Sisyphus=function(){function f(){return{setInstanceIdentifier:function(a){this.identifier=a},getInstanceIdentifier:function(){return this.identifier},setInitialOptions:function(b){var d={excludeFields:[],customKeySuffix:"",locationBased:!1,timeout:0,autoRelease:!0,onBeforeSave:function(){},onSave:function(){},onBeforeRestore:function(){},onRestore:function(){},onRelease:function(){}};this.options=this.options||a.extend(d,b),this.browserStorage=c},setOptions:function(b){this.options=this.options||this.setInitialOptions(b),this.options=a.extend(this.options,b)},protect:function(b,c){this.setOptions(c),b=b||{};var f=this;if(this.targets=this.targets||[],f.options.name?this.href=f.options.name:this.href=location.hostname+location.pathname+location.search+location.hash,this.targets=a.merge(this.targets,b),this.targets=a.unique(this.targets),this.targets=a(this.targets),!this.browserStorage.isAvailable())return!1;var g=f.options.onBeforeRestore.call(f);if((void 0===g||g)&&f.restoreAllData(),this.options.autoRelease&&f.bindReleaseData(),!d.started[this.getInstanceIdentifier()])if(f.isCKEditorPresent())var h=setInterval(function(){e.isLoaded&&(clearInterval(h),f.bindSaveData(),d.started[f.getInstanceIdentifier()]=!0)},100);else f.bindSaveData(),d.started[f.getInstanceIdentifier()]=!0},isCKEditorPresent:function(){return this.isCKEditorExists()?(e.isLoaded=!1,e.on("instanceReady",function(){e.isLoaded=!0}),!0):!1},isCKEditorExists:function(){return"undefined"!=typeof e},findFieldsToProtect:function(a){return a.find(":input").not(":submit").not(":reset").not(":button").not(":file").not(":password").not(":disabled").not("[readonly]")},bindSaveData:function(){var c=this;c.options.timeout&&c.saveDataByTimeout(),c.targets.each(function(){var d=b(a(this));c.findFieldsToProtect(a(this)).each(function(){if(-1!==a.inArray(this,c.options.excludeFields))return!0;var e=a(this),f=(c.options.locationBased?c.href:"")+d+b(e)+c.options.customKeySuffix;(e.is(":text")||e.is("textarea"))&&(c.options.timeout||c.bindSaveDataImmediately(e,f)),c.bindSaveDataOnChange(e)})})},saveAllData:function(){var c=this;c.targets.each(function(){var d=b(a(this)),f={};c.findFieldsToProtect(a(this)).each(function(){var g=a(this);if(-1!==a.inArray(this,c.options.excludeFields)||void 0===g.attr("name")&&void 0===g.attr("id"))return!0;var h=(c.options.locationBased?c.href:"")+d+b(g)+c.options.customKeySuffix,i=g.val();if(g.is(":checkbox")){var j=g.attr("name");if(void 0!==j&&-1!==j.indexOf("[")){if(f[j]===!0)return;i=[],a("[name='"+j+"']:checked").each(function(){i.push(a(this).val())}),f[j]=!0}else i=g.is(":checked");c.saveToBrowserStorage(h,i,!1)}else if(g.is(":radio"))g.is(":checked")&&(i=g.val(),c.saveToBrowserStorage(h,i,!1));else if(c.isCKEditorExists()){var k=e.instances[g.attr("name")]||e.instances[g.attr("id")];k?(k.updateElement(),c.saveToBrowserStorage(h,g.val(),!1)):c.saveToBrowserStorage(h,i,!1)}else c.saveToBrowserStorage(h,i,!1)})}),c.options.onSave.call(c)},restoreAllData:function(){var c=this,d=!1;c.targets.each(function(){var e=a(this),f=b(a(this));c.findFieldsToProtect(e).each(function(){if(-1!==a.inArray(this,c.options.excludeFields))return!0;var e=a(this),g=(c.options.locationBased?c.href:"")+f+b(e)+c.options.customKeySuffix,h=c.browserStorage.get(g);null!==h&&(c.restoreFieldsData(e,h),d=!0)})}),d&&c.options.onRestore.call(c)},restoreFieldsData:function(a,b){if(void 0===a.attr("name")&&void 0===a.attr("id"))return!1;var c=a.attr("name");!a.is(":checkbox")||"false"===b||void 0!==c&&-1!==c.indexOf("[")?!a.is(":checkbox")||"false"!==b||void 0!==c&&-1!==c.indexOf("[")?a.is(":radio")?a.val()===b&&a.prop("checked",!0):void 0===c||-1===c.indexOf("[")?a.val(b):(b=b.split(","),a.val(b)):a.prop("checked",!1):a.prop("checked",!0)},bindSaveDataImmediately:function(a,b){var c=this;if("onpropertychange"in a?a.get(0).onpropertychange=function(){c.saveToBrowserStorage(b,a.val())}:a.get(0).oninput=function(){c.saveToBrowserStorage(b,a.val())},this.isCKEditorExists()){var d=e.instances[a.attr("name")]||e.instances[a.attr("id")];d&&d.document.on("keyup",function(){d.updateElement(),c.saveToBrowserStorage(b,a.val())})}},saveToBrowserStorage:function(a,b,c){var d=this,e=d.options.onBeforeSave.call(d);(void 0===e||e!==!1)&&(c=void 0===c?!0:c,this.browserStorage.set(a,b),c&&""!==b&&this.options.onSave.call(this))},bindSaveDataOnChange:function(a){var b=this;a.change(function(){b.saveAllData()})},saveDataByTimeout:function(){var a=this,b=a.targets;setTimeout(function(){function b(){a.saveAllData(),setTimeout(b,1e3*a.options.timeout)}return b}(b),1e3*a.options.timeout)},bindReleaseData:function(){var c=this;c.targets.each(function(){var d=a(this),e=b(d);a(this).bind("submit reset",function(){c.releaseData(e,c.findFieldsToProtect(d))})})},manuallyReleaseData:function(){var c=this;c.targets.each(function(){var d=a(this),e=b(d);c.releaseData(e,c.findFieldsToProtect(d))})},releaseData:function(c,e){var f=!1,g=this;d.started[g.getInstanceIdentifier()]=!1,e.each(function(){if(-1!==a.inArray(this,g.options.excludeFields))return!0;var d=a(this),e=(g.options.locationBased?g.href:"")+c+b(d)+g.options.customKeySuffix;g.browserStorage.remove(e),f=!0}),f&&g.options.onRelease.call(g)}}}var d={instantiated:[],started:[]},e=window.CKEDITOR;return{getInstance:function(a){return d.instantiated[a]||(d.instantiated[a]=f(),d.instantiated[a].setInstanceIdentifier(a),d.instantiated[a].setInitialOptions()),a?d.instantiated[a]:d.instantiated[a]},free:function(){return d={instantiated:[],started:[]},null},version:"1.1.3"}}()}(jQuery);
$("form").sisyphus();






