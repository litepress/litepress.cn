<?php
/**
 * 应用发布Post Meta Box的模板
 */
?>
<!--html-->
<input type="hidden" name="_downloadable" id="_downloadable" value="on">
<input type="hidden" name="_is_api" id="_is_api" value="on">
<input type="hidden" name="comment_status" value="open">

<section class="app-release" id="woocommerce-product-data">
    <ul class="app_release_tabs">
        <li class="general_options general_tab theme-item active">
            <span class="dashicons dashicons-admin-tools"></span><span>基础设置</span>
        </li>
        <li class="inventory_options inventory_tab item"
            style="">
            <span class="dashicons dashicons-menu-alt"></span><span>短描述</span>
        </li>
        <li class="shipping_options shipping_tab theme-item">
            <span class="dashicons dashicons-editor-justify "></span><span>长描述</span>
        </li>
        <li class="linked_product_options linked_product_tab item">
            <span class="dashicons dashicons-admin-generic"></span><span>安装方法</span>
        </li>
        <li class="attribute_options attribute_tab item">
            <span class="dashicons dashicons-clipboard"></span><span>修订历史</span>
        </li>
        <li class="variations_options variations_tab item">
            <span class="dashicons dashicons-editor-ul"></span><span>常见问题</span>
        </li>
    </ul>
    <div id="box">
        <ul>
            <li class="active">
                <div class="options_group" style="display: block;">
                    <section class="form-field product-type_field ">
                        <label for="product-type">产品性质</label>
                        <div>
                            <select id="product-type" name="product-type">
                                <optgroup label="产品性质">
                                    <option value="simple" <?php selected( $value['product-type'], 'simple' ) ?>>平台应用
                                    </option>
                                    <option value="external" <?php selected( $value['product-type'], 'external' ) ?>>
                                        外部应用
                                    </option>
                                </optgroup>
                            </select>
                            <span class="description">如果你拥有自己的销售平台，则可以将“产品性质”设置为“外部应用”，这样就可以将购买按钮重定向到你自己的平台了^_^</span>
                        </div>
                    </section>
                    <section class="form-field app-type_field ">
                        <label for="app-type">产品类型</label>
                        <div>
                            <select id="app-type" name="tax_input[product_cat][]">
                                <optgroup label="产品类型">
                                    <option value="15" <?php if ( in_array( 15, $value['product_cat_ids'] ) ) {
										echo 'selected="selected"';
									} ?>>插件
                                    </option>
                                    <option value="17" <?php if ( in_array( 17, $value['product_cat_ids'] ) ) {
										echo 'selected="selected"';
									} ?>>主题
                                    </option>
                                </optgroup>
                            </select>
                        </div>
                    </section>
                    <section class="form-field app-sub-type_field ">
                        <label for="app-sub-type">产品子类型</label>
                        <div>
                            <ul id="app-sub-type" name="app-sub-type">
								<?php
								$plugin_cats = get_categories( [
										'hide_empty' => false,
										'taxonomy'   => 'product_cat',
										'child_of'   => 15
									]
								);

								foreach ( $plugin_cats as $plugin_cat_value ):
									?>
                                    <li class="plugin-sub-cat-item">
                                        <label>
                                            <input type="checkbox" name="tax_input[product_cat][]"
                                                   value="<?php echo $plugin_cat_value->term_id; ?>" <?php if ( in_array( $plugin_cat_value->term_id, $value['product_cat_ids'] ) ) {
												echo 'checked="checked"';
											} ?>> <?php echo $plugin_cat_value->name; ?>
                                        </label>
                                    </li>
								<?php endforeach; ?>

								<?php
								$plugin_cats = get_categories( [
										'hide_empty' => false,
										'taxonomy'   => 'product_cat',
										'child_of'   => 17
									]
								);

								foreach ( $plugin_cats as $plugin_cat_value ):
									?>
                                    <li class="theme-sub-cat-item">
                                        <label>
                                            <input type="checkbox" name="tax_input[product_cat][]"
                                                   value="<?php echo $plugin_cat_value->term_id; ?>" <?php if ( in_array( $plugin_cat_value->term_id, $value['product_cat_ids'] ) ) {
												echo 'checked="checked"';
											} ?>> <?php echo $plugin_cat_value->name; ?>
                                        </label>
                                    </li>
								<?php endforeach; ?>
                            </ul>
                            <span class="description">如果当前没有合适的子分类则可以选择留空。或向我们<a
                                        href="/store/wp-admin/admin.php?page=wcpv-vendor-support"
                                        target="_blank">提交建议</a>。</span>
                        </div>
                    </section>
                </div>
                <div class="options_group" style="display: block;">
                    <section class="form-field _regular_price_field ">
                        <label for="_regular_price">价格 (¥)</label>
                        <input type="text" class="short wc_input_price" style="" name="_regular_price"
                               id="_regular_price" value="<?php echo $value['_regular_price'][0] ?? '' ?>"
                               placeholder="0.00">
                    </section>
                </div>

                <div class="platform item">
                    <div class="options_group" style="display: block;">
                        <section class="form-field _access_expires_field ">
                            <label for="_access_expires">KEY有效期 (天)</label>
                            <div>
                                <input type="number" class="short wc_input_price" style="" name="_access_expires"
                                       id="_access_expires" value="<?php echo $value['_access_expires'][0] ?? '' ?>"
                                       placeholder="0">
                                <span class="description">留空为不限制</span>
                            </div>
                        </section>
                        <section class="form-field _api_activations_field ">
                            <label for="_sale_price">KEY激活次数限制</label>
                            <div>
                                <input type="number" class="short wc_input_price"
                                       style="" name="_api_activations" id="_api_activations"
                                       value="<?php echo $value['_api_activations'][0] ?? '' ?>" placeholder="0">
                                <span class="description">留空为不限制</span>
                            </div>
                        </section>
                    </div>
                    <div class="options_group" style="">
                        <section class="form-field _api_resource_product_id_field ">
                            <label for="_api_resource_product_id">应用ID</label>
                            <div>
                                <input type="text" name="_api_resource_product_id"
                                       value="<?php echo $value['_api_resource_product_id'][0] ?? '' ?>" disabled/>
                                <span class="description">应用ID是对接<a>应用授权SDK</a>的依据，如果此处为空请先发布应用并再次查看</span>
                            </div>
                        </section>
                        <section class="form-field _api_new_version_field ">
                            <label for="_api_new_version">应用版本号</label>
                            <div>
                                <input type="text" name="_api_new_version"
                                       value="<?php echo $value['_api_new_version'][0] ?? '' ?>" placeholder="1.0.0"/>
                                <span class="description">可参考：<a>应用版本号规范</a></span>
                            </div>
                        </section>
                        <section class="form-field _wcpv_product_commission_field downloadable_files">
                            <label for="_wcpv_product_commission">安装包</label>
                            <div>
                                <table class="widefat">
                                    <thead>
                                    <tr>
                                        <th>名称 <span class="woocommerce-help-tip"><!--展示给顾客的下载名称--></span></th>
                                        <th colspan="2">文件URL <span class="woocommerce-help-tip"><!--这里是用户可以访问的文件URL，输入在这里的URL应该已经经过编码--></span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="ui-sortable" style="">
                                    <tr>
										<?php
										$downloadable_files = array();
										if ( isset( $value['_downloadable_files'][0] ) ) {
											$downloadable_files = unserialize( $value['_downloadable_files'][0] );
										}

										$file_id   = '';
										$file_meta = array();

										foreach ( (array) $downloadable_files as $k => $v ) {
											$file_id   = $k;
											$file_meta = $v;
										}
										?>
                                        <td class="file_name">
                                            <input type="text" class="input_text" placeholder="文件名"
                                                   name="_wc_file_names[]"
                                                   value="<?php echo $file_meta['name'] ?? ''; ?>">
                                            <input type="hidden" name="_wc_file_hashes[]"
                                                   value="<?php echo $file_id; ?>">
                                        </td>
                                        <td class="file_url"><input type="text" class="input_text" placeholder="http://"
                                                                    name="_wc_file_urls[]"
                                                                    value="<?php echo $file_meta['file'] ?? ''; ?>">
                                        </td>
                                        <td class="file_url_choose" width="1%"><a href="#"
                                                                                  class="button upload_file_button"
                                                                                  data-choose="选择文件"
                                                                                  data-update="文件路径/URL">选择文件</a></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                </div>

                <div class="external item">
                    <div class="options_group">
                        <section class="form-field _product_url_field ">
                            <label for="_api_tested_up_to">外部产品URL</label>
                            <div>
                                <input type="text" name="_product_url"
                                       value="<?php echo $value['_product_url'][0] ?? '' ?>"
                                       placeholder="https://www.baidu.com"/>
                                <span class="description">该应用在外部平台的URL，当用户点击”购买“按钮时会被重定向到该URL。</span>
                            </div>
                        </section>
                    </div>

                </div>
                <div class="options_group" style="display: block;">
                    <section class="form-field _api_version_required_field ">
                        <label for="_api_version_required">至少需要WP版本</label>
                        <div>
                            <input type="text" name="_api_version_required"
                                   value="<?php echo $value['_api_version_required'][0] ?? '' ?>"
                                   placeholder="5.4.1"/>
                            <span class="description"></span>
                        </div>
                    </section>
                    <section class="form-field _api_tested_up_to_field ">
                        <label for="_api_tested_up_to">兼容至WP版本</label>
                        <div><input type="text" name="_api_tested_up_to"
                                    value="<?php echo $value['_api_tested_up_to'][0] ?? '' ?>" placeholder="5.8.1"/>
                            <span class="description"></span></div>
                    </section>
                    <section class="form-field _api_requires_php_field ">
                        <label for="_api_requires_php">至少需要PHP版本</label>
                        <div><input type="text" name="_api_requires_php"
                                    value="<?php echo $value['_api_requires_php'][0] ?? '' ?>" placeholder="8.1"/>
                            <span class="description"></span></div>
                    </section>
                </div>
            </li>
            <li>
                <textarea name="excerpt" class='theEditor'><?php echo $value['excerpt'][0] ?? '' ?></textarea>
                <p>不支持HTML，推荐字数在70个汉字或140个英文字母以内。</p>
            </li>
            <li><?php wp_editor( $value['51_default_editor'][0] ?? '', '51_default_editor', array( 'textarea_name' => 'yith_product_tabs[51][default_editor]' ) ); ?></li>
            <li><?php wp_editor( $value['365_default_editor'][0] ?? '', '365_default_editor', array( 'textarea_name' => 'yith_product_tabs[365][default_editor]' ) ); ?></li>
            <li><?php wp_editor( $value['47_default_editor'][0] ?? '', '47_default_editor', array( 'textarea_name' => 'yith_product_tabs[47][default_editor]' ) ); ?></li>
            <li>
                <div id="46_tab" class="panel woocommerce_options_panel yith_tab_manager_product">
                    <div class="custom_tab_options">
                        <div class="form-field downloadable_files" style="padding:10px;">
                            <table class="widefat" data-tab_id="46">
                                <thead>
                                <tr>
                                    <th class="sort">&nbsp;</th>
                                    <th style="text-align: center;">
                                        问题 <span class="tips" data-tip="这是向客户展示的问题。">[?]</span>
                                    </th>
                                    <th colspan="2" style="text-align: center;">
                                        答案 <span class="tips" data-tip="这是显示给客户的答案。">[?]</span>
                                    </th>
                                    <th>&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
								<?php
								$faqs = array();
								if ( isset( $value['46_custom_list_faqs'][0] ) ) {
									$faqs = unserialize( $value['46_custom_list_faqs'][0] );
								}
								?>
								<?php foreach ( $faqs as $k => $v ): ?>
                                    <tr style="">
                                        <td class="sort"></td>
                                        <td class="file_name"><input type="text" class="input_text"
                                                                     placeholder="这里添加常见问题"
                                                                     name="yith_product_tabs[46][faq_questions][]"
                                                                     value="<?php echo $v['question']; ?>"></td>
                                        <td class="file_url"><input type="text" class="input_text" placeholder="这里添加答案"
                                                                    name="yith_product_tabs[46][faq_answers][]"
                                                                    value="<?php echo $v['answer']; ?>"></td>
                                        <td width="1%"><a href="#" class="delete">Delete</a></td>
                                    </tr>
								<?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="5">
                                        <a href="#" class="button insert" data-row="&lt;tr&gt;
    &lt;td class=&quot;sort&quot;&gt;&lt;/td&gt;
    &lt;td class=&quot;file_name&quot;&gt;&lt;input type=&quot;text&quot; class=&quot;input_text&quot; placeholder=&quot;在这里添加问题&quot; name=&quot;yith_product_tabs[46][faq_questions][]&quot; value=&quot;&quot; /&gt;&lt;/td&gt;
    &lt;td class=&quot;file_url&quot;&gt;&lt;input type=&quot;text&quot; class=&quot;input_text&quot; placeholder=&quot;在这里添加答案&quot; name=&quot;yith_product_tabs[46][faq_answers][]&quot; value=&quot;&quot; /&gt;&lt;/td&gt;
    &lt;td width=&quot;1%&quot;&gt;&lt;a href=&quot;#&quot; class=&quot;delete&quot;&gt;删除&lt;/a&gt;&lt;/td&gt;
&lt;/tr&gt;">添加问题</a>
                                    </th>
                                </tr>
                                </tfoot>

                            </table>

                        </div>
                    </div>
            </li>
        </ul>
    </div>
</section>