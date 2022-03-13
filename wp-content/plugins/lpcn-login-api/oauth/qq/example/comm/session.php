<?php
/**
 * PHP SDK for QQ登录 OpenAPI
 *
 * @version 1.5
 * @author connect@qq.com
 * @copyright © 2011, Tencent Corporation. All rights reserved.
 */

/**
 * @brief 本文件用来设置session配置
 *        以解决“不同子域名下共享session”以及“同一个主域名不同服务器之间共享session”的问题。
 *        使用前请对下文的“修改点”按照说明进行修改
 */


/**
 * 修改点1：使用前，请创建数据库表。下面是在MySQL中创建tbl_session表的示例
 *
 * CREATE TABLE `tbl_session` (
 *     `session_id` varchar(255) binary NOT NULL default '',
 *     `session_expires` int(10) unsigned NOT NULL default '0',
 *     `session_data` text,
 *     PRIMARY KEY  (`session_id`)
 *    ) ENGINE=MyISAM;
 */


/**
 * @brief 实现自定义session的打开，关闭，读取，写入，销毁，垃圾回收等过程。
 *
 * 修改点2：请对下列需要指定的变量值进行指定
 */
class Session 
{
    //mysql的主机地址，需要第三方指定ip地址 
    const db_host = "localhost"; 

    //数据库用户名，需要第三方指定自己的用户名
    const db_user = "root";   

    //数据库密码，需要第三方指定自己的库据库密码
    const db_pwd = "root"; 

    //数据库，需要第三方指定数据库
    const db_name = "test";      

    //数据库表，需要第三方指定数据表
    const db_table = "tbl_session"; 

    //mysql-handle
    private $db_handle;

    //session-lifetime
    private $lifeTime;

    function open($savePath, $sessName) 
    {
        // get session-lifetime
        $this->lifeTime = get_cfg_var("session.gc_maxlifetime");

        // open database-connection
        $db_handle = mysql_connect(self::db_host, self::db_user, self::db_pwd);

        $dbSel = mysql_select_db(self::db_name, $db_handle);

        // return success
        if(!$db_handle || !$dbSel)
            return false;

        $this->db_handle = $db_handle;
        return true;
    }

    function close() 
    {
        $this->gc(ini_get('session.gc_maxlifetime'));
        // close database-connection
        return mysql_close($this->db_handle);
    }

    function read($sessID) 
    {
        // fetch session-data
        $res = mysql_query("SELECT session_data AS d FROM ".self::db_table." 
            WHERE session_id = '$sessID'
            AND session_expires > ".time(), $this->db_handle);

        // return data or an empty string at failure
        if($row = mysql_fetch_assoc($res))
            return $row['d'];

        return "";
    }

    function write($sessID, $sessData) 
    {
        // new session-expire-time
        $newExp = time() + $this->lifeTime;

        // is a session with this id in the database?
        $res = mysql_query("SELECT * FROM ".self::db_table." 
            WHERE session_id = '$sessID'", $this->db_handle);

        echo mysql_error();

        // if yes,
        if(mysql_num_rows($res)) 
        {
            // ...update session-data
            mysql_query("UPDATE ".self::db_table." 
                SET session_expires = '$newExp',
                session_data = '$sessData'
                WHERE session_id = '$sessID'", $this->db_handle);

            // if something happened, return true
            if(mysql_affected_rows($this->db_handle))
                return true;
        }
        else // if no session-data was found,
        {
            // create a new row
            mysql_query("INSERT INTO ".self::db_table." (
                session_id,
                session_expires,
                session_data)
                VALUES(
                    '$sessID',
                    '$newExp',
                    '$sessData')", $this->db_handle);
            // if row was created, return true
            if(mysql_affected_rows($this->db_handle))
                return true;
        }

        // an unknown error occured
        return false;
    }

    function destroy($sessID) 
    {
        // delete session-data
        @mysql_query("DELETE FROM ".self::db_table." WHERE session_id = '$sessID'", $this->db_handle);

        // if session was deleted, return true,
        if(@mysql_affected_rows($this->db_handle))
            return true;

        // ...else return false
        return false;
    }

    function gc($sessMaxLifeTime) 
    {
        // delete old sessions
        mysql_query("DELETE FROM ".self::db_table." WHERE session_expires < ".time(), $this->db_handle);

        // return affected rows
        return mysql_affected_rows($this->db_handle);
    }
}

/**
 * @brief 指定session有效的域名
 *
 * 修改点3：使用前，请将下面的.domain.com修改为网站的主域名（例如： .qq.com）,请注意前面个有一个'.'
 */
define("MAIN_DOMAIN", ".webpluz.org");   


/**
 * @brief 不同子域名下共享session信息
 *
 * 修改点4：使用前，请将下面的false修改为true
 * COOKIE_DOMAIN = false 表示：禁止该功能（默认禁止）
 * COOKIE_DOMAIN = true  表示：启用该功能（开启前提：需要定义MAIN_DOMAIN常量）
 */
define("COOKIE_DOMAIN", true); 
if (defined("COOKIE_DOMAIN") && COOKIE_DOMAIN)
{
    if (defined("MAIN_DOMAIN"))
        @ini_set("session.cookie_domain", MAIN_DOMAIN);
}



/**
 * @brief 同一个主域名，不同服务器之间共享session信息
 *
 * 修改点5：使用前，请将下面的false修改为true
 * COOKIE_DOMAIN = false 表示：禁止该功能（默认禁止）
 * COOKIE_DOMAIN = true  表示：启用该功能（开启前提：需要建立mysql数据表）
 */
define("USER_SESSION", true);
if (defined("USER_SESSION") && USER_SESSION)
{
    @ini_set("session.save_handler", "user");
    $session = new Session;
    @session_module_name("user");
    @session_set_save_handler(
        array(&$session, "open"),
        array(&$session, "close"),
        array(&$session, "read"),
        array(&$session, "write"),
        array(&$session, "destroy"),
        array(&$session, "gc"));
}

session_start();
?>
