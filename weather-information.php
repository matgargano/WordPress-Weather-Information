<?php
/**
 *
 * Plugin's main file, includes necessary classes and instantiates widget.
 *
 * Plugin utilizes:
 * Web Service from CDYNE Professional REST and SOAP API Provider (http://cdyne.com , http://wsf.cdyne.com/WeatherWS/Weather.asmx)
 * Class by Nicky Eomon (http://www.nickyeoman.com/) for zipcode validation
 *
 * @package WordPress_Weather_Information_plugin
 */

/*
Plugin Name: Weather Information
Description: Display local weather information
Version: 1.0
Author: Mat Gargano
Author URI: http://www.matgargano.com
*/



require_once("inc/class-weather-widget.php");
require_once("inc/class-weather-information.php");
require_once("inc/class-zipcode-validator.php");

add_action('widgets_init', create_function('', 'return register_widget("weather_information_widget");'));