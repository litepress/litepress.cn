<?php

class PP_Capabilities_Post_Features {

    /**
     * Recursive search in array.
     *
     * @param string $needle
     * @param array $haystack
     *
     * @return bool
     */
    private static function recursiveInArray($needle, $haystack)
    {
        if ('' === $haystack) {
            return false;
        }

        if (!$haystack) {
            return false;
        }

        foreach ($haystack as $stalk) {
            if ($needle === $stalk
                || (is_array($stalk)
                    && self::recursiveInArray($needle, $stalk)
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public static function elementsLayoutClassic()
    {
        $elements = [];

        $elements[esc_html__('Top Tabs', 'capsman-enhanced')] = [
            '#contextual-help-link-wrap' => ['label' => esc_html__('Help', 'capsman-enhanced')],
            '#screen-options-link-wrap' => ['label' => esc_html__('Screen Options', 'capsman-enhanced')],
        ];

        $elements[esc_html__('Editor', 'capsman-enhanced')] = [
            '.page-title-action' => [
                'label' => esc_html__('Add New', 'capsman-enhanced')
            ],
            '#title' => [
                'label'       => esc_html__('Title', 'capsman-enhanced'), 
                'elements'    => '#titlediv, #title, #titlewrap', 
                'support_key' => 'title'
            ],
            '#postdivrich' => [
                'label'       => esc_html__('Editor', 'capsman-enhanced'), 
                'support_key' => 'editor'
            ],
            '#pageslugdiv' => [
                'label' => esc_html__('Permalink', 'capsman-enhanced')
            ],
            '#media_buttons' => [
                'label'       => esc_html__('Media Buttons (all)', 'capsman-enhanced'), 
                'elements'    => '#media-buttons, #wp-content-media-buttons', 
                'support_key' => 'editor'
            ],
            '#html_editor_button' => [
                'label'       => esc_html__('HTML Editor Button', 'capsman-enhanced'), 
                'elements'    => '#editor-toolbar #edButtonHTML, #quicktags, #content-html, .wp-switch-editor.switch-html',
                'support_key' => 'editor'
            ],
            '#wp-word-count' => [
                'label'       => esc_html__('Word count', 'capsman-enhanced'),
                'support_key' => 'editor'
            ],
        ];

        $elements[esc_html__('Publish Box', 'capsman-enhanced')] = [
            '#submitdiv' => ['label' => esc_html__('Publish Box', 'capsman-enhanced')],
            '#save-post' => ['label' => esc_html__('Save Draft', 'capsman-enhanced')],
            '#post-preview' => ['label' => esc_html__('Preview', 'capsman-enhanced')],
            '.misc-pub-post-status' => ['label' => esc_html__('Publish Status ', 'capsman-enhanced')],
            '.misc-pub-visibility' => ['label' => esc_html__('Publish Visibility', 'capsman-enhanced')],
            '#passworddiv' => ['label' => esc_html__('Password Protect This Post', 'capsman-enhanced')],
            '#misc-publishing-actions' => ['label' => esc_html__('Publish Actions', 'capsman-enhanced')],
            '.misc-pub-curtime' => ['label' => esc_html__('Publish Schedule', 'capsman-enhanced')],
            '#date' => ['label' => esc_html__('Date', 'capsman-enhanced'),                            'elements' => '#date, #datediv, th.column-date, td.date, div.curtime'],
            '#publish' => ['label' => esc_html__('Publish', 'capsman-enhanced')],
        ];

        $elements[esc_html__('Taxonomy Boxes', 'capsman-enhanced')] = [
            '#category' => [
                'label'        => esc_html__('Categories', 'capsman-enhanced'),
                'elements'     => '#categories, #categorydiv, #categorydivsb, th.column-categories, td.categories, #screen-options-wrap label[for=categorydiv-hide]',
                'support_key'  => 'category',
                'support_type' => 'taxonomy'
            ],
            '#category-add-toggle' => [
                'label'        => esc_html__('Add New Category', 'capsman-enhanced'),
                'elements'     => '#category-add-toggle',
                'support_key'  => 'category',
                'support_type' => 'taxonomy'
            ],
            '#post_tag' => [
                'label'       => esc_html__('Tags', 'capsman-enhanced'), 
                'elements'    => '#tags, #tagsdiv,#tagsdivsb,#tagsdiv-post_tag, th.column-tags, td.tags, #screen-options-wrap label[for=tagsdiv-post_tag-hide]',
                'support_key' => 'post_tag',
                'support_type' => 'taxonomy'
            ],
        ];

        end($elements);
        $k = key($elements);
        
        foreach (get_taxonomies(['show_ui' => true], 'object') as $taxonomy => $tx_obj) {
            if (!in_array($taxonomy, ['category', 'post_tag', 'link_category'])) {
                $elements[$k]["#{$tx_obj->name}div"] = [
                    'label'        => $tx_obj->label,
                    'elements'     => "#{$tx_obj->name}, #{$tx_obj->name}div,#{$tx_obj->name}divsb,#tagsdiv-{$tx_obj->name}, th.column-{$tx_obj->name}, td.{$tx_obj->name}, #screen-options-wrap label[for=tagsdiv-{$tx_obj->name}-hide], #screen-options-wrap label[for={$tx_obj->name}div-hide]",
                    'support_key'  => $tx_obj->name,
                    'support_type' => 'taxonomy'
                ];
            }
        }

        $elements[esc_html__('Page Boxes', 'capsman-enhanced')] = [
            '#pageparentdiv' => [
                'label'       => esc_html__('Page Attributes', 'capsman-enhanced'),
                'support_key' => 'page-attributes'
            ],
            '#parent_id' => [
                'label'       => esc_html__('Parent', 'capsman-enhanced'), 
                'elements'    => 'p.parent-id-label-wrapper, #parent_id',
                'support_key' => 'page-attributes'
            ],
            '#page_template' => [
                'label' => esc_html__('Page Template', 'capsman-enhanced'),
                'elements'    => '#page_template',
                'support_key' => 'page-attributes'
            ],
            'p.menu-order-label-wrapper' => [
                'label'       => esc_html__('Order', 'capsman-enhanced'),
                'elements'    => 'p.menu-order-label-wrapper',
                'support_key' => 'page-attributes'
            ],
        ];

        $elements[esc_html__('Other Boxes', 'capsman-enhanced')] = [
            '#postimagediv' => [
                'label'       => esc_html__('Featured Image', 'capsman-enhanced'),
                'elements'    => '#postimagediv, #screen-options-wrap label[for=postimagediv-hide]',
                'support_key' => 'thumbnail'
            ],
            '#slug' => [
                'label' => esc_html__('Post Slug', 'capsman-enhanced'),
                'elements' => '#slugdiv,#edit-slug-box, #screen-options-wrap label[for=slugdiv-hide]'
            ],
            '#commentstatusdiv' => [
                'label' => esc_html__('Discussion', 'capsman-enhanced'),
                'elements'    => '#commentstatusdiv, #screen-options-wrap label[for=commentstatusdiv-hide]',
                'support_key' => 'comments'
            ],
            '#woocommerce-coupon-description' => [
                'label'        => esc_html__('Coupon Description', 'capsman-enhanced'),
                'elements'     => '#woocommerce-coupon-description',
                'support_type' => 'metabox',
                'support_key'  => ['shop_coupon']
            ],
        ];

        end($elements);
        $k = key($elements);

        $post_type_supports = [];

        $def_post_types = array_unique(apply_filters('pp_capabilities_feature_post_types', ['post', 'page']));

        foreach($def_post_types as $post_type) {
            $post_type_supports = array_merge($post_type_supports, get_all_post_type_supports($post_type));
        }

        foreach (array_keys($post_type_supports) as $feature) {
            $label = ucfirst(str_replace(['-', '_'], ' ', $feature));

            switch ($feature) {
                case 'excerpt' :
                    $id = 'postexcerpt';
                    break;

                case 'custom-fields' :
                    $id = 'postcustom';
                    break;

                case 'post-formats' :
                    $id = 'format';
                    break;

                case 'author':
                case 'excerpt':
                case 'trackbacks':
                case 'comments':
                case 'revisions':
                //default:
                    $id = $feature;
                    break;

                default:
                    continue 2;
            }

            $elements[$k][$feature] = [
                'label'    => $label, 
                'elements' => '#' . $id
                . ', #' . $id . 'div'
                . ', th.column-' . $id
                . ', #screen-options-wrap label[for='.$id.'-hide]'
                . ', #screen-options-wrap label[for='.$id.'div-hide]'
                . ', td.' . $id,
                'support_key' => $feature
            ]; //th and td for raw in edit screen
        }

        return apply_filters('pp_capabilities_post_feature_elements_classic', $elements);
    }


    /**
     * Classic Editor screen: Output Styles to Hide UI elements for Editor Features configured as restricted
     */
    public static function applyRestrictionsClassic()
    {
        $restrict_elements = [];

        if (!$post_type = pp_capabilities_get_post_type()) {
            return;
        }

        if (!is_array(get_option("capsman_feature_restrict_classic_{$post_type}", []))) {
            return;
        }

        // Only restrictions associated with this user's role(s) will be applied
        $role_restrictions = array_intersect_key(
            get_option("capsman_feature_restrict_classic_{$post_type}", []), 
            array_fill_keys(wp_get_current_user()->roles, true)
        );

        foreach($role_restrictions as $features) {
            $restrict_elements = array_merge($restrict_elements, self::getElements($features, ['editor_type' => 'classic']));
        }

        // apply the stored restrictions by css
        if ($restrict_elements = array_unique($restrict_elements)) {
            //add inline styles
            ppc_add_inline_style('' . implode(',', array_map('esc_attr', $restrict_elements)) . ' {display:none !important;}');
        }
    }

    /**
     * Classic Editor: Apply / Queue editor feature restrictions
     */
    public static function adminInitClassic($post_type)
    {
        // Get all user roles.
        $user_roles = wp_get_current_user()->roles;
        $ce_post_disabled = get_option("capsman_feature_restrict_classic_{$post_type}", []);

        $disabled_elements_post_ = [];
        $disabled_elements_post_all = [];

        foreach ($user_roles as $role) {
            if (!empty($ce_post_disabled[$role])) {
                $disabled_elements_post_[$role] = (array)$ce_post_disabled[$role];
            }

            if (!empty($disabled_elements_post_[$role])) {
                $disabled_elements_post_all[] = $disabled_elements_post_[$role];
            }
        }

        // Set default editor tinymce
        if (self::recursiveInArray(
            '#editor-toolbar #edButtonHTML, #quicktags, #content-html',
            $disabled_elements_post_all
        )
        ) {
            add_filter('wp_default_editor', function($default) {
                return 'tinymce';
            });
        }

        // Remove media buttons
        if (self::recursiveInArray('media_buttons', $disabled_elements_post_all)
        ) {
            remove_action('media_buttons', 'media_buttons');
        }

        // set meta-box post option
        add_action('admin_head', ['PP_Capabilities_Post_Features', 'applyRestrictionsClassic'], 1);
    }

    /**
     * Gutenberg Editor: Hide UI elements for editor features configured as restricted
     */
    public static function applyRestrictions($post_type)
    {
        $restrict_elements = [];


        if (!is_array(get_option("capsman_feature_restrict_{$post_type}", []))) {
            return;
        }

        // Only restrictions associated with this user's role(s) will be applied
        $role_restrictions = array_intersect_key(
            get_option("capsman_feature_restrict_{$post_type}", []), 
            array_fill_keys(wp_get_current_user()->roles, true)
        );

        foreach($role_restrictions as $features) {
            $restrict_elements = array_merge($restrict_elements, self::getElements($features));
        }

        // apply the stored restrictions by js and css
        if ($restrict_elements = array_unique($restrict_elements)) {

            // script file
            wp_register_script(
                'ppc-features-block-script',
                plugin_dir_url(CME_FILE) . 'includes/features/features-block-script.js',
                ['wp-blocks', 'wp-edit-post']
            );

            //localize script
            wp_localize_script(
                'ppc-features-block-script', 
                'ppc_features', 
                [
                'disabled_panel' => implode(',', $restrict_elements), 
                'taxonomies' => implode(",", get_taxonomies())
                ]
            );

            // register block editor script
            register_block_type(
                'ppc/features-block-script', 
                ['editor_script' => 'ppc-features-block-script']
            );

            //add inline styles
            ppc_add_inline_style('' . implode(',', array_map('esc_attr', $restrict_elements)) . ' {display:none !important;}');
        }
    }

    private static function getElements($feature_names, $args = []) {
        $is_classic = (!empty($args['editor_type']) && ('classic' == $args['editor_type']));

        $feature_names = (array) $feature_names;

        $arr = ($is_classic) ? self::elementsLayoutClassic() : self::elementsLayout();

        $elements = [];

        foreach($arr as $section_features) {
            foreach($section_features as $_feature_name => $feature_info) {
                if (in_array($_feature_name, $feature_names)) {
                    if (!empty($feature_info['elements'])) {
                        $elements = array_merge($elements, explode(',', $feature_info['elements']));
                    } else {
                        $elements[]= $_feature_name;
                    }
                }
            }
        }

        return $elements;
    }

    public static function elementsLayout()
    {
        $elements = [
            esc_html__('Top Bar - Left', 'capsman-enhanced') => [
                'add_block' => ['label' => esc_html__('Add block', 'capsman-enhanced'), 'elements' => '.edit-post-header-toolbar .edit-post-header-toolbar__inserter-toggle.has-icon'],
                'modes' =>     ['label' => esc_html__('Modes', 'capsman-enhanced'),     'elements' => '.edit-post-header-toolbar .components-dropdown:first-of-type'],
                'undo' =>      ['label' => esc_html__('Undo', 'capsman-enhanced'),      'elements' => '.edit-post-header-toolbar .editor-history__undo'],
                'redo' =>      ['label' => esc_html__('Redo', 'capsman-enhanced'),      'elements' => '.edit-post-header-toolbar .editor-history__redo'],
                'details' =>   ['label' => esc_html__('Details', 'capsman-enhanced'),   'elements' => '.edit-post-header__toolbar .table-of-contents'],
                'outline' =>   ['label' => esc_html__('Outline', 'capsman-enhanced'),   'elements' => '.edit-post-header__toolbar .edit-post-header-toolbar__list-view-toggle'],
            ],

            esc_html__('Top Bar - Right', 'capsman-enhanced') => [
                'save_draft' =>       ['label' => esc_html__('Save Draft', 'capsman-enhanced'),       'elements' => '.edit-post-header__settings .components-button.editor-post-save-draft'],
                'switch_to_draft' =>  ['label' => esc_html__('Switch to draft', 'capsman-enhanced'),  'elements' => '.edit-post-header__settings .components-button.editor-post-switch-to-draft'],
                'preview' =>          ['label' => esc_html__('Preview', 'capsman-enhanced'),          'elements' => '.edit-post-header__settings .block-editor-post-preview__dropdown'],
                'publish' =>          ['label' => esc_html__('Publish / Update', 'capsman-enhanced'), 'elements' => '.edit-post-header__settings .editor-post-publish-button__button'],
                'settings' =>         ['label' => esc_html__('Settings', 'capsman-enhanced'),         'elements' => '.edit-post-header__settings .interface-pinned-items button'],
                'options' =>          ['label' => esc_html__('Options', 'capsman-enhanced'),          'elements' => '.edit-post-header__settings .edit-post-more-menu .components-button'],
            ],

            esc_html__('Body', 'capsman-enhanced') => [
                'edit_title' =>   [
                    'label'       => esc_html__('Edit title', 'capsman-enhanced'), 
                    'elements'    => '.wp-block.editor-post-title__block, .wp-block.editor-post-title',
                    'support_key' => 'title'
                ],
                'content' =>      [
                    'label'       => esc_html__('Content', 'capsman-enhanced'), 
                    'elements'    => '.block-editor-block-list__layout',
                    'support_key' => 'editor'
                ],
                'add_new_block' => [
                  'label'         => esc_html__('Add new block', 'capsman-enhanced'), 
                  'elements'      => '.block-editor-inserter__toggle'
                ],
            ],

            esc_html__('Document Panel', 'capsman-enhanced') => [
                'status_visibility' => ['label' => esc_html__('Status & visibility', 'capsman-enhanced'),   'elements' => 'post-status'],
                'template'          => [
                    'label'       => esc_html__('Template', 'capsman-enhanced'),
                    'elements'    => 'template',
                    'support_key' => 'page-attributes'
                ],
                'revisions'         => ['label' => esc_html__('Revisions', 'capsman-enhanced'),             'elements' => '.editor-post-last-revision__title'],
                'permalink' =>         ['label' => esc_html__('Permalink', 'capsman-enhanced'),             'elements' => 'post-link'],
                'categories' =>        [
                    'label'        => esc_html__('Categories', 'capsman-enhanced'), 
                    'elements'     => 'taxonomy-panel-category',
                    'support_key'  => 'category',
                    'support_type' => 'taxonomy'
                ],
                'tags' =>              [
                    'label'        => esc_html__('Tags', 'capsman-enhanced'),
                    'elements'     => 'taxonomy-panel-post_tag',
                    'support_key'  => 'post_tag',
                    'support_type' => 'taxonomy'
                ],
            ]
        ];
        
        end($elements);
        $k = key($elements);

        foreach (get_taxonomies(['show_ui' => true], 'object') as $taxonomy => $tx_obj) {
            if (!in_array($taxonomy, ['category', 'post_tag', 'link_category'])) {
                $elements[$k][$tx_obj->name] = [
                    'label'        => $tx_obj->label, 
                    'elements'     => "taxonomy-panel-$taxonomy",
                    'support_key'  => $tx_obj->name,
                    'support_type' => 'taxonomy'
                ];
            }
        }

        $elements[$k] = array_merge($elements[$k], [
            'featured_image'  => [
                'label'       => esc_html__('Featured image', 'capsman-enhanced'),
                'elements'    => 'featured-image',
                'support_key' => 'thumbnail'
            ],
            'excerpt'         => [
                'label'       => esc_html__('Excerpt', 'capsman-enhanced'),
                'elements'    => 'post-excerpt',
                'support_key' => 'excerpt'
            ],
            'discussion'      => [
                'label'       => esc_html__('Discussion', 'capsman-enhanced'), 
                'elements'    => 'discussion-panel',
                'support_key' => 'comments'
            ],
            'post_attributes' => [
                'label'       => esc_html__('Post Attributes', 'capsman-enhanced'), 
                'elements'    => 'page-attributes',
                'support_key' => 'page-attributes'
            ],
        ]);

        $elements[esc_html__('Block Panel', 'capsman-enhanced')] = [
            'block_panel' =>   ['label' => esc_html__('Block Panel', 'capsman-enhanced'),       'elements' => '.block-editor-block-inspector'],
            'paragraph' =>     ['label' => esc_html__('Paragraph', 'capsman-enhanced'),         'elements' => '.block-editor-block-card'],
            'typography' =>    ['label' => esc_html__('Typography', 'capsman-enhanced'),        'elements' => '.block-editor-block-inspector .components-panel__body:first-of-type'],
            'color' =>         ['label' =>  esc_html__('Color settings', 'capsman-enhanced'),   'elements' => '.block-editor-panel-color-gradient-settings'],
            'text_settings' => ['label' => esc_html__('Text settings', 'capsman-enhanced'),     'elements' => '.block-editor-panel-color-gradient-settings + .components-panel__body'],
        ];

        return apply_filters('pp_capabilities_post_feature_elements', $elements);
    }
}
