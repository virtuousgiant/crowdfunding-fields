<?php
/**
 * Plugin Name: Crowdfunding by Astoundify - Custom Fields
 * Plugin URI:  https://github.com/astoundify/crowdfunding-fields
 * Description: An example plugin for adding custom fields to the Crowdfunding submission form.
 * Author:      Astoundify
 * Author URI:  http://astoundify.com
 * Version:     1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Astoundify_Crowdfunding_Fields {

	/**
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Make sure only one instance is only running.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param void
	 * @return object $instance The one true class instance.
	 */
	public static function instance() {
		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Start things up.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param void
	 * @return void
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Set some smart defaults to class variables.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param void
	 * @return void
	 */
	private function setup_globals() {
		$this->file         = __FILE__;
		
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file ); 
	}

	/**
	 * Hooks and filters.
	 *
	 * We need to hook into a couple of things:
	 * 1. Output fields on frontend, and save.
	 * 2. Output fields on backend, and save.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param void
	 * @return void
	 */
	private function setup_actions() {
		/**
		 * Filter the default fields that ship with Crowdfunding.
		 * The `form_fields` method is what we use to add our own custom fields.
		 */
		add_filter( 'atcf_shortcode_submit_fields', array( $this, 'form_fields' ) );

		/**
		 * When Crowdfunding is saving all of the default field data, we need to also
		 * save our custom fields. 
		 *
		 * If using a simple text field, or value that can be saved directly, the item
		 * will be saved automatically. Otherwise, you need to hook into the save action
		 * and perform your own processing and saving.
		 *
		 * Example:
		 *
		 * Where `subtitle` is the key of the field added to the fields array.
		 *
		 * The callback has four parameters: $key, $field, $campaign, $fields
		 */
		add_action( 'atcf_shortcode_submit_save_field_subtitle', array( $this, 'submit_save_field_subtitle' ), 10, 4 );

		/**
		 * Load the saved data for this field.
		 * Can do some fancy stuff, but will most likely just be retreiving our saved meta value.
		 */
		add_filter( 'atcf_shortcode_submit_saved_data_subtitle', array( $this, 'saved_data_subtitle' ), 10, 3 );

		/**
		 * Output the field in the Campaign metabox.
		 */
		add_action( 'atcf_metabox_campaign_info_after', array( $this, 'admin_submit_output' ) );

		/**
		 * Make sure our meta is saved if updated via the admin panel.
		 */
		add_filter( 'edd_metabox_fields_save', array( $this, 'admin_submit_save_field_subtitle' ) );
	}

	/**
	 * Add fields to the submission form.
	 *
	 * Currently the fields must fall between two sections: "job" or "company". Until
	 * WP Job Manager filters the data that passes to the registration template, these are the
	 * only two sections we can manipulate.
	 *
	 * You may use a custom field type, but you will then need to filter the `job_manager_locate_template`
	 * to search in `/templates/form-fields/$type-field.php` in your theme or plugin.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param array $fields The existing fields
	 * @return array $fields The modified fields
	 */
	function form_fields( $fields ) {
		$fields[ 'subtitle' ] = array(
			'label'       => 'Subtitle',       // The label for the field
			'type'        => 'text',           // text, radio, checkbox, select
			'placeholder' => null,             // Placeholder value
			'default'     => null,             // Default Value
			'required'    => true,             // If the field is required to submit the form
			'editable'    => true,             // If it should appear on the edit screen
			'priority'    => 4                 // Where should the field appear based on the others
		);

		/**
		 * Repeat this for any additional fields.
		 */

		return $fields;
	}

	/**
	 * Save callback, if custom processing is needed.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param string $key
	 * @param array $field
	 * @param int $campaign
	 * @param array $fields
	 * @return void
	 */
	function submit_save_field_subtitle( $key, $field, $campaign, $fields ) {
		$value = sanitize_text_field( $field[ 'value' ] );

		/**
		 * Do something else with the data potentially...
		 */

		update_post_meta( $campaign, 'campaign_subtitle', $value );
	}

	/**
	 * Subtitle saved data.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param null $data
	 * @param string $key
	 * @param object $campaign
	 * @return void
	 */
	function saved_data_subtitle( $data, $key, $campaign ) {
		/**
		 * Potentially do something more intensive, like getting term values, etc
		 */

		// Grab the meta
		$subtitle = $campaign->__get( 'campaign_subtitle' );

		return $subtitle;
	}

	/**
	 * Subtitle field on the backend.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param object $campaign
	 * @return void
	 */
	function admin_submit_output( $campaign ) {
		$subtitle = $campaign->__get( 'campaign_subtitle' );
	?>
		<p>
			<label for="campaign_subtitle"><strong><?php _e( 'Subtitle' ); ?></strong></label><br />
			<input type="text" name="campaign_subtitle" id="campaign_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="regular-text" />
		</p>
	<?php
	}

	/**
	 * Save subtitle key on the backend.
	 *
	 * @since Custom Fields for Crowdfunding 1.0
	 *
	 * @param array $fields
	 * @return array $fields
	 */
	function admin_submit_save_field_subtitle( $fields ) {
		$fields[] = 'campaign_subtitle';

		return $fields;
	}
}
add_action( 'init', array( 'Astoundify_Crowdfunding_Fields', 'instance' ) );