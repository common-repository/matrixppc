<?php
/**
 * The MatrixPPC core plugin class.
 *
 * @since      1.0.0
 * @package    MatrixPPC
 * @subpackage MatrixPPC/includes
 * @author     MatrixPPC <support@matrixppc.ai>
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

class MatrixPPC {
	/**
	 * @since    1.0.0
	 * @access   protected
	 * @var      MatrixPPC_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

    /**
     * The API.
     * @since    1.0.0
     * @access   protected
     * @var      MatrixPPC_API    $api
     */
    protected $api;

	/**
	 * The reactor.
	 * @since    1.0.0
	 * @access   protected
	 * @var      MatrixPPC_Reactor    $reactor
	 */
	protected $reactor;
	
	/**
	 * The unique identifier of this plugin.
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $MatrixPPC    The string used to uniquely identify this plugin.
	 */
	protected $MatrixPPC;
	
	/**
	 * The current version of the plugin.
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * This function sets the plugin name and the plugin version that can be used throughout the plugin.
	 * Loads the dependencies, defines the locale, and sets the hooks for the admin area and
	 * the public-facing side of the site.
	 * @since   1.0.0
     * @access  public
	 * @param   void
	 */
	public function __construct() {
        $this->MatrixPPC = 'matrixppc';
		$this->version = '1.0.0';
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}
	
	/**
	 * This function retrieves the version number of the plugin.
	 * @since   1.0.0
     * @access  public
	 * @param   void
	 * @return  string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * This function loads the required dependencies for this plugin.
	 * Includes the following files that make up the plugin:
	 * - MatrixPPC_Loader. Orchestrates the hooks of the plugin.
	 * - MatrixPPC_Admin. Defines all of the hooks for the admin area.
	 * Creates an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 * @since   1.0.0
	 * @access  private
	 * @param   void
	 * @return  void
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes'.DIRECTORY_SEPARATOR.'class-matrixppc-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin'.DIRECTORY_SEPARATOR.'class-matrixppc-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib'.DIRECTORY_SEPARATOR.'class-matrixppc-reactor.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib'.DIRECTORY_SEPARATOR.'class-matrixppc-utils.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib'.DIRECTORY_SEPARATOR.'class-matrixppc-api.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib'.DIRECTORY_SEPARATOR.'class-matrixppc-config.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib'.DIRECTORY_SEPARATOR.'class-matrixppc-db.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes'.DIRECTORY_SEPARATOR.'class-matrixppc-updater.php';

        $this->loader	= new MatrixPPC_Loader();
		$this->api		= MatrixPPC_Api::getInstance();
		$this->reactor  = MatrixPPC_Reactor::getInstance();
	}
	
	/**
	 * This function registers all of the hooks related to the admin area functionality
	 * of the plugin.
	 * @since   1.0.0
	 * @access  private
	 * @param   void
	 * @return  void
	 */
	private function define_admin_hooks() {
		$plugin_admin   = new MatrixPPC_Admin( $this->get_MatrixPPC(), $this->get_version() );
		$plugin_updater = new MatrixPPC_Updater( $this->get_version() );

		$this->loader->add_action( 'plugins_loaded',         $plugin_updater, 'check' );

		$this->loader->add_filter( 'plugin_action_links',		$plugin_admin, 'matrixppc_action_links', 10, 5);
		$this->loader->add_action( 'admin_menu',				$plugin_admin, 'matrixppc_add_menu_page' );
		$this->loader->add_action( 'admin_head',              $plugin_admin, 'matrixppc_add_css');
        $this->loader->add_action( 'admin_footer',            $plugin_admin, 'matrixppc_add_js');
		$this->loader->add_action( 'wp_ajax_matrixppc_ajax_actions', $plugin_admin, 'matrixppc_ajax_actions');
        //$this->loader->add_filter('cron_schedules', $plugin_admin, 'matrixppc_add_schedules');

        if(MatrixPPC_Utils::checkActiveAlgos()){
            $this->loader->add_filter('cron_schedules', $plugin_admin, 'matrixppc_add_schedules');
        }else{
            $this->loader->add_action("admin_notices", $plugin_admin,'matrixppc_disabled_notice');
        }

	}
	
	/** 
	 * This function registers all of the hooks related to the public-facing functionality
	 * of the plugin.
	 * @since   1.0.0
	 * @access  private
	 * @param   void
	 * @return  void
	 */
	private function define_public_hooks() {
        $this->loader->add_filter( 'cron_schedules',            $this->reactor, 'cronAddMatrixPPC', 99999 );

        $this->loader->add_action( 'wp_loaded',                 $this->reactor, 'detectAndSaveVisitor' );

        $this->loader->add_action( 'matrixppccronjob',			$this->reactor, 'send_data' );

        //$this->loader->add_action( 'template_redirect',         $this->reactor, 'template_redirect' , 0 );
    }
	
	/**
	 * This function runs the loader to execute all of the hooks with WordPress.
	 * @since   1.0.0
     * @access  public
	 * @param   void
	 * @return  void
	 */
	public function run() {
		$this->loader->run();
	}
	
	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 * @since   1.0.0
     * @access  public
	 * @param   void
	 * @return  string      The name of the plugin.
	 */
	public function get_MatrixPPC() {
		return $this->MatrixPPC;
	}
	
	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 * @since   1.0.0
     * @access  publix
	 * @param   void
	 * @return  MatrixPPC_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
}