<?php
/**
 * Template name: 更新LitePress
 * Description: 这是申请更新LitePress的模板
 */

get_header();
?>
<link type="text/css" rel="stylesheet" href="https://www.layuicdn.com/layui/css/layui.css"
/>
<script src="https://www.layuicdn.com/layui/layui.js">
</script>
<script src="https://cdn.staticfile.org/blueimp-md5/2.19.0/js/md5.min.js">
</script>
<style>
body {
    font: unset!important;
    font-family: var(--bs-body-font-family)!important;
    font-size: var(--bs-body-font-size)!important;
    font-weight: var(--bs-body-font-weight)!important;
    line-height: var(--bs-body-line-height)!important;
    color: var(--bs-body-color)!important;
}
.letter-spacing-xl > a {
    color: var(--wp-theme-color)!important;
}
</style>
<div class="layui-bg-gray lp-apply-field" style="padding: 30px;">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-panel">
                <div style="padding-left: 30px; padding-right: 30px;">
                    <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                        <ul class="layui-tab-title">
                            <li class="layui-this">
                                提交升级
                            </li>
                            <li>
                                退出升级
                            </li>
                            <li>
                                常见问题
                            </li>
                        </ul>
                        <div class="layui-tab-content" style="height: 100vh;">
                            <div class="layui-tab-item layui-show">
                                <blockquote class="layui-elem-quote">
                                    您可提交此表单来
                                    <span style="color: red;">
										升级
									</span>
                                    到LitePress并接受后续版本更新，在提交前，请确认您的站点
                                    <span style="color: red;">
										安装
									</span>
                                    有
                                    <span style="color: red;">
										WP-China-Yes
									</span>
                                    插件并切换到
                                    <span style="color: red;">
										本土市场
									</span>
                                    ，否则
                                    <span style="color: red;">
										无法接收到更新
									</span>
                                    。
                                </blockquote>
                                <blockquote class="layui-elem-quote">
                                    提交站点需要您完成站点
                                    <span style="color: red;">
										所有权验证
									</span>
                                    ，请按页面提示进行操作，如有问题请发帖咨询。
                                </blockquote>
                                <blockquote class="layui-elem-quote">
                                    LitePress与WordPress
                                    <span style="color: red;">
										完全兼容
									</span>
                                    ，在使用LitePress的过程中，您可以随时到下个页面
                                    <span style="color: red;">
										退出
									</span>
                                    LitePress版本并切换回
                                    <span style="color: red;">
										WordPress
									</span>
                                </blockquote>
                                <hr>
                                <legend>
                                    申请升级LitePress
                                </legend>
                                <form class="layui-form">
                                    <fieldset class="layui-elem-field">
                                        <div class="layui-field-box">
                                            <div class="layui-form-item" style="padding: 20px;">
                                                <label class="layui-form-label">站点</label>
                                                <div class="layui-input-block">
                                                    <input type="url" name="site" lay-reqText="站点不能为空" lay-verify="required"
                                                           autocomplete="off"
                                                           class="layui-input">
                                                </div>
                                            </div>
                                            <div class="layui-form-item">
                                                <label>例如：<span style="color: red;">https://litepress.cn</span>（带协议头，结尾不添加'/'）</label>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <div class="layui-form-item">
                                        <div class="layui-input-block" style="text-align: center;">
                                            <button type="submit" class="layui-btn" lay-submit
                                                    lay-filter="lp-apply">提交
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="layui-tab-item">
                                <blockquote class="layui-elem-quote">
                                    您可提交此表单来
                                    <span style="color: red;">
										退出
									</span>
                                    LitePress版本。
                                </blockquote>
                                <blockquote class="layui-elem-quote">
                                    提交站点需要您完成站点
                                    <span style="color: red;">
										所有权验证
									</span>
                                    ，请按页面提示进行操作，如有问题请发帖咨询。
                                </blockquote>
                                <blockquote class="layui-elem-quote">
                                    LitePress与WordPress
                                    <span style="color: red;">
										完全兼容
									</span>
                                    ，在退出LitePress后，您仍可以随时到上个页面
                                    <span style="color: red;">
										加入
									</span>
                                    LitePress版本。
                                </blockquote>
                                <hr>
                                <legend>
                                    退出升级LitePress
                                </legend>
                                <form class="layui-form">
                                    <fieldset class="layui-elem-field">
                                        <div class="layui-field-box">
                                            <div class="layui-form-item" style="padding: 20px;">
                                                <label class="layui-form-label">站点</label>
                                                <div class="layui-input-block">
                                                    <input type="url" name="site1" lay-reqText="站点不能为空" lay-verify="required"
                                                           autocomplete="off"
                                                           class="layui-input">
                                                </div>
                                            </div>
                                            <div class="layui-form-item">
                                                <label>例如：<span style="color: red;">https://litepress.cn</span>（带协议头，结尾不添加'/'）</label>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <div class="layui-form-item">
                                        <div class="layui-input-block" style="text-align: center;">
                                            <button type="submit" class="layui-btn" lay-submit
                                                    lay-filter="lp-exit">提交
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="layui-tab-item">
                                <div class="layui-collapse" lay-accordion="">
                                  <div class="layui-colla-item">
                                    <h2 class="layui-colla-title">老孙是男是女？</h2>
                                    <div class="layui-colla-content">
                                      <p>女的！
                                      <br>
                                      这个问题不要再出现了。</p>
                                    </div>
                                  </div>
                                  <div class="layui-colla-item">
                                    <h2 class="layui-colla-title">LitePress有何特色？</h2>
                                    <div class="layui-colla-content">
                                      <p>LitePress当前版本整合了Cravatar头像，翻译平台翻译推送等本土特色功能，将在不久后的将来整合新的本土应用市场。</p>
                                    </div>
                                  </div>
                                  <div class="layui-colla-item">
                                    <h2 class="layui-colla-title">LitePress兼容性怎么样？</h2>
                                    <div class="layui-colla-content">
                                      <p>LitePress理论完全兼容WordPress的插件/主题，如有兼容性问题，请发帖求助。</p>
                                    </div>
                                  </div>
                                  <div class="layui-colla-item">
                                    <h2 class="layui-colla-title">在此提交站点与安装Beta插件有何区别？</h2>
                                    <div class="layui-colla-content">
                                      <p>此处提交站点将可接收LitePress稳定版推送，而安装Beta插件将接收LitePress测试版推送。</p>
                                    </div>
                                  </div>
                                  <div class="layui-colla-item">
                                    <h2 class="layui-colla-title">为何提交后仍未收到LitePress推送？</h2>
                                    <div class="layui-colla-content">
                                      <p>提交站点后请确保WP-China-Yes插件设置市场为本土应用市场，然后前往站点后台->仪表盘->更新，点击重新安装按钮覆盖安装即可。</p>
                                    </div>
                                  </div>
                                  <div class="layui-colla-item">
                                    <h2 class="layui-colla-title">如何换回WordPress？</h2>
                                    <div class="layui-colla-content">
                                      <p>退出LitePress后请前往站点后台->仪表盘->更新，点击重新安装按钮覆盖安装即可。</p>
                                      <p>您很可能需要在安装完成后重新启用<span style="color: red;">WP-China-Yes</span>插件，以优化WordPress在天朝的体验。</p>
                                    </div>
                                  </div>
                                  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    layui.use(['jquery', 'layer', 'form'], function () {
        var $ = layui.jquery,
            layer = layui.layer,
            form = layui.form;
        form.on('submit(lp-apply)', function (data) {
            show_apply_panel(data.field.site);
            return false;
        });
        form.on('submit(lp-exit)', function (data) {
            show_exit_panel(data.field.site1);
            return false;
        });
    });
    function show_apply_panel(site) {
    layer.open({
        type: 0
        ,title: '站点所有权验证'
        ,closeBtn: false
        ,area: '105px;'
        ,shade: 0.8
        ,id: 'show_apply_panel' //设定一个id，防止重复弹出
        ,btn: ['开始验证', '取消']
        ,btnAlign: 'r'
        ,moveType: 1
        ,content: '<p>请在<span class="layui-badge">'+site+'</span>根目录下建立<span class="layui-badge">lp-check.txt</span>文件，内容为<span class="layui-badge">'+md5(site)+'</span>。</p><hr><p>配置完成后可<a href="'+site+'/lp-check.txt" target="_blank"><span style="color: red;">点此</span></a>测试访问是否正常，访问正常即可点击下面开始验证按钮提交后端验证。</p>'
        ,success: function(show_site_panel){
            var btn = show_site_panel.find('.layui-layer-btn');
            btn.find('.layui-layer-btn0').click(function() {
                var $ = layui.jquery;
                $.ajax({
                type:"POST",
                url: '/wp-json/lp/apply',
                data:{'site': site},
                datatype: "json",
                //在请求之前调用的函数
                beforeSend:function(){
                    layer.msg('验证中...');
                },
                //成功返回之后调用的函数
                success:function(data){
                    if (data.code == 0) {
                        layer.open({
                            title: '提示',
                            content: data.msg,
                            icon: 1,
                            closeBtn: 1,
                        });
                    } else {
                        layer.open({
                            title: '提示',
                            content: data.msg,
                            icon: 2,
                            closeBtn: 1,
                        });
                    }
                },
                //调用出错执行的函数
                error: function(){
                    layer.msg('请求失败，请检查本地网络！', {icon: 2});
                }
            });
            });
            // 取消按钮
            btn.find('.layui-layer-btn1').click(function() {
                layer.closeAll();
            });
            
        }
    });
    }
    function show_exit_panel(site) {
    layer.open({
        type: 0
        ,title: '站点所有权验证'
        ,closeBtn: false
        ,area: '105px;'
        ,shade: 0.8
        ,id: 'show_exit_panel' //设定一个id，防止重复弹出
        ,btn: ['开始验证', '取消']
        ,btnAlign: 'r'
        ,moveType: 1
        ,content: '<p>请在<span class="layui-badge">'+site+'</span>根目录下建立<span class="layui-badge">lp-check.txt</span>文件，内容为<span class="layui-badge">exit</span>。</p><hr><p>配置完成后可<a href="'+site+'/lp-check.txt" target="_blank"><span style="color: red;">点此</span></a>测试访问是否正常，访问正常即可点击下面开始验证按钮提交后端验证。</p>'
        ,success: function(show_site_panel){
            var btn = show_site_panel.find('.layui-layer-btn');
            btn.find('.layui-layer-btn0').click(function() {
                var $ = layui.jquery;
                $.ajax({
                type:"POST",
                url: '/wp-json/lp/exit',
                data:{'site': site},
                datatype: "json",
                //在请求之前调用的函数
                beforeSend:function(){
                    layer.msg('验证中...');
                },
                //成功返回之后调用的函数
                success:function(data){
                    if (data.code == 0) {
                        layer.open({
                            title: '提示',
                            content: data.msg,
                            icon: 1,
                            closeBtn: 1,
                        });
                    } else {
                        layer.open({
                            title: '提示',
                            content: data.msg,
                            icon: 2,
                            closeBtn: 1,
                        });
                    }
                },
                //调用出错执行的函数
                error: function(){
                    layer.msg('请求失败，请检查本地网络！', {icon: 2});
                }
            });
            });
            // 取消按钮
            btn.find('.layui-layer-btn1').click(function() {
                layer.closeAll();
            });
            
        }
    });
    }
</script>
<img class="bg-image" src="https://api.haozi.xyz/api/v1/pic/acg">
<style>
    .bg-image {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: -9999;
        min-width: 100%;
        min-height: 100%;
    }

    .lp-apply-field {
        opacity: 0.85;
        filter: alpha(Opacity=85);
        -moz-opacity: 0.85;
    }
</style>
<?php get_footer(); ?>
