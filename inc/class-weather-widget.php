<?php
/**
* Weather Information Widget class that creates the ability for users to use the Weather Information widget for WordPress.
*
* @package  WordPress_Weather_Information_plugin
* @todo wrap validation that tests if the web service has information for entered zip code
* @author   Matthew Gargano <mgargano@gmail.com>
* @version  1.0
* 
*/
class weather_information_widget extends WP_Widget {
    
    /**
     * Sets constants, plugin specific settings, output templates and enqueue actions for CSS
     *
     * @param  none
     * @return none
     */
    function __construct() {
	parent::WP_Widget(false, $name = 'Weather Information');
	
	/**
	 * Formatting template for error message within widget admin page
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->error_template		= '<div class="wi-error">%s</div>';
	
	/**
	 * Formatting template for form text input within the widget's form on the admin page
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->text_template 		= '<input type="text" value="%s" name="%s" />';
	
	/**
	 * Formatting template for form label within the widget's form on the admin page
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->label_template		= '<label for="%s" class="%s">%s</label>';
	
	/**
	 * Formatting skeleton template for ouputting of widget on front-end
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->widget_template		= '<li id="%s" class="widget-container %s"><h3 class="widget-title">%s</h3><ul id="%s"><li>%s</li></ul></li>';
	
	/**
	 * Formatting template for ouputting of widget on front-end
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->weather_template 	= '<h4 class="strong">%s</h4><img src ="%s" alt="%s" /><div class="wi-info"><p class="strong">Currently</p><p>%s</p><p>%s</p></div>';
	
	/**
	 * Error message for when widget form encounters invalid zipcode within the widget's form on the admin page
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->error_invalid_zipcode	= sprintf('<span>Invalid Zipcode</span> - Your widget will not display until this is resolved.<br /><br /> Please refer to <a href="%s" target="_BLANK">this tool</a> for more information. ', 'https://www.usps.com/zip4/');
	
	/**
	 * Current Version of this plugin
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->ver 			= "1.0";
	
	/**
	 * Name of widget
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->widget_name 		= "Weather";
	
	/**
	 * Name of plugin
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->plugin_name 		= "weather_information";
	
	/**
	 * Transient name to use when caching data from CDYNE API call
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->transient_name   	= $this->id; //set the transient name equal to the widget id to make sure we are caching the exact information we want to be caching
	
	/**
	 * Number of seconds to store cached data from CDYNE API call
	 *
	 * @since 1.0
	 * @var int
	 */
	$this->refresh_rate 		= 60 * 5; //cache for 5 minutes so as to not overload the web service
	
	/**
	 * Get location of this plugin, one level below current level (i.e. /this_plugin_directory/inc)
	 *
	 * @since 1.0
	 * @var string
	 */
	$this->plugin_location 		= substr(plugin_dir_url(__FILE__),0, strrpos(plugin_dir_url(__FILE__), "/",-2)) . "/"; // one directory below this subdirectory
	
	/**
	 * Add hook into widgets admin page and wp_enqueue_scripts for applicable enqueueing of CSS files
	 *
	 * @since 1.0
	 */
	add_action('admin_print_scripts-widgets.php', array($this, 'admin_enqueues' ));
	add_action('wp_enqueue_scripts', array($this, 'client_enqueues'));
    }

   /**
    * Style enqueue method for the widget admin page
    *
    * @since 1.0
    * @param  none
    * @return none
    */
    function admin_enqueues(){
	wp_register_style ( $this->plugin_name . '-admin-style', $this->plugin_location . "css/admin.css", false, $this->ver);
	wp_enqueue_style($this->plugin_name . '-admin-style');	
    }

   /**
    * Style enqueue method for the front-end
    *
    * @since 1.0
    * @param  none
    * @return none
    */
    function client_enqueues(){
	wp_register_style ( $this->plugin_name . '-style', $this->plugin_location . "css/style.css", false, $this->ver);
	wp_enqueue_style($this->plugin_name . '-style');	
    }

    /**
     * Front-end output of widget.
     *
     * @since 1.0
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */	
    function widget($args, $instance) {	
	extract( $args );
	$this->zip    = $instance['zip'];
	$validation = new Validate;
	if (!$validation->isZipCode($this->zip)) {     /* if zipcode is not valid, let's not allow the widget to be output */
	    return false;
	}
	echo $before_widget;
	echo $this->generate_output();  /* Call the helper method that builds our widget's output */ 
	echo $after_widget;
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @since 1.0
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array; updated values to be stored.
     */
    function update($new_instance, $old_instance) {		
	$instance = $old_instance;
	unset($instance['error']); /* make sure that error index of $instance is not set */
	$validation = new Validate; /* validate the zipcode; if not valid, clear value for zipcode and output error message */
	if (!$validation->isZipCode ($new_instance['zip'])) {
	    $instance['error'] 	= $this->error_invalid_zipcode;
	    $instance['zip']	= "";
	} else {
	    $instance['zip']	= $new_instance['zip']; 
	    delete_transient($this->transient_name); /* if valid zipcode, let's delete any cached to ensure the freshest content */
	}
    return $instance;
    }

    /**
     * Form for widget admin page
     *
     * @since 1.0
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */    
    function form($instance) {	
	$defaults 	= array('zip' => '', 'error'=> ''); /* set some defaults for the widget's settings */
	$instance 	= wp_parse_args( (array) $instance, $defaults );
	echo sprintf($this->label_template, $this->get_field_name("zip"), "wi-label", "Zip Code"); /* let's use the templates set up to output the form for the widget */
	echo sprintf($this->text_template, $instance['zip'], $this->get_field_name("zip"));
	if ($instance['error']){  /* If there's an error - let's output it */
	    echo sprintf($this->error_template, $instance['error']);
	}
    }

    /**
     * Generate ouptput method : here we uses the WordPress Transient API to store the values in fast memory if available (e.g., if the install uses W3 Total Cache or WP Super Cache) otherwise in the options table. This helps speed up the site and prevents it from overloading the CDYNE servers that host the web service.
     *
     * @since 1.0
     * @param  none
     * @return string; sanitized output for widget
     */
    private function generate_output(){
	$weather_output = get_transient( $this->transient_name ); 
	if (false === $weather_output){
	    $weather 	 		= new weather_information($this->zip);
	    $weather_info 		= $weather->parse_weather();
	    $this->weather_output 	= sprintf($this->weather_template, $weather_info->city . ", " . $weather_info->state, $weather_info->image, $weather_info->description, $weather_info->description, $weather_info->temperature . '&#176;' );
	    $weather_output 		= sprintf($this->widget_template, $this->id, $this->plugin_name, $this->widget_name, $this->plugin_name, $this->weather_output);
	    set_transient($this->transient_name, $weather_output, $this->refresh_rate);
	}
	return  $weather_output;
    }
}









