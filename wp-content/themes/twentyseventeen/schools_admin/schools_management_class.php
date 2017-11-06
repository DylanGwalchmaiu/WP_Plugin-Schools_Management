<?php
/**
 * Module: School Management
 * Class: Creates a 'school' content type to manage school's WordPress sites and information
 * Creator: Dylan Moss
 * Date: 2017/11/06
 */

class managed_school {

	//variables
	private $directory = '';
	private $singular_name = 'school';
	private $plural_name = 'schools';
	private $content_type_name = 'managed_school';

	//magic function, called on creation
	public function __construct() {
		$this->set_directory_value(); //set the directory url on creation
		add_action( 'init', array( $this, 'add_content_type' ) ); //add content type
		add_action( 'init', array(
			$this,
			'check_flush_rewrite_rules'
		) ); //flush re-write rules for permalinks (because of content type)
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_for_content_type' ) ); //add meta boxes
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts_and_styles' ) ); //enqueue public facing elements
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts_and_styles' ) ); //enqueues admin elements
		add_action( 'save_post_' . $this->content_type_name, array(
			$this,
			'save_custom_content_type'
		) ); //handles saving of content type meta info
		add_action( 'display_content_type_meta', array(
			$this,
			'display_additional_meta_data'
		) ); //displays the saved content type meta info
	}

	//sets the directory (path) so that we can use this for our enqueuing
	public function set_directory_value() {
		$this->directory = get_stylesheet_directory_uri() . '/schools_admin';
	}

	//check if we need to flush rewrite rules
	public function check_flush_rewrite_rules() {
		$has_been_flushed = get_option( $this->content_type_name . '_flush_rewrite_rules' );
		//if we haven't flushed re-write rules, flush them (should be triggered only once)
		if ( $has_been_flushed != true ) {
			flush_rewrite_rules( true );
			update_option( $this->content_type_name . '_flush_rewrite_rules', true );
		}
	}

	//enqueue public scripts and styles
	public function enqueue_public_scripts_and_styles() {
		//public styles
		wp_enqueue_style(
			$this->content_type_name . '_public_styles',
			$this->directory . '/css/' . $this->content_type_name . '_public_styles.css'
		);
		//public scripts
		wp_enqueue_script(
			$this->content_type_name . '_public_scripts',
			$this->directory . '/js/' . $this->content_type_name . '_public_scripts.js',
			array( 'jquery' )
		);
	}

	//enqueue admin scripts and styles
	public function enqueue_admin_scripts_and_styles() {
		global $pagenow, $post_type;

		//process only on post edit page for custom content type
		if(($post_type == $this->content_type_name) && ($pagenow == 'post-new.php' || $pagenow == 'post.php')) {

			//admin styles
			wp_enqueue_style(
				$this->content_type_name . '_public_styles',
				$this->directory . '/css/' . $this->content_type_name . '_admin_styles.css'
			);
		}
	}

	//adding our new content type
	public function add_content_type() {
			$labels = array(
				'name'               => ucwords($this->singular_name),
				'singular_name'      => ucwords($this->singular_name),
				'menu_name'          => ucwords($this->plural_name),
				'name_admin_bar'     => ucwords($this->singular_name),
				'add_new'            => ucwords($this->singular_name),
				'add_new_item'       => 'Add New ' . ucwords($this->singular_name),
				'new_item'           => 'New ' . ucwords($this->singular_name),
				'edit_item'          => 'Edit ' . ucwords($this->singular_name),
				'view_item'          => 'View ' . ucwords($this->plural_name),
				'all_items'          => 'All ' . ucwords($this->plural_name),
				'search_items'       => 'Search ' . ucwords($this->plural_name),
				'parent_item_colon'  => 'Parent ' . ucwords($this->plural_name) . ':',
				'not_found'          => 'No ' . ucwords($this->plural_name) . ' found.',
				'not_found_in_trash' => 'No ' . ucwords($this->plural_name) . ' found in Trash.',
			);

			$args = array(
				'labels'            => $labels,
				'public'            => true,
				'publicly_queryable'=> true,
				'show_ui'           => true,
				'show_in_nav'       => true,
				'query_var'         => true,
				'hierarchical'      => false,
				'supports'          => array('title','editor','thumbnail'),
				'has_archive'       => true,
				'menu_position'     => 20,
				'show_in_admin_bar' => true,
				'menu_icon'         => 'dashicons-format-status'
			);

			//register your content type
			register_post_type($this->content_type_name, $args);
	}

	//adding meta box to save additional meta data for the content type
	public function add_meta_boxes_for_content_type() {
		//add a meta box
		add_meta_box(
			$this->singular_name . '_meta_box', //id
			ucwords($this->singular_name) . ' Information', //box name
			array($this,'display_function_for_content_type_meta_box'), //display function
			$this->content_type_name, //content type
			'normal', //context
			'default' //priority
		);
	}

	//displays the visual output of the meta box in admin (where we will save our meta data)
	public function display_function_for_content_type_meta_box( $post ) {
		$sSchoolName = get_post_meta($post->ID, 'school_name', true);
		$sSchoolURL = get_post_meta($post->ID, 'school_url', true);
		$sSchoolTelephone = get_post_meta($post->ID, 'school_telephone', true);
		$sSchoolEmail = get_post_meta($post->ID, 'school_email', true);
		$fSchoolLatitude = get_post_meta($post->ID, 'school_latitude', true);
		$fSchoolLongitude = get_post_meta($post->ID, 'school_longitude', true);


	}

	//when saving the custom content type, save additional meta data
	public function save_custom_content_type( $post_id ) {
	}
	//display additional meta information for the content type
	//@hooked using 'display_additional_meta_data' in theme
	function display_additional_meta_data() {
	}
}

//create new object
$managed_school = new managed_school;