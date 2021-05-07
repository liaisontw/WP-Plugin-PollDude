<?php
namespace poll_dude;

class Poll_Dude_Utility {
    
	public function removeslashes( $string ) {
		//$string = implode( '', explode( '\\', $string ) );
		return stripslashes( trim( $string ) );
	}

    public function time_make($fieldname /*= 'pollq_timestamp'*/) {
        $time_parse = array('_hour'   => 0, '_minute' => 0, '_second' => 0,
                            '_day'    => 0, '_month'  => 0, '_year'   => 0
        );

        foreach($time_parse as $key => $value) {
            $time_parse[$key] = isset( $_POST[$fieldname.$key] ) ? 
                    (int) sanitize_key( $_POST[$fieldname.$key] ) : 0;
        }

        $return_timestamp = gmmktime( $time_parse['_hour']  , 
                                    $time_parse['_minute'], 
                                    $time_parse['_second'], 
                                    $time_parse['_month'] , 
                                    $time_parse['_day']   , 
                                    $time_parse['_year']   );

        return 	$return_timestamp;
    }

    public function time_select($poll_dude_time, 
                                $fieldname = 'pollq_timestamp', 
                                $display = 'block') {
	
        $time_select = array(
            '_hour'   => array('unit'=>'H', 'min'=>0   , 'max'=>24  , 'padding'=>'H:'),
            '_minute' => array('unit'=>'i', 'min'=>0   , 'max'=>61  , 'padding'=>'M:'),
            '_second' => array('unit'=>'s', 'min'=>0   , 'max'=>61  , 'padding'=>'S@'),
            '_day'    => array('unit'=>'j', 'min'=>0   , 'max'=>32  , 'padding'=>'D&nbsp;'),
            '_month'  => array('unit'=>'n', 'min'=>0   , 'max'=>13  , 'padding'=>'M&nbsp;'),
            '_year'   => array('unit'=>'Y', 'min'=>2010, 'max'=>2030, 'padding'=>'Y')
        );

        echo '<div id="'.$fieldname.'" style="display: '.$display.'">'."\n";
        echo '<span dir="ltr">'."\n";

        foreach($time_select as $key => $value) {
            $time_value = (int) gmdate($value['unit'], $poll_dude_time);
            $time_stamp = $fieldname.$key;
            echo "<select name=\"$time_stamp\" size=\"1\">"."\n";
            for($i = $value['min']; $i < $value['max']; $i++) {
                if($time_value === $i) {
                    echo "<option value=\"$i\" selected=\"selected\">$i</option>\n";
                } else {
                    echo "<option value=\"$i\">$i</option>\n";
                }
            }
            echo '</select>&nbsp;'.$value['padding']."\n";		
        }

        echo '</span>'."\n";
        echo '</div>'."\n";
    }

    ### Funcion: Get Latest Poll ID
    public function latest_poll() {
        global $wpdb;
	    $poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->pollsq WHERE pollq_active = 1 ORDER BY pollq_timestamp DESC LIMIT 1");
	    return (int) $poll_id;
    }

    ### Function: Check Who Is Allow To Vote
    public function vote_allow() {
        global $user_ID;
        $user_ID = (int) $user_ID;
        $allow_to_vote = (int) get_option( 'poll_allowtovote' );
        switch($allow_to_vote) {
            // Guests Only
            case 0:
                if($user_ID > 0) {
                    return false;
                }
                return true;
                break;
            // Registered Users Only
            case 1:
                if($user_ID === 0) {
                    return false;
                }
                return true;
                break;
            // Registered Users And Guests
            case 2:
            default:
                return true;
        }
    }

    ### Funcrion: Check Voted By Cookie Or IP
    public function is_voted($poll_id) {
        $poll_logging_method = (int) get_option( 'poll_logging_method' );
        switch($poll_logging_method) {
            // Do Not Log
            case 0:
                return 0;
                break;
            // Logged By Cookie
            case 1:
                return $this->voted_cookie($poll_id);
                break;
            // Logged By IP
            case 2:
                return $this->voted_ip($poll_id);
                break;
            // Logged By Cookie And IP
            case 3:
                $voted_cookie = $this->voted_cookie($poll_id);
                if(!empty($voted_cookie)) {
                    return $voted_cookie;
                }
                return $this->voted_ip($poll_id);
                break;
            // Logged By Username
            case 4:
                return $this->voted_username($poll_id);
                break;
        }
    }

    ### Function: Check Voted By Cookie
    public function voted_cookie($poll_id ) {
        $get_voted_aids = 0;
        if ( ! empty( $_COOKIE[ 'voted_' . $poll_id ] ) ) {
            $get_voted_aids = explode( ',', $_COOKIE[ 'voted_' . $poll_id ] );
            $get_voted_aids = array_map( 'intval', array_map( 'sanitize_key', $get_voted_aids ) );
        }
        return $get_voted_aids;
    }

    ### Function: Check Voted By IP
    public function voted_ip( $poll_id ) {
        global $wpdb;
        $log_expiry = (int) get_option( 'poll_cookielog_expiry' );
        $log_expiry_sql = '';
        if( $log_expiry > 0 ) {
            $log_expiry_sql = ' AND (' . current_time('timestamp') . '-(pollip_timestamp+0)) < ' . $log_expiry;
        }
        // Check IP From IP Logging Database
        $get_voted_aids = $wpdb->get_col( $wpdb->prepare( "SELECT pollip_aid FROM $wpdb->pollsip WHERE pollip_qid = %d AND (pollip_ip = %s OR pollip_ip = %s)", $poll_id, $this->hash_ipaddr(), $this->get_ipaddr() ) . $log_expiry_sql );
        if( $get_voted_aids ) {
            return $get_voted_aids;
        }

        return 0;
    }

    ### Function: Check Voted By Username
    public function voted_username($poll_id) {
        global $wpdb, $user_ID;
        // Check IP If User Is Guest
        if ( ! is_user_logged_in() ) {
            return 1;
        }
        $pollsip_userid = (int) $user_ID;
        $log_expiry = (int) get_option( 'poll_cookielog_expiry' );
        $log_expiry_sql = '';
        if( $log_expiry > 0 ) {
            $log_expiry_sql = 'AND (' . current_time('timestamp') . '-(pollip_timestamp+0)) < ' . $log_expiry;
        }
        // Check User ID From IP Logging Database
        $get_voted_aids = $wpdb->get_col( $wpdb->prepare( "SELECT pollip_aid FROM $wpdb->pollsip WHERE pollip_qid = %d AND pollip_userid = %d", $poll_id, $pollsip_userid ) . $log_expiry_sql );
        if($get_voted_aids) {
            return $get_voted_aids;
        } else {
            return 0;
        }
    }

    ### Function: Get IP Address
    public function get_ipaddr() {
        foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                foreach ( explode( ',', $_SERVER[$key] ) as $ip ) {
                    $ip = trim( $ip );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
                        return esc_attr( $ip );
                    }
                }
            }
        }
    }

    public function hash_ipaddr() {
        return apply_filters( 'wp_polls_ipaddress', wp_hash( $this->get_ipaddr() ) );
    }

    public function get_hostname() {
        $hostname = gethostbyaddr( $this->get_ipaddr() );
        if ( $hostname === $this->get_ipaddr() ) {
            $hostname = wp_privacy_anonymize_ip( $this->get_ipaddr() );
        }

        if ( false !== $hostname ) {
            $hostname = substr( $hostname, strpos( $hostname, '.' ) + 1 );
        }

        return apply_filters( 'wp_polls_hostname', $hostname );
    }

    ### Check If In Poll Archive Page
    function in_pollarchive() {
        $poll_archive_url = get_option('poll_archive_url');
        $poll_archive_url_array = explode('/', $poll_archive_url);
        $poll_archive_url = $poll_archive_url_array[count($poll_archive_url_array)-1];
        if(empty($poll_archive_url)) {
            $poll_archive_url = $poll_archive_url_array[count($poll_archive_url_array)-2];
        }
        $current_url = esc_url_raw( $_SERVER['REQUEST_URI'] );
        if(strpos($current_url, $poll_archive_url) === false) {
            return false;
        }

        return true;
    }
}