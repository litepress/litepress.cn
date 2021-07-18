<?php
/**
 * This file belongs to the YIT Plugin Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @var array  $sub_tabs
 * @var string $current_tab
 * @var string $current_sub_tab
 * @var string $page
 */
!defined( 'ABSPATH' ) && exit; // Exit if accessed directly

?>
<?php if ( !empty( $sub_tabs ) ): ?>
    <div class="yith-plugin-fw-sub-tabs-nav">
        <h3 class="nav-tab-wrapper yith-nav-sub-tab-wrapper">
            <?php foreach ( $sub_tabs as $_key => $_tab ):
                $_defaults = array(
                    'title' => '',
                    'class' => '',
                    'icon'  => '',
                    'url'   => $this->get_nav_url( $page, $current_tab, $_key )
                );
                $_tab = (object) wp_parse_args( $_tab, $_defaults );

                if ( is_array( $_tab->class ) ) {
                    $_tab->class = implode( ' ', $_tab->class );
                }

                if ( $current_sub_tab === $_key ) {
                    $_tab->class = 'nav-tab-active ' . $_tab->class;
                }
                ?>
                <a href="<?php echo $_tab->url ?>" class="yith-nav-sub-tab nav-tab <?php echo $_tab->class ?>">
                    <span class="yith-nav-sub-tab__title"><?php echo $_tab->title; ?></span>
                    <?php if ( $_tab->icon ) : ?>
                        <span class="yith-nav-sub-tab__icon yith-icon-<?php echo $_tab->icon ?>"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </h3>
    </div>
<?php endif; ?>
