<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Booking_Export
 * @subpackage Booking_Export/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Booking_Export
 * @subpackage Booking_Export/admin
 * @author     Your Name <email@example.com>
 */
class Booking_Export_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $booking_export    The ID of this plugin.
	 */
	private $booking_export;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $booking_export       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $booking_export, $version ) {

		$this->booking_export = $booking_export;
		$this->version = $version;

		add_action( 'wpbc_menu_created', array(&$this, 'register_sub_menu') );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Booking_Export_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Booking_Export_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();
		
		if ( in_array( $screen->id, array( 'reservation15_page_wpbc-dashboard' ) ) ) {
			wp_enqueue_style( $this->booking_export . '-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->booking_export . '-bootstrap-datatable', 'https://cdn.datatables.net/v/bs4/dt-1.10.24/datatables.min.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( $this->booking_export, plugin_dir_url( __FILE__ ) . 'css/booking-export-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Booking_Export_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Booking_Export_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();
		
		if ( in_array( $screen->id, array( 'reservation15_page_wpbc-dashboard' ) ) ) {
			wp_enqueue_script( $this->booking_export . '-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->booking_export . '-bootstrap-datatable', 'https://cdn.datatables.net/v/bs4/dt-1.10.24/datatables.min.js', array( 'jquery' ), $this->version, false );
		}
		wp_enqueue_script( $this->booking_export, plugin_dir_url( __FILE__ ) . 'js/booking-export-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function register_sub_menu($menu_tags) {
		add_submenu_page( $menu_tags, 'Tableau de bord', 'Tableau de bord', 'manage_options', 'wpbc-dashboard', array(&$this, 'submenu_dashboard_page_callback') );
		add_submenu_page( $menu_tags, 'Propriétaire', 'Propriétaire', 'manage_options', 'edit.php?post_type=owner', null );
	}

	public function submenu_dashboard_page_callback() {
		$start_date = "2021-03-01 00:00:00";
		$end_date   = "2021-03-31 23:59:59";

		$bookings = Booking_Class_Admin::get_booking_by_period($start_date, $end_date);

		$path = plugin_dir_path( __FILE__ );
		include $path . 'partials/booking-export-admin-display-dashboard.php';
	}

	public function init() {
		$args = array(
			'label'                => 'Propriétaires',
			'public'               => false,
			'publicly_queryable'   => true,
			'show_ui'              => true,
			'show_in_menu'         => false,
			'query_var'            => true,
			'rewrite'              => array( 'slug' => 'owner' ),
			'capability_type'      => 'post',
			'has_archive'          => true,
			'hierarchical'         => false,
			'menu_position'        => null,
			'supports'             => array( 'title' ),
			'register_meta_box_cb' => array( &$this, 'add_event_metaboxes' ),
		);

		register_post_type( 'owner', $args );
	}

	public function add_event_metaboxes() {
		/**
	  * Adds a metabox to the right side of the screen under the â€œPublishâ€ box
		*/
		add_meta_box(
			'information',
			'Informations',
			array( &$this, 'metabox_information_callback' ),
			'owner',
			'normal',
			'default'
		);
	}

	public function metabox_information_callback() {
		global $post;

		wp_nonce_field( basename( __FILE__ ), 'event_fields' );

		$wpbc_br_table = new WPBC_BR_Table( 'resources' );
		$resources_def = $wpbc_br_table->get_linear_data_for_one_page();

		array_unshift($resources_def, [
			'id'    => '0',
			'title' => 'Sélectionner une ressource',
		]);

		$resources = get_post_meta( $post->ID, 'resources', true ) ?: [
			[
				'id' => 0,
				'price_comission' => 0,
			]
		];

		$path = plugin_dir_path( __FILE__ );
		include $path . 'partials/booking-export-admin-display-owner-information-metabox.php';
	}

	public function save_post( $post_id, $post ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( ! isset( $_POST['resources'] ) || ! wp_verify_nonce( $_POST['event_fields'], basename(__FILE__) ) ) {
			return $post_id;
		}

		if ( 'revision' === $post->post_type ) {
			return;
		}

		$resources = (array) $_POST['resources'];
		$resources = array_values($resources);

		if ( get_post_meta( $post_id, 'resources', false ) ) {
			update_post_meta( $post_id, 'resources', $resources );
		} else {
			add_post_meta( $post_id, 'resources', $resources);
		}

		if ( ! $resources ) {
			delete_post_meta( $post_id, 'resources' );
		}
	}

	public function admin_post_filter_by_period() {
		wp_verify_nonce('filter_by_period');

		wp_redirect( admin_url( 'admin.php?page=wpbc-dashboard' ) );
	}
}
