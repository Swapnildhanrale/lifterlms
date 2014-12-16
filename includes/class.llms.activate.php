<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Activation base class
*
* Class used for connecting to the codeBOX activation api to activate lifterLMS
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Activate {
 
    /**
     * Instance of API connection
     * @var null
     */
    protected static $instance = null;
 
    /**
     * Constructor
     * Adds action to lifterlms_update_options to trigger API call
     */
    private function __construct() {
        add_action( 'lifterlms_update_options', array( $this, 'get_post_response' ) );
    }
 
    /**
     * Gets current instance of API connection
     * @return static string [instance of current API connection]
     */
    public static function get_instance() {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
 
        return self::$instance;
 
    }
 
    /**
     * Interprets response from activation request.
     * If successful updates options and sets plugin to activated.
     * @return void
     */
    public function get_post_response( ) {
    	$is_active = get_option('lifterlms_is_activated', '');

      	$action = 'llms_activate_plugin';
        $authkey = get_option('lifterlms_authkey', '');
        $license = get_option('lifterlms_activation_key', '');
        $site_url = get_bloginfo('url');

        if (($license && $is_active == 'yes') || $license == '') {
        	return;
        }

        $url = 'https://lifterlms.com/wp-admin/admin-ajax.php';

        $response = wp_remote_post(
            $url,
            array(
                'body' => array(
                'action'   => $action,
                'authkey'     => $authkey,
                'license' => $license,
                'url' => $site_url
                ),
                'sslverify' => false
            )
        );

        if ( is_wp_error( $response ) ) {

        	update_option('lifterlms_activation_message', $activation_response->message);

        }
        else {

			$activation_response = json_decode ($response['body']);
			if ($activation_response->success) {
				update_option('lifterlms_is_activated', 'yes');
				update_option('lifterlms_update_key', $activation_response->update_key);
			}
			else {
				update_option('lifterlms_activation_message', $activation_response->message);
			}

        }
    }
}
