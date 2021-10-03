<?php

namespace LitePress\GlotPress\Import_Docs;

use GP;
use GP_Project;
use LitePress\Logger\Logger;
use PO;
use Translation_Entry;
use function LitePress\Helper\html_split;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		$text = <<<html
<h1>How to install WordPress</h1>

					<p>WordPress is well-known for its ease of installation. Under most circumstances, installing WordPress is a very simple process and takes less than five minutes to complete. <a href="/support/installation/installing-wordpress-at-popular-hosting-companies/">Many web hosts</a> now offer <a href="/support/installation/automated-installation/">tools (e.g. Fantastico) to automatically install WordPress</a> for you. However, if you wish to install WordPress yourself, the following guide will help.</p>
<h2>Things to Know Before Installing WordPress</h2>
<p>Before you begin the install, there are a few things you need to have and do. Refer the article <a href="/support/installation/before-you-install/">Before You Install</a>.<br />If you need multiple WordPress instances, refer <a href="/support/article/installing-multiple-blogs/">Installing Multiple WordPress Instances</a>.</p>
<h2><span id="Famous_5-Minute_Installation" class="mw-headline">Basic Instructions</span></h2>
<p>Here&#8217;s the quick version of the instructions for those who are already comfortable with performing such installations. More <a href="#detailed-instructions">detailed instructions</a>follow.</p>
<ol>
<li>Download and unzip the WordPress package if you haven&#8217;t already.</li>
<li>Create a database for WordPress on your web server, as well as a <a title="Glossary" href="/support/article/glossary/#mysql">MySQL</a> (or MariaDB) user who has all privileges for accessing and modifying it.</li>
<li>(Optional) Find and rename <tt>wp-config-sample.php</tt>to<tt>wp-config.php</tt>, then edit the file (see <a title="Editing wp-config.php" href="/support/article/editing-wp-config-php/">Editing wp-config.php</a>) and add your database information.<br /><strong>Note</strong>: If you are not comfortable with renaming files, step 3 is optional and you can skip it as the install program will create the <tt>wp-config.php</tt> file for you.</li>
<li>Upload the WordPress files to the desired location on your web server:
<ul>
<li>If you want to integrate WordPress into the root of your domain (e.g. <tt>http://example.com/</tt>), move or upload all contents of the unzipped WordPress directory (excluding the WordPress directory itself) into the root directory of your web server.</li>
<li>If you want to have your WordPress installation in its own subdirectory on your website (e.g. <tt>http://example.com/blog/</tt>), create the <tt>blog</tt> directory on your server and upload the contents of the unzipped WordPress package to the directory via FTP.</li>
<li><i><b>Note:</b> If your FTP client has an option to convert file names to lower case, make sure it&#8217;s disabled.</i></li>
</ul>
</li>
<li>Run the WordPress installation script by accessing the URL in a web browser. This should be the URL where you uploaded the WordPress files.
<ul>
<li>If you installed WordPress in the root directory, you should visit: <tt>http://example.com/</tt></li>
<li>If you installed WordPress in its own subdirectory called <tt>blog</tt>, for example, you should visit: <tt>http://example.com/blog/</tt></li>
</ul>
</li>
</ol>
<p>That&#8217;s it! WordPress should now be installed.</p>
<h2><span id="Detailed_Instructions" class="mw-headline">Detailed Instructions</span></h2>
<h3><span id="Step_1:_Download_and_Extract" class="mw-headline">Step 1: Download and Extract</span></h3>
<p>Download and unzip the WordPress package from <a class="external free" href="https://wordpress.org/download/">https://wordpress.org/download/</a>.</p>
<ul>
<li>If you will be uploading WordPress to a remote web server, download the WordPress package to your computer with a web browser and unzip the package.</li>
<li>If you will be using FTP, skip to the next step &#8211; uploading files is covered later.</li>
<li>If you have <a title="Glossary" href="/support/article/glossary#shell">shell</a> access to your web server, and are comfortable using console-based tools, you may wish to download WordPress directly to your <a title="Glossary" href="/support/article/glossary#web-server">web server</a>using<tt>wget</tt>(or<tt>lynx</tt> or another console-based web browser) if you want to avoid <a title="Glossary" href="/support/article/glossary#ftp">FTPing</a>:
<ul>
<li><tt>wget https://wordpress.org/latest.tar.gz</tt></li>
<li>Then extract the package using:<br /><tt>tar -xzvf latest.tar.gz </tt>
<p> </p>
<p>The WordPress package will extract into a folder called <tt>wordpress</tt> in the same directory that you downloaded <tt>latest.tar.gz</tt>.</p>
</li>
</ul>
</li>
</ul>
<h3><span id="Step_2:_Create_the_Database_and_a_User" class="mw-headline">Step 2: Create the Database and a User</span></h3>
<p>If you are using a <a title="Glossary" href="https://wordpress.org/support/article/glossary/#hosting-provider">hosting provider</a>, you may already have a WordPress database set up for you, or there may be an automated setup solution to do so. Check your hosting provider&#8217;s support pages or your control panel for clues about whether or not you&#8217;ll need to create one manually.</p>
<p>If you determine that you’ll need to create one manually, follow the instructions for Using phpMyAdmin below to create your WordPress username and database. For other tools such as Plesk, cPanel and Using the MySQL Client, refer the article <a href="/support/article/creating-database-for-wordpress/">Creating Database for WordPress</a>.</p>
<p>If you have only one database and it is already in use, you can install WordPress in it &#8211; just make sure to have a distinctive prefix for your tables to avoid over-writing any existing database tables.</p>
<h4><span id="Using_phpMyAdmin" class="mw-headline">Using phpMyAdmin</span></h4>
<p>If your web server has <a title="Glossary" href="/support/article/glossary#phpmyadmin">phpMyAdmin</a> installed, you may follow these instructions to create your WordPress username and database. If you work on your own computer, on most Linux distributions you can install PhpMyAdmin automatically.</p>
<p><i><b>Note:</b> These instructions are written for phpMyAdmin 4.4; the phpMyAdmin user interface can vary slightly between versions.</i></p>
<ol>
<li>If a database relating to WordPress does not already exist in the <b>Database</b> dropdown on the left, create one:
<ol>
<li>Choose a name for your WordPress database: &#8216;<tt>wordpress</tt>&#8216; or &#8216;<tt>blog</tt>&#8216; are good, but most hosting services (especially shared hosting) will require a name beginning with your username and an underscore, so, even if you work on your own computer, we advise that you check your hosting service requirements so that you can follow them on your own server and be able to transfer your database without modification. Enter the chosen database name in the <b>Create database</b> field and choose the best collation for your language and encoding. In most cases it&#8217;s better to choose in the &#8220;utf8_&#8221; series and, if you don&#8217;t find your language, to choose &#8220;utf8mb4_general_ci&#8221; (Refer <a class="external autonumber" href="https://make.wordpress.org/core/2015/04/02/the-utf8mb4-upgrade/">this article</a>).<br /><a href="https://i2.wp.com/wordpress.org/support/files/2018/10/phpMyAdmin_create_database_4.4.jpg?ssl=1"><img loading="lazy" class="alignnone wp-image-2022" src="https://i2.wp.com/wordpress.org/support/files/2018/10/phpMyAdmin_create_database_4.4.jpg?resize=688%2C411&#038;ssl=1" alt="phpMyAdmin_create_database_4.4" width="688" height="411" data-recalc-dims="1" /></a></li>
</ol>
</li>
<li>Click the <b>phpMyAdmin</b> icon in the upper left to return to the main page, then click the <b>Users</b> tab. If a user relating to WordPress does not already exist in the list of users, create one:
<div class="center">
<div class="thumb tnone">
<div class="thumbinner"><a class="image" href="https://i0.wp.com/codex.wordpress.org/File:users.jpg?ssl=1"><img loading="lazy" class="thumbimage" src="https://i1.wp.com/codex.wordpress.org/images/2/26/users.jpg?resize=800%2C521&#038;ssl=1" alt="users.jpg" width="800" height="521" data-recalc-dims="1" /></a></p>
<p> </p>
<div class="thumbcaption">
<div class="magnify"><a class="internal" title="Enlarge" href="https://i0.wp.com/codex.wordpress.org/File:users.jpg?ssl=1"><img loading="lazy" src="https://i0.wp.com/codex.wordpress.org/skins/common/images/magnify-clip.png?resize=15%2C11&#038;ssl=1" alt="" width="15" height="11" data-recalc-dims="1" /></a></div>
</div>
</div>
</div>
</div>
<ol>
<li>Click<b>Add user</b>.</li>
<li>Choose a username for WordPress (&#8216;<tt>wordpress</tt>&#8216; is good) and enter it in the <b>User name</b> field. (Be sure <b>Use text field:</b> is selected from the dropdown.)</li>
<li>Choose a secure password (ideally containing a combination of upper- and lower-case letters, numbers, and symbols), and enter it in the <b>Password</b> field. (Be sure <b>Use text field:</b> is selected from the dropdown.) Re-enter the password in the <b>Re-type</b>field.</li>
<li>Write down the username and password you chose.</li>
<li>Leave all options under <b>Global privileges</b> at their defaults.</li>
<li>Click<b>Go</b>.</li>
<li># Return to the <strong>Users</strong> screen and click the <strong>Edit privileges</strong> icon on the user you&#8217;ve just created for WordPress.</li>
<li># In the <strong>Database-specific privileges</strong> section, select the database you&#8217;ve just created for WordPress under the <strong>Add privileges to the following database</strong> dropdown, and click <strong>Go</strong>.</li>
<li># The page will refresh with privileges for that database. Click <strong>Check All</strong> to select all privileges, and click <strong>Go</strong>.</li>
<li># On the resulting page, make note of the host name listed after <strong>Server:</strong> at the top of the page. (This will usually be <strong>localhost</strong>.)</li>
</ol>
</li>
</ol>
<p><a href="https://i2.wp.com/wordpress.org/support/files/2018/10/phpMyAdmin_server_info_4.4.jpg?ssl=1"><img loading="lazy" class="alignnone size-full wp-image-2023" src="https://i2.wp.com/wordpress.org/support/files/2018/10/phpMyAdmin_server_info_4.4.jpg?resize=682%2C107&#038;ssl=1" alt="phpMyAdmin_server_info_4.4" width="682" height="107" data-recalc-dims="1" /></a></p>
<h3><span id="Step_3:_Set_up_wp-config.php" class="mw-headline">Step 3: Set up wp-config.php</span></h3>
<p>You can either create and edit the <tt>wp-config.php</tt> file yourself, or you can skip this step and let WordPress try to do this itself when you run the installation script (step 5). (you’ll still need to tell WordPress your database information).</p>
<p>(For more extensive details, and step by step instructions for creating the configuration file and your secret key for password security, please see <a title="Editing wp-config.php" href="/support/article/editing-wp-config-php/">Editing wp-config.php</a>.)</p>
<p>Return to where you extracted the WordPress package in Step 1, rename the file <tt>wp-config-sample.php</tt>to<tt>wp-config.php</tt>, and open it in a text editor.</p>
<p><a title="Editing wp-config.php" href="/support/article/editing-wp-config-php/#configure-database-settings">Enter your database information</a> under the section labeled</p>
<pre> // ** MySQL settings - You can get this info from your web host ** //</pre>
<dl>
<dt>DB_NAME </dt>
<dd>The name of the database you created for WordPress in Step 2.</dd>
<dt>DB_USER </dt>
<dd>The username you created for WordPress in Step 2.</dd>
<dt>DB_PASSWORD </dt>
<dd>The password you chose for the WordPress username in Step 2.</dd>
<dt>DB_HOST </dt>
<dd>The hostname you determined in Step 2 (usually <tt>localhost</tt>, but not always; see <a title="Editing wp-config.php" href="/support/article/editing-wp-config-php/#set-database-host">some possible DB_HOST values</a>). If a port, socket, or pipe is necessary, append a colon (<tt>:</tt>) and then the relevant information to the hostname.</dd>
<dt>DB_CHARSET </dt>
<dd>The database character set, normally should not be changed (see <a title="Editing wp-config.php" href="/support/article/editing-wp-config-php/">Editing wp-config.php</a>).</dd>
<dt>DB_COLLATE </dt>
<dd>The database collation should normally be left blank (see <a title="Editing wp-config.php" href="/support/article/editing-wp-config-php/">Editing wp-config.php</a>).</dd>
</dl>
<p><a title="Editing wp-config.php" href="/support/article/editing-wp-config-php/">Enter your secret key values</a> under the section labeled</p>
<pre> * Authentication Unique Keys and Salts.</pre>
<p>Save the <tt>wp-config.php</tt>file.</p>
<h3><span id="Step_4:_Upload_the_files" class="mw-headline">Step 4: Upload the files</span></h3>
<p>Now you will need to decide where on your domain you&#8217;d like your WordPress-powered site to appear:</p>
<ul>
<li>In the root directory of your website. (For example, <tt>http://example.com/</tt>)</li>
<li>In a subdirectory of your website. (For example, <tt>http://example.com/blog/</tt>)</li>
</ul>
<p><i><b>Note:</b> The location of your root web directory in the filesystem on your <a title="Glossary" href="https://wordpress.org/support/article/glossary/#web-server">web server</a> will vary across hosting providers and operating systems. Check with your <a title="Glossary" href="/support/article/glossary#hosting-provider">hosting provider</a> or system administrator if you do not know where this is.</i></p>
<h4><span id="In_the_Root_Directory" class="mw-headline">In the Root Directory</span></h4>
<ul>
<li>If you need to upload your files to your web server, use an <a title="Glossary" href="/support/article/glossary#ftp">FTP</a> client to upload all the <i>contents</i> of the <tt>wordpress</tt> directory (but not the directory itself) into the root directory of your website.</li>
<li>If your files are already on your web server, and you are using <a title="Glossary" href="/support/article/glossary#shell">shell</a> access to install WordPress, move all of the <i>contents</i> of the <tt>wordpress</tt> directory (but not the directory itself) into the root directory of your website.</li>
</ul>
<h4><span id="In_a_Subdirectory" class="mw-headline">In a Subdirectory</span></h4>
<ul>
<li>If you need to upload your files to your web server, rename the <tt>wordpress</tt> directory to your desired name, then use an <a title="Glossary" href="/support/article/glossary#ftp">FTP</a> client to upload the directory to your desired location within the root directory of your website.</li>
<li>If your files are already on your web server, and you are using <a title="Glossary" href="/support/article/glossary#shell">shell</a> access to install WordPress, move the <tt>wordpress</tt> directory to your desired location within the root directory of your website, and rename the directory to your desired name.</li>
</ul>
<h3><span id="Step_5:_Run_the_Install_Script" class="mw-headline">Step 5: Run the Install Script</span></h3>
<p>Point a web browser to start the installation script.</p>
<ul>
<li>If you placed the WordPress files in the root directory, you should visit: <tt>http://example.com/wp-admin/install.php</tt></li>
<li>If you placed the WordPress files in a subdirectory called <tt>blog</tt>, for example, you should visit: <tt>http://example.com/blog/wp-admin/install.php</tt></li>
</ul>
<h4><span id="Setup_configuration_file" class="mw-headline">Setup configuration file</span></h4>
<p>If WordPress can&#8217;t find the <tt>wp-config.php</tt> file, it will tell you and offer to try to create and edit the file itself. (You can also do this directly by loading <tt>wp-admin/setup-config.php</tt> in your web browser.) WordPress will ask you the database details and write them to a new <tt>wp-config.php</tt> file. If this works, you can go ahead with the installation; otherwise, go back and <a href="#step-3-set-up-wp-config-php">create, edit, and upload the <tt>wp-config.php</tt> file yourself (step 3)</a>.<br /><a href="https://i0.wp.com/wordpress.org/support/files/2018/10/install-step3_v47.png?ssl=1"><img loading="lazy" class="alignnone wp-image-2025 size-full" src="https://i0.wp.com/wordpress.org/support/files/2018/10/install-step3_v47.png?resize=784%2C563&#038;ssl=1" alt="install-step3_v47" width="784" height="563" data-recalc-dims="1" /></a></p>
<h4><span id="Finishing_installation" class="mw-headline">Finishing installation</span></h4>
<p>The following screenshots show how the installation progresses. Notice that in entering the details screen, you enter your site title, your desired user name, your choice of a password (twice), and your e-mail address. Also displayed is a check-box asking if you would like your blog to appear in search engines like Google and DuckDuckGo. Leave the box unchecked if you would like your blog to be visible to everyone, including search engines, and check the box if you want to block search engines, but allow normal visitors. Note all this information can be changed later in your <a class="mw-redirect" title="Administration Panels" href="/support/article/administration-screens/">Administration Screen</a>.<br /><a href="https://i2.wp.com/wordpress.org/support/files/2018/10/install-step5_v47.png?ssl=1"><img loading="lazy" class="alignnone size-full wp-image-2026" src="https://i2.wp.com/wordpress.org/support/files/2018/10/install-step5_v47.png?resize=795%2C835&#038;ssl=1" alt="install-step5_v47" width="795" height="835" data-recalc-dims="1" /></a></p>
<p>If you successfully install the WordPress, login prompt will be displayed.</p>
<h4><span id="Install_script_troubleshooting" class="mw-headline">Install script troubleshooting</span></h4>
<ul>
<li>If you get an error about the database when you run the install script:
<ul>
<li>Go back to <a href="#step-2-create-the-database-and-a-user">Step 2</a>and<a href="#step-3-set-up-wp-config-php">Step 3</a>, and make sure you entered all the correct database information into <tt>wp-config.php</tt>.</li>
<li>Make sure you granted your WordPress user permission to access your WordPress database in <b>Step 3</b>.</li>
<li>Make sure the database server is running.</li>
</ul>
</li>
</ul>
<h2><span id="Common_Installation_Problems" class="mw-headline">Common Installation Problems</span></h2>
<p>The following are some of the most common installation problems. For more information and troubleshooting for problems with your WordPress installation, check out <a title="FAQ Installation" href="/support/article/faq-installation/">FAQ Installation</a>and<a title="FAQ Troubleshooting" href="/support/article/faq-troubleshooting/">FAQ Troubleshooting</a>.</p>
<p><b>I see a directory listing rather than a web page.</b></p>
<p>The web server needs to be told to view <tt>index.php</tt> by default. In Apache, use the <tt>DirectoryIndex index.php</tt> directive. The simplest option is to create a file named <tt>.htaccess</tt> in the installed directory and place the directive there. Another option is to add the directive to the web server&#8217;s configuration files.</p>
<p><b>I see lots of <tt>Headers already sent</tt> errors. How do I fix this?</b></p>
<p>You probably introduced a syntax error in editing <tt>wp-config.php</tt>.</p>
<ol>
<li>Download<tt>wp-config.php</tt> (if you don&#8217;t have <a title="Glossary" href="/support/article/glossary#shell">shell</a>access).</li>
<li>Open it in a <a title="Glossary" href="/support/article/glossary#text-editor">text editor</a>.</li>
<li>Check that the first line contains nothing but <tt>&lt;?php</tt>, and that there is <b>no</b> text before it (not even whitespace).</li>
<li>Check that the last line contains nothing but <tt>?&gt;</tt>, and that there is <b>no</b> text after it (not even whitespace).</li>
<li>If your text editor saves as Unicode, make sure it adds <b>no byte order mark (BOM)</b>. Most Unicode-enabled text editors do not inform the user whether or not it adds a BOM to files; if so, try using a different text editor.</li>
<li>Save the file, upload it again if necessary, and reload the page in your browser.</li>
</ol>
<p><b>My page comes out gibberish. When I look at the source I see a lot of &#8220;<tt>&lt;?php ?&gt;</tt>&#8221; tags.</b></p>
<p>If the <tt>&lt;?php ?&gt;</tt> tags are being sent to the browser, it means your <a title="Glossary" href="/support/article/glossary#php">PHP</a> is not working properly. All PHP code is supposed to be executed <i>before</i> the server sends the resulting <a title="Glossary" href="/support/article/glossary#html">HTML</a> to your web browser. (That&#8217;s why it&#8217;s called a <i>pre</i>processor.) Make sure your web server meets the requirements to run WordPress, that PHP is installed and configured properly, or contact your hosting provider or system administrator for assistance.</p>
<p><b>I keep getting an <tt>Error connecting to database</tt> message but I&#8217;m sure my configuration is correct.</b></p>
<p>Try resetting your MySQL password manually. If you have access to MySQL via shell, try issuing:</p>
<pre>SET PASSWORD FOR '<var>wordpressusername</var>'@'<var>hostname</var>' = OLD_PASSWORD('<var>password</var>');</pre>
<p>If you do not have shell access, you should be able to simply enter the above into an SQL query in phpMyAdmin. Failing that, you may need to use your host&#8217;s control panel to reset the password for your database user.</p>
<p><b>I keep getting an <tt>Your PHP installation appears to be missing the MySQL extension which is required by WordPress</tt> message but I&#8217;m sure my configuration is correct.</b></p>
<p>Check to make sure that your configuration of your web-server is correct and that the MySQL plugin is getting loaded correctly by your web-server program. Sometimes this issue requires everything in the path all the way from the web-server down to the MySQL installation to be checked and verified to be fully operational. Incorrect configuration files or settings are often the cause of this issue.</p>
<p><b>My image/MP3 uploads aren&#8217;t working.</b></p>
<p>If you use the Rich Text Editor on a blog that&#8217;s installed in a subdirectory, and drag a newly uploaded image into the editor field, the image may vanish a couple seconds later. This is due to a problem with TinyMCE (the rich text editor) not getting enough information during the drag operation to construct the path to the image or other file correctly. The solution is to NOT drag uploaded images into the editor. Instead, click and hold on the image and select <b>Send to Editor</b>.</p>
html;

		add_action( 'lpcn_gp_doc_import', array( $this, 'job' ), 10, 3 );

		if ( isset( $_GET['docs_test'] ) ) {
			do_action( 'lpcn_gp_doc_import', 'How to install WordPress',
				'docs-how-to-install-wordpress',
				$text
			);
		}
	}

	public function job( string $name, string $slug, string $content ) {
		if ( empty( $name ) || empty( $slug ) || empty( $content ) ) {
			Logger::error( 'DOC_POT', '传入了空的参数', array(
				'name'    => $name,
				'slug'    => $slug,
				'content' => $content,
			) );

			return;
		}

		$section_strings = html_split( $content );

		$pot = new PO();
		$pot->set_header( 'MIME-Version', '1.0' );
		$pot->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
		$pot->set_header( 'Content-Transfer-Encoding', '8bit' );

		foreach ( $section_strings as $text ) {
			// 如果字符串是空的就跳过
			if ( empty( $text ) || ' ' === $text ) {
				continue;
			}

			$pot->add_entry( new Translation_Entry( [
				'singular' => $text,
			] ) );
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'doc-pot' );
		$pot_file  = "$temp_file.pot";
		rename( $temp_file, $pot_file );

		$exported = $pot->export_to_file( $pot_file );
		if ( ! $exported ) {
			Logger::error( 'DOC_POT', '从文档内容创建 POT 文件失败', array(
				'name' => $name,
				'slug' => $slug
			) );

			return;
		}

		$project = $this->update_gp_project( $name, $slug );
		if ( empty( $project ) ) {
			Logger::error( 'DOC_POT', '获取 GlotPress 项目失败', array(
				'name' => $name,
				'slug' => $slug
			) );

			return;
		}

		$format    = gp_get_import_file_format( 'po', '' );
		$originals = $format->read_originals_from_file( $pot_file, $project );
		// 当读取了 pot 文件后删除临时文件
		unlink( $pot_file );

		if ( empty( $originals ) ) {
			Logger::error( 'DOC_POT', '无法从通过文档内容生成的 POT 文件中加载原文', array(
				'name' => $name,
				'slug' => $slug
			) );

			return;
		}

		GP::$original->import_for_project( $project, $originals );
	}

	/**
	 * 更新 GlotPress 上的项目，并返回子项目的 ID
	 *
	 * @param $name
	 * @param $slug
	 *
	 * @return \GP_Project
	 */
	private function update_gp_project( $name, $slug ): GP_Project {
		// 检查项目是否已存在
		$exist = GP::$project->find_one( array( 'path' => "docs/$slug/body" ) );
		if ( ! empty( $exist ) ) {
			return $exist;
		}

		// 创建父项目
		$args           = array(
			'name'                => $name,
			'author'              => '',
			'slug'                => $slug,
			'path'                => "docs/$slug",
			'description'         => '',
			'parent_project_id'   => 4,
			'source_url_template' => '',
			'active'              => 1
		);
		$parent_project = GP::$project->create_and_select( $args );

		// 创建子项目
		$args        = array(
			'name'                => '文档主体',
			'author'              => '',
			'slug'                => 'body',
			'path'                => "docs/$slug/body",
			'description'         => '',
			'parent_project_id'   => $parent_project->id,
			'source_url_template' => '',
			'active'              => 1
		);
		$sub_project = GP::$project->create_and_select( $args );

		// 为子项目创建翻译集
		$args = array(
			'name'       => '简体中文',
			'slug'       => 'default',
			'project_id' => $sub_project->id,
			'locale'     => 'zh-cn',
		);
		GP::$translation_set->create( $args );

		return $sub_project;
	}

}
