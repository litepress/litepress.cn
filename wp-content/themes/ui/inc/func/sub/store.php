<?php
/**
 * 禁止解析简码，否则可能被恶意插入内容，同时爬虫抓取的数据中也会有大量内容被解析为简码，索性全部禁用
 */
remove_all_shortcodes();
