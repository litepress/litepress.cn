<?php

add_filter( 'typekit_add_font_category_rules', function( $category_rules ) {

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'body',
			array(
				array( 'property' => 'font-size', 'value' => '1.05em' ),
			)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'body,
		textarea',
			array(
				array( 'property' => 'font-family', 'value' => '"Noto Serif", serif' ),
			)
	);
	
	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'h1',
			array(
				array( 'property' => 'font-size', 'value' => '2.2em' ),
				array( 'property' => 'font-weight', 'value' => '400' ),
			)
	);
		
	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'h2',
			array(
				array( 'property' => 'font-size', 'value' => '2em' ),
				array( 'property' => 'font-weight', 'value' => '400' ),
			)
	);
	
	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'h3',
			array(
				array( 'property' => 'font-size', 'value' => '1.6em' ),
				array( 'property' => 'font-weight', 'value' => '400' ),
			)
	);
	
	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'h4',
			array(
				array( 'property' => 'font-size', 'value' => '1.4em' ),
				array( 'property' => 'font-weight', 'value' => '400' ),
			)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.site-header .site-title',
			array(
				array( 'property' => 'font-family', 'value' => '"Noto Serif", serif' ),
			)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.site-header .site-description',
			array(
				array( 'property' => 'font-family', 'value' => '"Open Sans", sans-serif' ),
				array( 'property' => 'font-size', 'value' => '1.3em' ),
			)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'.entry-author',
			array(
				array( 'property' => 'font-family', 'value' => '"Open Sans", sans-serif' ),
				array( 'property' => 'font-size', 'value' => '1.2em' ),
			)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.widget-title',
			array(
				array( 'property' => 'font-family', 'value' => '"Open Sans", sans-serif' ),
				array( 'property' => 'font-size', 'value' => '1.9em' ),
			)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'.o2-app-controls',
		array(
				array( 'property' => 'font-family', 'value' => '"Open Sans", sans-serif' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.navigation-main ul li a,
		.o2 .comment-likes,
		.o2 .o2-comment-footer-actions ul li a,
		.o2 .o2-app-page-title,
		.o2 .o2-app-new-post h2,
		.o2 .o2-actions,
		.o2-save',
			array(
				array( 'property' => 'font-family', 'value' => '"Open Sans", sans-serif' ),
			)
	);

	return $category_rules;
} );
