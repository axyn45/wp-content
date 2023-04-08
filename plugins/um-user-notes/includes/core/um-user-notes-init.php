<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'UM_Notes' ) ) {


	/**
	 * Class UM_Notes
	 */
	class UM_Notes extends UM_Notes_Functions {


		/**
		 * @var
		 */
		private static $instance;


		/**
		 * @return UM_Notes
		 */
		static public function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->_um_notes_construct();
			}

			return self::$instance;
		}


		/**
		 * UM_Notes constructor.
		 *
		 * @since 1.0
		 */
		function __construct() {
			parent::__construct();
		}


		/**
		 * Notes constructor.
		 */
		function _um_notes_construct() {
			add_filter( 'um_call_object_Notes', [ &$this, 'get_this' ] );
			add_filter( 'um_settings_default_values', [ &$this, 'default_settings' ], 10, 1 );

			$this->post_type();

			if ( UM()->is_request( 'ajax' ) ) {
				$this->admin();
				$this->ajax();
			} elseif ( UM()->is_request( 'admin' ) ) {
				$this->admin();
			} elseif ( UM()->is_request( 'frontend' ) ) {
				$this->enqueue();
			}

			$this->profile();
			$this->activity();
		}


		/**
		 * @return $this
		 */
		function get_this() {
			return $this;
		}


		/**
		 * @param $defaults
		 *
		 * @return array
		 */
		function default_settings( $defaults ) {
			$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
			return $defaults;
		}


		/**
		 * @return um_ext\um_user_notes\core\Setup()
		 */
		function setup() {
			if ( empty( UM()->classes['um_user_notes_setup'] ) ) {
				UM()->classes['um_user_notes_setup'] = new um_ext\um_user_notes\core\Setup();
			}
			return UM()->classes['um_user_notes_setup'];
		}


		/**
		 * @return um_ext\um_user_notes\admin\Admin()
		 */
		function admin() {
			if ( empty( UM()->classes['um_user_notes_admin'] ) ) {
				UM()->classes['um_user_notes_admin'] = new um_ext\um_user_notes\admin\Admin();
			}
			return UM()->classes['um_user_notes_admin'];
		}


		/**
		 * @return um_ext\um_user_notes\core\PostType()
		 */
		function post_type() {
			if ( empty( UM()->classes['um_user_notes_post_type'] ) ) {
				UM()->classes['um_user_notes_post_type'] = new um_ext\um_user_notes\core\PostType();
			}
			return UM()->classes['um_user_notes_post_type'];
		}


		/**
		 * @return um_ext\um_user_notes\core\Enqueue()
		 */
		function enqueue() {
			if ( empty( UM()->classes['um_user_notes_enqueue'] ) ) {
				UM()->classes['um_user_notes_enqueue'] = new um_ext\um_user_notes\core\Enqueue();
			}
			return UM()->classes['um_user_notes_enqueue'];
		}


		/**
		 * @return um_ext\um_user_notes\core\Profile()
		 */
		function profile() {
			if ( empty( UM()->classes['um_user_notes_profile'] ) ) {
				UM()->classes['um_user_notes_profile'] = new um_ext\um_user_notes\core\Profile();
			}
			return UM()->classes['um_user_notes_profile'];
		}


		/**
		 * @return um_ext\um_user_notes\core\Ajax()
		 */
		function ajax() {
			if ( empty( UM()->classes['um_user_notes_ajax'] ) ) {
				UM()->classes['um_user_notes_ajax'] = new um_ext\um_user_notes\core\Ajax();
			}
			return UM()->classes['um_user_notes_ajax'];
		}


		/**
		 * @return bool|um_ext\um_user_notes\core\Activity()
		 */
		function activity() {
			if ( ! class_exists( 'UM_Activity_API' ) ) {
				return false;
			}

			if ( empty( UM()->classes['um_user_notes_activity'] ) ) {
				UM()->classes['um_user_notes_activity'] = new um_ext\um_user_notes\core\Activity();
			}

			return UM()->classes['um_user_notes_activity'];
		}
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_notes', -10, 1 );
function um_init_notes() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Notes', true );
	}
}