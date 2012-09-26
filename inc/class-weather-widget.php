<?php
/**
* weather_information_widget is a class that creates the ability for users to use the Weather Information widget for WordPress.
*
*
* @package  WordPress_Weather_Information_plugin
* @todo wrap validation that tests if the web service has information for entered zip code
* @author   Matthew Gargano <mgargano@gmail.com>
* @version  $Revision: 1.0 $
* 
*/



class weather_information_widget extends WP_Widget {
    
    /**
    * Constructor method - sets constants, sets up widget, sets up CSS to be enqueued
    *
    * @param  none
    * @return none
    */
 
    function __construct() {
	/* Constants */
	parent::WP_Widget(false, $name = 'Weather Information');
	
	/* Templates for specific elements of this plugin's front and back-ends */
	$this->error_template	= '<div class="wi-error">%s</div>';
	$this->text_template 	= '<input type="text" value="%s" name="%s" />';
	$this->label_template	= '<label for="%s" class="%s">%s</label>';
	$this->widget_template	= '<li id="%s" class="widget-container %s"><h3 class="widget-title">%s</h3><ul id="%s"><li>%s</li></ul></li>';
	$this->weather_template = '<h4>%s</h4><img src ="%s" alt="%s" /><div class="wi-info"><p class="currently">Currently</p><p>%s</p><p>%s</p></div>';
	
	/* Some plugin specific settings */
	$this->ver 		= "1.0";
	$this->widget_name 	= "Weather";
	$this->plugin_name 	= "weather_information";
	$this->transient_name   = $this->id; //set the transient name equal to the widget id to make sure we are caching the exact information we want to be caching
	$this->refresh_rate 	= 60 * 5; //cache for 5 minutes so as to not overload the web service
	$this->plugin_location 	= substr(plugin_dir_url(__FILE__),0, strrpos(plugin_dir_url(__FILE__), "/",-2)) . "/"; // one directory below this subdirectory
	add_action('admin_print_scripts-widgets.php', array($this, 'admin_enqueues' ));
	add_action('wp_enqueue_scripts', array($this, 'client_enqueues'));
    }

    /**
    * Enqueues for the back-end of WordPress
    *
    * @param  none
    * @return none
    */
    
    function admin_enqueues(){
	wp_register_style ( $this->plugin_name . '-admin-style', $this->plugin_location . "css/admin.css", false, $this->ver);
	wp_enqueue_style($this->plugin_name . '-admin-style');	
    }

    /**
    * Enqueues for the front-end of WordPress
    *
    * @param  none
    * @return none
    */
    
    function client_enqueues(){
	wp_register_style ( $this->plugin_name . '-style', $this->plugin_location . "css/style.css", false, $this->ver);
	wp_enqueue_style($this->plugin_name . '-style');	

    }

    /**
    * Front-end display of widget.
    *
    * @see WP_Widget::widget()
    *
    * @param array $args     Widget arguments.
    * @param array $instance Saved values from database.
    */	

    function widget($args, $instance) {	
	extract( $args );
	$this->zip    = $instance['zip'];
	$validation = new zipcode_validator;
	/* if zipcode is not valid, let's not allow the widget to be output */
	if (!$validation->isZipCode($this->zip)) {
	    return false;
	}
	?>    
	      <?php echo $before_widget; ?>
	      <?php echo $this->generate_output(); ?> /* Calling the helper function that builds our widget's output */
	      <?php echo $after_widget; ?>
	      
	<?php
    }
 
 
    /**
    * Sanitize widget form values as they are saved.
    *
    * @see WP_Widget::update()
    *
    * @param array $new_instance Values just sent to be saved.
    * @param array $old_instance Previously saved values from database.
    *
    * @return array Updated safe values to be saved.
    */
 
    function update($new_instance, $old_instance) {		
      $instance = $old_instance;
      unset($instance['error']); /* make sure that error index of $instance is not set */
      $validation = new zipcode_validator; /* validate the zipcode; if not valid, do not save the value and output error message */
      
      if (!is_numeric($new_instance['zip']) ||
	    !$validation->isZipCode($new_instance['zip'])) {
	
	$instance['error'] 	= sprintf('<span>Invalid Zipcode</span> - Your widget will not display until this is resolved.<br /><br /> Please refer to <a href="%s" target="_BLANK">this tool</a> for more information. ', 'https://www.usps.com/zip4/');
	$instance['zip']	= "";
      } else {
          $instance['zip']     	= $new_instance['zip']; /* if valid zipcode, let's set it to be saved and delete any caching to make sure our site outputs the freshest content */
	  delete_transient($this->transient_name);
      }
      return $instance;
    }
 
    

    /**
    * Back-end widget form.
    *
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
    * Generate ouptput method -      * Here we uses the WordPress Transient API to store the values in fast memory if available (e.g., if the install uses W3 Total Cache or WP Super Cache) otherwise in the options table. This helps speed up the site and prevents it from overloading the CDYNE servers that host the web service.
    *
    * @param  none
    * @return none
    */
    

    private function generate_output(){
	$weather_output = get_transient( $this->transient_name ); 
	if (false === $weather_output){
	    $weather 			= new weather_information($this->zip);
	    $weather_info 		= $weather->parse_weather();
	    $this->weather_output 	= sprintf($this->weather_template, $weather_info->city . ", " . $weather_info->state, $weather_info->image, $weather_info->description, $weather_info->description, $weather_info->temperature . '&#176;' );
	    $weather_output 		= sprintf($this->widget_template, $this->id, $this->plugin_name, $this->widget_name, $this->plugin_name, $this->weather_output);
	    set_transient($this->transient_name, $weather_output, $this->refresh_rate);
	}
	return  $weather_output;
    }




}





  



