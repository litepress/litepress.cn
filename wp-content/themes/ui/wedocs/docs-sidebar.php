<div class="ltp-sidebar wedocs-hide-mobile  col-xl-2">
    <?php
    $ancestors = [];
    $root      = $parent = false;

    if ( $post->post_parent ) {
        $ancestors = get_post_ancestors( $post->ID );
        $root      = count( $ancestors ) - 1;
        $parent    = $ancestors[$root];
    } else {
        $parent = $post->ID;
    }

    // var_dump( $parent, $ancestors, $root );
    $walker   = new WeDevs\WeDocs\Walker();
    $children = wp_list_pages( [
        'title_li'  => '',
        'order'     => 'menu_order',
        'child_of'  => $parent,
        'echo'      => false,
        'post_type' => 'docs',
        'walker'    => $walker,
    ] );
    ?>
    <header class="  d-flex aside-header align-items-center">
        <div class="me-2 wp-icon">
            <i class="fas fa-clipboard-list-check fa-fw" style=""></i></div>
        <span><?php echo get_post_field( 'post_title', $parent, 'display' ); ?></span></header>


    <?php if ( $children ) { ?>
        <ul class="doc-nav-list">
            <?php echo $children; ?>
        </ul>
    <?php } ?>
</div>
