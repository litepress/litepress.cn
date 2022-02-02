<?php
if (!class_exists('QL_Widget')) {

    class QL_Widget
    {
        protected static $instance;

        public function __construct()
        {
            if (is_admin()) {
                add_action('wp_network_dashboard_setup', [$this, 'add_dashboard_widget'], -10);
                add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget'], -10);
            }
        }

        public function add_dashboard_widget()
        {
            wp_add_dashboard_widget(
                'quadlayers-dashboard-overview',
                __('QuadLayers News', 'woocommerce-direct-checkout'),
                [$this, 'display_dashboard_widget']
            );
        }

        public function display_dashboard_widget()
        {
            $feed_items = $this->get_feed();

?>
            <div>
                <div>
                    <div style="margin-top: 11px;float: left;width: 70%;">
                        <?php esc_html_e('Hi! We are Quadlayers! Welcome to QuadLayers! We’re a team of international people who have been working in the WordPress sphere for the last ten years.', 'woocommerce-direct-checkout'); ?>
                        <div style="margin-top: 11px; float: left; width: 70%;"><a href="<?php echo admin_url('admin.php?page=' . QLWCDC_DOMAIN . '_suggestions'); ?>" target="_blank" class="button button-secondary"><?php esc_html_e('More products', 'woocommerce-direct-checkout'); ?></a></div>
                    </div>
                    <img style="width: 30%;margin-top: 11px;float: right; max-width: 95px;" src="<?php echo plugins_url('/assets/backend/img/quadlayers.jpg', QLWCDC_PLUGIN_FILE); ?>" />
                </div>
                <div style="clear: both;"></div>
            </div>
            <div style="margin: 16px -12px 0; padding: 12px 12px 0;border-top: 1px solid #eee;">
                <ul>
                    <?php if (is_array($feed_items)) { ?>
                        <?php
                        foreach ($feed_items  as $item) {
                            $link = $item['link'];
                            while (stristr($link, 'http') !== $link) {
                                $link = substr($link, 1);
                            }

                            $link = esc_url(strip_tags($link . '?utm_source=ql_dashboard'));
                            $title = esc_html(trim(strip_tags($item['title'])));

                            if (empty($title)) {
                                $title = __('Untitled', 'woocommerce-direct-checkout');
                            }

                            $desc = html_entity_decode($item['desc'], ENT_QUOTES, get_option('blog_charset'));
                            $desc = esc_attr(wp_trim_words($desc, 15, '...'));
                            $summary = $desc;
                            $summary = '<div class="rssSummary">' . $summary . '</div>';
                            $date = $item['date'];
                            if ($date) {
                                $date = '<span class="rss-date">' . date_i18n(get_option('date_format'), $date) . '</span>';
                            }
                            $author = $item['author'];
                            $author = ucfirst($author);
                            $author = ' <cite>' . esc_html(strip_tags($author)) . '</cite>';
                            printf(__('<li><a href="%s" target="_blank">%s </a>%s%s%s</li>', 'woocommerce-direct-checkout'), $link, $title, $date, $summary, $author);
                        }  ?>
                    <?php } else {
                        printf(__('<li>%s</li>', 'woocommerce-direct-checkout'), $feed_items);
                    } ?>
                </ul>
            </div>
            <div style="display: flex; justify-content: space-between;align-items: center;margin: 16px -12px 0;padding: 12px 12px 0; border-top: 1px solid #eee;">
                <a href="<?php printf('https://quadlayers.com/blog/?utm_source=%s&utm_medium=software&utm_campaign=wordpress&utm_content=dashboard', QLWCDC_DOMAIN); ?>" target="_blank"><?php esc_html_e('Read more like this on our blog', 'woocommerce-direct-checkout') ?></a>
                <a class="button-primary" href="<?php printf('https://quadlayers.com/?utm_source=%s&utm_medium=software&utm_campaign=wordpress&utm_content=dashboard', QLWCDC_DOMAIN); ?>" target="_blank"><?php esc_html_e('QuadLayers', 'woocommerce-direct-checkout') ?></a>
            </div>
<?php
        }

        public function get_feed()
        {
            $rss_items = get_transient('quadlayers_news_feed');

            if ($rss_items === false) {
                $rss = fetch_feed('https://quadlayers.com/news/feed/');

                if (is_wp_error($rss)) {
                    return "An error has occurred, which probably means the feed is down. Try again later";
                }

                if (!$rss->get_item_quantity()) {
                    $rss->__destruct();
                    unset($rss);
                    return "An error has occurred, which probably means the feed is down. Try again later";
                }

                $rss_items = [];

                foreach ($rss->get_items(0, 3) as $item) {
                    $rss_items[] = [
                        'link' =>  $item->get_link(),
                        'author' =>  $item->get_author()->get_name(),
                        'title' => $item->get_title(),
                        'desc' => $item->get_description(),
                        'date' => $item->get_date('U')
                    ];
                }

                set_transient('quadlayers_news_feed', $rss_items, DAY_IN_SECONDS);
            }

            return $rss_items;
        }

        public static function instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }


    QL_Widget::instance();
}
