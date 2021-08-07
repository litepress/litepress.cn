<?php
if( !isset( $lvBpSearchResult ) ) {
	return;
}

$is_ajax = 'ajax' == $lvBpSearchResult->search_type;

// $strThumbnailSize = 'post-thumbnail';
$strThumbnailSize = Array( 90, 90 );
$arrThumbnailArgs = Array( 'class' => 'd-flex align-self-center mr-3' );

if( $is_ajax ) {
	$strThumbnailSize = Array( 60, 60 );
}

if( in_array( $lvBpSearchResult->type, Array( 'posts', 'pages', 'forum', 'topic', 'reply' ) ) ) {
	$arrTermsResult = Array();
	foreach( array( 'category' ) as $strCurTerm ) {
		$strTermName = wp_get_object_terms( get_the_ID(), $strCurTerm, array( 'fields' => 'names' ) );
		if( ! is_wp_error( $strTermName ) && isset( $strTermName[0] ) ) {
			$arrTermsResult[] = sprintf( '<div class="item-meta-li %1$s">%2$s</div>', $strCurTerm, $strTermName[0] );
		}
	} ?>
	<div class="result-item type-<?php echo $lvBpSearchResult->type; ?>">
        <?php
        $reply_id = get_the_ID();
        $topic_id = get_post_meta( $reply_id, '_bbp_topic_id',  true );

        $reply_url = "/forums/topic/{$topic_id}#post-{$reply_id}";
        ?>
		<a href="<?php echo esc_url(add_query_arg( array( 'no_frame' => '1' ), $reply_url ));?>" data-object-id="<?php the_ID(); ?>">
			<div class="item media">
				<?php echo get_avatar(  get_the_author_meta( 'user_email' ), '60' );?>
				<div class="item-body media-body">
					<div class="item-title"><?php the_title();?></div>
					<div class="item-date">
						<i class="jvbpd-icon2-clock2"></i>
						<?php echo get_the_date( get_option( 'date_format' ), get_the_ID() ); ?>
					</div>
					<div class="item-meta">
						<?php echo join( '', $arrTermsResult ); ?>
					</div>
					<?php
					$content = wp_strip_all_tags( get_the_content() );
					preg_match_all("^\[(.*?)\]^", $content, $matches, PREG_PATTERN_ORDER);  //strip all shortcodes in the ajax search content
					$content = str_replace( $matches[0], '', $content );
					$trimmed_content = wp_trim_words( $content, 20, '...' );
					printf( '<div class="item-desc">%s</div>', $trimmed_content ); ?>
				</div>
			</div>
		</a>
	</div>
	<?php
}

if( in_array( $lvBpSearchResult->type, Array( 'listings' ) ) ) {
	$arrTermsResult = Array();
	foreach( array( 'listing_category', 'listing_location' ) as $strCurTerm ) {
		$strTermName = wp_get_object_terms( get_the_ID(), $strCurTerm, array( 'fields' => 'names' ) );
		if( ! is_wp_error( $strTermName ) && isset( $strTermName[0] ) ) {
			$arrTermsResult[] = sprintf( '<div class="item-meta-li %1$s">%2$s</div>', $strCurTerm, $strTermName[0] );
		}
	} ?>
	<div class="result-item type-<?php echo $lvBpSearchResult->type; ?>">
		<a href="<?php echo esc_url(add_query_arg( array( 'no_frame' => '1' ), get_permalink() ));?>" data-object-id="<?php the_ID(); ?>">
			<div class="item media">
				<?php
				if( has_post_thumbnail() ) {
					the_post_thumbnail( $strThumbnailSize, $arrThumbnailArgs );
				}else{
					printf( '<img src="%1$s" class="%2$s">', lava_ajaxSearch()->image_url . '/no-image.png', $arrThumbnailArgs[ 'class' ] );
				} ?>
				<div class="item-body media-body">
					<div class="item-title">
						<?php the_title();?>
					</div>
					<div class="item-meta">
						<?php echo join( '', $arrTermsResult ); ?>
						<div class="item-meta-open-hours">Open now</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<?php
}


if( function_exists('wc_get_product') && in_array( $lvBpSearchResult->type, Array( 'products' ) ) ) {
	$product = wc_get_product(get_the_ID());
	$arrTermsResult = Array();
	foreach( array( 'product_cat' ) as $strCurTerm ) {
		$strTermName = wp_get_object_terms( get_the_ID(), $strCurTerm, array( 'fields' => 'names' ) );
		if( ! is_wp_error( $strTermName ) && isset( $strTermName[0] ) ) {
			$arrTermsResult[] = sprintf( '<div class="item-meta-li %1$s">%2$s : %3$s</div>',
				$strCurTerm,
				get_taxonomy($strCurTerm)->labels->singular_name,
				$strTermName[0]
			);
		}
	} ?>
	<div class="result-item type-<?php echo $lvBpSearchResult->type; ?>">
		<a href="<?php echo esc_url(add_query_arg( array( 'no_frame' => '1' ), get_permalink() ));?>" data-object-id="<?php the_ID(); ?>">
			<div class="item media">
				<?php
				if( has_post_thumbnail() ) {
					the_post_thumbnail( $strThumbnailSize, $arrThumbnailArgs );
				}else{
					printf( '<img src="%1$s" class="%2$s">', lava_ajaxSearch()->image_url . '/no-image.png', $arrThumbnailArgs[ 'class' ] );
				} ?>
				<div class="item-body media-body">
					<div class="item-title"><?php the_title();?></div>
					<div class="item-date">
						<i class="jvbpd-icon2-clock2"></i>
						<?php echo get_the_date( get_option( 'date_format' ), get_the_ID() ); ?>
					</div>
					<div class="item-meta">
						<?php echo join( ',', $arrTermsResult ); ?>
					</div>
					<p><?php echo $product->get_price_html(); ?></p>
					<?php
					$content = wp_strip_all_tags( get_the_content() );
					preg_match_all("^\[(.*?)\]^", $content, $matches, PREG_PATTERN_ORDER);  //strip all shortcodes in the ajax search content
					$content = str_replace( $matches[0], '', $content );
					$trimmed_content = wp_trim_words( $content, 20, '...' );
					printf( '<div class="item-desc">%s</div>', $trimmed_content ); ?>
				</div>
			</div>
		</a>
	</div>
	<?php
}

if( in_array( $lvBpSearchResult->type, Array( 'listing_category', 'listing_keyword' ) ) ) {
	$currentTerm = get_term( $lvBpSearchResult->queried_id, $lvBpSearchResult->type );
	if( ! $currentTerm instanceof WP_Term ) {
		return;
	}
	$termIcon = false;
	if( function_exists( 'lava_directory' ) ) {
		$termIcon = lava_directory()->admin->getTermOption( $currentTerm->term_id, 'icon', $currentTerm->taxonomy, false );
	} ?>
	<div class="result-item type-<?php echo $lvBpSearchResult->type; ?>">
		<a href="<?php echo get_term_link( $currentTerm ); ?>" data-object-id="<?php echo $currentTerm->term_id; ?>"><?php
			$formatTitle = $termIcon ? '<i class="%1$s"></i> %2$s' : '%2$s';
			printf( $formatTitle, $termIcon, $currentTerm->name ); ?>
			<?php /* <span class="item-count"><?php printf( '(%1$s)', $lvBpSearchResult->count ); </span>  */ ?>
		</a>
	</div>
	<?php
}

if( $lvBpSearchResult->type == 'members' ) {
	?>
	<div class="result-item type-<?php echo $lvBpSearchResult->type; ?>">
		<div class="media">
			<a href="<?php echo esc_url( add_query_arg( array( 'no_frame' => '1' ), bp_get_member_permalink() ) ); ?>">
				<?php bp_member_avatar( 'type=thumb&width=60&height=60&class=' . join( ' ', $arrThumbnailArgs ) ); ?>
				<div class="item media-body">
					<div class="item-title"><?php bp_member_name(); ?></div>
					<div class="item-date">
						<i class="jvbpd-icon2-clock2"></i>
						<span class="activity">
							<?php bp_member_last_active(); ?>
						</span>
					</div>
					<div class="item-desc">
						<?php if ( bp_get_member_latest_update() ) : ?>
							<span class="update"> <?php bp_member_latest_update( array( 'view_link' => true  ) ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</a>
		</div>
	</div>
	<?php
}

if( $lvBpSearchResult->type == 'groups' ) {
	?>
	<div class="result-item type-<?php echo $lvBpSearchResult->type; ?>">
		<div class="media">
			<a href="<?php echo esc_url(add_query_arg( array( 'no_frame' => '1' ), bp_get_group_permalink() )); ?>">
				<?php bp_group_avatar( 'type=thumb&width=50&height=50&class=' . join( ' ', $arrThumbnailArgs ) ); ?>
				<div class="item media-body">
					<div class="item-title"><?php bp_group_name(); ?></div>
					<small>
						<i class="jvbpd-icon2-clock2"></i>
						<span class="activity" data-livestamp="<?php bp_core_iso8601_date( bp_get_group_last_active( 0, array( 'relative' => false ) ) ); ?>">
							<?php printf( __( 'active %s', 'lvbp-ajax-search' ), bp_get_group_last_active() ); ?>
						</span>
					</small>

					<div class="item-desc">
						<span class="update"> <?php bp_group_description_excerpt(); ?></span>
					</div>
				</div>
			</a>
		</div>
	</div>
	<?php
}