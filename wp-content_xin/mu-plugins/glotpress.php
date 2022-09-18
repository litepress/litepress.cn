<?php
/**
 * 因为 GlotPress 很多初始化工作并没有等到全部插件加载完后才执行，所以为了让钩子代码能被处罚，只能在 mu-plugins 中写这一部分自定义代码了
 */

/**
 * 开启 GlotPress 简体中文的复数翻译支持，这个功能不开的话很多项目没法正常翻译，因为有的单数和复数不光是单词形式的转变，就连句子都会变
 */
add_filter( 'gp_locale_definitions_array', function ( array $def_vars ): array {
	if ( isset( $def_vars['zh_cn'] ) ) {
		$def_vars['zh_cn']->nplurals          = 2;
		$def_vars['zh_cn']->plural_expression = 'n != 1';
	}

	return $def_vars;
} );
