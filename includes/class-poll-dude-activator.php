<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Poll_Dude_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate($network_wide) {
		if ( is_multisite() && $network_wide ) {
			$ms_sites = wp_get_sites();

			if( 0 < count( $ms_sites ) ) {
				foreach ( $ms_sites as $ms_site ) {
					switch_to_blog( $ms_site['blog_id'] );
					
					self::activation();
					restore_current_blog();
				}
			}
		} else {
			
			self::activation();
		}
	}

	private static function activation() {
		global $wpdb;

		if(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
			include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
		} elseif(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
			include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
		} else {
			die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
		}

		// Create Poll Tables (3 Tables)
		$charset_collate = $wpdb->get_charset_collate();

		$create_table = array();
		$create_table['polldude_q'] = "CREATE TABLE $wpdb->polldude_q (".
								"pollq_id int(10) NOT NULL auto_increment," .
								"pollq_question varchar(200) character set utf8 NOT NULL default ''," .
								"pollq_timestamp varchar(20) NOT NULL default ''," .
								"pollq_totalvotes int(10) NOT NULL default '0'," .
								"pollq_active tinyint(1) NOT NULL default '1'," .
								"pollq_expiry int(10) NOT NULL default '0'," .
								"pollq_multiple tinyint(3) NOT NULL default '0'," .
								"pollq_totalvoters int(10) NOT NULL default '0'," .
								"pollq_recaptcha tinyint(1) NOT NULL default '1',".
								"PRIMARY KEY  (pollq_id)" .
								") $charset_collate;";
		$create_table['polldude_a'] = "CREATE TABLE $wpdb->polldude_a (" .
								"polla_aid int(10) NOT NULL auto_increment," .
								"polla_qid int(10) NOT NULL default '0'," .
								"polla_answers varchar(200) character set utf8 NOT NULL default ''," .
								"polla_votes int(10) NOT NULL default '0'," .
								"polla_colors varchar(20) character set utf8 NOT NULL default '#0000FF'," .
								"PRIMARY KEY  (polla_aid)" .
								") $charset_collate;";
		$create_table['polldude_ip'] = "CREATE TABLE $wpdb->polldude_ip (" .
								"pollip_id int(10) NOT NULL auto_increment," .
								"pollip_qid int(10) NOT NULL default '0'," .
								"pollip_aid int(10) NOT NULL default '0'," .
								"pollip_ip varchar(100) NOT NULL default ''," .
								"pollip_host VARCHAR(200) NOT NULL default ''," .
								"pollip_timestamp int(10) NOT NULL default '0'," .
								"pollip_user tinytext NOT NULL," .
								"pollip_userid int(10) NOT NULL default '0'," .
								"PRIMARY KEY  (pollip_id)," .
								"KEY pollip_ip (pollip_ip)," .
								"KEY pollip_qid (pollip_qid)," .
								"KEY pollip_ip_qid (pollip_ip, pollip_qid)" .
								") $charset_collate;";
		dbDelta( $create_table['polldude_q'] );
		dbDelta( $create_table['polldude_a'] );
		dbDelta( $create_table['polldude_ip'] );
		// Check Whether It is Install Or Upgrade
		$first_poll = $wpdb->get_var( "SELECT pollq_id FROM $wpdb->polldude_q LIMIT 1" );
		// If Install, Insert 1st Poll Question With 5 Poll Answers
		if ( empty( $first_poll ) ) {
			// Insert Poll Question (1 Record)
			$insert_pollq = $wpdb->insert( $wpdb->polldude_q, array( 'pollq_question' => __( 'How Is My Site?', 'poll-dude' ), 'pollq_timestamp' => current_time( 'timestamp' ) ), array( '%s', '%s' ) );
			if ( $insert_pollq ) {
				// Insert Poll Answers  (5 Records)
				$wpdb->insert( $wpdb->polldude_a, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Good', 'poll-dude' ) ), array( '%d', '%s' ) );
				$wpdb->insert( $wpdb->polldude_a, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Excellent', 'poll-dude' ) ), array( '%d', '%s' ) );
				$wpdb->insert( $wpdb->polldude_a, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Bad', 'poll-dude' ) ), array( '%d', '%s' ) );
				$wpdb->insert( $wpdb->polldude_a, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Can Be Improved', 'poll-dude' ) ), array( '%d', '%s' ) );
				$wpdb->insert( $wpdb->polldude_a, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'No Comments', 'poll-dude' ) ), array( '%d', '%s' ) );
			}
		}
		add_option('pd_recaptcha_enable', 0);
		add_option('pd_recaptcha_sitekey',   '__abcdefghijklmnopqrstuvwxyz-0123456789_');
		add_option('pd_recaptcha_secretkey', '__abcdefghijklmnopqrstuvwxyz-0123456789_');
		add_option('pd_default_color', '#b0c3d4');
		add_option('pd_currentpoll', 0);
		add_option('pd_latestpoll', 1);
		add_option('pd_bar', array('style' => 'default', 'background' => 'b0c3d4', 'border' => 'b0c3d4', 'height' => 8));
		add_option('pd_close', 1);
		add_option('pd_ajax_style', array('loading' => 1, 'fading' => 1));
		$pollq_totalvoters = (int) $wpdb->get_var( "SELECT SUM(pollq_totalvoters) FROM $wpdb->polldude_q" );
		if ( 0 === $pollq_totalvoters ) {
			$wpdb->query( "UPDATE $wpdb->polldude_q SET pollq_totalvoters = pollq_totalvotes" );
		}

		add_option('pd_cookielog_expiry', 0);
		// Index
		$index = $wpdb->get_results( "SHOW INDEX FROM $wpdb->polldude_ip;" );
		$key_name = array();
		if( count( $index ) > 0 ) {
			foreach( $index as $i ) {
				$key_name[]= $i->Key_name;
			}
		}
		if ( ! in_array( 'pollip_ip', $key_name, true ) ) {
			$wpdb->query( "ALTER TABLE $wpdb->polldude_ip ADD INDEX pollip_ip (pollip_ip);" );
		}
		if ( ! in_array( 'pollip_qid', $key_name, true ) ) {
			$wpdb->query( "ALTER TABLE $wpdb->polldude_ip ADD INDEX pollip_qid (pollip_qid);" );
		}
		if ( ! in_array( 'pollip_ip_qid_aid', $key_name, true ) ) {
			$wpdb->query( "ALTER TABLE $wpdb->polldude_ip ADD INDEX pollip_ip_qid_aid (pollip_ip, pollip_qid, pollip_aid);" );
		}
		// No longer needed index
		if ( in_array( 'pollip_ip_qid', $key_name, true ) ) {
			$wpdb->query( "ALTER TABLE $wpdb->polldude_ip DROP INDEX pollip_ip_qid;" );
		}

		// Change column datatype for wp_polldude_ip
		$col_pollip_qid = $wpdb->get_row( "DESCRIBE $wpdb->polldude_ip pollip_qid" );
		if( 'varchar(10)' === $col_pollip_qid->Type ) {
			$wpdb->query( "ALTER TABLE $wpdb->polldude_ip MODIFY COLUMN pollip_qid int(10) NOT NULL default '0';" );
			$wpdb->query( "ALTER TABLE $wpdb->polldude_ip MODIFY COLUMN pollip_aid int(10) NOT NULL default '0';" );
			$wpdb->query( "ALTER TABLE $wpdb->polldude_ip MODIFY COLUMN pollip_timestamp int(10) NOT NULL default '0';" );
			$wpdb->query( "ALTER TABLE $wpdb->polldude_q MODIFY COLUMN pollq_expiry int(10) NOT NULL default '0';" );
		}

		// Set 'manage_polls' Capabilities To Administrator
		$role = get_role( 'administrator' );
		if( ! $role->has_cap( 'manage_polls' ) ) {
			$role->add_cap( 'manage_polls' );
		}
		
	}

}


