<?php
/**
 * Plugin Name: GP-Super-More
 * Description: 为GlotPress添加翻译记忆库以及基于GitHub的持续集成支持，并开放外部API允许任何人查询记忆库。此插件为WP-China项目的自动化翻译系统定制，不适合通用环境。
 * Author: WP-China
 * Version: 1.0.0
 * Author URI:https://wp-china.org
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

(new GP_SUPER_MORE)->init();

class GP_SUPER_MORE {
    public function init() {
        add_action('gp_translation_saved', [$this, 'translation_saved'], 10, 1);
        add_action('traduttore.updated', [$this, 'traduttore_updated'], 10, 3);
        add_action('rest_api_init', [$this, 'register_rest_routes' ]);
    }

    public function register_rest_routes() {
        register_rest_route(
            'gp-super-more/v1',
            '/query-trans',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'query_trans'],
            ]
        );

        register_rest_route(
            'gp-super-more/v1',
            '/get-task',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_task'],
                'permission_callback' => [$this, 'task_permission'],
            ]
        );

        register_rest_route(
            'gp-super-more/v1',
            '/post-task',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'post_task'],
                'permission_callback' => [$this, 'task_permission'],
            ]
        );

        register_rest_route(
            'gp-super-more/v1',
            '/post-translation',
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'post_translation'],
            ]
        );

        register_rest_route(
            'gp-super-more/v1',
            '/traduttore_update',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'traduttore_update'],
            ]
        );
    }

    /**
     * 分布式翻译网络接口权限校验
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function task_permission(WP_REST_Request $request) {
        global $wpdb;

        $r = $wpdb->get_row($wpdb->prepare('select * from wp_4_gp_trans_servers where `ip`=%s and `key`=%s;', [$_SERVER['REMOTE_ADDR'], $request['key']]));
        if ($r) {
            return true;
        }

        return false;
    }

    /**
     * 提供给任务队列调用的接口，用于从自建Git托管拉取翻译更新
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function traduttore_update(WP_REST_Request $request) {
        do_action('traduttore.update', $request['project_id']);

        return new WP_REST_Response(['code' => 0, 'message' => 'success']);
    }

    /**
     * 用于上传单条翻译结果，这个接口主要供给WP-China-Yes插件端的翻译纠错之用
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function post_translation(WP_REST_Request $request) {
        if (empty($request['slug'])) {
            return new WP_REST_Response(['code' => 1000, 'message' => 'slug is empty'], 400);
        }
        if (empty($request['data'])) {
            return new WP_REST_Response(['code' => 1000, 'message' => 'data is empty'], 400);
        }
        $typy = $request['type'] == 'plugins' ? 1 : 2;
        $project = GP::$project->find_one(['slug' => $request['slug'], 'parent_project_id' => $typy]);
        if (!$project) {
            return new WP_REST_Response(['code' => 1002, 'message' => 'project not found'], 404);
        }

        $data = json_decode($request['data'], true);
        foreach ($data as $v) {
            $original = GP::$original->find_one(['singular' => $v['source'], 'project_id' => $project->id]);
            if (!$original) {
                return new WP_REST_Response(['code' => 1002, 'message' => 'original string not found'], 404);
            }
            $translation_set = GP::$translation_set->find_one(['project_id' => $original->project_id, 'locale' => 'zh-cn']);
            if (!$translation_set) {
                return new WP_REST_Response(['code' => 1002, 'message' => 'translation_set not found'], 404);
            }
            $translation = new GP_Translation();
            $translation_exist = $translation->find_one(['original_id' => $original->id, 'translation_0' => $v['target']]);
            if ($translation_exist) {
                return new WP_REST_Response(['code' => 1110, 'message' => 'the same translation already exists'], 500);
            }
            $translation->translation_set_id = $translation_set->id;
            $translation->original_id = $original->id;
            $translation->status = 'waiting';
            $translation->create([
                'original_id' => $original->id,
                'translation_set_id' => $translation_set->id,
                'translation_0' => $v['target'],
                'user_id' => 1,
                'user_id_last_modified' => 1,
                'status' => 'waiting'
            ]);
        }

        return new WP_REST_Response(['code' => 0, 'message' => 'success']);
    }

    /**
     * 用于分布式翻译网络节点获取翻译任务
     *
     * @return WP_REST_Response
     */
    public function get_task() {
        global $wpdb;

        $r = $wpdb->get_results('select * from wp_4_gp_mt_tasks where `handled_at`<unix_timestamp(now())-600 and `deleted_at` is null limit 100;');
        if ($r) {
            $ids = [];
            $data = [];
            foreach ($r as $task) {
                $ids = array_merge($ids, [$task->id]);
                $data = array_merge($data, [[
                    'original_id' => $task->original_id,
                    'string' => base64_encode($task->source),
                    'exc' => base64_encode($task->exc)
                ]]);
            }

            $update_sql = 'update wp_4_gp_mt_tasks set handled_at=unix_timestamp(now()) where ';
            foreach ($data as $v) {
                if (end($data)['original_id'] === $v['original_id']) {
                    $update_sql .= 'original_id='.$v['original_id'].';';
                } else {
                    $update_sql .= 'original_id='.$v['original_id'].' or ';
                }
            }

            $wpdb->query($update_sql);

            return new WP_REST_Response([
                'code' => 200,
                'data' => $data
            ]);
        }

        return new WP_REST_Response(['code' => 404]);
    }

    /**
     * 用于分布式翻译网络节点上传翻译任务的处理结果
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function post_task(WP_REST_Request $request) {
        global $wpdb;

        $translation = new GP_Translation();

        $data = json_decode($request['data'], true);
        foreach ($data as $v) {
            $tr_text = base64_decode($v['tr_text']);
            $keywords = base64_decode($v['keywords']);

            $original = GP::$original->find_one(['id' => $v['original_id']]);
            $translation_set = GP::$translation_set->find_one(['project_id' => $original->project_id, 'locale' => 'zh-cn']);

            $translation->translation_set_id = $translation_set->id;
            $translation->original_id = $original->id;
            $translation->status = 'fuzzy';
            $r = $translation->create([
                'original_id' => $original->id,
                'translation_set_id' => $translation_set->id,
                'keywords' => $keywords,
                'translation_0' => $tr_text,
                'user_id' => 1,
                'user_id_last_modified' => 1,
                'status' => 'fuzzy'
            ]);

            if ($r) {
                $wpdb->update('wp_4_gp_mt_tasks', [
                    'deleted_at' => time()
                ], [
                    'original_id' => $v['original_id']
                ]);
            }
        }

        // 机器翻译的内容不触发gp_translation_saved钩子，这样也就不会生成语言包也不会生成关键字。语言包会由一个单独的监控系统统一唤醒URL生成，关键字由分布式翻译生成
        // do_action('gp_translation_saved', $translation);

        return new WP_REST_Response(['code' => 200]);
    }

    public function traduttore_updated($project, $stats, $translations) {
        global $wpdb;

        // 对于无法提取出翻译的项目，则跳过并删除之。无法提取翻译的表现是提取出的词条小于等于5条（这五条通常是作者、作者主页、插件名、插件详情、插件主页）
        if (count($translations->entries) <= 5) {
            $wpdb->delete('wp_4_gp_projects', ['id' => $project->get_id()]);
            $wpdb->delete('wp_4_gp_translation_sets', ['project_id' => $project->get_id()]);

            return;
        }

        // 因为在项目数据库中加入了很多自定义字段，为了最小的改动GlotPress核心程序，所以这里根据项目ID手工查询项目数据，以求获取自定义信息
        $project = $wpdb->get_row($wpdb->prepare('select * from wp_4_gp_projects where id=%d;', [$project->get_id()]));

        // 查询是否存在官方翻译，并将官方翻译加入记忆库中
        $ch = curl_init();
        if ($project->parent_project_id == 1) {
            curl_setopt($ch, CURLOPT_URL, 'https://api.wordpress.org/translations/plugins/1.0/?slug='. $project->slug);
        } else {
            curl_setopt($ch, CURLOPT_URL, 'https://api.wordpress.org/translations/themes/1.0/?slug='. $project->slug);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $official_trans = json_decode(curl_exec($ch), true)['translations'];
        curl_close($ch);
        foreach ($official_trans as $language) {
            if ($language['language'] === 'zh_CN') {
                $trans_package_filename = '/tmp/trans-package-'.(string)rand(100000000, 999999999);
                copy($language['package'], $trans_package_filename.'.zip');

                $zip = new ZipArchive();
                $openRes = $zip->open($trans_package_filename.'.zip');
                if ($openRes === true) {
                    $zip->extractTo($trans_package_filename);
                    $zip->close();
                }

                unlink($trans_package_filename.'.zip');

                $official_trans_po_obj = new PO();
                $official_trans_po_obj->import_from_file($trans_package_filename.'/'.$project->slug.'-zh_CN.po');
                foreach ($official_trans_po_obj->entries as $entry) {
                    if (!isset($entry->translations[0]) || !preg_match("/[\x7f-\xff]/", $entry->translations[0])) { // 不存在翻译或翻译不包含中文就跳过
                        continue;
                    }

                    $results = $wpdb->get_row($wpdb->prepare("SELECT level FROM wp_4_gp_memory WHERE md5 = %s ",
                        md5(strtolower($entry->singular))));
                    if ($results && $results->level === 2) {
                        continue;
                    }

                    $wpdb->replace('wp_4_gp_memory', [
                        'md5' => md5(strtolower(trim($entry->singular))),
                        'source' => trim($entry->singular),
                        'target' => $entry->translations[0],
                        'user_id' => 1
                    ]);
                }
            }
        }

        foreach ($translations->entries as $entry) {
            $original = GP::$original->find_one(['project_id' => $project->id, 'singular' => $entry->singular]);
            $translation = new GP_Translation();
            $trans_exist = $translation->find_one(['original_id' => $original->id]);

            if (!$trans_exist) { // 如果是新增的翻译语句
                $trans_memory = $wpdb->get_row($wpdb->prepare("select target from wp_4_gp_memory where `md5` = %s;", md5(strtolower(trim($entry->singular)))));
                if ($entry->singular == $project->name || $entry->singular == $project->slug || $entry->singular == $project->author) {
                    // 字段如果是需要排除的项目名、项目slug、项目作者这些，则直接调用原文
                    $translation_set = GP::$translation_set->find_one(['project_id' => $project->id, 'locale' => 'zh-cn']);
                    $translation->translation_set_id = $translation_set->id;
                    $translation->original_id = $original->id;
                    $translation->translation_0 = $entry->singular;
                    $translation->status = 'current';
                    $translation->create([
                        'original_id' => $original->id,
                        'translation_set_id' => $translation_set->id,
                        'translation_0' => $entry->singular,
                        'user_id' => 1,
                        'user_id_last_modified' => 1,
                        'status' => 'current'
                    ]);
                    $translation->id = $wpdb->insert_id;
                    do_action('gp_translation_saved', $translation);
                } else if (!empty($trans_memory)) { // 如果翻译记忆库中存在翻译结果
                    $translation_set = GP::$translation_set->find_one(['project_id' => $project->id, 'locale' => 'zh-cn']);
                    $translation->translation_set_id = $translation_set->id;
                    $translation->original_id = $original->id;
                    $translation->translation_0 = $trans_memory->target;
                    $translation->status = 'current';
                    $translation->create([
                        'original_id' => $original->id,
                        'translation_set_id' => $translation_set->id,
                        'translation_0' => $trans_memory->target,
                        'user_id' => 1,
                        'user_id_last_modified' => 1,
                        'status' => 'current'
                    ]);
                    $translation->id = $wpdb->insert_id;
                    do_action('gp_translation_saved', $translation);
                } else { // 如果翻译记忆库中不存在翻译结果
                    $wpdb->replace('wp_4_gp_mt_tasks', [
                        'original_id' => $original->id,
                        'source' => $entry->singular,
                        'exc' => sprintf('%s,,%s,,%s', $project->name, $project->slug, $project->author)
                    ]);
                }
            }
        }

        // 通知分布式翻译节点执行任务
        $servers = $wpdb->get_results('select * from wp_4_gp_trans_servers where error_count<3;');

        foreach ($servers as $server) {
            $request = new WP_Http();
            $r = $request->post( 'http://'.$server->ip.':4308/task-notice', ['timeout' => 5]);

            if (!key_exists('body', $r) || empty($r['body'])) {
                $wpdb->update('wp_4_gp_trans_servers', [
                    'error_count' => $server->error_count + 1
                ], [
                    'id' => $server->id
                ]);
            } else {
                $body = json_decode($r['body'], true);
                $wpdb->update('wp_4_gp_trans_servers', [
                    'version' => $body['version']
                ], [
                    'id' => $server->id
                ]);
            }
        }
    }


    public function translation_saved($translation_object) {
        global $wpdb;

        // 已经审核通过的字符串和机器翻译的模糊字符串需要生成关键字索引，机翻的索引由分布式翻译网络中的节点生成，这里只生成审核通过的
        if ($translation_object->status === 'current') {
            // TODO
            // 当有已审核通过的翻译提交时立刻自动保存mo文件到文档翻译
            $format = gp_array_get( GP::$formats, 'mo', null );
            $project = GP::$project->by_path( 'docs/what-is-a-plugin' );
            $locale  = GP_Locales::by_slug( 'zh-cn' );
            $translation_set = GP::$translation_set->by_project_id_slug_and_locale( $project->id,'default', 'zh-cn' );
            $entries = GP::$translation->for_export( $project, $translation_set, '/projects/docs/what-is-a-plugin/zh-cn/default/export-translations/?filters%5Bstatus%5D=current_or_waiting_or_fuzzy_or_untranslated' );

            file_put_contents(WP_CONTENT_DIR.'/plugins/wcorg-doc-trans/languages/what-is-a-plugin-zh_CN.mo', $format->print_exported_file( $project, $locale, $translation_set, $entries ));


            if (!empty($translation_object->user_id) && $translation_object->user_id != 1 && !stristr($_SERVER['REQUEST_URI'], '/zh-cn/default/import-translations/')) {
                $translator = $wpdb->get_row($wpdb->prepare('SELECT count FROM wp_4_gp_translators WHERE user_id = %d ', [$translation_object->user_id]));
                if ($translator) {
                    $wpdb->update('wp_4_gp_translators', [
                        'count' => $translator->count + 1
                    ], [
                        'user_id' => $translation_object->user_id
                    ]);
                } else {
                    $wpdb->insert('wp_4_gp_translators', [
                        'user_id' => $translation_object->user_id,
                        'count' => 1
                    ]);
                }
            }

            $results = $wpdb->get_row($wpdb->prepare("SELECT singular FROM wp_4_gp_originals WHERE id = %d ",
                $translation_object->original_id));

            $wpdb->replace('wp_4_gp_memory', [
                'md5' => md5(strtolower(trim($results->singular))),
                'source' => $results->singular,
                'target' => $translation_object->translation_0,
                'user_id' => 1
            ]);
        }
    }

    /**
     * 用于测试通知分布式节点执行任务
     */
    public function query_trans() {
        $post_data = [
            'app_key' => 'sbvrgrgbg10rgye5y5ebfbgdyyhdgsrg',
            'app_token' => 'usbvbhu0srfefeafrrgy5rgrgrfrfegsrg',
            'queue_name' => 'translation_extraction',
            'type' => 'real_time',
            'stepping_time' => 0,
            'max_time_interval' => 0
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://127.0.0.1:5999/api/addQueue');
        curl_setopt($curl, CURLOPT_POST, 1 );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_exec($curl);

        $post_data = [
            'app_key' => 'sbvrgrgbg10rgye5y5ebfbgdyyhdgsrg',
            'app_token' => 'usbvbhu0srfefeafrrgy5rgrgrfrfegsrg',
            'queue_name' => 'translation_extraction',
            'url' => 'real_time'
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://127.0.0.1:5999/api/addQueue');
        curl_setopt($curl, CURLOPT_POST, 1 );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_exec($curl);
        /*
        global $wpdb;

        $servers = $wpdb->get_results('select * from wp_4_gp_trans_servers where error_count<3;');

        foreach ($servers as $server) {
            $request = new WP_Http();
            $r = $request->post( 'http://'.$server->ip.':4308/task-notice', ['timeout' => 5]);

            if (!key_exists('body', $r) || empty($r['body'])) {
                $wpdb->update('wp_4_gp_trans_servers', [
                    'error_count' => $server->error_count + 1
                ], [
                    'id' => $server->id
                ]);
            } else {
                $body = json_decode($r['body'], true);
                $wpdb->update('wp_4_gp_trans_servers', [
                    'version' => $body['version']
                ], [
                    'id' => $server->id
                ]);
            }
        }*/
    }
}
