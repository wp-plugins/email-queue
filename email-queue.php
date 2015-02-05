<?php /*
Plugin Name: Email Queue
Plugin URI: http://bestwebsoft.com/products/
Description: This plugin allows you to manage email massages sent by BestWebSoft plugins.
Author: BestWebSoft
Version: 1.0.4
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Add menu and submenu.
* @return void
*/
if ( ! function_exists( 'mlq_admin_default_setup' ) ) {
	function mlq_admin_default_setup() {
		global $wp_version, $bstwbsftwppdtplgns_options, $bstwbsftwppdtplgns_added_menu;
		$bws_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) . "bws_menu/bws_menu.php" );
		$bws_menu_version = $bws_menu_info["Version"];
		$base = plugin_basename( __FILE__ );

		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			if ( is_multisite() ) {
				if ( ! get_site_option( 'bstwbsftwppdtplgns_options' ) )
					add_site_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
				$bstwbsftwppdtplgns_options = get_site_option( 'bstwbsftwppdtplgns_options' );
			} else {
				if ( ! get_option( 'bstwbsftwppdtplgns_options' ) )
					add_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
				$bstwbsftwppdtplgns_options = get_option( 'bstwbsftwppdtplgns_options' );
			}
		}

		if ( isset( $bstwbsftwppdtplgns_options['bws_menu_version'] ) ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			unset( $bstwbsftwppdtplgns_options['bws_menu_version'] );
			if ( is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] ) || $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] < $bws_menu_version ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			if ( is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_added_menu ) ) {
			$plugin_with_newer_menu = $base;
			foreach ( $bstwbsftwppdtplgns_options['bws_menu']['version'] as $key => $value ) {
				if ( $bws_menu_version < $value && is_plugin_active( $base ) ) {
					$plugin_with_newer_menu = $key;
				}
			}
			$plugin_with_newer_menu = explode( '/', $plugin_with_newer_menu );
			$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? basename( WP_CONTENT_DIR ) : 'wp-content';
			if ( file_exists( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' ) )
				require_once( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' );
			else
				require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );	
			$bstwbsftwppdtplgns_added_menu = true;			
		}

		$icon_path    = $wp_version < 3.8 ? plugins_url( "images/plugin_icon_37.png",  __FILE__ ) : plugins_url( "images/plugin_icon_38.png",  __FILE__ );
		$capabilities = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_menu_page( 'BWS Plugins', 'BWS Plugins', $capabilities, 'bws_plugins',  'bws_add_menu_render', plugins_url( "images/px.png", __FILE__ ), 1001 );
		$settings_hook = add_submenu_page( 'bws_plugins', __( 'Email Queue', 'email-queue' ), __( 'Email Queue', 'email-queue' ), $capabilities, 'mlq_settings', 'mlq_admin_settings_content' );
		add_action( "load-$settings_hook", 'mlq_plugin_screen_options' );
		$hook = add_menu_page( __( 'Email Queue', 'email-queue' ), __( 'Email Queue', 'email-queue' ), $capabilities, 'mlq_view_mail_queue', 'mlq_mail_view', $icon_path, "33.123" );
		add_action( "load-$hook", 'mlq_screen_options' );
	}
}

/**
 * Plugin functions for init
 * @return void
 */
if ( ! function_exists ( 'mlq_init' ) ) {
	function mlq_init() {
		/* check WordPress version */
		mlq_version_check();
	}
}

/**
 * Plugin functions for admin init
 * @return void
 */
if ( ! function_exists ( 'mlq_admin_init' ) ) {
	function mlq_admin_init() {
		global $bws_plugin_info, $mlq_plugin_info;
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'email-queue', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		if ( ! $mlq_plugin_info )
			$mlq_plugin_info = get_plugin_data( __FILE__ );

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '138', 'version' => $mlq_plugin_info["Version"] );

		if ( isset( $_REQUEST['page'] ) && ( 'mlq_view_mail_queue' == $_REQUEST['page'] || 'mlq_settings' == $_REQUEST['page'] ) ) {
			/* register plugin settings */
			mlq_register_settings();
		}
	}
}

/**
 * Function check if plugin is compatible with current WP version
 * @return void
 */
if ( ! function_exists ( 'mlq_version_check' ) ) {
	function mlq_version_check() {
		global $wp_version, $mlq_plugin_info;
		$require_wp		=	"3.1"; /* Wordpress at least requires version */
		$plugin			=	plugin_basename( __FILE__ );
		if ( version_compare( $wp_version, $require_wp, "<" ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				$admin_url = ( function_exists( 'get_admin_url' ) ) ? get_admin_url( null, 'plugins.php' ) : esc_url( '/wp-admin/plugins.php' );
				if ( ! $mlq_plugin_info )
					$mlq_plugin_info = get_plugin_data( __FILE__, false );
				wp_die( "<strong>" . $mlq_plugin_info['Name'] . " </strong> " . __( 'requires', 'email-queue' ) . " <strong>WordPress " . $require_wp . "</strong> " . __( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'email-queue') . "<br /><br />" . __( 'Back to the WordPress', 'email-queue') . " <a href='" . $admin_url . "'>" . __( 'Plugins page', 'email-queue') . "</a>." );
			}
		}
	}
}

/**
 * Register settings function
 * @return void
 */
if ( ! function_exists( 'mlq_register_settings' ) ) {
	function mlq_register_settings() {
		global $wpdb, $mlq_options, $mlq_options_default, $mlq_plugin_info;
		$mlq_db_version = '0.2';

		$mlq_options_default = array(
			'plugin_option_version'	=> $mlq_plugin_info["Version"],
			'plugin_db_version'		=> $mlq_db_version,
			'mail_run_time'			=> 1,
			'mail_send_count'		=> 2,
			'display_options'		=> false,
			'mail_method'			=> 'wp_mail',
			'smtp_settings'			=> array( 
				'host'				=> 'smtp.example.com',
				'accaunt'			=> 'youraccaunt',
				'password'			=> 'yourpassword',
				'port'				=> 25,
				'ssl'				=> true
			),
			'delete_old_mail'		=> false,
			'delete_old_mail_days'	=> 30,
		);

		/* install the default plugin options */
		if ( 1 == is_multisite() ) {
			if ( ! get_site_option( 'mlq_options' ) )
				add_site_option( 'mlq_options', $mlq_options_default, '', 'yes' );
		} else {
			if ( ! get_option( 'mlq_options' ) )
				add_option( 'mlq_options', $mlq_options_default, '', 'yes' );
		}

		/* get plugin options from the database */
		$mlq_options = is_multisite() ? get_site_option( 'mlq_options' ) : get_option( 'mlq_options' );

		/* array merge incase new version of plugin has added new options */
		if ( ! isset( $mlq_options['plugin_option_version'] ) || $mlq_options['plugin_option_version'] != $mlq_plugin_info["Version"] ) {
			/* update table 'mlq_mail_plugins' */
			/* get array with plugins in DB */
			$plugins_in_db = $wpdb->get_results( "SELECT `plugin_name`, `plugin_slug`, `plugin_link`, `install_link`, `pro_status`, `parallel_plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins`", ARRAY_A );
			foreach ( $plugins_in_db as $key => $value ) {
				$plugins_in_db[ $value['plugin_link'] ] = $value;
				unset( $plugins_in_db[ $key ] );
			}
			/* get default plugins' values */
			$default_plugins = mlq_get_default_plugins();
			/* Check each default plugin against the ones in DB */
			foreach ( $default_plugins as $def_plugin_key => $def_plugin_value ) {
				/* to prevent from repeated data-insert after plugin update */
				if ( ! array_key_exists( $def_plugin_key, $plugins_in_db ) ) {
					if ( ! isset( $def_plugin_value['pro_status'] ) ) {
						$def_plugin_value['pro_status'] = 0;
					}
					if ( ! isset( $def_plugin_value['parallel_plugin_link'] ) ) {
						$def_plugin_value['parallel_plugin_link'] = '0';
					}
					$wpdb->insert( $wpdb->base_prefix . 'mlq_mail_plugins', array(
						'plugin_name' 			=> $def_plugin_value['plugin_name'],
						'plugin_slug' 			=> $def_plugin_value['plugin_slug'],
						'plugin_link'			=> $def_plugin_key,
						'install_link'			=> $def_plugin_value['install_link'],
						'pro_status'			=> $def_plugin_value['pro_status'],
						'parallel_plugin_link'	=> $def_plugin_value['parallel_plugin_link'],
						)
					);
				} else {
					/* check if plugin info needs an update */
					$update_data = array();
					foreach ( $plugins_in_db[ $def_plugin_key ] as $in_db_plugin_data_key => $in_db_plugin_data_value ) {
						if ( isset( $def_plugin_value[ $in_db_plugin_data_key ] ) && $def_plugin_value[ $in_db_plugin_data_key ] != $in_db_plugin_data_value ) {
							$update_data[ $in_db_plugin_data_key ] = $def_plugin_value[ $in_db_plugin_data_key ];
						}
					}
					if ( ! empty( $update_data ) ) {
						/* update plugin info if changed */
						$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins',
							$update_data, 
							array( 'plugin_link' => $def_plugin_key )
						);
					}
				}
			}

			$mlq_options = array_merge( $mlq_options_default, $mlq_options );
			$mlq_options['plugin_option_version'] = $mlq_plugin_info["Version"];
			if ( is_multisite() ) {
				update_site_option( 'mlq_options', $mlq_options );
			} else {
				update_option( 'mlq_options', $mlq_options );
			}
		}
	}
}

/**
 * Add action links on plugin page in to Plugin Name block
 * @param $links array() action links
 * @param $file  string  relative path to pugin "email-queue/email-queue.php"
 * @return $links array() action links
 */
if ( ! function_exists ( 'mlq_plugin_action_links' ) ) {
	function mlq_plugin_action_links( $links, $file ) {
		/* Static so we don't call plugin_basename on every plugin row. */
		static $this_plugin;
		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="admin.php?page=mlq_settings">' . __( 'Settings', 'email-queue' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}

/**
 * Add action links on plugin page in to Plugin Description block
 * @param $links array() action links
 * @param $file  string  relative path to pugin "email-queue/email-queue.php"
 * @return $links array() action links
 */
if ( ! function_exists ( 'mlq_register_plugin_links' ) ) {
	function mlq_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=mlq_settings">' . __( 'Settings', 'email-queue' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/email-queue/faq/" target="_blank">' . __( 'FAQ', 'email-queue' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com" target="_blank">' . __( 'Support', 'email-queue' ) . '</a>';
		}
		return $links;
	}
}

/**
 * Get $default_plugins array with info on defaults plugins
 *
 * @return array $default_plugins with info on defaults plugins
 */
if ( ! function_exists ( 'mlq_get_default_plugins' ) ) {
	function mlq_get_default_plugins() {
		/* Default set of data for 4 free and 4 PRO BWS plugins that send mail */
		$default_plugins = array(
			'contact-form-plugin/contact_form.php' => array(
				'plugin_name'			=> 'Contact form',
				'plugin_slug' 			=> 'contact_form',
				'plugin_link'			=> 'contact-form-plugin/contact_form.php',
				'install_link'			=> '/wp-admin/plugin-install.php?tab=search&type=term&s=Contact+Form+bestwebsoft&plugin-search-input=Search+Plugins',
				'pro_status'			=> 1,
				'parallel_plugin_link'	=> 'contact-form-pro/contact_form_pro.php',
				'plugin_function' 		=> 'cntctfrm_check_for_compatibility_with_mlq',
			),
			'sender/sender.php' => array(
				'plugin_name'			=> 'Sender', 
				'plugin_slug' 			=> 'sender',
				'plugin_link'			=> 'sender/sender.php',
				'install_link'			=> '/wp-admin/plugin-install.php?tab=search&s=Sender+Bestwebsoft&plugin-search-input=Search+Plugins',
				'pro_status'			=> 1,
				'parallel_plugin_link'	=> 'sender-pro/sender-pro.php',
				'plugin_function' 		=> 'sndr_get_update_on_mail_from_email_queue',
			),
			'subscriber/subscriber.php' => array(
				'plugin_name'			=> 'Subscriber', 
				'plugin_slug' 			=> 'subscriber',
				'plugin_link'			=> 'subscriber/subscriber.php',
				'install_link'			=> '/wp-admin/plugin-install.php?tab=search&s=Subscriber+Bestwebsoft&plugin-search-input=Search+Plugins',
				'pro_status'			=> 1,
				'parallel_plugin_link'	=> 'subscriber-pro/subscriber-pro.php',
				'plugin_function' 		=> 'sbscrbr_check_for_compatibility_with_mlq',
			),
			'updater/updater.php' => array(
				'plugin_name'			=> 'Updater', 
				'plugin_slug' 			=> 'updater',
				'plugin_link'			=> 'updater/updater.php',
				'install_link'			=> '/wp-admin/plugin-install.php?tab=search&type=term&s=updater+bestwebsoft&plugin-search-input=Search+Plugins',
				'pro_status'			=> 1,
				'parallel_plugin_link'	=> 'updater-pro/updater_pro.php',
				'plugin_function' 		=> 'pdtr_check_for_compatibility_with_mlq',
			),
			/* pro-versions */
			'contact-form-pro/contact_form_pro.php' => array(
				'plugin_name'			=> 'Contact form Pro',
				'plugin_slug' 			=> 'contact_form_pro',
				'plugin_link'			=> 'contact-form-pro/contact_form_pro.php',
				'install_link'			=> 'http://bestwebsoft.com/products/contact-form/?k=773dc97bb3551975db0e32edca1a6d7',
				'pro_status'			=> 2,
				'parallel_plugin_link'	=> 'contact-form-plugin/contact_form.php',
				'plugin_function' 		=> 'cntctfrmpr_check_for_compatibility_with_mlq',
			),
			'sender-pro/sender-pro.php' => array(
				'plugin_name'			=> 'Sender Pro', 
				'plugin_slug' 			=> 'sender-pro',
				'plugin_link'			=> 'sender-pro/sender-pro.php',
				'install_link'			=> 'http://bestwebsoft.com/products/sender/?k=dc5d1a87bdc8aeab2de40ffb99b38054',
				'pro_status'			=> 2,
				'parallel_plugin_link'	=> 'sender/sender.php',
				'plugin_function' 		=> 'sndrpr_get_update_on_mail_from_email_queue',
			),
			'subscriber-pro/subscriber-pro.php' => array(
				'plugin_name'			=> 'Subscriber Pro', 
				'plugin_slug' 			=> 'subscriber_pro',
				'plugin_link'			=> 'subscriber-pro/subscriber-pro.php',
				'install_link'			=> 'http://bestwebsoft.com/products/subscriber/?k=cf633acbefbdff78545347fe08a3aecb',
				'pro_status'			=> 2,
				'parallel_plugin_link'	=> 'subscriber/subscriber.php',
				'plugin_function' 		=> 'sbscrbrpr_check_for_compatibility_with_mlq',
			),
			'updater-pro/updater_pro.php' => array(
				'plugin_name'			=> 'Updater Pro', 
				'plugin_slug' 			=> 'updater_pro',
				'plugin_link'			=> 'updater-pro/updater_pro.php',
				'install_link'			=> 'http://bestwebsoft.com/products/updater/?k=cf633acbefbdff78545347fe08a3aecb',
				'pro_status'			=> 2,
				'parallel_plugin_link'	=> 'updater/updater.php',
				'plugin_function' 		=> 'pdtrpr_check_for_compatibility_with_mlq',
			),
		);
		return $default_plugins;
	}
}

/**
* Performed at activation.
* @return void
*/
if ( ! function_exists( 'mlq_send_activate' ) ) {
	function mlq_send_activate() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		/* table for mail messages */
		$mlq_sql = 
			"CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "mlq_mail_send` (
			`mail_send_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			`subject` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
			`body` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
			`headers` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
			`date_create` INT UNSIGNED NOT NULL ,
			`mail_status` INT( 1 ) NOT NULL DEFAULT '0' ,
			`trash_status` INT( 1 ) NOT NULL DEFAULT '0' ,
			`plugin_id` INT NOT NULL ,
			`priority` INT ( 1 ) NOT NULL DEFAULT '3' ,
			`attachment_path` VARCHAR( 255 ) NOT NULL ,
			`priority_sndrpr` INT NOT NULL DEFAULT '0',
			PRIMARY KEY ( `mail_send_id` )
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $mlq_sql );

		/* table for mail addresses to send */
		$mlq_sql = 
			"CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "mlq_mail_users` (
			`mail_users_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			`user_email` VARCHAR( 255 ) NOT NULL ,
			`id_mail` INT UNSIGNED NOT NULL ,
			`status` INT( 1 ) NOT NULL DEFAULT '0' ,
			`plugin_id` INT NOT NULL ,
			`priority` INT ( 1 ) NOT NULL DEFAULT '3' ,
			`user_id_in_sender` INT NOT NULL DEFAULT '0' ,
			`mail_id_in_sender` INT NOT NULL DEFAULT '0' ,
			`view` INT( 1 ) NOT NULL DEFAULT '0' ,
			`try` INT( 1 ) NOT NULL DEFAULT '0' ,
			PRIMARY KEY ( `mail_users_id` )
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $mlq_sql );

		/** 
		 * Table for mail plugins and settings
		 * priority 1 - low, 3 - normal, 5 - high
		 * pro_status: 0 - free plugin that doesn't have pro version; 1 - free plugin that has pro version; 2 - pro version of plugin;
		 */
		$mlq_sql = 
			"CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "mlq_mail_plugins` (
			`mail_plugin_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			`plugin_name` VARCHAR( 255 ) NOT NULL ,
			`plugin_slug` VARCHAR( 255 ) NOT NULL ,
			`plugin_link` VARCHAR( 255 ) NOT NULL ,
			`install_status` INT( 1 ) NOT NULL DEFAULT '0',
			`install_link` VARCHAR( 255 ) NOT NULL ,
			`active_status` INT( 1 ) NOT NULL DEFAULT '0',
			`in_queue_status` INT( 1 ) NOT NULL DEFAULT '1',
			`priority_general` INT( 1 ) NOT NULL DEFAULT '3',
			`pro_status` INT( 1 ) NOT NULL DEFAULT '0',
			`parallel_plugin_link` VARCHAR( 255 ) NOT NULL DEFAULT '0',
			PRIMARY KEY ( `mail_plugin_id` )
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $mlq_sql );

		/* get an array with info on default plugins */
		$default_plugins = mlq_get_default_plugins();

		/* insert plugin info in DB table if doesn't already exist */ 
		$mail_plugins_in_db = $wpdb->get_col( "SELECT `plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins`", 0 );
		foreach ( $default_plugins as $plugin_key => $plugin_value ) {
			/* to prevent from repeated data-insert after plugin re-activation */
			if ( ! in_array( $plugin_key, $mail_plugins_in_db ) ) {
				if ( ! isset( $plugin_value['pro_status'] ) ) {
					$plugin_value['pro_status'] = 0;
				}
				if ( ! isset( $plugin_value['parallel_plugin_link'] ) ) {
					$plugin_value['parallel_plugin_link'] = '0';
				}
				$wpdb->insert( $wpdb->base_prefix . 'mlq_mail_plugins', array(
					'plugin_name' 			=> $plugin_value['plugin_name'],
					'plugin_slug' 			=> $plugin_value['plugin_slug'],
					'plugin_link'			=> $plugin_key,
					'install_link'			=> $plugin_value['install_link'],
					'pro_status'			=> $plugin_value['pro_status'],
					'parallel_plugin_link'	=> $plugin_value['parallel_plugin_link'],
					)
				);
			}
		}

		/* add cron to delete old mail once a day */
		if ( ! wp_next_scheduled( 'mlq_mail_delete_hook' ) ) {
			wp_schedule_event( time(), 'daily', 'mlq_mail_delete_hook' );
		}
		/* add cron mail_hook if there are unsent letters when plugin is reactivated */
		$next_mails = $wpdb->get_var( "SELECT `mail_send_id` FROM `" . $wpdb->base_prefix . "mlq_mail_send` WHERE `mail_status`='0' AND `trash_status`=0;" );
		if ( ! empty( $next_mails ) ) {
			mlq_cron_hook_activate();
		}
	}
}

/**
 * Function to add plugin scripts
 * @return void
 */
if ( ! function_exists ( 'mlq_admin_head' ) ) {
	function mlq_admin_head() {
		if ( isset( $_REQUEST['page'] ) && ( 'mlq_view_mail_queue' == $_REQUEST['page'] || 'mlq_settings' == $_REQUEST['page'] ) ) {
			wp_enqueue_style( 'mlq_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			$script_vars = array(
				'toLongMessage' => __( 'Are you sure that you want to enter such a large value?', 'email-queue' )
			);
			wp_enqueue_script( 'mlq_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			wp_localize_script( 'mlq_script', 'mlqScriptVars', $script_vars );
		}
	}
}


 /**
  * Functions that deal with mail
  */

/**
 * Function to check if mail-capable plugin has in_queue_status in our plugin.
 * For use in other plugins' code
 * @param plugin_link 	plugin basename
 * @return true / false
 */
if ( ! function_exists( 'mlq_if_mail_plugin_is_in_queue' ) ) {
	function mlq_if_mail_plugin_is_in_queue( $plugin_link ) {
		global $wpdb;
		/* look up in DB if mail plugin is set to put his mail in queue of our plugin */
		if ( '1' === $wpdb->get_var( "SELECT `in_queue_status` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='" . $plugin_link . "'" ) ) 
			return true;
		else
			return false;
	}
}

/**
 * Function to get mail data from Contact Form and save it to DB table
 * @param plugin_link 		unique plugin identifier
 * @param sendto 			email address of receiver
 * @param message_subject 	subject of mail
 * @param message_text 		text of mail
 * @param attachments 		attachment of mail
 * @param headers 			headers of mail
 * @global bool mlq_mail_result true/false on operation of insert in DB
 * @return void
 */
if ( ! function_exists( 'mlq_get_mail_data_from_contact_form' ) ) {
	function mlq_get_mail_data_from_contact_form( $plugin_link, $sendto, $message_subject, $message_text, $attachments, $headers = '' ) {
		global $wpdb, $mlq_mail_result;
		/* get plugin id, in_queue_status and priority from DB based on plugin link*/
		$plugin_info = $wpdb->get_row( "SELECT `mail_plugin_id`, `in_queue_status`, `priority_general` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='" . $plugin_link . "';", ARRAY_A );
		/* save mail and put it in queue if plugin has in_queue_status 'ON' */
		if ( 1 == $plugin_info['in_queue_status'] ) {
			$plugin_id = $plugin_info['mail_plugin_id'];
			$mail_priority = $plugin_info['priority_general'];
			if ( empty( $headers ) ) {
				/* get contact-form options for headers */
				$cntctfrm_options = get_option( 'cntctfrm_options' );
				if ( 1 == $cntctfrm_options['cntctfrm_html_email'] )
					$headers = 'Content-type: text/html; charset=utf-8' . "\n";
				else
					$headers = 'Content-type: text/plain; charset=utf-8' . "\n";
			}
			
			/* Save message into database */
			$mlq_message_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_send', 
				array( 
					'subject'			=> $message_subject, 
					'body'				=> $message_text,
					'headers'			=> $headers,
					'date_create'		=> time(),
					'plugin_id'			=> $plugin_id,
					'priority'			=> $mail_priority,
					'attachment_path'	=> $attachments,
				)
			);
			$last_id = $wpdb->insert_id;
			/* Save email address into database */
			$mlq_user_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_users', 
				array( 
					'user_email'	=> $sendto, 
					'id_mail'		=> $last_id,
					'plugin_id'		=> $plugin_id,
					'priority'		=> $mail_priority,
				)
			);
			$mlq_mail_result = ( false !== $mlq_message_save && false !== $mlq_user_save ) ? true : false;
			/* register cron hook */
			mlq_cron_hook_activate();
		}
	}
}

/**
 * Function to get mail data from Contact Form PRO and save it to DB table
 * @param plugin_link 		unique plugin identifier
 * @param sendto 			email address of receiver
 * @param message_subject 	subject of mail
 * @param message_text 		text of mail
 * @param attachments 		attachment of mail
 * @param mlq_user_email 	if not false (email of user)
 * @param headers 			headers of mail
 * @global bool mlq_mail_result true/false on operation of insert in DB
 * @return void
 */
if ( ! function_exists( 'mlq_get_mail_data_from_contact_form_pro' ) ) {
	function mlq_get_mail_data_from_contact_form_pro( $plugin_link, $sendto, $message_subject, $message_text, $attachments, $mlq_user_email, $headers = '' ) {
		global $cntctfrmpr_options, $wpdb, $mlq_mail_result;
		/* get plugin id, in_queue_status and priority from DB based on plugin link*/
		$plugin_info = $wpdb->get_row( "SELECT `mail_plugin_id`, `in_queue_status`, `priority_general` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='" . $plugin_link . "';", ARRAY_A );
		/* save mail and put it in queue if plugin has in_queue_status 'ON' */
		if ( 1 == $plugin_info['in_queue_status'] ) {
			$plugin_id = $plugin_info['mail_plugin_id'];
			$mail_priority = $plugin_info['priority_general'];

			if ( empty( $headers ) ) {
				if ( false === $mlq_user_email ) {
					$mlq_user_email = $sendto;
				}
				/* set headers based on contact-form-pro options */
				$headers = ( 1 == $cntctfrmpr_options['html_email'] ) ? 'Content-type: text/html; charset=utf-8' . "\n" : 'Content-type: text/plain; charset=utf-8' . "\n";
				/* Add reply-to */
				if ( 1 == $cntctfrmpr_options['header_reply_to'] ) {
					$headers .= 'Reply-To: ' . $mlq_user_email . "\n";
				}
				/* Additional headers */
				$headers .= ( 'custom' == $cntctfrmpr_options['from_email'] ) ? 'From: ' . stripslashes( $cntctfrmpr_options['custom_from_email'] ) . '' : 'From: ' . $mlq_user_email . '';
			}

			/* Save message into database */
			$mlq_message_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_send', 
				array( 
					'subject'			=> $message_subject, 
					'body'				=> $message_text,
					'headers'			=> $headers,
					'date_create'		=> time(),
					'plugin_id'			=> $plugin_id,
					'priority'			=> $mail_priority,
					'attachment_path'	=> $attachments,
				)
			);
			$last_id = $wpdb->insert_id;
			/* Save email address into database */
			$mlq_user_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_users', 
				array( 
					'user_email'	=> $sendto, 
					'id_mail'		=> $last_id,
					'plugin_id'		=> $plugin_id,
					'priority'		=> $mail_priority,
				)
			);
			$mlq_mail_result = ( false !== $mlq_message_save && false !== $mlq_user_save ) ? true : false;
			/* register cron hook */
			mlq_cron_hook_activate();
		}
	}
}

/**
 * Function to get mail data from Sender and save it to our DB tables
 * @param plugin_link 		unique plugin identifier
 * @param users_to 			array with users IDs (we get into DB to get user's email by ID)
 * @param message_subject 	subject of mail
 * @param message_text 		text of mail
 * @param mail_id_in_sender ID of mail in Sender DB table
 * @return void 
 */
if ( ! function_exists( 'mlq_get_mail_data_from_sender' ) ) {
	function mlq_get_mail_data_from_sender( $plugin_link, $users_to, $message_subject, $message_text, $mail_id_in_sender ) {
		global $wpdb;
		/* get plugin id, in_queue_status and priority from DB based on plugin link*/
		$plugin_info = $wpdb->get_row( "SELECT `mail_plugin_id`, `in_queue_status`, `priority_general` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='" . $plugin_link . "';", ARRAY_A );
		/* save mail and put it in queue if plugin has in_queue_status 'ON' */
		if ( 1 == $plugin_info['in_queue_status'] ) {
			$plugin_id = $plugin_info['mail_plugin_id'];
			$mail_priority = $plugin_info['priority_general'];
			/* get sender options for headers */
			$sndr_options = get_option( 'sndr_options' );
			$from_name  = 'admin_name' == $sndr_options['sndr_select_from_field'] ? $sndr_options['sndr_from_admin_name'] : $sndr_options['sndr_from_custom_name'];
			$from_email = empty( $sndr_options['sndr_from_email'] ) ? get_option( 'admin_email' ) : $sndr_options['sndr_from_email'];
			$sndr_headers = 'From: ' . $from_name . ' <' . $from_email . '>' . "\r\n"; 
			/* Save mail message into database */
			$wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_send', 
				array( 
					'subject'		=> $message_subject, 
					'body'			=> $message_text,
					'headers'		=> $sndr_headers,
					'date_create'	=> time(),
					'plugin_id'		=> $plugin_id,
					'priority'		=> $mail_priority,
				)
			);
			$last_id = $wpdb->insert_id;
			/* Save email address into database */
			foreach ( $users_to as $user_to ) {
				$user_email = $wpdb->get_var( "SELECT `user_email` FROM `" . $wpdb->base_prefix . "sndr_mail_users_info` WHERE `id_user`=" . $user_to . ";" );
				$wpdb->insert( 
					$wpdb->base_prefix . 'mlq_mail_users', 
					array( 
						'user_email'		=> $user_email, 
						'id_mail'			=> $last_id,
						'plugin_id'			=> $plugin_id,
						'priority'			=> $mail_priority,
						'user_id_in_sender'	=> $user_to,
						'mail_id_in_sender'	=> $mail_id_in_sender,
					)
				);
			}
			/* register cron hook */
			mlq_cron_hook_activate();
		}
	}
}

/**
 * Function to get mail data of started mailout from Sender Pro and save it to our DB tables
 * @param plugin_link unique plugin identifier
 * @param mailout_id  ID of mailout in Sender DB table
 * @return void 
 */
if ( ! function_exists( 'mlq_start_mailout_from_sender_pro' ) ) {
	function mlq_start_mailout_from_sender_pro( $plugin_link, $mailout_id ) {
		global $wpdb;
		/* get plugin id, in_queue_status and priority from DB based on plugin link*/
		$plugin_info = $wpdb->get_row( "SELECT `mail_plugin_id`, `in_queue_status`, `priority_general` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='" . $plugin_link . "';", ARRAY_A );
		/* save mail and put it in queue if plugin has in_queue_status 'ON' */
		if ( 1 == $plugin_info['in_queue_status'] ) {
			$plugin_id = $plugin_info['mail_plugin_id'];
			$mail_priority = $plugin_info['priority_general'];
			/* get mail data from Sender Pro DB table */
			$mailout_data_sndrpr = $wpdb->get_row( 
				"SELECT `mail_id`, `distridution_lists_ids`, `use_plugin_settings`, `from_name`, `from_email`, `mailout_priority_number`, `mailout_create`, `remote_delivery` FROM `" . $wpdb->base_prefix . "sndr_mailout` WHERE `mailout_id`='" . $mailout_id . "';", ARRAY_A );

			if ( '1' == $mailout_data_sndrpr['remote_delivery'] ) {
				/* get headers for message */
				if ( '1' != $mailout_data_sndrpr['use_plugin_settings'] ) {
					$sndrpr_options = get_option( 'sndrpr_options' );
					$from_name  = 'admin_name' == $sndrpr_options['select_from_field'] ? $sndrpr_options['from_admin_name'] : $sndrpr_options['from_custom_name'];
					$from_email = empty( $sndrpr_options['from_email'] ) ? get_option( 'admin_email' ) : $sndrpr_options['from_email'];
				} else {
					$from_name  = $mailout_data_sndrpr['from_name'];
					$from_email = $mailout_data_sndrpr['from_email'];
				}
				$headers_for_sndrpr = 'From: ' . $from_name . ' <' . $from_email . '>' . "\r\n"; 

				/* get message subject and text from Sender Pro DB table*/
				$message_data_in_sndrpr = $wpdb->get_row( "SELECT `subject`, `body` FROM `" . $wpdb->base_prefix . "sndr_mail_send` WHERE `mail_send_id`='" . $mailout_data_sndrpr['mail_id'] . "';", ARRAY_A );

				/* Save mail message into database */
				$wpdb->insert( 
					$wpdb->base_prefix . 'mlq_mail_send', 
					array( 
						'subject'			=> $message_data_in_sndrpr['subject'], 
						'body'				=> $message_data_in_sndrpr['body'], 
						'headers'			=> $headers_for_sndrpr,
						'date_create'		=> strtotime( $mailout_data_sndrpr['mailout_create'] ), 
						'plugin_id'			=> $plugin_id,
						'priority'			=> $mail_priority,
						'priority_sndrpr'	=> $mailout_data_sndrpr['mailout_priority_number'],
					)
				);
				$last_id_inserted = $wpdb->insert_id;

				/* Save email addresses into database */
				$users_to = mlq_get_user_ids_from_sndrpr( $mailout_data_sndrpr['distridution_lists_ids'] );
				foreach ( $users_to as $user_to ) {
					$user_email = $wpdb->get_var( "SELECT `user_email` FROM `" . $wpdb->base_prefix . "sndr_mail_users_info` WHERE `id_user`=" . $user_to . ";" );
					$wpdb->insert( 
						$wpdb->base_prefix . 'mlq_mail_users', 
						array( 
							'user_email'		=> $user_email, 
							'id_mail'			=> $last_id_inserted,
							'plugin_id'			=> $plugin_id,
							'priority'			=> $mail_priority,
							'user_id_in_sender'	=> $user_to,
							'mail_id_in_sender'	=> $mailout_id,
						)
					);
				}
				/* register cron hook */
				mlq_cron_hook_activate();
			}
		}
	}
}

/**
 * Function to get users' ids list from Sender Pro
 * @param mailing_ids 	serialized array with mailing lists from Sender Pro
 * @return users_to 	array - list with users ids
 */
if ( ! function_exists( 'mlq_get_user_ids_from_sndrpr' ) ) {
	function mlq_get_user_ids_from_sndrpr( $mailing_ids ) {
		global $wpdb; 
		if ( empty( $mailing_ids ) ) {
			return false;
		} else {
			$main_condition = $users_id = $insert_data = '';
			$users_to = array();
			/* get array of mailing lists */
			$mailing_ids  = implode ( ',', unserialize( $mailing_ids ) ); 
			$mailing_data = $wpdb->get_results( "SELECT `list_value` FROM `" . $wpdb->base_prefix . "sndr_distribution_lists` WHERE `list_id` IN (" . $mailing_ids . ");", ARRAY_A );
			if ( empty( $mailing_data ) ) {
				return false;
			} else { 
				foreach( $mailing_data as $list ) {
					/* get array with mailing list`s IDs */
					$mailing_list = unserialize( $list['list_value'] );
					foreach( $mailing_list as $data ) {
						if ( 'all' == $data['role'] ) { /* if were selected all users */
							break;
						} else {
							if ( array_key_exists( 'all', $data ) ) { /* if were selected all users by role */
								$main_condition .= empty( $main_condition ) ? "`meta_value` LIKE '%\"" . $data['role'] . "\"%' " : "OR `meta_value` LIKE '%\"" . $data['role'] . "\"%' ";
							}
							if ( array_key_exists( 'users_id', $data ) ) { /* if were selected specific users */
								$users_id .= empty( $users_id ) ? implode( ',', $data['users_id'] ) : ',' . implode( ',', $data['users_id'] );
							}
						}
					}
				}
				/* 
				 * forming request to database
				 */
				if ( empty( $main_condition ) ) {
					$main_condition = empty( $users_id ) ? '' : '`user_id` IN (' . $users_id . ') AND ';
				} else {
					$users_id       = empty( $users_id ) ? '' : ' OR `user_id` IN (' . $users_id . ') ';
					$main_condition = "`meta_key` LIKE '%capabilities%' AND (" . $main_condition . $users_id . ") AND ";
				}
				/* look for black list and deleted accounts if Subscriber is installed */
				$add_condition  = mlq_unsubscribe_code_exists() ? " AND `black_list`=0 AND `delete`=0": '';
				/* get array with user`s IDs */
				$users_ids = $wpdb->get_results( "SELECT DISTINCT `user_id` FROM `" . $wpdb->base_prefix . "usermeta`
					LEFT JOIN `" . $wpdb->base_prefix . "sndr_mail_users_info` ON `" . $wpdb->base_prefix . "sndr_mail_users_info`.`id_user`=`" . $wpdb->base_prefix . "usermeta`.`user_id`
					WHERE " . $main_condition . "`subscribe`=1" . $add_condition . ";",
					ARRAY_A );
				/* transform user`s IDs array to more simple array */
				foreach ( $users_ids as $user_id ) {
					$users_to[] .= $user_id['user_id'];
				}
				return $users_to;
			}
		}
	}
}

/**
 * Function to get mail data from Subscriber and save message in database 
 * @param plugin_link 		unique plugin identifier
 * @param sendto 			email address of receiver
 * @param message_subject 	subject of mail
 * @param message_text 		text of mail
 * @param headers 			headers of mail
 * @global bool mlq_mail_result true/false on operation of insert in DB
 * @return void 
 */
if ( ! function_exists( 'mlq_get_mail_data_from_subscriber' ) ) {
	function mlq_get_mail_data_from_subscriber( $plugin_link, $sendto, $message_subject, $message_text, $headers ) {
		global $wpdb, $mlq_mail_result;
		/* get plugin id, in_queue_status and priority from DB based on plugin link*/
		$plugin_info = $wpdb->get_row( "SELECT `mail_plugin_id`, `in_queue_status`, `priority_general` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='" . $plugin_link . "';", ARRAY_A );
		/* save mail and put it in queue if plugin has in_queue_status 'ON' */
		if ( 1 == $plugin_info['in_queue_status'] ) {
			$plugin_id = $plugin_info['mail_plugin_id'];
			$mail_priority = $plugin_info['priority_general'];
			/* Save mail message into database */
			$mlq_message_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_send', 
				array( 
					'subject'		=> $message_subject, 
					'body'			=> $message_text,
					'headers'		=> $headers,
					'date_create'	=> time(),
					'plugin_id'		=> $plugin_id,
					'priority'		=> $mail_priority,
				)
			);
			$last_id = $wpdb->insert_id;
			/* Save email address into database */
			$mlq_user_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_users', 
				array( 
					'user_email'	=> $sendto, 
					'id_mail'		=> $last_id,
					'plugin_id'		=> $plugin_id,
					'priority'		=> $mail_priority,
				)
			);
			$mlq_mail_result = ( false !== $mlq_message_save && false !== $mlq_user_save ) ? true : false;
			/* register cron hook */
			mlq_cron_hook_activate();
		}
	}
}

/**
 * Function to get mail data from Updater
 * @param plugin_link 		unique plugin identifier
 * @param sendto 			email address of receiver
 * @param message_subject 	subject of mail
 * @param message_text 		text of mail
 * @param headers 			headers of mail
 * @global bool mlq_mail_result true/false on operation of insert in DB
 * @return void  
 */
if ( ! function_exists( 'mlq_get_mail_data_from_updater' ) ) {
	function mlq_get_mail_data_from_updater( $plugin_link, $sendto, $message_subject, $message_text, $headers ) {
		global $wpdb, $mlq_mail_result;
		/* get plugin id, in_queue_status and priority from DB based on plugin link */
		$plugin_info = $wpdb->get_row( "SELECT `mail_plugin_id`, `in_queue_status`, `priority_general` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='" . $plugin_link . "';", ARRAY_A );
		/* save mail and put it in queue if plugin has in_queue_status 'ON' */
		if ( 1 == $plugin_info['in_queue_status'] ) {
			$plugin_id = $plugin_info['mail_plugin_id'];
			$mail_priority = $plugin_info['priority_general'];
			/* switch Updater's headers to text/html */
			$message_headers		= 'Content-type: text/html; charset=utf-8' . "\n";
			foreach ( $headers as $header ) {
				$message_headers .= $header;
			}
			/* Save mail message into database */
			$mlq_message_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_send', 
				array( 
					'subject'		=> $message_subject, 
					'body'			=> $message_text,
					'headers'		=> $message_headers,
					'date_create'	=> time(),
					'plugin_id'		=> $plugin_id,
					'priority'		=> $mail_priority,
				)
			);
			$last_id = $wpdb->insert_id;
			/* Save email address into database */
			$mlq_user_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_users', 
				array( 
					'user_email'	=> $sendto, 
					'id_mail'		=> $last_id,
					'plugin_id'		=> $plugin_id,
					'priority'		=> $mail_priority,
				)
			);
			$mlq_mail_result = ( false !== $mlq_message_save && false !== $mlq_user_save ) ? true : false;
			/* register cron hook */
			mlq_cron_hook_activate();
		}
	}
}

/**
 * PLUGIN API
 * use these functions to put your mail into this plugin's queue 
 */

/**
 * Function to add external plugin info into this plugin's 'mail_plugins' DB table
 * @param plugin_info 	an associative array with the following plugin information:
 * - plugin_name - a string with the name of the plugin that appears on settings page (may have spaces)
 * - plugin_slug - a string with the slug name to refer to this plugin when setting priority, usually it's a name of plugin main php-file (no spaces allowed)
 * - plugin_link - the result of plugin_basename( __FILE__ ) function, executed in plugin code, i.e. plugin folder name and plugin php-file name with slash as a separator
 * - install_link - a string with a relative link to search page; it appears on this plugin's setting page if external plugin is not installed but its info is present in this plugin DB table
 * @return void
 */
if ( ! function_exists( 'mlq_add_extra_plugin_to_mail_queue' ) ) {
	function mlq_add_extra_plugin_to_mail_queue( $plugin_info ) {
		global $wpdb;
		/* if the given variable is an array and has expected keys */
		if ( is_array( $plugin_info ) && ! empty( $plugin_info['plugin_name'] ) && ! empty( $plugin_info['plugin_slug'] ) && ! empty( $plugin_info['plugin_link'] ) && ! empty( $plugin_info['install_link'] ) ) {
			/* insert plugin info in DB table if doesn't already exist */ 
			$mail_plugin_in_db = $wpdb->get_col( "SELECT `plugin_name` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins`", 0 );
			/* if the added plugin name is unique */
			if ( ! in_array( $plugin_info['plugin_name'], $mail_plugin_in_db ) ) {
				$mail_plugin_in_db = $wpdb->get_col( "SELECT `plugin_slug` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins`", 0 );
				/* if the added plugin slug is unique */
				if ( ! in_array( $plugin_info['plugin_slug'], $mail_plugin_in_db ) ) {
					$mail_plugin_in_db = $wpdb->get_col( "SELECT `plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins`", 0 );
					/* if the added plugin slug is unique */
					if ( ! in_array( $plugin_info['plugin_link'], $mail_plugin_in_db ) ) {
						$wpdb->insert( $wpdb->base_prefix . 'mlq_mail_plugins', array(
							'plugin_name'	=> $plugin_info['plugin_name'],
							'plugin_slug'	=> $plugin_info['plugin_slug'],
							'plugin_link'	=> $plugin_info['plugin_link'],
							'install_link'	=> $plugin_info['install_link'],
							)
						);
					}
				}
			}
		}
	}
}

/**
 * Universal Function to get mail data for our plugin
 * takes maximum of 6 arguments (4 required, 2 optional) and global $mlq_mail_result
 * if success mlq_mail_result is true, else - false
 * @param plugin_link 		unique plugin identifier (plugin basename)
 * @param sendto 			email address of receiver
 * @param message_subject 	subject of mail
 * @param message_text 		text of mail
 * @param headers 			headers of mail
 * @param attachment 		attachment of mail (string with attachment path)
 * @global bool mlq_mail_result true/false on operation of insert in DB
 * @return void  
 */
if ( ! function_exists( 'mlq_get_mail_data_for_email_queue_and_save' ) ) {
	function mlq_get_mail_data_for_email_queue_and_save( $plugin_link, $sendto, $message_subject, $message_text, $headers = '', $attachment = '' ) {
		global $wpdb, $mlq_mail_result;
		/* get plugin id, in_queue_status and priority from DB based on plugin link */
		$plugin_info = $wpdb->get_row( "SELECT `mail_plugin_id`, `in_queue_status`, `priority_general` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='" . $plugin_link . "';", ARRAY_A );
		/* save mail and put it in queue if plugin has in_queue_status 'ON' */
		if ( 1 == $plugin_info['in_queue_status'] ) {
			$plugin_id = $plugin_info['mail_plugin_id'];
			$mail_priority = $plugin_info['priority_general'];
			/* check if headers exist */
			if ( "" == $headers ) {
				$message_headers = 'Content-type: text/html; charset=utf-8' . "\n";
			}
			/* Save mail message into database */
			$mlq_message_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_send', 
				array( 
					'subject'			=> $message_subject, 
					'body'				=> $message_text,
					'headers'			=> $message_headers,
					'date_create'		=> time(),
					'plugin_id'			=> $plugin_id,
					'priority'			=> $mail_priority,
					'attachment_path'	=> $attachment,
				)
			);
			$last_id = $wpdb->insert_id;
			/* Save email address into database */
			$mlq_user_save = $wpdb->insert( 
				$wpdb->base_prefix . 'mlq_mail_users', 
				array( 
					'user_email'	=> $sendto, 
					'id_mail'		=> $last_id,
					'plugin_id'		=> $plugin_id,
					'priority'		=> $mail_priority,
				)
			);
			$mlq_mail_result = ( false !== $mlq_message_save && false !== $mlq_user_save ) ? true : false;
			/* register cron hook */
			mlq_cron_hook_activate();
		}
	}
}

/**
 * Check if "Subscriber" plugin is installed
 * @return    bool       "true" if exists 'unsubscribe_code'-column in 'sndr_mail_users_info'-table
 */
if ( ! function_exists( 'mlq_unsubscribe_code_exists' ) ) {
	function mlq_unsubscribe_code_exists() {
		global $wpdb; 
		$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->base_prefix . "sndr_mail_users_info` LIKE 'unsubscribe_code'" );
		if ( $column_exists ) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Modified core cron functions for multisite
 */

/**
 * Modified core cron function 'wp_next_scheduled'
 * to check 'cron' option in main table
 *
 * Retrieve the next timestamp for a cron event.
 *
 * @param string $hook Action hook to execute when cron is run.
 * @param array $args Optional. Arguments to pass to the hook's callback function.
 * @return bool|int The UNIX timestamp of the next time the scheduled event will occur.
 * @return void
 */
if ( ! function_exists( 'mlq_wp_next_scheduled' ) ) {
	function mlq_wp_next_scheduled( $hook, $args = array() ) {
		$crons = mlq_get_cron_array();
		$key = md5( serialize( $args ) );
		if ( empty( $crons ) ) {
			return false;
		}
		foreach ( $crons as $timestamp => $cron ) {
			if ( isset( $cron[$hook][$key] ) ) {
				return $timestamp;
			}
		}
		return false;
	}
}

/**
 * Modified core cron function '_get_cron_array'
 * Retrieve cron info array option.
 *
 * @return array CRON info array.
 */
if ( ! function_exists( 'mlq_get_cron_array' ) ) {
	function mlq_get_cron_array()  {
		$cron = mlq_get_network_option( 'cron' );
		if ( ! is_array( $cron ) ) {
			return false;
		}
		if ( ! isset( $cron['version'] ) ) {
			$cron = mlq_upgrade_cron_array( $cron );
		}
		unset( $cron['version']) ;
		return $cron;
	}
}

/**
 * Modified core cron function '_upgrade_cron_array'
 * Upgrade a Cron info array.
 *
 * @param array $cron Cron info array from {@link _get_cron_array()}.
 * @return array An upgraded Cron info array.
 */
if ( ! function_exists( 'mlq_upgrade_cron_array' ) ) {
	function mlq_upgrade_cron_array( $cron ) {
		if ( isset( $cron['version'] ) && 2 == $cron['version'] ) {
			return $cron;
		}
		$new_cron = array();
		foreach ( (array) $cron as $timestamp => $hooks ) {
			foreach ( (array) $hooks as $hook => $args ) {
				$key = md5( serialize( $args['args'] ) );
				$new_cron[ $timestamp ][ $hook ][ $key ] = $args;
			}
		}
		$new_cron['version'] = 2;
		mlq_update_option( 'cron', $new_cron );
		return $new_cron;
	}
}

/**
 * Modified core cron function 'wp_schedule_event'
 * Schedule a periodic event in main table if multisite.
 *
 * Schedules a hook which will be executed by the WordPress actions core on a
 * specific interval, specified by you. The action will trigger when someone
 * visits your WordPress site, if the scheduled time has passed.
 *
 * @param int $timestamp Timestamp for when to run the event.
 * @param string $recurrence How often the event should recur.
 * @param string $hook Action hook to execute when cron is run.
 * @param array $args Optional. Arguments to pass to the hook's callback function.
 * @return bool|null False on failure, null when complete with scheduling event.
 */
if ( ! function_exists( 'mlq_wp_schedule_event' ) ) {
	function mlq_wp_schedule_event( $timestamp, $recurrence, $hook, $args = array() ) {
		$crons = _get_cron_array();
		$schedules = wp_get_schedules();

		if ( ! isset( $schedules[ $recurrence ] ) ) {
			return false;
		}

		$event = (object) array( 'hook' => $hook, 'timestamp' => $timestamp, 'schedule' => $recurrence, 'args' => $args, 'interval' => $schedules[$recurrence]['interval'] );
		/** This filter is documented in wp-includes/cron.php */
		$event = apply_filters( 'schedule_event', $event );

		/* A plugin disallowed this event */
		if ( ! $event ) {
			return false;
		}

		$key = md5( serialize( $event->args ) );

		$crons[ $event->timestamp ][ $event->hook ][ $key ] = array( 'schedule' => $event->schedule, 'args' => $event->args, 'interval' => $event->interval );
		uksort( $crons, "strnatcasecmp" );
		mlq_set_cron_array( $crons );
	}
}

/**
 * Modified core cron function '_set_cron_array'
 * Updates the CRON option with the new CRON array.
 *
 * @param array $cron Cron info array from {@link _get_cron_array()}.
 */
if ( ! function_exists( 'mlq_set_cron_array' ) ) {
	function mlq_set_cron_array( $cron ) {
		$cron['version'] = 2;
		mlq_update_option( 'cron', $cron );
	}
}

/**
 * Modified core function 'get_option'
 * Retrieve network option value based on name of option (in main, i.e. network, table) in main table if multisite.
 *
 * If the option does not exist or does not have a value, then the return value
 * will be false. This is useful to check whether you need to install an option
 * and is commonly used during installation of plugin options and to test
 * whether upgrading is required.
 *
 * If the option was serialized then it will be unserialized when it is returned.
 *
 * @param string $option Name of option to retrieve. Expected to not be SQL-escaped.
 * @param mixed $default Optional. Default value to return if the option does not exist.
 * @return mixed Value set for the option.
 */
if ( ! function_exists( 'mlq_get_network_option' ) ) {
	function mlq_get_network_option( $option, $default = false ) {
		global $wpdb;
		$option = trim( $option );
		if ( empty( $option ) ) {
			return false;
		}
		/* Filter the value of an existing option before it is retrieved */
		$pre = apply_filters( 'pre_option_' . $option, false );
		if ( false !== $pre ) {
			return $pre;
		}
		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
		}
		if ( ! defined( 'WP_INSTALLING' ) ) {
			/* prevent non-existent options from triggering multiple queries */
			$notoptions = wp_cache_get( 'notoptions', 'options' );
			if ( isset( $notoptions[$option] ) ) {
				/* Filter the default value for an option */
				return apply_filters( 'default_option_' . $option, $default );
			}
			$alloptions = wp_load_alloptions();
			if ( isset( $alloptions[ $option ] ) ) {
				$value = $alloptions[ $option ];
			} else {
				$value = wp_cache_get( $option, 'options' );
				if ( false === $value ) {
					/* get option value from the main table */
					$row = $wpdb->get_row( "SELECT `option_value` FROM '" . $wpdb->base_prefix . "options` WHERE `option_name` = " . $option . " LIMIT 1" );
					/* Has to be get_row instead of get_var because of funkiness with 0, false, null values */
					if ( is_object( $row ) ) {
						$value = $row->option_value;
						wp_cache_add( $option, $value, 'options' );
					} else { /* option does not exist, so we must cache its non-existence */
						$notoptions[$option] = true;
						wp_cache_set( 'notoptions', $notoptions, 'options' );
						/* This filter is documented in wp-includes/option.php */
						return apply_filters( 'default_option_' . $option, $default );
					}
				}
			}
		} else {
			$suppress = $wpdb->suppress_errors();
			$row = $wpdb->get_row( "SELECT `option_value` FROM '" . $wpdb->base_prefix . "options` WHERE `option_name` = " . $option . " LIMIT 1" );
			$wpdb->suppress_errors( $suppress );
			if ( is_object( $row ) ) {
				$value = $row->option_value;
			} else {
				/* This filter is documented in wp-includes/option.php */
				return apply_filters( 'default_option_' . $option, $default );
			}
		}
		/* If home is not set use siteurl */
		if ( 'home' == $option && '' == $value ) {
			return get_option( 'siteurl' );
		}
		if ( in_array( $option, array('siteurl', 'home', 'category_base', 'tag_base') ) ) {
			$value = untrailingslashit( $value );
		}
		/* Filter the value of an existing option */
		return apply_filters( 'option_' . $option, maybe_unserialize( $value ) );
	}
}

/**
 * Modified core function 'update_option'
 * Update the value of an option in a main network 'options' table if multisite.
 *
 * @param string $option Option name. Expected to not be SQL-escaped.
 * @param mixed $value Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @return bool False if value was not updated and true if value was updated.
 */
if ( ! function_exists( 'mlq_update_option' ) ) {
	function mlq_update_option( $option, $value ) {
		global $wpdb;
		$option = trim( $option );
		if ( empty( $option ) ) {
			return false;
		}
		wp_protect_special_option( $option );
		if ( is_object( $value ) ) {
			$value = clone $value;
		}
		$value = sanitize_option( $option, $value );
		$old_value = get_option( $option );
		/* Filter a specific option before its value is (maybe) serialized and updated */
		$value = apply_filters( 'pre_update_option_' . $option, $value, $old_value );
		/* Filter an option before its value is (maybe) serialized and updated */
		$value = apply_filters( 'pre_update_option', $value, $option, $old_value );
		/* If the new and old values are the same, no need to update */
		if ( $value === $old_value ) {
			return false;
		}
		if ( false === $old_value ) {
			return add_option( $option, $value );
		}
		$serialized_value = maybe_serialize( $value );
		/* Fires immediately before an option value is updated */
		do_action( 'update_option', $option, $old_value, $value );
		/* there we update option in main DB table */
		$result = $wpdb->update( $wpdb->base_prefix . 'options', 
			array( 'option_value' => $serialized_value ), 
			array( 'option_name' => $option ) 
		);
		if ( ! $result ) {
			return false;
		}
		$notoptions = wp_cache_get( 'notoptions', 'options' );
		if ( is_array( $notoptions ) && isset( $notoptions[$option] ) ) {
			unset( $notoptions[$option] );
			wp_cache_set( 'notoptions', $notoptions, 'options' );
		}
		if ( ! defined( 'WP_INSTALLING' ) ) {
			$alloptions = wp_load_alloptions();
			if ( isset( $alloptions[$option] ) ) {
				$alloptions[ $option ] = $serialized_value;
				wp_cache_set( 'alloptions', $alloptions, 'options' );
			} else {
				wp_cache_set( $option, $serialized_value, 'options' );
			}
		}
		/* Fires after the value of a specific option has been successfully updated */
		do_action( "update_option_{$option}", $old_value, $value );
		/* Fires after the value of an option has been successfully updated */
		do_action( 'updated_option', $option, $old_value, $value );
		return true;
	}
}

/**
 * Functions to register cron, send mail from queue
 */

/**
 * Function to add activation cron hook
 * @return void
 */
if ( ! function_exists( 'mlq_cron_hook_activate' ) ) {
	function mlq_cron_hook_activate() {
		add_filter( 'cron_schedules', 'mlq_more_reccurences' );
		if ( is_multisite() ) { /* use custom 'mlq_wp' cron functions for addressing main table only */
			if ( ! mlq_wp_next_scheduled( 'mlq_mail_hook' ) ) {
				mlq_wp_schedule_event( time(), 'my_cron_period', 'mlq_mail_hook' );
			}
		} else { /* standart wp functions */
			if ( ! wp_next_scheduled( 'mlq_mail_hook' ) ) {
				wp_schedule_event( time(), 'my_cron_period', 'mlq_mail_hook' );
			}
		}		
	}
}

/**
 * Function to add new preiod between mail sending
 * @return void
 */
if ( ! function_exists( 'mlq_more_reccurences' ) ) {
	function mlq_more_reccurences( $schedules ) {
		$mlq_options = ( 1 == is_multisite() ) ? get_site_option( 'mlq_options' ) : get_option( 'mlq_options' );
		$schedules['my_cron_period'] = array( 'interval' => $mlq_options['mail_run_time'] * 60, 'display' => __( 'Your interval', 'email-queue' ) );
		return $schedules;
	}
}

/**
 * Function to periodicaly send mail
 * @return void
 */
if ( ! function_exists( 'mlq_cron_mail' ) ) {
	function mlq_cron_mail() {
		global $wpdb;
		/* get options from DB */
		$mlq_options = ( 1 == is_multisite() ) ? get_site_option( 'mlq_options' ) : get_option( 'mlq_options' );
		/* create an instance of phpmailer class if wp_mail is not used */
		if ( 'wp_mail' != $mlq_options['mail_method'] ) {
			require_once( ABSPATH . WPINC . '/class-phpmailer.php' );
			$mail = new PHPMailer();
		} else {
			require_once( ABSPATH . "wp-includes/pluggable.php" );
		}
		$sended = $errors = array();
		/* get list of messages with the highest priority that are not sent */
		$users_mail_sends = $wpdb->get_results( "
			SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_users` AS users 
			JOIN `" . $wpdb->base_prefix . "mlq_mail_send` AS mails ON ( 
				users.`id_mail` = mails.`mail_send_id` AND 
				mails.`trash_status` = 0 ) 
			WHERE users.`status`=0 
			ORDER BY users.`priority` DESC 
			LIMIT " . $mlq_options['mail_send_count'] . ";", 
			ARRAY_A );
		/* perform mailout if messages are not sent */
		if ( ! empty( $users_mail_sends ) ) {
			/* get the ID of Sender free plugin */
			$sender_plugin_id = $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='sender/sender.php';" );
			/* get info (ID & slug) of Sender PRO plugin */
			$sender_pro_plugin = $wpdb->get_row( "SELECT `mail_plugin_id`, `plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='sender-pro/sender-pro.php';", ARRAY_A );
			/* get the IDs of CF free and PRO versions */
			$mlq_cntctfrms_ids = $wpdb->get_col( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link` IN ('contact-form-plugin/contact_form.php', 'contact-form-pro/contact_form_pro.php');" );
			/* loop through the mails */
			foreach ( $users_mail_sends as $users_mail_send ) {
				/* get message fields for function sending mail */
				$current_message_id			= $users_mail_send['id_mail'];
				$current_message_email		= $users_mail_send['user_email'];
				$current_message_subject	= $users_mail_send['subject'];
				$current_message_body		= $users_mail_send['body'];
				$current_message_headers	= $users_mail_send['headers'];
				$current_message_attachment = $users_mail_send['attachment_path'];

				/* what plugin the mail has come from */
				if ( $users_mail_send['plugin_id'] == $sender_plugin_id ) {
					/* if current plugin is sender - add unsubscribe link for sender */
					$current_message_body	= apply_filters( 'sbscrbr_add_unsubscribe_link', $current_message_body, array( 'user_email' => $users_mail_send['user_email'], ) );
				} else if ( $users_mail_send['plugin_id'] == $sender_pro_plugin['mail_plugin_id'] ) {
					/* if current plugin is Sender Pro */
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					$plugins_list = get_plugins();
					if ( array_key_exists( $sender_pro_plugin['plugin_link'], $plugins_list ) ) {
						if ( ! is_plugin_active( $sender_pro_plugin['plugin_link'] ) ) {
							$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? basename( WP_CONTENT_DIR ) : 'wp-content';
							require_once( ABSPATH . $wp_content_dir . "/plugins/sender-pro/sender-pro.php" );
						}
						/* user data in sender */
						$user_data = $wpdb->get_row( "SELECT * FROM `" . $wpdb->base_prefix . "sndr_mail_users_info` AS info 
							JOIN `" . $wpdb->base_prefix . "sndr_users` AS sent ON ( info.`id_user`=sent.`id_user` ) WHERE sent.`id_mailout`= " . $users_mail_send['mail_id_in_sender'] . " AND sent.`id_user`=" . $users_mail_send['user_id_in_sender'] . " LIMIT 1;", ARRAY_A );
						$current_user_data = array(
							'mail_users_id'     => $user_data['mail_users_id'],
							'id_user'           => $user_data['id_user'],
							'user_email'        => $user_data['user_email'],
							'user_display_name' => $user_data['user_display_name'],
							'unsubscribe_code'  => isset( $user_data['unsubscribe_code'] ) ? $user_data['unsubscribe_code']: ''
						);
						/* mail data in sender */
						$letter_data = $wpdb->get_row( "SELECT * FROM `" . $wpdb->base_prefix . "sndr_mail_send` WHERE `mail_send_id`=" . $user_data['id_mail'] . ";", ARRAY_A );
						global $sndrpr_options;
						/* get sender-pro options */
						if ( empty( $sndrpr_options ) ) {
							$sndrpr_options = ( is_multisite() ) ? get_site_option( 'sndrpr_options' ) : get_option( 'sndrpr_options' );
						}
						if ( '1' == $user_data['use_plugin_settings'] ) {
							$from_name  = $user_data['from_name'];
							$from_email = $user_data['from_email'];
						} else {
							$from_name  = $sndrpr_options['from_custom_name'];
							if ( empty( $from_name ) )
								$from_name = get_bloginfo( 'name' );

							if ( empty( $sndrpr_options['from_email'] ) ) {
								$sitename = strtolower( $_SERVER['SERVER_NAME'] );
								if ( substr( $sitename, 0, 4 ) == 'www.' ) {
									$sitename = substr( $sitename, 4 );
								}
								$from_email = 'wordpress@' . $sitename;
							} else
								$from_email = $sndrpr_options['from_email']; 
						}

						/* start forming letter content */
						$content     = sndrpr_replace_shortcodes( $current_user_data, $letter_data );
						$fonts       = sndrpr_get_fonts( $letter_data['fonts'] );
						/* get letter data */
						$body           = '';
						/* $subject        = '=?UTF-8?B?' . base64_encode( $subject ) . '?=';
						$from_name      = '=?UTF-8?B?' . base64_encode( $from_name ) . '?='; */
						$to             = $user_data['user_email'];
						$bound_text     = "jimmyP123";
						/* $headers        = $current_message_headers . "\n"; */
						$headers	= 'MIME-Version: 1.0' . "\n";
						$headers	.= 'Content-type: text/html; charset=utf-8' . "\n";
						$headers	.= "From: " .  $from_name . " <" . $from_email . ">\n";

						/* forming content and headers */
						if ( '1' == $sndrpr_options['html_email'] ) { /* send html version of letter */
							if ( $mlq_options['mail_method'] != 'smtp' ) {
								$body = $content;
								$body .= "<img src=\"" . plugins_url( "files/get-view.php", 'sender-pro/sender-pro.php' ) . "?get_mes=" . $user_data['mail_users_id'] . "&s=" . md5( 'bws' . $user_data['mail_users_id'] . 'sndrpr_send' ) .  "\" width=\"0\" height=\"0\" />";
							} else {
								$smtp_array = sndrpr_get_letter_content_for_smtp( $content, $fonts );
								$body = $smtp_array['content'];
								if ( '1' == $sndrpr_options['confirm'] ) {
									$body .= '<img src="' . plugins_url( "files/get-view.php", 'sender-pro/sender-pro.php' ) . "?get_mes=" . $user_data['mail_users_id'] . "&s=" . md5( 'bws' . $user_data['mail_users_id'] . 'sndrpr_send' ) . '" />';
								}

							}
						} else { /* send text version of letter */
							$headers .= "Content-type: text/plain; charset=utf-8\n";
							$body = chunk_split( preg_replace( "/\r\n/", "", strip_tags( html_entity_decode( $content ) ) ) );
						}
						/* assign processed values to message text and headers */
						$current_message_body		= $body;
						$current_message_headers	= $headers;
					} else { /* if Sender Pro was deleted before we sent its messages */
						$current_message_body		= apply_filters( 'sbscrbr_add_unsubscribe_link', $current_message_body, array( 'user_email' => $users_mail_send['user_email'], ) );
					}
				} else if ( "" != $current_message_attachment && in_array( $users_mail_send['plugin_id'], $mlq_cntctfrms_ids ) ) {
					/* get filename for attachment in CF or CF Pro */
					$path_parts = pathinfo( $current_message_attachment );
					if ( $users_mail_send['plugin_id'] == $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='contact-form-plugin/contact_form.php';" ) ) {
						$path_of_uploaded_file_changed = $path_parts['dirname'] . '/' . preg_replace( '/^cntctfrm_[A-Z,a-z,0-9]{32}_/i', '', $path_parts['basename'] );
					} else {
						$path_of_uploaded_file_changed = $path_parts['dirname'] . '/' . preg_replace( '/^cntctfrmpr_[A-Z,a-z,0-9]{32}_/i', '', $path_parts['basename'] );
					}
					/* copy file with original name for sending */
					if ( ! @copy( $current_message_attachment, $path_of_uploaded_file_changed ) )
						$path_of_uploaded_file_changed = $current_message_attachment;
					/* get the filepath of copied file */
					$current_message_attachment = $path_of_uploaded_file_changed;	
				}

				/* prepare mail data for php or smtp mailing */
				if ( 'wp_mail' != $mlq_options['mail_method'] ) {
					/* Adjust headers for phpmail or smtp mail method */
					if ( empty( $current_message_headers ) ) {
						$current_message_headers = array();
					} else {
						if ( ! is_array( $current_message_headers ) ) {
							/* Explode the headers out */
							$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $current_message_headers ) );
						} else {
							$tempheaders = $current_message_headers;
						}
						$current_message_headers = array();
						/* If it's actually got contents */
						if ( ! empty( $tempheaders ) ) {
							/* Iterate through the raw headers */
							foreach ( (array) $tempheaders as $header ) {
								if ( strpos( $header, ':') === false ) {
									if ( false !== stripos( $header, 'boundary=' ) ) {
										$parts = preg_split( '/boundary=/i', trim( $header ) );
										$boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
									}
									continue;
								}
								/* Explode them out */
								list( $name, $content ) = explode( ':', trim( $header ), 2 );
								/* Cleanup crew */
								$name    = trim( $name );
								$content = trim( $content );
								switch ( strtolower( $name ) ) {
									/* process a From: header if it's there */
									case 'from':
										if ( strpos( $content, '<' ) !== false ) {
											$from_name 	= substr( $content, 0, strpos( $content, '<' ) - 1 );
											$from_name 	= str_replace( '"', '', $from_name );
											$from_name 	= trim( $from_name );
											$from_email = substr( $content, strpos( $content, '<' ) + 1 );
											$from_email = str_replace( '>', '', $from_email );
											$from_email = trim( $from_email );
										} else {
											$from_email = trim( $content );
										}
										break;
									case 'content-type':
										if ( strpos( $content, ';' ) !== false ) {
											list( $type, $charset ) = explode( ';', $content );
											$content_type = trim( $type );
											if ( false !== stripos( $charset, 'charset=' ) ) {
												$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
											} elseif ( false !== stripos( $charset, 'boundary=' ) ) {
												$boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset ) );
												$charset = '';
											}
										} else {
											$content_type = trim( $content );
										}
										break;
									default:
										/* Add it to our grand headers array */
										$current_message_headers[trim( $name )] = trim( $content );
										break;
								}
							}
						}
					}
					/* From name, from email and content-type*/
					if ( ! isset( $from_name ) ) {
						$from_name = 'WordPress';
					}
					if ( ! isset( $from_email ) ) {
						/* Get the site domain and get rid of www. */
						$sitename = strtolower( $_SERVER['SERVER_NAME'] );
						if ( substr( $sitename, 0, 4 ) == 'www.' ) {
							$sitename = substr( $sitename, 4 );
						}
						$from_email = 'wordpress@' . $sitename;
					}
					if ( ! isset( $content_type ) )
						$content_type = 'text/plain';
					$mail->ContentType = $content_type;
				} 

				/* send messages based on mail method */
				if ( 'mail' == $mlq_options['mail_method'] ) {
					$mail->CharSet = 'utf-8';
					$mail->AddAddress( $current_message_email );
					$mail->Subject = $current_message_subject;
					$mail->MsgHTML( $current_message_body );
					$mail->SetFrom( $from_email, $from_name );
					$mail->AddAttachment( $current_message_attachment );
					if ( $mail->Send() )
						$sended[] = $users_mail_send;
					else
						$errors[] = $users_mail_send;
					$mail->ClearAddresses();
					$mail->ClearAllRecipients();
					$mail->clearAttachments();
				} elseif ( 'smtp' == $mlq_options['mail_method'] ) {
					$mail->IsSMTP();
					$mail->SMTPAuth = true;
					if ( $mlq_options['smtp_settings']['ssl'] ) {
						$mail->SMTPSecure = 'ssl';
					}
					$mail->Host = $mlq_options['smtp_settings']['host'];
					$mail->Port = $mlq_options['smtp_settings']['port']; 
					$mail->Username = $mlq_options['smtp_settings']['accaunt'];
					$mail->Password = html_entity_decode( $mlq_options['smtp_settings']['password'] );
					$mail->AddAddress( $current_message_email );
					$mail->Subject = $current_message_subject;
					$mail->MsgHTML( $current_message_body );
					$mail->SetFrom( $from_email, $from_name );
					$mail->AddAttachment( $current_message_attachment );
					if ( $mail->Send() )
						$sended[] = $users_mail_send;
					else
						$errors[] = $users_mail_send;
					$mail->ClearAddresses();
					$mail->ClearAllRecipients();
					$mail->clearAttachments();
				} elseif ( 'wp_mail' == $mlq_options['mail_method'] ) {
					if ( wp_mail( $current_message_email, $current_message_subject, $current_message_body, $current_message_headers, $current_message_attachment ) ) {
						$sended[] = $users_mail_send;
					}
					else {
						$errors[] = $users_mail_send;
					}
				}
				/* if the mail is from CF we need to delete temporary file */
				if ( "" != $current_message_attachment && in_array( $users_mail_send['plugin_id'], $mlq_cntctfrms_ids ) ) {
					@unlink( $path_of_uploaded_file_changed );
				}
			}
			/* update mlq_mail_send and mlq_mail_users tables */
			if ( ! empty( $sended ) ) {
				foreach( $sended as $send ) {
					$mailing_try = $send['try'] + 1;
					/* update address list */
					$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_users` SET `status`=1, `try`=" . $mailing_try . " WHERE `mail_users_id`=" . $send['mail_users_id'] . ";" );
					/* update current mailout if done */
					$current_mailout = $wpdb->get_var( "SELECT `id_mail` FROM `" . $wpdb->base_prefix . "mlq_mail_users` WHERE `status`='0' AND `id_mail`=" . $send['id_mail'] . ";" );
					if ( empty( $current_mailout ) ) {
						$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_send` SET `mail_status`=1 WHERE `mail_send_id`=" . $send['id_mail'] . ";" );
					}
					/* if the mail from Sender plugin we need to update Sender table */
					if ( $send['plugin_id'] == $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='sender/sender.php';" ) ) {
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
						if ( is_plugin_active( 'sender/sender.php' ) ) {
							do_action( 'mlq_change_status_on_sender_mail', $send, $mailing_try, true );
						} else {
							$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "sndr_users` SET `status`=1, `try`=" . $mailing_try . " WHERE `id_mail`= " . $send['mail_id_in_sender'] . " AND `id_user`=" . $send['user_id_in_sender'] . ";" );
							/* set sent status on current mailout if no unsent users left  */
							$sender_mails = $wpdb->get_var( "SELECT `mail_users_id` FROM `" . $wpdb->base_prefix . "sndr_users` WHERE `status`='0' AND `id_mail`=" . $send['mail_id_in_sender'] . ";");
							if ( empty( $sender_mails ) ) {
								/* set done status for curremt mailout */
								$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "sndr_mail_send` SET `mail_status`=1 WHERE `mail_send_id`=" . $send['mail_id_in_sender'] . ";" );
							}
						}
					}
					/* if the mail from Sender Pro plugin we need to update Sender Pro table */
					if ( $send['plugin_id'] == $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='sender-pro/sender-pro.php';" ) ) {
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
						if ( is_plugin_active( 'sender-pro/sender-pro.php' ) ) {
							do_action( 'mlq_change_status_on_sender_pro_mail', $send, $mailing_try, true );
						} else {
							mlq_update_sender_pro_mail_data( $send, $mailing_try, true );
						}
					}
					/* if the mail is from CF and has attachment - we need to delete main file if Contact form options say so*/
					if ( "" != $send['attachment_path'] ) {
						/* Delete main file if Contact form options say so */
						if ( $send['plugin_id'] == $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='contact-form-plugin/contact_form.php';" ) ) { /* if Contact form */
							$cntctfrm_options = get_option( 'cntctfrm_options' );
							if ( '1' == $cntctfrm_options['cntctfrm_delete_attached_file'] ) {
								@unlink( $send['attachment_path'] );	
							}
						} elseif ( $send['plugin_id'] == $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='contact-form-pro/contact_form_pro.php';" ) ) { /* if Contact form Pro */
							$cntctfrmpr_options = get_option( 'cntctfrmpr_options' );
							if ( '1' == $cntctfrmpr_options['delete_attached_file'] ) {
								@unlink( $send['attachment_path'] );	
							}
						}
					}
				}
				/* clear cron if no unsent non-trashed mailout exists */
				$next_mails = $wpdb->get_var( "SELECT `mail_send_id` FROM `" . $wpdb->base_prefix . "mlq_mail_send` WHERE `mail_status`='0' AND `trash_status`=0;" );
				if ( empty( $next_mails ) ) {
					wp_clear_scheduled_hook( 'mlq_mail_hook' );
				}
			}
			/* increase the number of mailout tries in case of errors */
			if ( ! empty( $errors ) ) {
				foreach( $errors as $error ) {
					$mailing_try = $error['try'] + 1;
					$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_users` SET `try`=" . $mailing_try . " WHERE `mail_users_id`=" . $error['mail_users_id'] . ";" );
					/* if the mail from Sender plugin we need to update Sender table*/
					if ( $error['plugin_id'] == $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='sender/sender.php';" ) ) {
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
						if ( is_plugin_active( 'sender/sender.php' ) ) {
							do_action( 'mlq_change_status_on_sender_mail', $error, $mailing_try, false );
						} else {
							$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "sndr_users` SET `try`=" . $mailing_try . " WHERE `id_mail`= " . $error['mail_id_in_sender'] . " AND `id_user`=" . $error['user_id_in_sender'] . ";" );
						}
					}
					if ( $error['plugin_id'] == $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_link`='sender-pro/sender-pro.php';" ) ) {
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
						if ( is_plugin_active( 'sender-pro/sender-pro.php' ) ) {
							do_action( 'mlq_change_status_on_sender_pro_mail', $error, $mailing_try, false );
						} else {
							mlq_update_sender_pro_mail_data( $error, $mailing_try, false );
						}
					}
				}
			}
		} else {
			wp_clear_scheduled_hook( 'mlq_mail_hook' );
		}
	}
}

/**
 * Function to change mailout status to "sent" in Sender Pro if the letter was sent via our plugin.
 * @param arr $mailout 		current mailout info ( user_id and mailout_id)
 * @param int $try_counter	mailing try for current mailout
 * @param bool $sent_or_not	whether message was successfuly sent or not
 * @return void 
 */
if ( ! function_exists( 'mlq_update_sender_pro_mail_data' ) ) {
	function mlq_update_sender_pro_mail_data( $mailout, $try_counter, $sent_or_not ) {
		global $wpdb, $sndrpr_options;
		$sndrpr_options = get_option( 'sndrpr_options' );

		if ( $sent_or_not ) { /* if letter was sent successfully */
			$wpdb->update(
				$wpdb->base_prefix . 'sndr_users',
				array(
					'try'    => $try_counter,
					'status' => 1,
				),
				array( 
					'id_mailout' => $mailout['mail_id_in_sender'], 
					'id_user' 	 => $mailout['user_id_in_sender'], 
				)
			);
			/* set sent status on current mailout if no unsent users left  */
			$mails = $wpdb->query( 
				"SELECT `mail_users_id` FROM `" . $wpdb->base_prefix . "sndr_users` WHERE `status`='0' AND `id_mail`=( SELECT `mail_id` FROM `" . $wpdb->base_prefix . "sndr_mailout` WHERE `mailout_id`=" . $mailout['mail_id_in_sender'] . " );" );
			if ( empty( $mails ) ) {
				/* set 'done' status for current mailout */
				$wpdb->update(
					$wpdb->base_prefix . 'sndr_mailout',
					array( 
						'mailout_status' => 1,
						'mailout_end'    => date( 'Y-m-d H:i:s', time() + get_option( 'gmt_offset' ) * 3600 )
					),
					array( 'mailout_id'  => $mailout['mail_id_in_sender'] )
				);
			}
		} else { /* if letter was not sent */
			/* if the number of attempts has reached its limit */
			if ( $sndrpr_options['max_try_count'] == $try_counter ) {
				$wpdb->update(
					$wpdb->base_prefix . 'sndr_users',
					array(
						'try'    => $try_counter,
						'status' => 2
					),
					array( 
						'id_mailout' => $mailout['mail_id_in_sender'], 
						'id_user' 	 => $mailout['user_id_in_sender'], 
					)
				);
			} else {
				$wpdb->update(
					$wpdb->base_prefix . 'sndr_users',
					array( 'try'     => $try_counter ),
					array( 
						'id_mailout' => $mailout['mail_id_in_sender'], 
						'id_user' 	 => $mailout['user_id_in_sender'], 
					)
				);
			}
		}
	}
}

/**
 * Function to clear DB from old messages once a day
 * @return void
 */
if ( ! function_exists( 'mlq_cron_mail_clear' ) ) {
	function mlq_cron_mail_clear() {
		global $wpdb, $mlq_options;
		/* get options from DB */
		$mlq_options = ( 1 == is_multisite() ) ? get_site_option( 'mlq_options' ) : get_option( 'mlq_options' );
		if ( $mlq_options['delete_old_mail'] ) {
			$delete_from = time() - ( 24 * 60 * 60) * $mlq_options['delete_old_mail_days'];
			$mails_to_delete = $wpdb->get_col( "SELECT `mail_send_id` FROM `" . $wpdb->base_prefix . "mlq_mail_send` WHERE `mail_status`=1 AND `date_create`<" . $delete_from .";");
			if ( ! empty( $mails_to_delete ) ) {
				foreach ( $mails_to_delete as $mail_to_delete ) {
					$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "mlq_mail_users` WHERE `id_mail`=" . $mail_to_delete );
					$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "mlq_mail_send` WHERE `mail_send_id`=" . $mail_to_delete );
				}
			}
		}
	}
}

/**
 * create class MLQ_Plugin_List to display settings of plugins,
 * that send emails via our plugin
 */	
if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	if ( ! class_exists( 'MLQ_Plugin_List' ) ) {
		class MLQ_Plugin_List extends WP_List_Table {

			/**
			* Constructor of class 
			*/
			function __construct() {
				global $status, $page;
				parent::__construct( array(
					'singular'  => __( 'mail plugin', 'email-queue' ),
					'plural'    => __( 'mail plugins', 'email-queue' ),
					'ajax'      => true,
					)
				);
			}

			/**
			* Function to prepare data before display 
			* @return void
			*/
			function prepare_items() {
				global $wpdb, $plugin_status;
				$plugin_status = isset( $_REQUEST['plugin_status'] ) ? $_REQUEST['plugin_status'] : 'all';
				if ( ! in_array( $plugin_status, array( 'all', 'not_in_queue', 'install', 'not_isntall', 'not_read_messages', 'active', 'inactive' ) ) )
				$plugin_status = 'all';
				
				$columns               	= $this->get_columns();
				$hidden                	= array();
				$sortable              	= $this->get_sortable_columns();
				$this->found_data      	= $this->plugin_list();
				$this->items           	= $this->found_data;
				$per_page              	= $this->get_items_per_page( 'plugins_per_page', 20 );
				$current_page          	= $this->get_pagenum();
				$total_items           	= $this->items_count();
				$this->set_pagination_args( array(
						'total_items' 	=> $total_items,
						'per_page'    	=> $per_page,
					)
				);
			}

			/**
			* Function to show message if no plugins found
			* @return void
			*/
			function no_items() { ?>
				<p style="color:red;"><?php _e( 'No plugins found', 'email-queue' ); ?></p>
			<?php }

			/**
			 * Function to add column of checboxes 
			 * @param int    $item->comment_ID The custom column's unique ID number.
			 * @return string                  with html-structure of <input type=['checkbox']>
			 */
			function column_cb( $item ) {
				return sprintf( '<input id="cb_%1s" type="checkbox" name="plugin_id[]" value="%2s" />', $item['id'], $item['id'] );
			}

			/**
			 * Get a list of columns.
			 * @return array list of columns and titles
			 */
			function get_columns() {
				$columns = array(
					'cb'				=> '<input type="checkbox" />',
					'title'				=> __( 'Plugin name', 'email-queue' ),
					'install_status'	=> __( 'Installed', 'email-queue' ),
					'active_status'		=> __( 'Activated', 'email-queue' ),
					'priority_general'	=> __( 'General priority for all mails of plugin', 'email-queue' ),
				);
				return $columns;
			}

			/**
			 * Get a list of sortable columns.
			 * @return array list of sortable columns
			 */
			function get_sortable_columns() {
				$sortable_columns = array(
					'title'				=> array( 'title', false ),
					'install_status'	=> array( 'install_status', false ),
					'active_status'		=> array( 'active_status', false ),
					'priority_general'	=> array( 'priority_general', false ),
				);
				return $sortable_columns;
			}

			/**
			* Function to add filters below and above plugins list
			* @param array $which An array of plugin states. Accepts 'Not in queue', Installed', 'Not installed', 'Active', 'Inactive'.
			* @return void 
			*/
			function extra_tablenav( $which ) {
				global $not_in_queue_count, $install_count, $not_isntall_count, $active_count, $inactive_count; ?>
				<ul class="subsubsub">
					<li><a class="mlq-filter<?php if ( ! isset( $_REQUEST['plugin_status'] ) || 'all' == $_REQUEST['plugin_status'] ) { echo " current"; } ?>" href="?page=mlq_settings"><?php _e( 'All', 'email-queue' ); ?></a> | </li>
					<li><a class="mlq-filter<?php if( isset( $_REQUEST['plugin_status'] ) && "not_in_queue" == $_REQUEST['plugin_status'] ) { echo " current"; } ?>" href="?page=mlq_settings&plugin_status=not_in_queue"><?php _e( 'Not in queue', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $not_in_queue_count; ?> )</span></a> | </li>
					<li><a class="mlq-filter<?php if( isset( $_REQUEST['plugin_status'] ) && "install" == $_REQUEST['plugin_status'] ) { echo " current"; } ?>" href="?page=mlq_settings&plugin_status=install"><?php _e( 'Installed ', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $install_count; ?> )</span></a> | </li>
					<li><a class="mlq-filter<?php if( isset( $_REQUEST['plugin_status'] ) && "not_isntall" == $_REQUEST['plugin_status'] ) { echo " current"; } ?>" href="?page=mlq_settings&plugin_status=not_isntall"><?php _e( 'Not installed', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $not_isntall_count; ?> )</span></a> | </li>
					<li><a class="mlq-filter<?php if( isset( $_REQUEST['plugin_status'] ) && "active" == $_REQUEST['plugin_status'] ) { echo " current"; } ?>" href="?page=mlq_settings&plugin_status=active"><?php _e( 'Active', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $active_count; ?> )</span></a> | </li>
					<li><a class="mlq-filter<?php if( isset( $_REQUEST['plugin_status'] ) && "inactive" == $_REQUEST['plugin_status'] ) { echo " current"; } ?>" href="?page=mlq_settings&plugin_status=inactive"><?php _e( 'Installed but inactive', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $inactive_count; ?> )</span></a> </li>				
				</ul> <!-- .subsubsub -->
			<?php  }

			/**
			 * Function to add action links to drop down menu before and after plugins list depending on plugin status page
			 * @return array of actions
			 */
			function get_bulk_actions() {
				global $plugin_status;
				$actions = array();
				if ( in_array( $plugin_status, array( 'all', 'install', 'not_isntall', 'active', 'inactive' ) ) ) {
					$actions['remove_plugins_from_queue'] = __( 'Remove from queue', 'email-queue' );
				} else {
					$actions['restore_plugins_to_queue'] = __( 'Restore to queue', 'email-queue' );
				}
				return $actions;
			}

			/**
			 * Fires when the default column output is displayed for a single row.
			 * @param string $column_name      The custom column's name.
			 * @param int    $item->comment_ID The custom column's unique ID number.
			 * @return void
			 */
			function column_default( $item, $column_name ) {
				switch( $column_name ) {
					case 'title':
					case 'install_status':
					case 'active_status':
					case 'priority_general':
						return $item[ $column_name ];
					default:
						return print_r( $item, true ) ;
				}
			}

			/**
			 * Function to add action links to plugin_name column depenting on status page
			 * @param int      $item->comment_ID The custom column's unique ID number.
			 * @return string                     with action links
			 */
			function column_title( $item ) {
				global $plugin_status;
				$actions = array();
				if ( in_array( $plugin_status, array( 'all', 'install', 'not_isntall', 'active', 'inactive' ) ) ) {
					$actions['remove_plugin_from_queue'] = '<a href="' . wp_nonce_url( '?page=mlq_settings&action=remove_plugin_from_queue&plugin_id=' . $item['id'] . '&plugin_status=' . $plugin_status, 'plugin_out_' . $item['id'] ) . '">' . __( 'Remove from queue', 'email-queue' ) . '</a>';
				} else {
					$actions['restore_plugin_to_queue'] = '<a href="' . wp_nonce_url( '?page=mlq_settings&action=restore_plugin_to_queue&plugin_id=' . $item['id'] . '&plugin_status=' . $plugin_status, 'plugin_in_' . $item['id'] ) . '">' . __( 'Restore to queue', 'email-queue' ) . '</a>';
				}
				return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
			}
			
			/**
			 * Function to check if plugins are installed
			 */
			function is_plugin_install( $plugin_path ) {
				global $plugins_list;
				if ( array_key_exists( $plugin_path, $plugins_list ) ) {
					return true;
				} else {
					return false;
				}
			}
			/**
			 * Function to update plugin info in DB before display
			 */
			function check_plugin_install_and_active() {
				global $wpdb, $plugins_list, $mlq_active_plugin_list;
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$plugins_list = get_plugins();
				/* get mail-able plugin list from our table */
				$plugins_in_db = $wpdb->get_results( "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_plugins`", ARRAY_A );
				/* get an array of all active plugins if multisite */
				if ( is_multisite() ) {
					/* create an array for a list of all active plugins */
					$mlq_active_plugin_list = array();
					/* get array with ids of subsite-blogs */
					$mlq_blog_ids = $wpdb->get_col( "SELECT `blog_id` FROM `" . $wpdb->base_prefix . "blogs` WHERE `blog_id`<>`site_id` " );
					if ( ! empty( $mlq_blog_ids ) ) {
						/* start putting together sql-query that brings us list of all active plugins on all sites */
						$i = 0;
						$last_blog = count( $mlq_blog_ids );
						$sql_query = '';
						foreach( $mlq_blog_ids as $mlq_blog_id ) {
							$sql_query .= 
								"SELECT `option_value` FROM `" . $wpdb->base_prefix . $mlq_blog_id . "_" . "options` WHERE `option_name` LIKE '%active_plugins%'";
							$i ++;
							if ( $last_blog !== $i ) { /* if this is not last blog_id in our array */
								$sql_query .= " UNION ";
							} else {
								$sql_query .= ";";
							}
						}
						/* get list of all active plugin as an array of serialized strings */
						$active_blog_plugins = $wpdb->get_col( $sql_query );
						foreach ($active_blog_plugins as $active_blog_plugin ) {
							$active_blog_plugin = unserialize( $active_blog_plugin );
							if ( ! empty( $active_blog_plugin ) ) {
								/* unserialize and loop through non-empty plugin list on every subsite */
								foreach ( $active_blog_plugin as $active_plugin ) {
									/* add active plugin to our list if it's not already there */
									if ( ! in_array( $active_plugin, $mlq_active_plugin_list ) ) {
										$mlq_active_plugin_list[] = $active_plugin;
									}
								}
							}
						}
					} 
					/* get active plugins on main site */
					$active_blog_plugins = get_option( 'active_plugins' );
					/* add them to our list */
					$mlq_active_plugin_list = array_merge( $mlq_active_plugin_list, $active_blog_plugins );
					/* get active plugins on network */
					$network_active_plugins = get_site_option( 'active_sitewide_plugins');
					foreach ( $network_active_plugins as $active_plugin_link => $plugin_value ) {
						/* add network active plugin to our list if it's not already there */
						if ( ! in_array( $active_plugin_link, $mlq_active_plugin_list ) ) {
							$mlq_active_plugin_list[] = $active_plugin_link;
						}
					}
				} else {
					/* get an array of active plugins if not multisite */
					$mlq_active_plugin_list = get_option( 'active_plugins' );
				}

				/* check if every plugin from our list is installed */
				foreach ( $plugins_in_db as $plugin ) {
					/* update DB info on install-status */
					if ( '1' == $plugin['pro_status'] ) {
						/* if it's free plugin that has Pro version */
						$plugin_pro = $wpdb->get_row( "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `pro_status`=2 AND `parallel_plugin_link`='" . $plugin['plugin_link'] . "';", ARRAY_A );
					} elseif ( '2' == $plugin['pro_status'] ) {
						/* if it's Pro plugin - look for free version */
						$plugin_free = $wpdb->get_row( "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `pro_status`=1 AND `parallel_plugin_link`='" . $plugin['plugin_link'] . "';", ARRAY_A );
					}
					if ( '0' == $plugin['pro_status'] || '1' == $plugin['pro_status'] && empty( $plugin_pro ) || '2' == $plugin['pro_status'] && empty( $plugin_free ) ) {
						/*if 1) no Pro; 2) has Pro, but it's not in DB; 3) is Pro, but free ver isn't in DB */
						if ( $this->is_plugin_install( $plugin['plugin_link'] ) ) {
							/* if installed */
							if ( '1' != $plugin['install_status'] ) {
								$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
									array( 'install_status'	=> 1, ),
									array( 'mail_plugin_id'	=> $plugin['mail_plugin_id'], )
								);
							}
							/* if installed, check if active and update DB info on active-status*/
							if ( in_array( $plugin['plugin_link'], $mlq_active_plugin_list ) ) {
								if ( '1' != $plugin['active_status'] ) {
									$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
										array( 'active_status'	=> 1, ),
										array( 'mail_plugin_id'	=> $plugin['mail_plugin_id'], )
									);
								}
							} else {
								/* if not active */
								if ( '0' != $plugin['active_status'] ) {
									$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
										array( 'active_status'	=> 0, ),
										array( 'mail_plugin_id'	=> $plugin['mail_plugin_id'], )
									);
								}
							}
						} else {
							/* if not installed */
							if ( '0' != $plugin['install_status'] ) {
								$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
									array(
										'install_status'	=> 0,
										'active_status'		=> 0,
									),
									array( 'mail_plugin_id'	=> $plugin['mail_plugin_id'], )
								);
							}
						}
					} elseif ( '1' == $plugin['pro_status'] && ! empty( $plugin_pro ) ) {
						/* has Pro and it's in DB */
						if ( $this->is_plugin_install( $plugin['plugin_link'] ) || $this->is_plugin_install( $plugin_pro['plugin_link'] ) ) {
							/* if installed */
							if ( '1' != $plugin['install_status'] ) {
								$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
									array( 'install_status'	=> 1, ),
									array( 'mail_plugin_id'	=> $plugin['mail_plugin_id'], )
								);
							}
							if ( '1' != $plugin_pro['install_status'] ) {
								$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
									array( 'install_status'	=> 1, ),
									array( 'mail_plugin_id'	=> $plugin_pro['mail_plugin_id'], )
								);
							}
							/* if installed, check if active and update DB info on active-status*/
							if ( in_array( $plugin['plugin_link'], $mlq_active_plugin_list ) || in_array( $plugin_pro['plugin_link'], $mlq_active_plugin_list ) ) {
								/* if active */
								if ( '1' != $plugin['active_status'] ) {
									$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
										array( 'active_status'	=> 1, ),
										array( 'mail_plugin_id'	=> $plugin['mail_plugin_id'], )
									);
								}
								if ( '1' != $plugin_pro['active_status'] ) {
									$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
										array( 'active_status'	=> 1, ),
										array( 'mail_plugin_id'	=> $plugin_pro['mail_plugin_id'], )
									);
								}
							} else {
								/* if not active */
								if ( '0' != $plugin['active_status'] ) {
									$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
										array( 'active_status'	=> 0, ),
										array( 'mail_plugin_id'	=> $plugin['mail_plugin_id'], )
									);
								}
								if ( '0' != $plugin_pro['active_status'] ) {
									$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
										array( 'active_status'	=> 0, ),
										array( 'mail_plugin_id'	=> $plugin_pro['mail_plugin_id'], )
									);
								}
							}
						} else {
							/* if not installed */
							if ( '0' != $plugin['install_status'] ) {
								$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
									array(
										'install_status'	=> 0,
										'active_status'		=> 0,
									),
									array( 'mail_plugin_id'	=> $plugin['mail_plugin_id'], )
								);
							}
							if ( '0' != $plugin_pro['install_status'] ) {
								/* if not installed */
								$wpdb->update( $wpdb->base_prefix . 'mlq_mail_plugins', 
									array(
										'install_status'	=> 0,
										'active_status'		=> 0,
									),
									array( 'mail_plugin_id'	=> $plugin_pro['mail_plugin_id'], )
								);
							}
						}
					}
				}
			}

			/**
			 * Function to get plugin list
			 * @return array list of plugins
			 */
			function plugin_list() {
				global $wpdb;
				$i                  = 0;
				$plugins_list       = array();  
				$per_page = intval( get_user_option( 'plugins_per_page' ) );
				if ( empty( $per_page ) || $per_page < 1 ) {
					$per_page = 30;
				}
				$start_row = ( isset( $_REQUEST['paged'] ) && '1' != $_REQUEST['paged'] ) ? $per_page * ( absint( $_REQUEST['paged'] - 1 ) ) : 0;
				if ( isset( $_REQUEST['orderby'] ) ) {
					switch ( $_REQUEST['orderby'] ) {
						case 'title':
							$order_by = 'plugin_name';
							break;
						case 'install_status':
							$order_by = 'install_status';
							break;
						case 'active_status':
							$order_by = 'active_status';
							break;
						case 'priority_general':
							$order_by = 'priority_general';
							break;
						default:
							$order_by = 'mail_plugin_id';
							break;
					}
				} else {
					$order_by = 'mail_plugin_id';
				}
				$order     = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC';
				$sql_query = "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` ";
				if ( isset( $_REQUEST['plugin_status'] ) ) {
					switch ( $_REQUEST['plugin_status'] ) {
						case 'install':
							$sql_query .= "WHERE `install_status`=1 AND `in_queue_status`=1";
							break;
						case 'not_isntall':
							$sql_query .= "WHERE `install_status`=0 AND `in_queue_status`=1";
							break;
						case 'active':
							$sql_query .= "WHERE `install_status`=1 AND `active_status`=1 AND `in_queue_status`=1";
							break;
						case 'inactive':
							$sql_query .= "WHERE `install_status`=1 AND `active_status`=0 AND `in_queue_status`=1";
							break;
						case 'not_in_queue':
							$sql_query .= "WHERE `in_queue_status`=0";
							break;
						default:
							$sql_query .= "WHERE `in_queue_status`=1";
							break;
					}
				} else {
					$sql_query .= " WHERE `in_queue_status`=1";
				}
				
				$sql_query   .= " ORDER BY " . $order_by . " " . $order . " LIMIT " . $per_page . " OFFSET " . $start_row . ";";
				$plugins_data = $wpdb->get_results( $sql_query, ARRAY_A );
				foreach ( $plugins_data as $plugin ) {
					if ( '1' == $plugin['pro_status'] ) {
						/* if it's free plugin that has Pro version */
						$plugin_pro = $wpdb->get_row( "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `pro_status`=2 AND `parallel_plugin_link`='" . $plugin['plugin_link'] . "';", ARRAY_A );
					} elseif ( '2' == $plugin['pro_status'] ) {
						/* if it's Pro plugin - look for free version */
						$plugin_free = $wpdb->get_row( "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `pro_status`=1 AND `parallel_plugin_link`='" . $plugin['plugin_link'] . "';", ARRAY_A );
					}
					if ( '2' == $plugin['pro_status'] && ! empty( $plugin_free ) ) {
						continue;
					}
					/* start making plugin list */
					$plugins_list[$i]					= array();
					$plugins_list[$i]['id']				= $plugin['mail_plugin_id'];
					/* display title based on whether there's two version of plugin (free and Pro) */
					$plugins_list[$i]['title'] 			= '1' == $plugin['pro_status'] && ! empty( $plugin_pro ) ? $plugin['plugin_name'] . '&nbsp;<span class="mlq-linking-word">' . __( 'and', 'email-queue' ) . '</span>&nbsp;' . $plugin_pro['plugin_name'] : $plugin['plugin_name'];
					/* get install and active status */
					$plugins_list[$i]['install_status']	= ( '1' == $plugin['install_status'] ) ? __( 'Yes', 'email-queue' ) : __( 'No', 'email-queue' );
					$plugins_list[$i]['active_status']	= ( '1' == $plugin['active_status'] ) ? __( 'Yes', 'email-queue' ) : __( 'No', 'email-queue' );
					/* Display select-list with priority values if plugin is installed, activated and has "in_queue" status */
					if ( '1' == $plugin['active_status'] && '1' == $plugin['in_queue_status'] )	{
						$plugins_list[$i]['priority_general'] ='<select name="priority[' . $plugin['plugin_slug'] . ']"><option disabled >' . __( "Choose a priority", 'email-queue' ) . '</option><option value="5" '; 
						if ( 5 == $plugin['priority_general'] ) $plugins_list[$i]['priority_general'] .= 'selected="selected"';
						$plugins_list[$i]['priority_general'] .= '>' . __( "High priority", 'email-queue' ) . '</option><option value="3" ';
						if ( 3 == $plugin['priority_general'] ) $plugins_list[$i]['priority_general'] .= 'selected="selected"';
						$plugins_list[$i]['priority_general'] .= '>' . __( "Normal priority", 'email-queue' ) . '</option><option value="1" ';
						if ( 1 == $plugin['priority_general'] ) $plugins_list[$i]['priority_general'] .= 'selected="selected"';
						$plugins_list[$i]['priority_general'] .= '>' . __( "Low priority", 'email-queue' ) . '</option></select>';
					} else if ( '0' == $plugin['in_queue_status'] ) {
						/* if not in queue */
						$plugins_list[$i]['priority_general'] = __( 'You should', 'email-queue' ) . '&nbsp;' . sprintf( '<a href="?page=mlq_settings&action=restore_plugin_to_queue&plugin_id[]=%s' . '&plugin_status=not_in_queue">' . __( 'restore', 'email-queue' ) . '</a>', $plugin['mail_plugin_id'] ) . '&nbsp;' . __( 'plugin back to queue to set a priority', 'email-queue' );
					} else {
						/* if not activated or installed - display links to do so */
						if ( '0' == $plugin['install_status'] ) {
							if ( '0' == $plugin['pro_status'] || '1' == $plugin['pro_status'] && empty( $plugin_pro ) || '2' == $plugin['pro_status'] && empty( $plugin_free ) ) {
								/* if 1) no Pro; 2) has Pro, but it's not in DB; 3) is Pro, but free version isn't in DB */
								$plugins_list[$i]['priority_general'] = __( 'You need to', 'email-queue' ) . '&nbsp;' . '<a href="' . $plugin['install_link'] . '" title="' . __( "You need to install plugin to set a priority", 'email-queue' ) . '" target="_blank">' . __( "install", 'email-queue' ) . '</a>' . '&nbsp;' . __( 'plugin to set a priority', 'email-queue' );
							} else {
								/* has Pro and it's in DB */
								$plugins_list[$i]['priority_general'] = __( 'You need to', 'email-queue' ) . '&nbsp;' . '<a href="' . $plugin['install_link'] . '" title="' . __( "Install free version", 'email-queue' ) . '" target="_blank">' . __( "install free", 'email-queue' ) . '</a>' . '&nbsp;' . __( 'or', 'email-queue' ) . '&nbsp;' . '<a href="' . $plugin_pro['install_link'] . '" title="' . __( "Buy Pro version", 'email-queue' ) . '" target="_blank">' . __( "buy Pro", 'email-queue' ) . '</a>' . '&nbsp;' . __( 'version of plugin to set a priority', 'email-queue' ) ;
							}
						} else {
							/* if need activation */
							$plugins_list[$i]['priority_general'] = __( 'You need to', 'email-queue' ) . '&nbsp;' . '<a href="plugins.php" title="' . __( "You need to activate plugin to set a priority", 'email-queue' ) . '" target="_blank">' . __( "activate", 'email-queue' ) . '</a>' . '&nbsp;' . __( 'plugin to set a priority', 'email-queue' ) ;
						}
					}
					$i ++;
				}
				return $plugins_list;
			}

			/**
			 * Function to get number of all plugins
			 * @return sting plugins number
			 */
			public function items_count() {
				global $wpdb, $all_count, $not_in_queue_count, $install_count, $not_isntall_count, $active_count, $inactive_count;
				$all_count = $not_in_queue_count = $install_count = $not_isntall_count = $active_count = $inactive_count = 0;
				$plugins_data = $wpdb->get_results( "SELECT * FROM " . $wpdb->base_prefix . "mlq_mail_plugins;", ARRAY_A );
				foreach ( $plugins_data as $plugin ) {
					if ( '1' == $plugin['pro_status'] ) {
						/* if it's free plugin that has Pro version */
						$plugin_pro = $wpdb->get_row( "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `pro_status`=2 AND `parallel_plugin_link`='" . $plugin['plugin_link'] . "';", ARRAY_A );
					} elseif ( '2' == $plugin['pro_status'] ) {
						/* if it's Pro plugin - look for free version */
						$plugin_free = $wpdb->get_row( "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `pro_status`=1 AND `parallel_plugin_link`='" . $plugin['plugin_link'] . "';", ARRAY_A );
					}
					if ( '0' == $plugin['pro_status'] || '1' == $plugin['pro_status'] && empty( $plugin_pro ) || '2' == $plugin['pro_status'] && empty( $plugin_free ) || '1' == $plugin['pro_status'] && ! empty( $plugin_pro ) ) {
						if ( '1' == $plugin['in_queue_status'] ) {
							$install_count = ( '1' == $plugin['install_status'] ) ? ++$install_count : $install_count;
							$not_isntall_count = ( '1' != $plugin['install_status'] ) ? ++$not_isntall_count : $not_isntall_count;
							$active_count = ( '1' == $plugin['active_status'] ) ? ++$active_count : $active_count;
							$inactive_count = ( '1' == $plugin['install_status'] && '1' != $plugin['active_status'] ) ? ++$inactive_count : $inactive_count;
							$all_count++;
						} else {
							$not_in_queue_count++;
						}
					}
				}

				if ( isset( $_REQUEST['plugin_status'] ) ) {
					switch ( $_REQUEST['plugin_status'] ) {
						case 'not_in_queue':
							$items_count = $not_in_queue_count;
							break;
						case 'install':
							$items_count = $install_count;
							break;
						case 'not_isntall':
							$items_count = $not_isntall_count;
							break;
						case 'active':
							$items_count = $active_count;
							break;
						case 'inactive':
							$items_count = $inactive_count;
							break;
						default:
							$items_count = $all_count;
							break;
					}
				} else {
					$items_count = $all_count;
				}
				return $items_count;
			}
		}
	}
}
/* the end of the MLQ_Plugin_List class definition	*/

/**
 * Add screen options and initialize instance of class MLQ_Plugin_List
 * @return void 
 */
if ( ! function_exists( 'mlq_plugin_screen_options' ) ) {
	function mlq_plugin_screen_options() {
		global $mlq_plugin_list;
		$option = 'per_page';
		$args = array(
			'label'   => __( 'Plugins per page', 'email-queue' ),
			'default' => 20,
			'option'  => 'plugins_per_page'
		);
		add_screen_option( $option, $args );
		$mlq_plugin_list = new MLQ_Plugin_List;
	}
}

/**
 * Function to save and load settings from screen options
 * @return void 
 */
if ( ! function_exists( 'mlq_table_set_option' ) ) {
	function mlq_table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/**
 * Function to check for compatibility with installed versions of plugins
 * @return void 
 */
if ( ! function_exists( 'mlq_check_plugins_versions' ) ) {
	function mlq_check_plugins_versions( $action_message ) {
		global $mlq_active_plugin_list;
		/* get an array with info on default plugins */
		$default_plugins 		= mlq_get_default_plugins();
		/* get string var for our uncompatible plugins */
		$non_versioned_plugins 	= '';
		/* get the name of wp-content dir for using to check through files */
		if ( is_multisite() ) {
			$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? basename( WP_CONTENT_DIR ) : 'wp-content';
		}
		/* check for version of default plugins */
		foreach ( $default_plugins as $plugin_link => $plugin_data ) {
			/* use the list of active plugins from our class MLQ_plugin_list */
			if ( isset( $plugin_data['plugin_function'] ) && in_array( $plugin_link, $mlq_active_plugin_list ) ) {
				if ( is_network_admin() && ! function_exists( $plugin_data['plugin_function'] ) ) {
					require_once( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_link );
				}
				if ( ! function_exists( $plugin_data['plugin_function'] ) ) {
				/* add an error message */
				$non_versioned_plugins .= __( 'It seems that you use version of', 'email-queue' ) . '&nbsp;' . $plugin_data['plugin_name'] . '&nbsp;' .  
				__( 'that is uncompatible with Email Queue plugin. Please check for', 'email-queue' ) . '&nbsp;' . 
				'<a class="mlq-link-for-updates" href="/wp-admin/update-core.php" title="' . __( "Check for updates", 'email-queue' ) . '" target="_blank">' . __( "updates", 'email-queue' ) . '</a>.<br />';
				}
			}
		}
		if ( '' != $non_versioned_plugins && ! empty( $action_message['error'] ) ) {
			/* add new line if we have errors in actions */
			$action_message['error'] .= '<br />';
		}
		$action_message['error'] .= $non_versioned_plugins;
		return $action_message;
	}
}

/**
 * Function to handle actions from "settings" page on plugin list
 * @return array with messages about action results
 */
if ( ! function_exists( 'mlq_plugin_list_actions' ) ) {
	function mlq_plugin_list_actions() {
		global $wpdb, $mlq_active_plugin_list;
		$action_message = array(
			'error' 	=> false,
			'done'  	=> false
		);
		$error = $done = 0;
		if ( isset( $_REQUEST['page'] ) && ( 'mlq_settings' == $_REQUEST['page'] ) ) {
			$message_list = array(
				'empty_plugins_list'	=> __( 'You need to choose some plugins.', 'email-queue' ),
				'plugin_remove_error'	=> __( 'Error while removing plugin from the queue.', 'email-queue' ),
				'plugin_restore_error'	=> __( 'Error while restoring plugin to the queue.', 'email-queue' ),
				'try_later'				=> __( 'Please, try it later.', 'email-queue' ),
			);
			if ( isset( $_REQUEST['action'] ) || isset( $_REQUEST['action2'] ) ) {
				$action = '';
				if ( isset( $_REQUEST['action'] ) && '-1' != $_REQUEST['action'] ) {
					$action = $_REQUEST['action'];
				} elseif ( isset( $_POST['action2'] ) && '-1' != $_REQUEST['action2'] ) {
					$action = $_POST['action2'];
				}
				switch ( $action ) {
					case 'remove_plugin_from_queue':
						if ( check_admin_referer( 'plugin_out_' . $_GET['plugin_id'] ) ) {
							if ( empty( $_GET['plugin_id'] ) ) {
								$action_message['error'] = $message_list['empty_plugins_list'];
							} else {
								$plugin = $_GET['plugin_id'];
								/* check for Pro version and remove it if it's present */
								$parallel_plugin_id = $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `parallel_plugin_link`=( 
									SELECT `plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `mail_plugin_id`=" . $plugin . ");" );
								if ( ! empty( $parallel_plugin_id ) ) {
									$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `in_queue_status`=0 WHERE `mail_plugin_id`=" . $parallel_plugin_id );
								}
								/* remove single plugin */
								$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `in_queue_status`=0 WHERE `mail_plugin_id`=" . $plugin );
									if ( $wpdb->last_error ) { 
										$error ++;
									} else {
										$done ++;
									}
								/* set message */
								if ( 0 == $error ) {
									$action_message['done'] = sprintf( _nx( __( 'Plugin was removed from the queue.', 'email-queue'),	'%s&nbsp;' . __( 'Plugins were removed from the queue.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
								} else {
									$action_message['error'] = $message_list['plugin_remove_error'] . '<br />' . $message_list['try_later'];
								}
							}
						}
						break;
					case 'remove_plugins_from_queue':
						if ( check_admin_referer( plugin_basename( __FILE__ ), 'mlq_nonce_name' ) ) {
							if ( empty( $_POST['plugin_id'] ) ) {
								$action_message['error'] = $message_list['empty_plugins_list'];
							} else {
								foreach ( $_POST['plugin_id'] as $plugin ) {
									/* check for Pro version and remove it if it's present */
									$parallel_plugin_id = $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `parallel_plugin_link`=( 
										SELECT `plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `mail_plugin_id`=" . $plugin . ");" );
									if ( ! empty( $parallel_plugin_id ) ) {
										$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `in_queue_status`=0 WHERE `mail_plugin_id`=" . $parallel_plugin_id );
									}
									/* remove single plugin */
									$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `in_queue_status`=0 WHERE `mail_plugin_id`=" . $plugin );
										if ( $wpdb->last_error ) { 
											$error ++;
										} else {
											$done ++;
										}
									/* set message */
									if ( 0 == $error ) {
										$action_message['done'] = sprintf( _nx( __( 'Plugin was removed from the queue.', 'email-queue'),	'%s&nbsp;' . __( 'Plugins were removed from the queue.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
									} else {
										$action_message['error'] = $message_list['plugin_remove_error'] . '<br />' . $message_list['try_later'];
									}
								}
							}
						}
						break;
					case 'restore_plugin_to_queue':
						if ( check_admin_referer( 'plugin_in_' . $_GET['plugin_id'] ) ) {
							$plugin = $_GET['plugin_id'];
							if ( empty( $plugin ) ) {
								$action_message['error'] = $message_list['empty_plugins_list'];
							} else {
								/* check for Pro version and restore it if it's present */
								$parallel_plugin_id = $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `parallel_plugin_link`=( 
									SELECT `plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `mail_plugin_id`=" . $plugin . ");" );
								if ( ! empty( $parallel_plugin_id ) ) {
									$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `in_queue_status`=1 WHERE `mail_plugin_id`=" . $parallel_plugin_id );
								}
								/* restore single plugin */
								$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `in_queue_status`=1 WHERE `mail_plugin_id`=" . $plugin );
								if ( $wpdb->last_error ) { 
									$error ++;
								} else {
									$done ++;
								}
								/* set message */
								if ( 0 == $error ) {
									$action_message['done'] = sprintf( _nx( __( 'Plugin was restored to the queue.', 'email-queue'), '%s&nbsp;' . __( 'Plugins were restored to the queue.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
								} else {
									$action_message['error'] = $message_list['plugin_restore_error'] . '<br />' . $message_list['try_later'];
								}
							}
						}
						break;
					case 'restore_plugins_to_queue':
						if ( check_admin_referer( plugin_basename( __FILE__ ), 'mlq_nonce_name' ) ) {
							if ( empty( $_POST['plugin_id'] ) ) {
								$action_message['error'] = $message_list['empty_plugins_list'];
							} else {
								foreach( $_POST['plugin_id'] as $plugin ) {
									/* check for Pro version and restore it if it's present */
									$parallel_plugin_id = $wpdb->get_var( "SELECT `mail_plugin_id` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `parallel_plugin_link`=( 
										SELECT `plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `mail_plugin_id`=" . $plugin . ");" );
									if ( ! empty( $parallel_plugin_id ) ) {
										$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `in_queue_status`=1 WHERE `mail_plugin_id`=" . $parallel_plugin_id );
									}
									/* restore single plugin */
									$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `in_queue_status`=1 WHERE `mail_plugin_id`=" . $plugin );
									if ( $wpdb->last_error ) { 
										$error ++;
									} else {
										$done ++;
									}
									/* set message */
									if ( 0 == $error ) {
										$action_message['done'] = sprintf( _nx( __( 'Plugin was restored to the queue.', 'email-queue'), '%s&nbsp;' . __( 'Plugins were restored to the queue.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
									} else {
										$action_message['error'] = $message_list['plugin_restore_error'] . '<br />' . $message_list['try_later'];
									}
								}
							}
						}
						break;
					default:
						break;
				}
			} 
		}
		return $action_message = mlq_check_plugins_versions( $action_message );
	}
}

/**
 * Function to display plugin settings page in the admin area
 * @return void
 */
if ( ! function_exists( 'mlq_admin_settings_content' ) ) {
	function mlq_admin_settings_content() {
		global $wp_version, $wpdb, $mlq_options, $mlq_options_default, $title, $mlq_message, $mlq_error, $mlq_plugin_list, $mlq_active_plugin_list;
		$message = '';
		if ( empty( $mlq_options ) ) {
			$mlq_options = ( 1 == is_multisite() ) ? get_site_option( 'mlq_options' ) : get_option( 'mlq_options' );
		}
		/* Save plugin's settings */
		if ( isset( $_POST['mlq_form_submit'] ) && isset( $_POST['mlq_button_form_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'mlq_nonce_name' ) ) {
			/* save priorities values to DB if changed */
			if ( isset( $_POST['priority']) ) {
				foreach ( $_POST['priority'] as $plugin_slug => $plugin_priority ) {
					if ( is_numeric( $plugin_priority ) && in_array( $plugin_priority, array( 1, 3, 5 ) ) ) {
						$current_plugin = $wpdb->get_row( "SELECT `priority_general`, `parallel_plugin_link` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `plugin_slug`='" . stripslashes( esc_html( $plugin_slug ) ). "';", ARRAY_A );
						if ( ! empty( $current_plugin ) && $current_plugin['priority_general'] != $plugin_priority ) {
							$query = "UPDATE `" . $wpdb->base_prefix . "mlq_mail_plugins` SET `priority_general` = " . $plugin_priority . " WHERE `plugin_slug`='" . $plugin_slug . "' ";
							if ( $current_plugin['parallel_plugin_link'] != '0' ) {
								$query .= "OR `plugin_link`='" . $current_plugin['parallel_plugin_link'] . "'";
							}
							$wpdb->query( $query );
						}
					} else {
						$message .= __( 'Something is wrong with priority values. Previous values were restored. Check please.', 'email-queue' ) . '<br />';
					}
				}
			}
			/* additional options */
			$mlq_options['display_options'] = ( isset( $_POST['mlq_additions_options'] ) ) ? true : false;
			if ( $mlq_options['display_options'] ) {
				/* check value from "Interval for sending mail" option */
				if ( isset( $_POST['mlq_mail_run_time'] ) ) {
					if ( empty( $_POST['mlq_mail_run_time'] ) || 1 > intval( $_POST['mlq_mail_run_time'] ) || ( ! preg_match( '/^\d+$/', $_POST['mlq_mail_run_time'] ) ) ) {
						$mlq_options['mail_run_time'] = '1';
					} else {
						if ( 360 < $_POST['mlq_mail_run_time'] ) {
							$message .= __( 'You may have entered too large a value in the "Interval for sending mail" option. Check please.', 'email-queue' ) . '<br />';
						}
						$mlq_options['mail_run_time'] = $_POST['mlq_mail_run_time'];
					}
					add_filter( 'cron_schedules', 'mlq_more_reccurences' );
				} else {
					$mlq_options['mail_run_time'] = $mlq_options_default['mail_run_time'];
				}
				/* check value from "Number of messages sent at one time" option */
				if ( isset( $_POST['mlq_mail_send_count'] ) ) {
					if ( empty( $_POST['mlq_mail_send_count'] ) || 1 > intval( $_POST['mlq_mail_send_count'] ) || ( ! preg_match( '/^\d+$/', $_POST['mlq_mail_send_count'] ) ) ) {
						$mlq_options['mail_send_count'] = '1';
					} else {
						if ( 50 < $_POST['mlq_mail_send_count'] ) {
							$message .= __( 'You may have entered too large a value in the "Number of sent messages at one time" option. Check please.', 'email-queue' ) . '<br />';
						}
						$mlq_options['mail_send_count'] = $_POST['mlq_mail_send_count'];
					}
				} else {
					$mlq_options['mail_send_count'] = $mlq_options_default['mail_send_count'];
				}
				/* set mail method */
				$mlq_options['mail_method'] = $_POST['mlq_mail_method'];
				if ( $_POST['mlq_mail_method'] == 'smtp' ) {
					$mlq_options['smtp_settings']['host']     	= stripcslashes( esc_html( $_POST['mlq_mail_smtp_host'] ) );
					$mlq_options['smtp_settings']['accaunt']  	= stripcslashes( esc_html( $_POST['mlq_mail_smtp_accaunt'] ) );
					$mlq_options['smtp_settings']['password'] 	= stripcslashes( esc_html( $_POST['mlq_mail_smtp_password'] ) );
					/* check value from "SMTP port" option */
					if ( isset( $_POST['mlq_mail_smtp_port'] ) ) {
						if ( empty( $_POST['mlq_mail_smtp_port'] ) || 1 > intval( $_POST['mlq_mail_smtp_port'] ) || ( ! preg_match( '/^\d+$/', $_POST['mlq_mail_smtp_port'] ) ) ) {
							$mlq_options['smtp_settings']['port'] = '25';
						} else {
							$mlq_options['smtp_settings']['port'] = $_POST['mlq_mail_smtp_port'];
						}
					} else {
						$mlq_options['smtp_settings']['port'] = $mlq_options_default['smtp_settings']['port'];
					}
					$mlq_options['smtp_settings']['ssl'] =  ( isset( $_POST['mlq_ssl'] ) ) ? true : false ;

					if ( isset( $_POST['mlq_ssl'] ) ) {
						$mlq_options['smtp_settings']['ssl'] = true;
					} else {
						$mlq_options['smtp_settings']['ssl'] = false;
					}
				}
				/* check value from "Delete messages that are older than this number of days" option */
				$mlq_options['delete_old_mail'] = ( isset( $_POST['mlq_delete_old_mail'] ) ) ? true : false;
				if ( isset( $_POST['mlq_delete_old_mail'] ) ) {
					/* check the value of days entered */
					if ( isset( $_POST['mlq_delete_old_mail_days'] ) ) {
						if ( empty( $_POST['mlq_delete_old_mail_days'] ) || 1 > intval( $_POST['mlq_delete_old_mail_days'] ) || ( ! preg_match( '/^\d+$/', $_POST['mlq_delete_old_mail_days'] ) ) ) {
							$mlq_options['delete_old_mail_days'] = '30';
						} else {
							if ( 360 < $_POST['mlq_delete_old_mail_days'] ) {
								$message .= __( 'You may have entered too large a value in the "Delete messages that are older than this number of days" option. Check please.', 'email-queue' ) . '<br />';
							}
							$mlq_options['delete_old_mail_days'] = $_POST['mlq_delete_old_mail_days'];
						}
					} else {
						$mlq_options['delete_old_mail_days'] = $mlq_options_default['delete_old_mail_days'];
					}

				}
			}
			/* update options from the form to wp-options */
			if ( is_multisite() ) {
				update_site_option( 'mlq_options', $mlq_options );
			} else {
				update_option( 'mlq_options', $mlq_options );
			}
			$message .= __( "Settings saved.", 'email-queue' );
		} /* display settings page */ ?>
		<div class="wrap mlq-report-list-page mlq-mail" id="mlq-mail">
			<div id="icon-options-general" class="icon32 icon32-bws"></div>
			<h2><?php _e( 'Email Queue Settings', 'email-queue' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="admin.php?page=mlq_settings"><?php _e( 'Settings', 'email-queue' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/email-queue/faq/" target="_blank"><?php _e( 'FAQ', 'email-queue' ); ?></a>
			</h2>
			<?php /* update info on plugins before display and get list of active plugins */
			$mlq_plugin_list->check_plugin_install_and_active();
			$action_message = mlq_plugin_list_actions();
			if ( $action_message['error'] ) {
				$mlq_error = $action_message['error'];
			} 
			if ( $action_message['done'] ) {
				$mlq_message = $action_message['done'];
			} ?>
			<div class="error" <?php if ( empty( $mlq_error ) ) { echo 'style="display:none"'; } ?>><p><strong><?php echo $mlq_error; ?></strong></div>
			<div class="updated" <?php if ( empty( $mlq_message ) ) echo 'style="display: none;"'?>><p><?php echo $mlq_message ?></p></div>
			<style>
				<?php if ( ! $mlq_options['display_options'] ) { ?>
					.mlq_ad_opt {
						display: none; 
					}
				<?php }
				if ( 'smtp' != $mlq_options['mail_method'] ) { ?>
					.mlq_smtp_options {
						display: none;
					}
				<?php }
				if ( ! $mlq_options['delete_old_mail'] ) { ?>
					.mlq_delete_old_mail_option {
						display: none; 
					}
				<?php } ?>
			</style>
			<div id="mlq-settings-notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'email-queue' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save changes' button.", 'email-queue' ); ?></p></div>
			<div class="updated fade" <?php if( empty( $message ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<form id="mlq-settings-form" method="post" action="admin.php?page=mlq_settings">
				<h3><?php _e( 'Plugins with email function', 'email-queue' ); ?></h3>
				<?php $mlq_plugin_list->prepare_items();
				$bulk_actions = $mlq_plugin_list->current_action();
				$mlq_plugin_list->display(); ?>
				<table id="mlq-settings-table" class="form-table">
					<tr style="height: 45px;">
						<th>
							<label>
								<input type="checkbox" value="1" id="mlq_change_options" name="mlq_additions_options" <?php if ( $mlq_options['display_options'] ) echo 'checked="checked"'; ?> /> 
								<?php _e( 'Additional options', 'email-queue' ); ?>
							</label>
						</th>
					</tr>
					<tr class="mlq_ad_opt">
						<th scope="row"><?php _e( 'Interval for sending mail', 'email-queue' ); ?></th>
						<td><input id="mlq_mail_run_time" name='mlq_mail_run_time' type='text' value='<?php echo $mlq_options['mail_run_time']; ?>'> <?php _e( '(min)', 'email-queue' ); ?></td>
					</tr>
					<tr class="mlq_ad_opt">
						<th><?php _e( 'Number of messages sent at one time', 'email-queue' ); ?></th>
						<td>
							<input id="mlq_mail_send_count" name='mlq_mail_send_count' type='text' value='<?php echo $mlq_options['mail_send_count']; ?>'><br />
							<span class="mlq_info">
								<?php $number = floor( ( 60 / intval( $mlq_options['mail_run_time'] ) ) * intval( $mlq_options['mail_send_count'] ) );
								_e( 'maximum number of sent mails:', 'email-queue' );?>&nbsp;<span id="mlq-calculate"><?php echo $number; ?></span>&nbsp;<?php _e( 'per hour', 'email-queue' ); ?>.&nbsp;<br /><span id="mlq_calc_info"><?php _e( 'Please make sure that this number is smaller than max allowed number of sent mails from your hosting account.', 'email-queue' ); ?></span>
							</span>
						</td>
					</tr>
					<tr class="mlq_ad_opt">
						<th><?php _e( 'What to use?', 'email-queue' ); ?></th>
						<td>
							<label>
								<input id="mlq_wp_mail_radio" type='radio' name='mlq_mail_method' value='wp_mail' <?php if ( $mlq_options['mail_method'] == 'wp_mail' ) echo 'checked="checked"'; ?>/> 
								<?php _e( 'Wp-mail', 'email-queue' ); ?> <span class="mlq_info">(<?php _e( 'You can use the wp_mail function for mailing', 'email-queue' ); ?>)</span>
							</label><br />
							<label>
								<input id="mlq_php_mail_radio" type='radio' name='mlq_mail_method' value='mail' <?php if ( $mlq_options['mail_method'] == 'mail' ) echo 'checked="checked"'; ?>/> 
								<?php _e( 'Mail', 'email-queue' ); ?> <span class="mlq_info">(<?php _e( 'To send mail you can use the php mail function', 'email-queue' ); ?>)</span>
							</label><br />
							<label>
								<input id="mlq_smtp_mail_radio" type='radio' name='mlq_mail_method' value='smtp' <?php if ( $mlq_options['mail_method'] == 'smtp' ) echo 'checked="checked"'; ?>/> 
								<?php _e( 'SMTP', 'email-queue' ); ?> <span class="mlq_info">(<?php _e( 'You can use SMTP for sending mails', 'email-queue' ); ?>)</span>
							</label>
						</td>
					</tr>
					<tr class="mlq_ad_opt mlq_smtp_options">
						<th><?php _e( 'SMTP Settings', 'email-queue' ); ?></td>
						<td colspan="2">
							<input type='text' name='mlq_mail_smtp_host' value='<?php echo $mlq_options['smtp_settings']['host']; ?>' /> 
							<?php _e( 'SMTP server', 'email-queue' ); ?><br/>
							<input type='text' name='mlq_mail_smtp_port' value='<?php echo $mlq_options['smtp_settings']['port']; ?>' /> 
							<?php _e( 'SMTP port', 'email-queue' ); ?><br/>
							<input type='text' name='mlq_mail_smtp_accaunt' value='<?php echo $mlq_options['smtp_settings']['accaunt']; ?>' /> 
							<?php _e( 'SMTP account', 'email-queue' ); ?><br/>
							<input type='password' name='mlq_mail_smtp_password' value='<?php echo $mlq_options['smtp_settings']['password']; ?>' />  
							<?php _e( 'SMTP password', 'email-queue' ); ?><br/>
							<label>
								<input type='checkbox' name='mlq_ssl' <?php if ( $mlq_options['smtp_settings']['ssl'] ) echo 'checked="checked"'; ?>/> 
								<?php _e( 'Use SMTP SSL', 'email-queue' ); ?>
							</label>
						</td>
					</tr>
					<tr class="mlq_ad_opt">
						<th scope="row"><?php _e( "Delete old messages in database", 'email-queue' ); ?></th>
						<td>
							<input id='mlq_delete_old_mail' name='mlq_delete_old_mail' type="checkbox" value="1" <?php if ( $mlq_options['delete_old_mail'] ) echo "checked=\"checked\" "; ?>/><br />
							<label  class="mlq_ad_opt mlq_delete_old_mail_option">
								<span class="mlq_info"><?php _e( 'Delete messages that are older than this number of days', 'email-queue' ); ?></span><br />
								<input id="mlq_delete_old_mail_days" name='mlq_delete_old_mail_days' type='text' value='<?php echo $mlq_options['delete_old_mail_days']; ?>'><br />
							</label>
							<span class="mlq_info">
								<?php $mlq_messages_in_db = $wpdb->get_var("SELECT COUNT( `mail_send_id` ) FROM `" . $wpdb->base_prefix . "mlq_mail_send` WHERE `mail_status`='1';");
								_e( 'Currnet number of sent messages in database', 'email-queue' ); ?>:&nbsp;<span id="mlq-total-messages"><?php echo $mlq_messages_in_db; ?></span>
							</span>
						</td>
					</tr>
				</table>
				<input type="hidden" name="mlq_form_submit" value="submit" />
				<p class="submit">
					<input type="submit" class="button-primary" name="mlq_button_form_submit" value="<?php _e( 'Save changes', 'email-queue' ) ?>" />
				</p>
				<?php wp_nonce_field( plugin_basename( __FILE__ ), 'mlq_nonce_name' ); ?>				
			</form>
			<div class="bws-plugin-reviews">
				<div class="bws-plugin-reviews-rate">
					<?php _e( 'If you enjoy our plugin, please give it 5 stars on WordPress', 'email-queue' ); ?>: 
					<a href="http://wordpress.org/support/view/plugin-reviews/email-queue" target="_blank" title="Email Queue reviews"><?php _e( 'Rate the plugin', 'email-queue' ); ?></a>
				</div>
				<div class="bws-plugin-reviews-support">
					<?php _e( 'If there is something wrong about it, please contact us', 'email-queue' ); ?>: 
					<a href="http://support.bestwebsoft.com">http://support.bestwebsoft.com</a>
				</div>
			</div>
		</div><!--  #mlq-mail .wrap .mlq-report-list-page .mlq-mail -->
	<?php }
}

/**
 * create class MLQ_Mail_Queue_List to display mail queue
 */	
if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
	if ( ! class_exists( 'MLQ_Mail_Queue_List' ) ) {
		class MLQ_Mail_Queue_List extends WP_List_Table {

			/**
			 * Constructor of class 
			 */
			function __construct() {
				global $status, $page;
				parent::__construct( array(
					'singular'  => __( 'mail', 'email-queue' ),
					'plural'    => __( 'mails', 'email-queue' ),
					'ajax'      => true,
					)
				);
			}

			/**
			 * Function to prepare data before display 
			 * @return void
			 */
			function prepare_items() {
				global $wpdb, $mail_status;
				$mail_status = isset( $_REQUEST['mail_status'] ) ? $_REQUEST['mail_status'] : 'all';
				if ( ! in_array( $mail_status, array( 'all', 'in_progress', 'done', 'trash' ) ) )
					$mail_status = 'all';
				$search 			= ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : '';
				$columns 			= $this->get_columns();
				$hidden 			= array();
				$sortable 			= $this->get_sortable_columns();
				$this->found_data	= $this->mail_list();
				$this->items 		= $this->found_data;
				$per_page 			= $this->get_items_per_page( 'reports_per_page', 30 );
				$current_page 		= $this->get_pagenum();
				$total_items 		= $this->items_count();
				$this->set_pagination_args( array(
						'total_items' => $total_items,
						'per_page'    => $per_page,
					)
				);
			}

			/**
			* Function to show message if no reports found
			* @return void
			*/
			function no_items() { ?>
				<p style="color:red;"><?php _e( 'No messages found', 'email-queue' ); ?></p>
			<?php }

			/**
			 * Function to add column of checboxes 
			 * @param int $item->comment_ID 	The custom column's unique ID number.
			 * @return string 					with html-structure of <input type=['checkbox']>
			 */
			function column_cb( $item ) {
				return sprintf( '<input id="cb_%1s" type="checkbox" name="report_id[]" value="%2s" />', $item['id'], $item['id'] );
			}

			/**
			 * Get a list of columns.
			 * @return array list of columns and titles
			 */
			function get_columns() {
				$columns = array(
					'cb'		=> '<input type="checkbox" />',
					'title'		=> __( 'Subject', 'email-queue' ),
					'plugin'	=> __( 'Plugin', 'email-queue' ),
					'priority'	=> __( 'Priority', 'email-queue' ),
					'status'	=> __( 'Status', 'email-queue' ),
					'date'		=> __( 'Date', 'email-queue' ),
				);
				return $columns;
			}

			/**
			 * Get a list of sortable columns.
			 * @return array list of sortable columns
			 */
			function get_sortable_columns() {
				$sortable_columns = array(
					'title'		=> array( 'title', false ),
					'plugin'	=> array( 'plugin', false ),
					'priority'	=> array( 'priority', false ),
					'status'	=> array( 'status', false ),
					'date'		=> array( 'date', false )
				);
				return $sortable_columns;
			}

			/**
			* Function to add filters below and above reports ist
			* @param array $which An array of report types. Accepts 'Done', ''In progress.
			* @return void 
			*/
			function extra_tablenav( $which ) {
				global $wpdb;
				$all_count     = $done_count = $in_progress_count = $trash_count = 0;
				$filters_count = $wpdb->get_results (
					"SELECT COUNT(`mail_send_id`) AS `all`,
						( SELECT COUNT(`mail_send_id`) FROM " . $wpdb->base_prefix . "mlq_mail_send WHERE `mail_status`=1 AND `trash_status`=0 ) AS `done`,
						( SELECT COUNT(`mail_send_id`) FROM " . $wpdb->base_prefix . "mlq_mail_send WHERE `mail_status`=0 AND `trash_status`=0 ) AS `in_progress`,
						( SELECT COUNT(`mail_send_id`) FROM " . $wpdb->base_prefix . "mlq_mail_send WHERE `trash_status`=1 ) AS `trash`
					FROM " . $wpdb->base_prefix . "mlq_mail_send WHERE `trash_status`=0"
				); 
				foreach( $filters_count as $count ) {
					$all_count         = empty( $count->all ) ? 0 : $count->all;
					$done_count        = empty( $count->done ) ? 0 : $count->done;
					$in_progress_count = empty( $count->in_progress ) ? 0 : $count->in_progress;
					$trash_count	   = empty( $count->trash ) ? 0 : $count->trash;
				} ?>
				<ul class="subsubsub">
					<li><a class="mlq-filter<?php if ( ! isset( $_REQUEST['mail_status'] ) || 'all'==$_REQUEST['mail_status'] ) { echo " current"; } ?>" href="?page=mlq_view_mail_queue"><?php _e( 'All', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $all_count; ?> )</span></a> | </li>
					<li><a class="mlq-filter<?php if( isset( $_REQUEST['mail_status'] ) && "in_progress" == $_REQUEST['mail_status'] ) { echo " current"; } ?>" href="?page=mlq_view_mail_queue&mail_status=in_progress"><?php _e( 'In progress', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $in_progress_count; ?> )</span></a> | </li>
					<li><a class="mlq-filter<?php if( isset( $_REQUEST['mail_status'] ) && "done" == $_REQUEST['mail_status'] ) { echo " current"; } ?>" href="?page=mlq_view_mail_queue&mail_status=done"><?php _e( 'Done', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $done_count; ?> )</span></a> | </li>
					<li><a class="mlq-filter<?php if( isset( $_REQUEST['mail_status'] ) && "trash" == $_REQUEST['mail_status'] ) { echo " current"; } ?>" href="?page=mlq_view_mail_queue&mail_status=trash"><?php _e( 'Trash', 'email-queue' ); ?><span class="mlq-count"> ( <?php echo $trash_count; ?> )</span></a></li>
				</ul><!-- .subsubsub --> 
			<?php  }

			/**
			 * Function to add action links to drop down menu before and after mail queue list
			 * @return array of actions
			 */
			function get_bulk_actions() {
				$mail_status = isset( $_REQUEST['mail_status'] ) ? '&mail_status=' . $_REQUEST['mail_status'] : '&mail_status=all';
				$actions = array();

				if ( in_array( $mail_status, array( '&mail_status=all', '&mail_status=in_progress', '&mail_status=done', ) ) ) {
					$actions['trash_reports']  = __( 'Move to trash', 'email-queue' );
				}
				if ( $mail_status == '&mail_status=trash' ) {
					$actions['untrash_reports'] = __( 'Restore', 'email-queue' );
					$actions['delete_reports']  = __( 'Delete permanently', 'email-queue' );
				}
				return $actions;
			}

			/**
			 * Fires when the default column output is displayed for a single row.
			 * @param string $column_name      The custom column's name.
			 * @param int    $item->comment_ID The custom column's unique ID number.
			 * @return void
			 */
			function column_default( $item, $column_name ) {
				switch( $column_name ) {
					case 'status':
					case 'date':
					case 'title':
					case 'plugin':
					case 'priority':
						return $item[ $column_name ];
					default:
						return print_r( $item, true ) ;
				}
			}

			/**
			 * Function to add action links to subject column depenting on status page
			 * @param int $item->comment_ID	The custom column's unique ID number.
			 * @return string 				with action links
			 */
			function column_title( $item ) {
				$mail_status = isset( $_REQUEST['mail_status'] ) ? '&mail_status=' . $_REQUEST['mail_status'] : '&mail_status=all';
				$actions = array();
				if ( in_array( $mail_status, array( '&mail_status=all', '&mail_status=in_progress', '&mail_status=done', ) ) ) {
					$actions['show_report']  = '<a class="mlq-show-users-list" href="' . wp_nonce_url( '?page=mlq_view_mail_queue&action=show_report&report_id=' . $item['id'] . '&list_paged=0&list_per_page=30' . $mail_status, 'show_mail_' . $item['id'] ) . '">' . __( 'Mail details', 'email-queue' ) . '</a>';
					$actions['trash_report'] = '<a href="' . wp_nonce_url( '?page=mlq_view_mail_queue&action=trash_report&report_id=' . $item['id'] . $mail_status, 'trash_mail_' . $item['id'] ) . '">' . __( 'Move to trash', 'email-queue' ) . '</a>';
				}
				if ( $mail_status == '&mail_status=trash' ) {
					$actions['untrash_report'] = '<a href="' . wp_nonce_url( '?page=mlq_view_mail_queue&action=untrash_report&report_id=' . $item['id'] . $mail_status, 'untrash_mail_' . $item['id'] ) . '">' . __( 'Restore', 'email-queue' ) . '</a>';
					$actions['delete_report']  = '<a href="' . wp_nonce_url( '?page=mlq_view_mail_queue&action=delete_report&report_id=' . $item['id'] . $mail_status, 'delete_mail_' . $item['id'] ) . '">' . __( 'Delete permanently', 'email-queue' ) . '</a>';
				}
				return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
			}

			/**
			 * Function to add necessary class and id to table row
			 * @param array $mail with report data 
			 * @return void
			 */
			function single_row( $mail ) {
				if( preg_match( '/done-status/', $mail['status'] ) ) {
					$row_class = 'mlq-done-row';
				} elseif( preg_match( '/inprogress-status/', $mail['status'] ) ) {
					$row_class = 'mlq-inprogress-row';
				} else {
					$row_class = null;
				}
				echo '<tr id="mlq-report-' . $mail['id'] . '" class="' . trim( $row_class ) . '">';
					$this->single_row_columns( $mail );
				echo "</tr>\n";
			}
			
			/**
			 * Function to get mail queue list
			 * @return array list of mails
			 */
			function mail_list() {
				global $wpdb;
				$i 					= 0;
				$done_status 		= '<p class="mlq-done-status" title="' . __( 'All Done', 'email-queue' ) . '">' . __( 'done', 'email-queue' ) . '</p>';
				$in_progress_status = '<p class="mlq-inprogress-status" title="' . __( 'In progress', 'email-queue' ) . '">' . __( 'In progress', 'email-queue' ) . '</p>';
				$mails_list 		= array();  
				$per_page 			= intval( get_user_option( 'reports_per_page' ) );
				if ( empty( $per_page ) || $per_page < 1 ) {
					$per_page = 30;
				}
				$start_row = ( isset( $_REQUEST['paged'] ) && '1' != $_REQUEST['paged'] ) ? $per_page * ( absint( $_REQUEST['paged'] - 1 ) ) : 0;
				if ( isset( $_REQUEST['orderby'] ) ) {
					switch ( $_REQUEST['orderby'] ) {
						case 'date':
							$order_by = 'date_create';
							break;
						case 'title':
							$order_by = 'subject';
							break;
						case 'status':
							$order_by = 'mail_status';
							break;
						case 'plugin':
							$order_by = 'plugin_id';
							break;
						case 'priority':
							$order_by = 'priority';
							break;
						default:
							$order_by = 'mail_send_id';
							break;
					}
				} else {
					$order_by = 'mail_send_id';
				}
				$order     = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'DESC';
				$sql_query = "SELECT * FROM `" . $wpdb->base_prefix . "mlq_mail_send` ";
				if ( isset( $_REQUEST['s'] ) && "" != $_REQUEST['s'] )  {
					$sql_query .= "WHERE `subject`LIKE '%" . $_REQUEST['s'] . "%'";
				} else {
					if ( isset( $_REQUEST['mail_status'] ) ) {
						switch ( $_REQUEST['mail_status'] ) {
							case 'in_progress':
								$sql_query .= "WHERE `mail_status`=0  AND `trash_status`=0";
								break;
							case 'done':
								$sql_query .= "WHERE `mail_status`=1  AND `trash_status`=0";
								break;
							case 'trash':
								$sql_query .= "WHERE `trash_status`=1";
								break;
							default:
								$sql_query .= "WHERE `trash_status`=0";
								break;
						}
					} else {
						$sql_query .= "WHERE `trash_status`=0";
					}
				}
				$sql_query   .= " ORDER BY " . $order_by . " " . $order . " LIMIT " . $per_page . " OFFSET " . $start_row . ";";
				$mails_data = $wpdb->get_results( $sql_query, ARRAY_A );
				foreach ( $mails_data as $mail ) {
					$title = empty( $mail['subject'] ) ? '( ' . __( 'No subject', 'email-queue' ) . ' )' : $mail['subject'];
					$mails_list[$i] 			= array();
					$mails_list[$i]['id'] 		= $mail['mail_send_id'];
					$mails_list[$i]['status'] 	= '1' == $mail['mail_status'] ? $done_status : $in_progress_status;
					$mails_list[$i]['title'] 	= $title . '<input type="hidden" name="report_' . $mail['mail_send_id'] . '" value="' . $mail['mail_send_id'] . '">' . $this->show_report( $mail['mail_send_id'] );
					$mails_list[$i]['date'] 	= date( 'd M Y H:i', $mail['date_create'] + get_option('gmt_offset') * 3600 );
					$mails_list[$i]['plugin'] 	= $this->mlq_get_plugin_name( $mail['plugin_id'] );
					$mails_list[$i]['priority']	= $this->mlq_get_priority_name( $mail['priority'] );
					$i ++;
				}
				return $mails_list;
			}

			/**
			 * Function to get plugin name by its ID
			 * @return string with Plugin name
			 */
			function mlq_get_plugin_name( $plugin_id ) {
				global $wpdb;
				$plugin_name = $wpdb->get_var( "SELECT `plugin_name` FROM `" . $wpdb->base_prefix . "mlq_mail_plugins` WHERE `mail_plugin_id`=" . $plugin_id . ";" );
				return $plugin_name;
			}

			/**
			 * Function to get priority name by its ID
			 * @return string with Priority name
			 */
			function mlq_get_priority_name( $priority_id ) {
				switch ( $priority_id ) {
					case '1':
						$priority_name = __( 'Low', 'email-queue' );
						break;
					case '3':
						$priority_name = __( 'Normal', 'email-queue' );
						break;
					case '5':
						$priority_name = __( 'High', 'email-queue' );
						break;
					default:
						$priority_name = '';
						break;
				}
				return $priority_name;
			}

			/**
			 * Function to get number of all reports
			 * @return sting reports number
			 */
			public function items_count() {
				global $wpdb;
				$sql_query = "SELECT COUNT(`mail_send_id`) FROM `" . $wpdb->base_prefix . "mlq_mail_send`";
				if ( isset( $_REQUEST['mail_status'] ) ) {
					switch ( $_REQUEST['mail_status'] ) {
						case 'in_progress':
							$sql_query .= " WHERE `mail_status`=0 AND `trash_status`=0;";
							break;
						case 'done':
							$sql_query .= " WHERE `mail_status`=1 AND `trash_status`=0;";
							break;
						case 'trash':
							$sql_query .= " WHERE `trash_status`=1;";
							break;
						default:
							$sql_query .= " WHERE `trash_status`=0;";
							break;
					}
				} else {
					$sql_query .= " WHERE `trash_status`=0;";
				}
				$items_count  = $wpdb->get_var( $sql_query );
				return $items_count;
			}

			/**
			 * Function to display status of mail
			 * @param string $mail_id id of mail 
			 * @return string 'done'- ,'inprogress' or 'unknown'- statuses
			 */
			public function show_status( $mail_id ) {
				global $wpdb;
				$total_count = $send_count = $status = null;
				$count_mail = $wpdb->get_results(
					"SELECT COUNT(`id_mail`) AS `total`, 
						( SELECT COUNT(`id_mail`) FROM `" . $wpdb->base_prefix . "mlq_users` WHERE `id_mail`=" . $mail_id . " AND `status`=1 ) AS `send`
					FROM `" . $wpdb->base_prefix . "mlq_users` WHERE `id_mail`=" . $mail_id
				);
				if ( ! empty( $count_mail ) ) {
					foreach ( $count_mail as $count ) {
						$total_count = $count->total;
						$send_count  = $count->send;
					}
					if ( $total_count == $send_count ) {
						$status = '<span class="mlq-done-status" title="' . __( 'All Done', 'email-queue' ) . '">' . __( 'done', 'email-queue' ) . '</span>';
					} else {
						$status = '<span class="mlq-inprogress-status" title="' . __( 'In progress', 'email-queue' ) . '">' . $send_count .' / ' . $total_count . '</span>';
					}
				} else {
					$status = '<span class="mlq-unknown-status" title="' . __( 'Unknown status', 'email-queue' ) . '">' . '?' . '</span>';
				}
				return $status;
			}

			/**
			 * Function to show list of mail receivers
			 * @param string $mail_id - id of mail 
			 * @return string         list of mail receivers in table format
			 */
			public function show_report( $mail_id ) {
				$list_table = null;
				if ( isset( $_REQUEST['action'] ) && 'show_report' == $_REQUEST['action'] && $mail_id == $_REQUEST['report_id'] && check_admin_referer( 'show_mail_' . $_REQUEST['report_id'] ) ) {
					global $wpdb;
					$pagination = '';
					$mail     = $_REQUEST['report_id'];
					if ( isset( $_POST['set_list_per_page_top'] ) || isset( $_POST['set_list_per_page_bottom'] ) ) { /* query from mail receivers pagination blocks */
						/* check if user want change number of mail receiver which will br dysplayed on page */
						if ( $_POST['set_list_per_page_top'] != $_POST['list_per_page'] ) {
							$per_page = ( empty( $_POST['set_list_per_page_top'] ) || ( ! preg_match( '/^\d+$/', $_POST['set_list_per_page_top'] ) ) ) ? $_REQUEST['list_per_page'] : $_POST['set_list_per_page_top'];
							$paged    = 0;
						} elseif( $_POST['set_list_per_page_bottom'] != $_POST['list_per_page'] ) {
							$per_page = ( empty( $_POST['set_list_per_page_bottom'] ) || ( ! preg_match( '/^\d+$/', $_POST['set_list_per_page_bottom'] ) ) ) ? $_REQUEST['list_per_page'] : $_POST['set_list_per_page_bottom'];
							$paged    = 0;
						/* cheeck if user want to change number of page in text field */
						} elseif( $_POST['list_paged_top'] != $_POST['current_page'] ) {
							$per_page   = $_REQUEST['list_per_page'];
							/* if entered value is empty or not only digital */
							$list_paged = ( empty( $_POST['list_paged_top'] ) || ( ! preg_match( '/^\d+$/', $_POST['list_paged_top'] ) ) ) ? '1' : $_REQUEST['list_paged_top'];
							/* if entered value bigger than last page number */
							$list_paged = intval( $_REQUEST['max_page_number'] ) < intval( $list_paged ) ? $_REQUEST['max_page_number'] : $list_paged;
							$paged      = intval( $list_paged ) - 1;
						} elseif( $_POST['list_paged_bottom'] != $_POST['current_page'] ) {
							$per_page   = $_REQUEST['list_per_page'];
							/* if entered value is empty or not only digital */
							$list_paged = ( empty( $_POST['list_paged_bottom'] ) || ( ! preg_match( '/^\d+$/', $_POST['list_paged_bottom'] ) ) ) ? '1' : $_REQUEST['list_paged_bottom'];
							/* if entered value bigger than last page number */
							$list_paged = intval( $_REQUEST['max_page_number'] ) < intval( $list_paged ) ? $_REQUEST['max_page_number'] : $list_paged;
							$paged      = intval( $list_paged ) - 1;
						} else {
							$per_page   = $_REQUEST['list_per_page'];
							$paged      = $_POST['current_page'];
						}
					} else { /* query from action link on "Subject" row */
						$per_page = $_REQUEST['list_per_page'];
						$paged    = intval( $_GET['list_paged'] );
					}
					$list_order_by = isset( $_REQUEST['list_order_by'] ) ? $_REQUEST['list_order_by'] : 'user_email';
					if( isset( $_REQUEST['list_order'] ) ) {
						$list_order = 'ASC' == $_REQUEST['list_order'] ? 'DESC' : 'ASC';
						$link_list_order = $_REQUEST['list_order'];
					} else {
						$list_order = $link_list_order = 'ASC';
					}
					$mail_status = isset( $_REQUEST['mail_status'] ) ? '&mail_status=' . $_REQUEST['mail_status'] : '';
					$start_row   = $per_page * $paged;
					$users_list  = $wpdb->get_results( 
						"SELECT `status`, `view`, `try`, `user_email` 
						FROM `" . $wpdb->base_prefix . "mlq_mail_users` 
						WHERE `id_mail`=" . $mail . " ORDER BY " . $list_order_by . " " . $list_order . " LIMIT " . $per_page . " OFFSET " . $start_row . ";"
					);
					if ( ! empty( $users_list ) ) { 
						$list_table =
							'<table class="mlq-receivers-list">
								<thead>
									<tr scope="row">
										<td colspan="4">' . $this->mail_receivers_pagination( $mail, $per_page, $paged, $list_order_by, $link_list_order, false, 'top' ) . '</td>
									</tr>
									<tr>
										<td class="mlq-username"><a href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail . '&list_paged=' . $paged . '&list_per_page=' . $per_page . '&list_order_by=user_email&list_order=' . $list_order . $mail_status . '">' . __( 'Receiver&#39;s email', 'email-queue' ) . '</a></td>
										<td><a href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail . '&list_paged=' . $paged . '&list_per_page=' . $per_page . '&list_order_by=status&list_order=' . $list_order . $mail_status . '">' . __( 'Status', 'email-queue' ) . '</a></td>
										<td style="display: none;"><a href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail . '&list_paged=' . $paged . '&list_per_page=' . $per_page . '&list_order_by=view&list_order=' . $list_order . $mail_status . '">' . __( 'View', 'email-queue' ) . '</a></td>
										<td>' . __( 'Try', 'email-queue' ) . '</td>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td class="mlq-username"><a href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail . '&list_paged=' . $paged . '&list_per_page=' . $per_page . '&list_order_by=user_email&list_order=' . $list_order . $mail_status . '">' . __( 'Receiver&#39;s email', 'email-queue' ) . '</a></td>
										<td><a href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail . '&list_paged=' . $paged . '&list_per_page=' . $per_page . '&list_order_by=status&list_order=' . $list_order . $mail_status . '">' . __( 'Status', 'email-queue' ) . '</a></td>
										<td style="display: none;"><a href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail . '&list_paged=' . $paged . '&list_per_page=' . $per_page . '&list_order_by=view&list_order=' . $list_order . $mail_status . '">' . __( 'View', 'email-queue' ) . '</a></td>
										<td>' . __( 'Try', 'email-queue' ) . '</td>
									</tr>
									<tr scope="row">
										<td colspan="4">' . $this->mail_receivers_pagination( $mail, $per_page, $paged, $list_order_by, $link_list_order, true, 'bottom' ) . '</td>
									</tr>
								</tfoot>
								<tbody>';
						foreach( $users_list as $list ) {
							$user_email = $list->user_email;
							if ( empty( $user_email ) ) {
								$user_email = '<i>- ' . __( 'Receiver&#39;s email not found', 'email-queue' ) . ' -</i>';
							}
							$list_table .= '<tr>
												<td class="mlq-username">' . $user_email . '</td>
												<td>';
							if( '1' == $list->status ) {
								$list_table .= '<p style="color: #006505;">' . __( 'sent', 'email-queue' ) . '</p>';
							} else { 
								$list_table .= '<p style="color: #700;">' . __( 'in queue', 'email-queue' ) . '</p>'; 
							}
							$list_table .=		'</td>
												<td style="display: none;">';
							if( '1' == $list->view ) {
								$list_table .= '<p style="color: green;">' . __( 'read', 'email-queue' ) . '</p>';
							} else { 
								$list_table .= '<p style="color: #555;">' . __( 'not read', 'email-queue' ) . '</p>'; 
							}
							$list_table .=	'</td>
											<td>' . $list->try . '</td>
										</tr>';
						}
						$list_table .= 
								'</tbody>
							</table>';
					} else {
						/* if( empty( $users_list ) ) */
						$list_table = '<p style="color:red;">' . __( "The list of mail receivers can't be found.", 'email-queue' ) . '</p>';
					}
				}
				return $list_table;
			}

			/** 
			 * Function to get mail receivers list pagination
			 * @param string  $mail_id        id of report
			 * @param string  $per_page       number of mail receivers on each page
			 * @param string  $paged          desired page number
			 * @param string  $list_order_by  on what grounds will be sorting
			 * @param string  $list_order     "ASC" or "DESC
			 * @param bool    $show_hidden    show/not hidden fields 
			 * @param string  $place          postfix to fields name
			 * @return string                 pagination elements
			 */
			function mail_receivers_pagination( $mail_id, $per_page, $paged, $list_order_by, $list_order, $show_hidden, $place ) {
				global $wpdb;
				$users_count = $wpdb->get_var(
					"SELECT COUNT( `mail_users_id` ) FROM `" . $wpdb->base_prefix . "mlq_mail_users` WHERE `id_mail`=" . $mail_id . ";"
				);
				$mail_status = isset( $_REQUEST['mail_status'] ) ? '&mail_status=' . $_REQUEST['mail_status'] : '';
				/* open block with pagination elements */
				$pagination_block = 
					'<div class="mlq-pagination">
						<p class="mlq-total-users">' . __( 'Total mail receivers:', 'email-queue' ) . ' ' . $users_count . '</p>';
				if ( 'top' == $place) {
					$pagination_block .= '<input type="hidden" id="mlq-total-users" value="'. $users_count . '"/>';
				}
				/* if more than 1 page */
				if ( intval( $users_count ) > $per_page ) {
					$pagination_block .= 
						'<div class="mlq-list-per-page">
							<input type="text" name="set_list_per_page_' . $place . '" value="' . $per_page . '" size="3" title="' . __( 'Number of mail receivers on page', 'email-queue' ) . '"/>
							<span class="mlq-total-pages">' . __( 'on page', 'email-queue' ) . '</span>
						</div>';
					/* get number of all pages */
					$total_pages 		 = ceil( $users_count / $per_page ) - 1;
					$total_pages_display = $total_pages + 1;
					$current_page 		 = $paged + 1;
					/* get size of <input type="text"/> */
					if ( '9' < $total_pages && '99' >= $total_pages ) {
						$input_size = 2;
					} elseif ( '100' < $total_pages && '999' >= $total_pages ) {
						$input_size = 3;
					} elseif ( '1000' < $total_pages && '9999' >= $total_pages ) {
						$input_size = 4;
					} elseif ( '10000' < $total_pages && '99999' >= $total_pages ) {
						$input_size = 5;
					} else {
						$input_size = 1;
					}
					$pagination_block .= 
						'<div class="mlq-list-paged">';
					if ( 0 < $paged ) { /* if this is NOT first page of mail receivers list */
						$previous_page_link = ( 1 < $paged ) ? $paged - 1 : 0;
						$pagination_block .= 
							'<a class="first-page" href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail_id . '&list_paged=0&list_per_page=' . $per_page . $mail_status . '&list_order_by=' . $list_order_by . '&list_order=' . $list_order . '" title="' . __( 'Go to the first page', 'email-queue' ) . '">&laquo;</a>
							<a class="previous-page" href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail_id . '&list_paged=' . $previous_page_link . '&list_per_page=' . $per_page . $mail_status . '&list_order_by=' . $list_order_by . '&list_order=' . $list_order . '" title="' . __( 'Go to the previous page', 'email-queue' ) . '">&lsaquo;</a>';
					} else { /* if this is first page of mail receivers list */
						$pagination_block .= 
							'<span class="mlq-first-page-disabled">&laquo;</span>
							<span class="mlq-previous-page-disabled">&lsaquo;</span>';
					}
					/* field to choose number of mail receivers on page and current page */
					$pagination_block .= 
						'<input type="text" class="mlq-page-number" name="list_paged_' . $place . '" value="' . $current_page . '" size="' . $input_size . '" title="' . __( 'Current page', 'email-queue' ) . '"/>
						<span class="mlq-total-pages">' . __( 'of', 'email-queue' ) . '&nbsp;' . $total_pages_display . '&nbsp;' . __( 'pages', 'email-queue' ) . '</span>';
					if ( $show_hidden ) {
						$pagination_block .= 
							'<input type="hidden" name="action" value="show_report"/>
							<input type="hidden" name="report_id" value="' . $mail_id . '"/>
							<input type="hidden" name="list_per_page" value="' . $per_page . '"/>
							<input type="hidden" name="current_page" value="' . $current_page . '"/>
							<input type="hidden" name="list_order_by" value="' . $list_order_by . '"/>
							<input type="hidden" name="list_order" value="' . $list_order . '"/>
							<input type="hidden" name="max_page_number" value="' . $total_pages_display . '"/>';
					}
					if ( ! empty( $mail_status ) ) {
						$pagination_block .= '<input type="hidden" name="mail_status" value="' . $_REQUEST['mail_status'] . '"/>';
					}
					if ( $paged < $total_pages ) { /* if this is NOT last page */
						$next_page_link = ( ( $paged - 1 ) < $total_pages ) ? $paged + 1 : $total_pages;
						$pagination_block .= 
							'<a class="next-page" href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail_id . '&list_paged=' . $next_page_link . '&list_per_page=' . $per_page . $mail_status . '&list_order_by=' . $list_order_by . '&list_order=' . $list_order . '" title="' . __( 'Go to the next page', 'email-queue' ) . '">&rsaquo;</a>
							<a class="last-page" href="?page=mlq_view_mail_queue&action=show_report&report_id=' . $mail_id . '&list_paged=' . $total_pages . '&list_per_page=' . $per_page . $mail_status . '&list_order_by=' . $list_order_by . '&list_order=' . $list_order . '" title="' . __( 'Go to the last page', 'email-queue' ) . '">&raquo;</a>';
					} else { /* if this is last page */
						$pagination_block .= 
							'<span class="mlq-next-page-disabled">&rsaquo;</span>
							<span class="mlq-last-page-disabled">&raquo;</span>';
					}
					$pagination_block .= '</div><!-- .list-paged -->';
				}
				/* close block with pagination elememnts */
				$pagination_block .= '</div><!-- .mlq-pagination -->';
				return $pagination_block;
			}
		}
	}
}
/* the end of the MLQ_Mail_Queue_List class definition	*/

/**
 * Add screen options and initialize instance of class MLQ_Mail_Queue_List
 * @return void 
 */
if ( ! function_exists( 'mlq_screen_options' ) ) {
	function mlq_screen_options() {
		global $mlq_mails_list;
		$option = 'per_page';
		$args = array(
			'label'   => __( 'Mails per page', 'email-queue' ),
			'default' => 30,
			'option'  => 'reports_per_page'
		);
		add_screen_option( $option, $args );
		$mlq_mails_list = new MLQ_Mail_Queue_List;
	}
}

/**
 * Function to display template of page with mail queue
 * @return void 
 */
if ( ! function_exists( 'mlq_mail_view' ) ) {
	function mlq_mail_view() { 
		global $mlq_message, $mlq_error, $mlq_mails_list; ?>
		<div class="wrap mlq-report-list-page">
			<div id="icon-options-general" class="icon32 icon32-bws"></div>
			<h2><?php _e( 'Email Queue', 'email-queue' ); ?></h2>
			<?php $action_message = mlq_mail_actions();
			if ( $action_message['error'] ) {
				$mlq_error = $action_message['error'];
			} 
			if ( $action_message['done'] ) {
				$mlq_message = $action_message['done'];
			} ?>
			<div class="error" <?php if ( empty( $mlq_error ) ) { echo 'style="display:none"'; } ?>><p><strong><?php echo $mlq_error; ?></strong></div>
			<div class="updated" <?php if ( empty( $mlq_message ) ) echo 'style="display: none;"'?>><p><?php echo $mlq_message ?></p></div>
			<?php if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] ) {
				printf( '<span class="subtitle">' . sprintf( __( 'Search results for &#8220;%s&#8221;', 'email-queue' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ) . '</span>' );
			} ?>
			<form method="post">
				<?php $mlq_mails_list->prepare_items();
				$mlq_mails_list->search_box( __( 'search', 'email-queue' ), 'mlq' );
				$bulk_actions = $mlq_mails_list->current_action();
				$mlq_mails_list->display();
				wp_nonce_field( plugin_basename( __FILE__ ), 'mlq_nonce_name' ); ?>	
			</form>
		</div><!-- .wrap .mlq-report-list-page -->
	<?php }
}

/**
 * Function to handle actions from "mail queue" page 
 * @return array with messages about action results
 */
if ( ! function_exists( 'mlq_mail_actions' ) ) {
	function mlq_mail_actions() {
		global $wpdb;
		$action_message = array(
			'error' => false,
			'done'  => false
		);
		$error = $done = $mail_error = $mail_done = 0;
		if ( isset( $_REQUEST['page'] ) && 'mlq_view_mail_queue' == $_REQUEST['page'] ) {
			$message_list = array(
				'empty_mails_list'			=> __( 'You need to choose some mails.', 'email-queue' ),
				'mail_trash_error'			=> __( 'Error while trashing mail.', 'email-queue' ),
				'mail_untrash_error'		=> __( 'Error while untrashing mail.', 'email-queue' ),
				'receiver_delete_error'		=> __( 'Error while deleting mail receiver.', 'email-queue' ),
				'mail_delete_error'			=> __( 'Error while deleting mail.', 'email-queue' ),
				'try_later'					=> __( 'Please, try it later.', 'email-queue' ),
			);
			if ( isset( $_REQUEST['action'] ) || isset( $_REQUEST['action2'] ) ) {
				$action = '';
				if ( isset( $_REQUEST['action'] ) && '-1' != $_REQUEST['action'] ) {
					$action = $_REQUEST['action'];
				} elseif ( isset( $_POST['action2'] ) && '-1' != $_REQUEST['action2'] ) {
					$action = $_POST['action2'];
				}
				switch ( $action ) {
					case 'trash_report':
						if ( check_admin_referer( 'trash_mail_' . $_GET['report_id'] ) ) {
							if ( empty( $_GET['report_id'] ) ) {
								$action_message['error'] = $message_list['empty_mails_list'];
							} else {
								$mail = $_GET['report_id'];
								$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_send` SET `trash_status`=1 WHERE `mail_send_id`=" . $mail );
								if ( $wpdb->last_error ) { 
									$error ++;
								} else {
									$done ++;
								}
								/* set message */
								if ( 0 == $error ) {
									$action_message['done'] = sprintf( _nx( __( 'Mail was moved to trash.', 'email-queue'), '%s&nbsp;' . __( 'Mails were moved to trash.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
								} else {
									$action_message['error'] = $message_list['mail_trash_error'] . '<br />' . $message_list['try_later'];
								}
							}
						}
						break;
					case 'trash_reports':
						/* change trash status to '1' */
						if ( check_admin_referer( plugin_basename( __FILE__ ), 'mlq_nonce_name' ) ) {
							if ( empty( $_POST['report_id'] ) ) {
								$action_message['error'] = $message_list['empty_mails_list'];
							} else {
								foreach( $_POST['report_id'] as $mail ) {
									$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_send` SET `trash_status`=1 WHERE `mail_send_id`=" . $mail );
									if ( $wpdb->last_error ) { 
										$error ++;
									} else {
										$done ++;
									}
									/* set message */
									if ( 0 == $error ) {
										$action_message['done'] = sprintf( _nx( __( 'Mail was moved to trash.', 'email-queue'), '%s&nbsp;' . __( 'Mails were moved to trash.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
									} else {
										$action_message['error'] = $message_list['mail_trash_error'] . '<br />' . $message_list['try_later'];
									}
								}
							}
						}
						break;
					case 'untrash_report':
						if ( check_admin_referer( 'untrash_mail_' . $_GET['report_id'] ) ) {
							if ( empty( $_GET['report_id'] ) ) {
								$action_message['error'] = $message_list['empty_mails_list'];
							} else {
								$mail = $_GET['report_id'];
								$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_send` SET `trash_status`=0 WHERE `mail_send_id`=" . $mail );
								if ( $wpdb->last_error ) { 
									$error ++;
								} else {
									$done ++;
								}
								/* set message */
								if ( 0 == $error ) {
									$action_message['done'] = sprintf( _nx( __( 'Mail was restored.', 'email-queue'),	'%s&nbsp;' . __( 'Mails were restored.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
								} else {
									$action_message['error'] = $message_list['mail_untrash_error'] . '<br />' . $message_list['try_later'];
								}
								/* register cron hook in case trashed mail needs to be sent*/
								mlq_cron_hook_activate();
							}
						}
						break;
					case 'untrash_reports':
						/* change trash status to '0' */
						if ( check_admin_referer( plugin_basename( __FILE__ ), 'mlq_nonce_name' ) ) {
							if ( empty( $_POST['report_id'] ) ) {
								$action_message['error'] = $message_list['empty_mails_list'];
							} else {
								foreach( $_POST['report_id'] as $mail ) {
									$wpdb->query( "UPDATE `" . $wpdb->base_prefix . "mlq_mail_send` SET `trash_status`=0 WHERE `mail_send_id`=" . $mail );
									if ( $wpdb->last_error ) { 
										$error ++;
									} else {
										$done ++;
									}
									/* set message */
									if ( 0 == $error ) {
										$action_message['done'] = sprintf( _nx( __( 'Mail was restored.', 'email-queue'),	'%s&nbsp;' . __( 'Mails were restored.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
									} else {
										$action_message['error'] = $message_list['mail_untrash_error'] . '<br />' . $message_list['try_later'];
									}
								}
								/* register cron hook in case trashed mail needs to be sent*/
								mlq_cron_hook_activate();
							}
						}
						break;
					case 'delete_report':
						if ( check_admin_referer( 'delete_mail_' . $_GET['report_id'] ) ) {
							if ( empty( $_GET['report_id'] ) ) {
								$action_message['error'] = $message_list['empty_mails_list'];
							} else {
								$mail = $_GET['report_id'];
								/* delete all records with email addresses from mlq_mail_users table */
								$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "mlq_mail_users` WHERE `id_mail`=" . $mail );
								if ( $wpdb->last_error ) {
									$error ++;
								} else {
									$done ++;
								}
								/* delete mail message from mlq_mail_send table */
								$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "mlq_mail_send` WHERE `mail_send_id`=" . $mail );
								if ( $wpdb->last_error ) { 
									$mail_error ++;
								} else {
									$mail_done ++;
								}
								/* set message */
								if ( 0 == $error && 0 == $mail_error ) {
									$action_message['done'] = sprintf( _nx( __( 'Mail was deleted.', 'email-queue'), '%s&nbsp;' . __( 'Mails were deleted.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
								} else {
									if ( 0 != $error ) {
										$action_message['error'] = $message_list['receiver_delete_error'] . '<br />' . $message_list['try_later'];
									} elseif ( 0 != $mail_error ) {
										$action_message['error'] = $message_list['mail_delete_error'] . '<br />' . $message_list['try_later'];
									}
								}
							}
						}
						break;
					case 'delete_reports':
						if ( check_admin_referer( plugin_basename( __FILE__ ), 'mlq_nonce_name' ) ) {
							if ( empty( $_POST['report_id'] ) ) {
								$action_message['error'] = $message_list['empty_mails_list'];
							} else {
								foreach ( $_POST['report_id'] as $mail ) {
									/* delete all records with email addresses from mlq_mail_users table */
									$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "mlq_mail_users` WHERE `id_mail`=" . $mail );
									if ( $wpdb->last_error ) {
										$error ++;
									} else {
										$done ++;
									}
									/* delete mail message from mlq_mail_send table */
									$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "mlq_mail_send` WHERE `mail_send_id`=" . $mail );
									if ( $wpdb->last_error ) { 
										$mail_error ++;
									} else {
										$mail_done ++;
									}
								}
								/* set message */
								if ( 0 == $error && 0 == $mail_error ) {
									$action_message['done'] = sprintf( _nx( __( 'Mail was deleted.', 'email-queue'), '%s&nbsp;' . __( 'Mails were deleted.', 'email-queue'), $done, 'email-queue' ), number_format_i18n( $done ) );
								} else {
									if ( 0 != $error ) {
										$action_message['error'] = $message_list['receiver_delete_error'] . '<br />' . $message_list['try_later'];
									} elseif ( 0 != $mail_error ) {
										$action_message['error'] = $message_list['mail_delete_error'] . '<br />' . $message_list['try_later'];
									}
								}
							}
						}
						break;
					case 'show_report':
					default:
						break;
				}
			} 
		}
		return $action_message;
	}
}

/**
 * Performed at deactivation.
 * @return void
 */
if ( ! function_exists( 'mlq_send_deactivate' ) ) {
	function mlq_send_deactivate() {
		/* Delete cron hooks */
		wp_clear_scheduled_hook( 'mlq_mail_hook' );
		wp_clear_scheduled_hook( 'mlq_mail_delete_hook' );
	}
}

/**
 * Performed at uninstal.
 * @return void
 */
if ( ! function_exists( 'mlq_send_uninstall' ) ) {
	function mlq_send_uninstall() {
		global $wpdb;
		/* Delete cron hooks */
		wp_clear_scheduled_hook( 'mlq_mail_hook' );
		wp_clear_scheduled_hook( 'mlq_mail_delete_hook' );
		/* drop database tables of our plugin */
		$mlq_sql = "DROP TABLE `" . $wpdb->base_prefix . "mlq_mail_send`, `" . $wpdb->base_prefix . "mlq_mail_users`, `" . $wpdb->base_prefix . "mlq_mail_plugins`;";
		$wpdb->query( $mlq_sql );
		/* delete plugin options */
		delete_site_option( 'mlq_options' );
		delete_option( 'mlq_options' );
	}
}

/**
 * Add all hooks
 */
/* actions on activation */
register_activation_hook( plugin_basename( __FILE__ ), 'mlq_send_activate' );
/* add action links to plugin page */
add_filter( 'plugin_action_links', 'mlq_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'mlq_register_plugin_links', 10, 2 );
/* add menu on dashboard */
if ( function_exists( 'is_multisite' ) ) {
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', 'mlq_admin_default_setup' );
	} else {
		add_action( 'admin_menu', 'mlq_admin_default_setup' );
	}
}
/* admin settings, styles and scripts */
add_action( 'init', 'mlq_init' );
add_action( 'admin_init', 'mlq_admin_init' );
add_action( 'admin_enqueue_scripts', 'mlq_admin_head' );
/* grab mail data from other plugins */
add_action( 'cntctfrm_get_mail_data_for_mlq', 'mlq_get_mail_data_from_contact_form', 10, 6 );
add_action( 'cntctfrmpr_get_mail_data_for_mlq', 'mlq_get_mail_data_from_contact_form_pro', 10, 7 );
add_action( 'sndr_get_mail_data', 'mlq_get_mail_data_from_sender', 10, 5 );
add_action( 'sndrpr_get_data_start_mailout', 'mlq_start_mailout_from_sender_pro', 10, 2 );
add_action( 'sbscrbr_get_mail_data', 'mlq_get_mail_data_from_subscriber', 10, 5 );
add_action( 'sbscrbrpr_get_mail_data', 'mlq_get_mail_data_from_subscriber', 10, 5 );
add_action( 'pdtr_get_mail_data', 'mlq_get_mail_data_from_updater', 10, 5 );
add_action( 'mlq_get_mail_data_for_email_queue', 'mlq_get_mail_data_for_email_queue_and_save', 10, 6 );
/* add external plugin info into our plugin's table of mail plugins */
add_action( 'mlq_add_extra_plugin_to_mail_queue', 'mlq_add_extra_plugin_to_mail_queue', 10, 1 );
/* add mail functions that work with cron */
add_filter( 'cron_schedules', 'mlq_more_reccurences' );
add_action( 'mlq_mail_hook', 'mlq_cron_mail' );
add_action( 'mlq_mail_delete_hook', 'mlq_cron_mail_clear' );
add_filter( 'set-screen-option', 'mlq_table_set_option', 10, 3 );
/* actions on deactivation and unistallation */
register_deactivation_hook( plugin_basename( __FILE__ ), 'mlq_send_deactivate' );
register_uninstall_hook( plugin_basename( __FILE__ ), 'mlq_send_uninstall' );
