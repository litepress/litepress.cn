<?php
gp_title( __( '申请托管第三方项目 - LitePress翻译平台', 'glotpress' ) );
gp_breadcrumb(
	array(
		'<a href="/translate/projects/">项目</a>',
		'<a href="/translate/projects/others/">第三方托管</a>',
		'<a href="/translate/projects/others/-new/">申请</a>',
	)
);
gp_tmpl_header();
?>
    <div class="container">
		<?php echo gp_breadcrumb(); ?>
    </div>
    <div class="container ">
        <div class="setting mb-4">
			<?php /*echo do_shortcode('[contact-form-7 id="1766" title="翻译托管申请"]') */ ?>
			<?php if ( is_user_logged_in() ): ?>
                <form style="max-width: 640px" class="trusteeship_form needs-validation">
                    <h5 >基本信息</h5><small>必填</small>
                    <li class="form-floating  my-3">
                        <input type="text" class="form-control" id="tf_project_name" placeholder="项目名称" required>
                        <label for="">项目名称</label>
                        <div class="invalid-feedback">
                            必填项 *
                        </div>
                    </li>

                    <li class="form-floating mb-3">
                        <input type="text" class="form-control" id="tf_project_textdomin" placeholder="文本域" required>
                        <label for="">文本域</label>
                        <div class="form-text">说明：指 TextDomin，通常为项目的 slug，但也有例外。请务必正确填（例如：a-zh_cn的【a】)</div>
                        <div class="invalid-feedback">
                            必填项 *
                        </div>
                    </li>
                    <li class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="plugin" value="plugin" required>
                        <label class="form-check-label" for="inlineRadio1">插件</label>

                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="thene" value="thene" required>
                        <label class="form-check-label" for="inlineRadio2">主题</label>
                    </div>
                    </li>
                    <hr>
                    <h5 class="mb-3">其他信息</h5>
                    <li class="mb-3">
                        <label for="formFile" class="form-label">图标</label>
                        <input class="form-control" type="file" id="tf_project_icon">
                    </li>

                    <li class="form-floating mb-3">
                        <textarea class="form-control" placeholder="Leave a comment here" id="tf_project_textarea"
                                  style="height: 100px"></textarea>
                        <label for="floatingTextarea2">简介</label>
                    </li>
                    <button type="submit" class="btn btn-primary"><i class="fad fa-paper-plane"></i> 提交</button>
                </form>
			<?php else: ?>
                你需要先 <a href="/login">登录</a>
			<?php endif; ?>
        </div>
    </div>
<?php
gp_tmpl_footer();
