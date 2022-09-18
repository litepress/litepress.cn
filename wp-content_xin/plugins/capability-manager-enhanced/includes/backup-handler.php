<?php
/*
 * PublishPress Capabilities [Free]
 * 
 * Process update operations from Backup screen
 * 
 */

class Capsman_BackupHandler
{
	var $cm;

	function __construct( $manager_obj ) {
		if ((!is_multisite() || !is_super_admin()) && !current_user_can('administrator') && !current_user_can('restore_roles'))
			wp_die( esc_html__( 'You do not have permission to restore roles.', 'capsman-enhanced' ) );
	
		$this->cm = $manager_obj;
	}
	
	/**
	 * Processes backups and restores.
	 *
	 * @return void
	 */
	function processBackupTool ()
	{
		global $wpdb;

        if (isset($_POST['save_backup'])) {
			check_admin_referer('pp-capabilities-backup');
		
			$wp_roles = $wpdb->prefix . 'user_roles';
			$cm_roles = $this->cm->ID . '_backup';
			$cm_roles_initial = $this->cm->ID . '_backup_initial';

            $backup_sections = pp_capabilities_backup_sections();

			if ( ! get_option( $cm_roles_initial ) ) {
				if ( $current_backup = get_option( $cm_roles ) ) {
					update_option( $cm_roles_initial, $current_backup, false );

					if ( $initial_datestamp = get_option( $this->cm->ID . '_backup_datestamp' ) ) {
						update_option($this->cm->ID . '_backup_initial_datestamp', $initial_datestamp, false );
					}
				}
			}

            $active_backup = ['Roles and Capabilities'];

            //role backup
			$roles = get_option($wp_roles);
			update_option($cm_roles, $roles, false);

            //other backup
            foreach($backup_sections as $backup_section){
                $section_options = $backup_section['options'];
                if(is_array($section_options) && !empty($section_options)){
                    foreach($section_options as $section_option){
                        $active_backup[] = $backup_section['label'];
                        $current_option = get_option($section_option);
                        update_option($section_option.'_backup', $current_option, false);
                    }
                }
            }

            $active_backup = array_unique($active_backup);

            //update last backup
            update_option($this->cm->ID . '_last_backup', implode(', ', $active_backup));

            //backup datestamp and response
			update_option($this->cm->ID . '_backup_datestamp', current_time( 'timestamp' ), false );
			ak_admin_notify(__('New backup saved.', 'capsman-enhanced'));
				
        }

        if (isset($_POST['restore_backup']) && !empty($_POST['select_restore'])) {
            check_admin_referer('pp-capabilities-backup');

            $wp_roles = $wpdb->prefix . 'user_roles';
            $cm_roles = $this->cm->ID . '_backup';
            $cm_roles_initial = $this->cm->ID . '_backup_initial';

            $backup_sections = pp_capabilities_backup_sections();

            switch ($_POST['select_restore']) {
				case 'restore_initial':
					if ($roles = get_option($cm_roles_initial)) {
						update_option($wp_roles, $roles);
						ak_admin_notify(__('Roles and Capabilities restored from initial backup.', 'capsman-enhanced'));
					} else {
						ak_admin_error(__('Restore failed. No backup found.', 'capsman-enhanced'));
					}
					break;

				case 'restore':
					if ($roles = get_option($cm_roles)) {

                        $restored_backup = ['Roles and Capabilities'];

                        //restore role backup
						update_option($wp_roles, $roles);

                        //restore other backup
                        foreach($backup_sections as $backup_section){
                            $section_options = $backup_section['options'];
                            if(is_array($section_options) && !empty($section_options)){
                                foreach($section_options as $section_option){
                                    $backup_option = get_option($section_option.'_backup');
                                    if ($backup_option) {
                                        $restored_backup[] = $backup_section['label'];
                                        update_option($section_option, $backup_option);
                                    }
                                }
                            }
                        }

                        $restored_backup = array_unique($restored_backup);
						ak_admin_notify(sprintf(__('%s restored from last backup.', 'capsman-enhanced'), implode(', ', $restored_backup)));
					} else {
						ak_admin_error(__('Restore failed. No backup found.', 'capsman-enhanced'));
					}
					break;

				default:
                    if ($roles = get_option(sanitize_key($_POST['select_restore']))) {
						update_option($wp_roles, $roles);
						ak_admin_notify(__('Roles and Capabilities restored from selected auto-backup.', 'capsman-enhanced'));
					} else {
						ak_admin_error(__('Restore failed. No backup found.', 'capsman-enhanced'));
					}
			}
		}

        if (isset($_POST['import_backup'])) {

            check_admin_referer('pp-capabilities-backup');

            if (empty($_FILES['import_file']['tmp_name']) || empty($_FILES['import_file']['name'])) {
                ak_admin_error(__( 'Please upload a file to import', 'capsman-enhanced'));
                return;
            }
            
            if (pathinfo(sanitize_text_field($_FILES['import_file']['name']), PATHINFO_EXTENSION) !== 'json') {
                ak_admin_error(__( 'Please upload a valid .json file', 'capsman-enhanced'));
                return;
            }

            // Make sure WordPress upload support is loaded.
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }

            // Setup internal vars.
            $overrides   = array( 'test_form' => false, 'test_type' => false, 'mimes' => array('json' => 'application/json') );
            $file         = wp_handle_upload( $_FILES['import_file'], $overrides );

            // Make sure we have an uploaded file.
            if (isset($file['error'])) {
                ak_admin_error($file['error']);
                return;
            }

            if ( ! file_exists( $file['file'] ) ) {
                ak_admin_error(__( 'Error importing settings! Please try again.', 'capsman-enhanced'));
                return;
            }

            // Get the upload data.
            $raw  = file_get_contents( $file['file'] );
            $data = maybe_unserialize( $raw );
            
            // Remove the uploaded file.
            wp_delete_file( $file['file'] );

            // Data checks.
            if ( 'array' != gettype( $data ) ) {
                ak_admin_error(__( 'Error importing settings! Please check that you uploaded a valid json file.', 'capsman-enhanced'));
                return;
            }

            $backup_sections = pp_capabilities_backup_sections();
            $restored_backup = [];

            foreach ( $data as $option_key => $option_value ) {
                if($option_key === 'user_roles'){
                    $restored_backup[] = 'Roles and Capabilities';
                    $section_data = $this->santize_import_role($option_value);
                    update_option($wpdb->prefix . 'user_roles', $section_data);
                }else{
                    $restored_backup[] = $this->get_import_option_section($option_key, $backup_sections);
                    $section_data = $this->santize_import_data($option_value);
                    update_option($option_key, $section_data);
                }
			}

            $restored_backup = array_unique($restored_backup);

            ak_admin_notify(sprintf(__('%s successfully imported from uploaded data.', 'capsman-enhanced'), implode(', ', $restored_backup)));

		}
	}

	/**
	 * Sanitize role data before import.
	 *
	 * @return array
	 */
    function get_import_option_section($option_key, $backup_sections)
    {
        $option_section = '';

        foreach($backup_sections as $backup_section){
            $section_options = $backup_section['options'];
            if(is_array($section_options) && in_array($option_key, $section_options)){
                $option_section= $backup_section['label'];
            }
        }

        return $option_section;
    }

	/**
	 * Sanitize role data before import.
	 *
	 * @return array
	 */
    function santize_import_role($role){

        $sanitized_role = [];

        foreach($role as $role_key => $role_data){
            $role_key           = sanitize_key($role_key);
            $role_name          = sanitize_text_field($role_data['name']);
            $capabilities       = $role_data['capabilities'];
            $role_capabilities  = array_combine(
                                    array_map('sanitize_key', array_keys($capabilities)), 
                                    array_map('sanitize_text_field', array_values($capabilities))
                                );
            
            //return sanitized data                   
            $sanitized_role[$role_key] = ['name' => $role_name, 'capabilities' => $role_capabilities];
        }

        return $sanitized_role;
    }

	/**
	 * Sanitize other data before import.
	 *
	 * @return mixed
	 */
    function santize_import_data($data){

        $sanitized_data = [];

        if (is_array($data)) {
            foreach ($data as $data_key => $data_content) {
                $new_key           = sanitize_key($data_key);
                $new_content       = is_array($data_content) ? array_map('sanitize_text_field', $data_content) : sanitize_text_field($data_content);
                //return sanitized data
                $sanitized_data[$new_key] = $new_content;
            }
        }else{
            $sanitized_data = sanitize_text_field($data);
        }

        return $sanitized_data;
    }
	
	/**
	 * Resets roles to WordPress defaults.
	 *
	 * @return void
	 */
	function backupToolReset ()
	{
		check_admin_referer('capsman-reset-defaults');
	
		require_once(ABSPATH . 'wp-admin/includes/schema.php');

		if ( ! function_exists('populate_roles') ) {
			ak_admin_error(__('Needed function to create default roles not found!', 'capsman-enhanced'));
			return;
		}

		$roles = array_keys( ak_get_roles(true) );

		foreach ( $roles as $role) {
			remove_role($role);
		}

		populate_roles();
		$this->cm->setAdminCapability();

		$msg = esc_html__('Roles and Capabilities reset to WordPress defaults', 'capsman-enhanced');
		
		if ( function_exists( 'pp_populate_roles' ) ) {
			pp_populate_roles();
		} else {
			// force PP to repopulate roles
			$pp_ver = get_option( 'pp_c_version', true );
			if ( $pp_ver && is_array($pp_ver) ) {
				$pp_ver['version'] = ( preg_match( "/dev|alpha|beta|rc/i", $pp_ver['version'] ) ) ? '0.1-beta' : 0.1;
			} else {
				$pp_ver = array( 'version' => '0.1', 'db_version' => '1.0' );
			}

			update_option( 'pp_c_version', $pp_ver );
			delete_option( 'ppperm_added_role_caps_10beta' );
		}
		
		ak_admin_notify($msg);
	}
}
