<?php
/**
 * Click Counter Button
 *
 * @package   Click_Counter_Button
 * @author    Rafael dos Santos <contato@rafael.work>
 * @license   GPL-2.0+
 * @link      https://github.com/rafael-business/click-counter-btn
 * @copyright 2023 Rafael dos Santos, Rafael Business ME
 *
 * @wordpress-plugin
 * Plugin Name:       Click Counter Button
 * Plugin URI:        https://github.com/rafael-business/click-counter-btn
 * Description:       Creates a button that counts your clicks in the WordPress database.
 * Version:           1.0.0
 * Author:            Rafael dos Santos
 * Author URI:        https://rafael.work
 * Text Domain:       click-counter-btn
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/rafael-business/click-counter-btn
 * GitHub Branch:     master
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'CCB_VER' ) ) {
	define( 'CCB_VER', '1.0.0' );
}

class Click_Counter_Button
{

	/**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct() {
		// back end
		add_action( 'plugins_loaded', array( $this, 'textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        add_action( 'widgets_init', array( $this, 'register_click_counter_widget' ) );
        add_action( 'rest_api_init', array( $this, 'register_api_route' ) );
        register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );

		// front end
		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ), 10 );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return Click_Counter_Button
	 */

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	/**
	 * load textdomain
	 *
	 * @return void
	 */

	public function textdomain() {

		load_plugin_textdomain( 'click-counter-btn', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Admin styles
	 *
	 * @return void
	 */

	public function admin_scripts() {

		$screen	= get_current_screen();

		wp_enqueue_style( 'ccb-admin', plugins_url('css/admin.css', __FILE__), array(), CCB_VER, 'all' );

	}

    /**
	 * call front-end CSS
	 *
	 * @return void
	 */

	public function front_scripts() {

		wp_enqueue_style( 'ccb-front', plugins_url( 'css/front.css', __FILE__ ), array(), CCB_VER, 'all' );

        wp_enqueue_script( 'click-counter-script', plugins_url( '/js/click-counter-script.js', __FILE__ ), array( 'jquery' ), CCB_VER, true );
        
        wp_localize_script( 'click-counter-script', 'click_counter_vars', array(
            'api_url' => esc_url( rest_url( 'click-counter/v1/record-click/' ) ),
        ) );

	}

    public function register_click_counter_widget() {
        
        require_once plugin_dir_path( __FILE__ ) . 'click-counter-widget.php';
        register_widget( 'Click_Counter_Widget' );
    }

    public function register_api_route() {
        register_rest_route( 'click-counter/v1', '/record-click/', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'record_click_callback' ),
        ) );
    }

    public function record_click_callback( $data ) {
        $inserted = $this->insert_click_record();

        if ( $inserted ) {
            $response = array(
                'success' => true,
                'message' => __( 'Click recorded successfully.', 'click-counter-btn' ),
            );
        } else {
            $response = array(
                'success' => false,
                'message' => __( 'Failed to register the click.', 'click-counter-btn' ),
            );
        }

        //sleep(5);
        
        return rest_ensure_response( $response );
    }

    public function activate_plugin() {
        $table_created = $this->create_custom_table();

        if ( $table_created ) {
            $this->save_log('Tabela do plugin Click_Counter_Button criada com sucesso.');
        } else {
            $this->save_log('Falha ao criar a tabela do plugin Click_Counter_Button.');
        }
    }

    public function save_log( $message ) {
        $log_file_path = plugin_dir_path( __FILE__ ) . 'click_counter_log.txt';

        $log_message = '[' . date( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL;

        file_put_contents( $log_file_path, $log_message, FILE_APPEND | LOCK_EX );
    }

    private function create_custom_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'click_counter';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                click_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            $result = dbDelta( $sql );

            if ( $result === false ) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function insert_click_record() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'click_counter';

        $result = $wpdb->insert(
            $table_name,
            array('click_date' => current_time( 'mysql' )),
            array('%s')
        );
        
        return $result !== false;
    }
}

$Click_Counter_Button = Click_Counter_Button::getInstance();