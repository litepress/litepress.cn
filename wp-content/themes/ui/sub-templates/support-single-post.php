<?php
/**
 * 使用文档单页模板
 */

get_header();

$cats = get_categories();
?>
	<div class="wedocs-single-wrap">
		<div class="ltp-sidebar wedocs-hide-mobile  col-xl-2">


			<ul class="doc-nav-list">
				<?php foreach ( $cats as $item ): ?>
					<li class="page_item page-item-687">
						<a href="<?php echo get_category_link( $item ) ?>"><?php echo $item->cat_name ?><span
								class="wedocs-caret"></span></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="ltp-single-content col-xl-7">
			<article class="docs">
				<?php lpcn_breadcrumb(); ?>
				<main class="heti">
					<header><h1><?php the_title(); ?></h1></header>

					<article class="entry-content"><?php the_content(); ?></article>
				</main>
			</article>
		</div>
		<aside class="col-xl-3">
			<nav class="js-toc"></nav>
			<link href="https://cdn.bootcdn.net/ajax/libs/tocbot/4.12.2/tocbot.min.css" rel="stylesheet">
			<script src="https://cdn.bootcdn.net/ajax/libs/tocbot/4.12.2/tocbot.min.js"></script>

		</aside>
	</div>

	<!-- Modal -->
	<div class="modal  fade" id="translation_doc" tabindex="-1" aria-labelledby="translation_doc" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">翻译</h5>
					<small class="ms-3 badge rounded-pill">

					</small>

					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<div class="mb-3 source-string">
						<p class="original"></p>
						<small>
							<blockquote class="original_zh-cn">
							</blockquote>
						</small>
					</div>
					<div class="form-floating">
                        <textarea class="form-control translation_text" id="floatingTextarea"
                                  style="height: 150px"></textarea>
						<label for="floatingTextarea">译文</label>
						<button class="copy_original mt-2 btn btn-outline-primary btn-sm" tabindex="-1"
						        title="将原始字符串复制到翻译区域（覆盖现有文本）。">
							从原文复制
						</button>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
					<button type="button" class="btn btn-primary ok" id="ok" data-bs-dismiss="modal">提交翻译</button>
				</div>
			</div>
		</div>
	</div>


	<style>

        @font-face {
            font-family: 'wedocs';
            src: url('https://litepress.cn/developer/wp-content/plugins/wedocs/assets/fonts/wedocs.ttf?7jluzu') format('truetype'), url('https://litepress.cn/developer/wp-content/plugins/wedocs/assets/fonts/wedocs.woff?7jluzu') format('woff'), url('https://litepress.cn/developer/wp-content/plugins/wedocs/assets/fonts/wedocs.svg?7jluzu#wedocs') format('svg');
            font-weight: normal;
            font-style: normal;
            font-display: block;
        }

        .sub-banner.banner + .breadcrumb {
            display: none;
        }

        .wedocs-shortcode-wrap ul.wedocs-docs-list li.wedocs-docs-single {
            -webkit-box-shadow: inherit;
            border: 0;
        }

        .wedocs-breadcrumb {
            display: flex;
            padding: 0;
            flex-wrap: wrap;
        }

        .wedocs-breadcrumb [class^='wedocs-icon-'], [class*=' wedocs-icon-'] {
            margin: 0 5px;
        }

        .wp-body {
            padding: 15px !important;
        }

        .wedocs-shortcode-wrap ul.wedocs-docs-list ul.wedocs-doc-sections li {
            list-style: inherit;
        }

        .wp-body-container {
            background: #fff;
        }

        .wedocs-single-wrap .ltp-single-content {
            /*padding-right: calc(var(--bs-gutter-x)/ 2);
			padding-left: calc(var(--bs-gutter-x)/ 2);*/


            padding: 15px;
        }

        .wedocs-single-wrap .ltp-single-content article.docs {
            padding: 0 15%;
        }

        @media screen and (max-width: 640px) {
            .wedocs-single-wrap .ltp-single-content {
                margin: 0;
            }

            .wedocs-single-wrap .ltp-single-content article.docs {
                padding: initial;
            }
        }

        .ltp-sidebar {
            background-color: #fafafa;
        }

        .ltp-single-content .wedocs-breadcrumb {
            margin-bottom: 0 !important;
        }

        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer time {
            font-style: normal !important;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list {
            padding: 12px;
        }

        .heti .entry-title {
            /*margin-block-start:0*/
        }

        .wp-code pre {
            margin: 10px 0;
        }

        .wedocs-single-wrap .ltp-sidebar h3.widget-title {
            font-weight: bold;
            border-bottom: 1px solid #eee;
            margin: 0 0 15px 0;
            padding: 0 0 15px 0;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li > a:before {
            content: '\f07c';
            margin-right: 10px;
            font-family: 'wedocs';
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-open > a > .wedocs-caret,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-closed > a > .wedocs-caret {
            float: right;
            line-height: inherit;
            padding: 0 10px;
            border: 1px solid #fff;
            border-radius: 3px;
            color: #999;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-open > a > .wedocs-caret:hover,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-closed > a > .wedocs-caret:hover {
            border: 1px solid #eee;
            color: #ccc;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-open > a > .wedocs-caret::before,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-closed > a > .wedocs-caret::before {
            font-family: 'wedocs' !important;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-open > a > .wedocs-caret::before {
            content: '\f106';
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-closed > a > .wedocs-caret::before {
            content: '\f107';
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li.wd-state-closed ul.children {
            display: none;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li a {
            display: block;
            padding: 8px 10px 8px 20px;
            text-decoration: none;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li ul.children {
            list-style: none;
            margin: 15px 0 15px 26px;
            padding: 0;
            border: none;
            border-left: 1px solid #ddd;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list li ul.children a {
            color: #333333;
            font-weight: normal;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_parent > a,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_item > a,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_ancestor > a {
            background: var(--wp-theme-color);
            color: #fff;
            border-radius: 5px;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_parent > a .wedocs-caret,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_item > a .wedocs-caret,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_ancestor > a .wedocs-caret {
            color: #fff;
            display: none;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_parent li.current_page_item > a, .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_item li.current_page_item > a, .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_ancestor li.current_page_item > a {
            border-left: 2px solid #3598DB;
            color: #191919;
            font-weight: bold;
            margin-left: -1.5px;
        }

        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_parent ul.children,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_item ul.children,
        .wedocs-single-wrap .ltp-sidebar ul.doc-nav-list > li.current_page_ancestor ul.children {
            display: block;
        }

        .heti {
            color: #262626;
            font-size: 14px;
        }

        .heti p {
            color: #404040
        }

        .wedocs-single-wrap .ltp-single-content {
            border-left: 1px solid #eee;
            padding-left: 25px;
        }

        .wedocs-single-wrap .ltp-single-content ol.wedocs-breadcrumb {
            margin-bottom: 30px;
            list-style: none;
            margin-left: 0;
            padding-left: 0;
        }

        .wedocs-single-wrap .ltp-single-content ol.wedocs-breadcrumb li {
            display: inline;
        }

        .wedocs-single-wrap .ltp-single-content ol.wedocs-breadcrumb li.delimiter {
            color: #999;
            margin: 0 2px;
        }

        .wedocs-single-wrap .ltp-single-content ol.wedocs-breadcrumb a {
            text-decoration: none;
        }

        .wedocs-single-wrap .ltp-single-content article {
            position: relative;
        }

        .wedocs-single-wrap .ltp-single-content article a.wedocs-print-article {
            position: absolute;
            right: 0;
            top: 0;
            color: #777;
        }

        .wedocs-single-wrap .ltp-single-content article a.wedocs-print-article:hover {
            color: #555;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content {
            width: 100% !important;
            float: none !important;
            display: block;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content a.anchorjs-link {
            text-decoration: none;
            box-shadow: none !important;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content img {
            max-width: 100%;
            height: auto;

        }

        .wedocs-single-wrap .ltp-single-content article .entry-content dl {
            margin-bottom: 1em;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content dl dt {
            float: left;
            display: inline-block;
            font-weight: 400;
            text-align: center;
            padding: 4px 9px;
            margin-bottom: 1em;
            font-size: 18px;
            line-height: 1.2em;
            border-radius: 50px;
            color: #fff;
            background: #3197d1;
            vertical-align: baseline;
            white-space: nowrap;
            font-size: 15px;
            min-width: 9px;
            position: relative;
            top: 2px;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content dl dd {
            padding-top: 3px;
            margin: 0 0 5px 45px;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content dl dd:before,
        .wedocs-single-wrap .ltp-single-content article .entry-content dl dd:after {
            display: table;
            content: "";
            line-height: 0;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content dl dd:after {
            clear: both;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content .wedocs-callout {
            border-left: 5px solid;
            color: #333;
            font-size: 14px;
            margin-bottom: 2em;
            padding: 16px 25px 16px 20px;
            background: #f1f1f1;
            border-color: #cfcfcf;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content .wedocs-callout.callout-yellow {
            background: #fffcd5;
            border-color: #e7dca4;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content .wedocs-callout.callout-blue {
            background: #e8f4ff;
            border-color: #bed1e6;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content .wedocs-callout.callout-green {
            background: #e5fadc;
            border-color: #bbdaaf;
        }

        .wedocs-single-wrap .ltp-single-content article .entry-content .wedocs-callout.callout-red {
            background: #fde8e8;
            border-color: #e7aaaa;
        }

        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer {
            margin-top: 60px;
            float: none;
            width: 100%;
            display: block;
        }

        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer:before,
        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer:after {
            content: " ";
            display: table;
        }

        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer:after {
            clear: both;
        }

        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer .wedocs-article-author {
            display: none;
        }

        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer .wedocs-help-link a {
            border-bottom: 1px dotted #C5C5C5;
            text-decoration: none;
        }

        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer .wedocs-help-link a:hover {
            text-decoration: none;
            border-bottom: 1px dotted #999;
        }

        .wedocs-single-wrap .ltp-single-content article footer.entry-footer.wedocs-entry-footer time {
            float: right;
            font-weight: normal;
            font-style: italic;
            font-size: 14px;
        }

        .wedocs-single-wrap .ltp-single-content .wedocs-comments-wrap {
            position: relative;
        }

        .wedocs-single-wrap {
            width: 100%;
            display: -ms-flex;
            display: -webkit-flex;
            display: flex;
        }

        .js-toc {
            padding: 15px;
        }

        .is-active-link::before {
            background-color: var(--wp-theme-color) !important;
        }

        .wedocs-feedback-wrap a.positive {
            background: var(--wp-theme-color) !important;
            border: 1px solid var(--wp-theme-color) !important;
        }

        .wedocs-feedback-wrap a.negative {
            background: none;
            border: 1px solid var(--wp-theme-color) !important;
            color: var(--wp-theme-color) !important;
        }

        .wedocs-feedback-wrap a.negative:hover {
            background: none;
            border: 1px solid var(--wp-theme-color) !important;
            color: var(--wp-theme-color) !important;
        }

        .wedocs-shortcode-wrap ul.wedocs-docs-list li.wedocs-docs-single .inside {
            padding: 5px 0 !important;
            min-height: 0px !important;
            margin-bottom: .5rem;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        .doc-list-meta {
            display: flex;
            flex-wrap: wrap;
            padding: 10px 20px 0px;
            border-top: 1px dashed #b7b7b7;
            justify-content: space-between;
            margin-top: 15px;
        }

        .doc-list-meta i {
            color: #bbb;
            min-width: 17px;
        }

        .doc-list-meta, .doc-list-meta a {
            color: #32373c;
            font-size: 12px;
        }

        .wedocs-docs-single .project-top {
            padding: 15px;
        }

        .wedocs-docs-single .card-body {
            padding: 15px 0 15px;
        }

        .wedocs-docs-single img {
            height: auto;
            max-width: 100%;
        }

        .wedocs-docs-single i {
            margin-right: 10px;
        }

        .project-top .col-7 {
            display: flex;
            flex-direction: column;
        }

        .project-top .btn {
            margin-top: auto;
        }

        .toc-list-item {
            position: relative;
            padding-left: 10px;
            display: block;
            font-size: 13px;
        }

        .toc-list .toc-list .toc-list-item {
            padding: 5px 0 5px 10px;
        }

        .is-active-link {
            color: var(--wp-theme-color) !important;
            font-weight: inherit !important;
        }

        .project-top .col-5 .placeholder {
            height: 180px;
            background: var(--wp-theme-color);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15%;
            font-size: 20px;
            text-align: center;
        }

        .toc-list > li > .node-name--H2 {
            position: relative;
            padding: 5px 0 5px 10px;
            display: block;
        }

        .toc-list > li > .node-name--H2::before {
            top: 0;
        }

        #breadcrumbs {
            color: rgba(0, 0, 0, .65);
        }

        @media (min-width: 992px) {
            .js-toc {
                position: -webkit-sticky;
                position: sticky;
                top: 6rem;
                right: 0;
                z-index: 2;
                height: calc(100vh - 7rem);
                overflow-y: auto
            }
        }

        @media (min-width: 768px) {
            .ltp-sidebar {
                position: -webkit-sticky;
                position: sticky;
                top: 5rem;
                display: block !important;
                height: calc(100vh - 5rem);
                padding-left: .25rem;
                margin-left: -.25rem;
                overflow-y: auto
            }
        }

        .entry-content p, .entry-content li {
            position: relative;
        }

        .entry-content p:hover, .entry-content li:hover, .entry-content h2:hover {
            opacity: .9;
            z-index: 999;
        }

        .entry-content *[original_id] button:hover {
            border-block-end: 0;
            border: 2px solid #fff;
            opacity: .8;
            z-index: 999;
        }

        .customize-partial-edit-shortcut-button {
            position: absolute;
            left: -40px;
            color: #fff;
            width: 30px;
            height: 30px;
            min-width: 30px;
            min-height: 30px;
            line-height: 1 !important;
            font-size: 18px;
            z-index: 5;
            background: #3582c4 !important;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 1px rgb(60 67 74 / 15%);
            text-align: center;
            cursor: pointer;
            box-sizing: border-box;
            padding: 3px;
            animation-fill-mode: both;
            animation-duration: .4s;
            text-shadow: 0 -1px 1px #135e96, 1px 0 1px #135e96, 0 1px 1px #135e96, -1px 0 1px #135e96;
            margin: auto;
            top: 0;
            bottom: 0;
        }

        li .customize-partial-edit-shortcut-button {
            left: -50px;
        }

        .customize-partial-edit-shortcut-button svg {
            fill: #fff;
            min-width: 20px;
            min-height: 20px;
            width: 20px;
            height: 20px;
            margin: auto;
        }


	</style>

	<script>
        var $ = jQuery.noConflict();


        $(function () {

            var toastTrigger = document.getElementById('ok')
            var toastLiveExample = document.getElementById('liveToast')
            if (toastTrigger) {
                toastTrigger.addEventListener('click', function () {
                    var toast = new bootstrap.Toast(toastLiveExample)

                    toast.show()
                })
            }

            $(".heti h2,.heti h3").each(function () {
                text = $(this).text();
                $(this).attr("id", text);
            });
            $("p,h1,h2,h3,h4,li").filter("[original_id]").prepend("<button data-bs-toggle=\"modal\" data-bs-target=\"#translation_doc\" title=\"点击翻译\" class=\"customize-partial-edit-shortcut-button\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 20 20\"><path d=\"M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z\"></path></svg></button>")

            tocbot.init({
                // Where to render the table of contents.
                tocSelector: '.js-toc',
                // Where to grab the headings to build the table of contents.
                contentSelector: '.entry-content ',
                // Which headings to grab inside of the contentSelector element.
                headingSelector: 'h1, h2, h3',
                // For headings inside relative or absolute positioned containers within content.
                hasInnerContainers: true,
                scrollSmooth: true,
                scrollSmoothOffset: -90,
                headingsOffset: 150
            });


            $(".customize-partial-edit-shortcut-button").click(function () {
                var content = "";
                const formData = new FormData();
                const id = $(this).parent().attr("original_id");
                formData.append("project", gp_project_path);
                formData.append("translation_set_slug", "default");
                formData.append("locale_slug", "zh-cn");
                formData.append("original_ids", JSON.stringify({"original_ids": id}));
                $.ajax({
                    url: "/translate/api/translations/-query-by-originals",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function (b) {

                    },
                    success: function (s) {
                        console.log(s);

                        $(".original").text(s[0].original).attr("original_id", s[0].original_id);
                        if (s[0].translations.length != 0) {
                            if (s[0].translations[0].status === "waitting") {
                                $(".modal-header small").addClass("bg-primary").text("状态：正在审核");
                            } else if (s[0].translations[0].status === "fuzzy") {
                                $(".modal-header small").addClass("bg-warning text-dark").text("状态：模糊");
                            } else if (s[0].translations[0].status === "current") {
                                $(".modal-header small").addClass("bg-success").text("状态：当前");
                            } else if (s[0].translations[0].status === "old") {
                                $(".modal-header small").addClass("bg-secondary").text("状态：旧");
                            }

                            if (s[0].translations[0].translation_0 != null) {
                                $(".original_zh-cn").text(s[0].translations[0].translation_0);
                                $(".translation_text").val($(".original_zh-cn").text())
                            }
                        }

                        $(".copy_original").click(function () {
                            $(".translation_text").val($(".original").text())
                        });
                    },
                    error: function (e) {
                        console.log(e);
                    }
                });

            });


            $("#translation_doc .btn.ok").click(function () {
                const content = "";
                const formData = new FormData();
                const wp_original_id = $(".original").attr("original_id");
                const translation_text = $(".translation_text").val()

                let translation = {
                    [wp_original_id]:
                        [
                            translation_text
                        ]
                }

                formData.append("project", gp_project_path);
                formData.append("locale_slug", "zh-cn");
                formData.append("translation", JSON.stringify(translation));

                $.ajax({
                    url: "/translate/api/translations/-new",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function (b) {

                    },
                    success: function (s) {
                        console.log(s);
                        if (s[wp_original_id].translation_status === "current") {

                            $(" .toast-body").html("提交翻译成功,2秒后刷新页面");
                            setTimeout(function () {
                                window.location.reload();//刷新当前页面.
                            }, 2000)
                        } else if (s[wp_original_id].translation_status === "waitting") {
                            $(" .toast-body").html("提交成功，请等待管理员审核");
                        }

                    },
                    error: function (e) {
                        console.log(e);
                        $(" .toast-body").text(e.responseText);
                    }
                });

            });


        });
	</script>


<?php
get_footer();
