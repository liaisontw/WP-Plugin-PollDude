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
					//poll_dude_activate();
					self::activation();
					restore_current_blog();
				}
			}
		} else {
			//poll_dude_activate();
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
		$create_table['pollsq'] = "CREATE TABLE $wpdb->pollsq (".
								"pollq_id int(10) NOT NULL auto_increment," .
								"pollq_question varchar(200) character set utf8 NOT NULL default ''," .
								"pollq_timestamp varchar(20) NOT NULL default ''," .
								"pollq_totalvotes int(10) NOT NULL default '0'," .
								"pollq_active tinyint(1) NOT NULL default '1'," .
								"pollq_expiry int(10) NOT NULL default '0'," .
								"pollq_multiple tinyint(3) NOT NULL default '0'," .
								"pollq_totalvoters int(10) NOT NULL default '0'," .
								"PRIMARY KEY  (pollq_id)" .
								") $charset_collate;";
		$create_table['pollsa'] = "CREATE TABLE $wpdb->pollsa (" .
								"polla_aid int(10) NOT NULL auto_increment," .
								"polla_qid int(10) NOT NULL default '0'," .
								"polla_answers varchar(200) character set utf8 NOT NULL default ''," .
								"polla_votes int(10) NOT NULL default '0'," .
								"PRIMARY KEY  (polla_aid)" .
								") $charset_collate;";
		$create_table['pollsip'] = "CREATE TABLE $wpdb->pollsip (" .
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
		dbDelta( $create_table['pollsq'] );
		dbDelta( $create_table['pollsa'] );
		dbDelta( $create_table['pollsip'] );
		// Check Whether It is Install Or Upgrade
		$first_poll = $wpdb->get_var( "SELECT pollq_id FROM $wpdb->pollsq LIMIT 1" );
		// If Install, Insert 1st Poll Question With 5 Poll Answers
		if ( empty( $first_poll ) ) {
			// Insert Poll Question (1 Record)
			$insert_pollq = $wpdb->insert( $wpdb->pollsq, array( 'pollq_question' => __( 'How Is My Site?', 'poll-dude-domain' ), 'pollq_timestamp' => current_time( 'timestamp' ) ), array( '%s', '%s' ) );
			if ( $insert_pollq ) {
				// Insert Poll Answers  (5 Records)
				$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Good', 'poll-dude-domain' ) ), array( '%d', '%s' ) );
				$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Excellent', 'poll-dude-domain' ) ), array( '%d', '%s' ) );
				$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Bad', 'poll-dude-domain' ) ), array( '%d', '%s' ) );
				$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Can Be Improved', 'poll-dude-domain' ) ), array( '%d', '%s' ) );
				$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'No Comments', 'poll-dude-domain' ) ), array( '%d', '%s' ) );
			}
		}
		add_option('poll_template_disable', __('Sorry, there are no polls available at the moment.', 'poll-dude-domain'));
		add_option('poll_template_error', __('An error has occurred when processing your poll.', 'poll-dude-domain'));

		add_option('poll_currentpoll', 0);
		add_option('poll_latestpoll', 1);
		add_option('poll_archive_perpage', 5);
		add_option('poll_ans_sortby', 'polla_aid');
		add_option('poll_ans_sortorder', 'asc');
		add_option('poll_ans_result_sortby', 'polla_votes');
		add_option('poll_ans_result_sortorder', 'desc');
		// Database Upgrade For WP-Polls 2.1
		add_option('poll_logging_method', '3');
		add_option('poll_allowtovote', '2');
		// Database Upgrade For WP-Polls 2.12
		add_option('poll_archive_url', site_url('pollsarchive'));
		// Database Upgrade For WP-Polls 2.13
		add_option('poll_bar', array('style' => 'default', 'background' => 'd8e1eb', 'border' => 'c8c8c8', 'height' => 8));
		// Database Upgrade For WP-Polls 2.14
		add_option('poll_close', 1);
		// Database Upgrade For WP-Polls 2.20
		add_option('poll_ajax_style', array('loading' => 1, 'fading' => 1));
		add_option('poll_template_pollarchivelink', '<ul>'.
		'<li><a href="%POLL_ARCHIVE_URL%">'.__('Polls Archive', 'poll-dude-domain').'</a></li>'.
		'</ul>');
		add_option('poll_archive_displaypoll', 2);
		add_option('poll_template_pollarchiveheader', '');
		add_option('poll_template_pollarchivefooter', '<p>'.__('Start Date:', 'poll-dude-domain').' %POLL_START_DATE%<br />'.__('End Date:', 'poll-dude-domain').' %POLL_END_DATE%</p>');

		$pollq_totalvoters = (int) $wpdb->get_var( "SELECT SUM(pollq_totalvoters) FROM $wpdb->pollsq" );
		if ( 0 === $pollq_totalvoters ) {
			$wpdb->query( "UPDATE $wpdb->pollsq SET pollq_totalvoters = pollq_totalvotes" );
		}

		// Database Upgrade For WP-Polls 2.30
		add_option('poll_cookielog_expiry', 0);
		add_option('poll_template_pollarchivepagingheader', '');
		add_option('poll_template_pollarchivepagingfooter', '');
		// Database Upgrade For WP-Polls 2.50
		delete_option('poll_archive_show');

		// Index
		$index = $wpdb->get_results( "SHOW INDEX FROM $wpdb->pollsip;" );
		$key_name = array();
		if( count( $index ) > 0 ) {
			foreach( $index as $i ) {
				$key_name[]= $i->Key_name;
			}
		}
		if ( ! in_array( 'pollip_ip', $key_name, true ) ) {
			$wpdb->query( "ALTER TABLE $wpdb->pollsip ADD INDEX pollip_ip (pollip_ip);" );
		}
		if ( ! in_array( 'pollip_qid', $key_name, true ) ) {
			$wpdb->query( "ALTER TABLE $wpdb->pollsip ADD INDEX pollip_qid (pollip_qid);" );
		}
		if ( ! in_array( 'pollip_ip_qid_aid', $key_name, true ) ) {
			$wpdb->query( "ALTER TABLE $wpdb->pollsip ADD INDEX pollip_ip_qid_aid (pollip_ip, pollip_qid, pollip_aid);" );
		}
		// No longer needed index
		if ( in_array( 'pollip_ip_qid', $key_name, true ) ) {
			$wpdb->query( "ALTER TABLE $wpdb->pollsip DROP INDEX pollip_ip_qid;" );
		}

		// Change column datatype for wp_pollsip
		$col_pollip_qid = $wpdb->get_row( "DESCRIBE $wpdb->pollsip pollip_qid" );
		if( 'varchar(10)' === $col_pollip_qid->Type ) {
			$wpdb->query( "ALTER TABLE $wpdb->pollsip MODIFY COLUMN pollip_qid int(10) NOT NULL default '0';" );
			$wpdb->query( "ALTER TABLE $wpdb->pollsip MODIFY COLUMN pollip_aid int(10) NOT NULL default '0';" );
			$wpdb->query( "ALTER TABLE $wpdb->pollsip MODIFY COLUMN pollip_timestamp int(10) NOT NULL default '0';" );
			$wpdb->query( "ALTER TABLE $wpdb->pollsq MODIFY COLUMN pollq_expiry int(10) NOT NULL default '0';" );
		}

		// Set 'manage_polls' Capabilities To Administrator
		$role = get_role( 'administrator' );
		if( ! $role->has_cap( 'manage_polls' ) ) {
			$role->add_cap( 'manage_polls' );
		}
		
	}

}


