<?php
/*
    Copyright 2012 Matthew Gargano (email : mgargano@gmail.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
    
Plugin Name: Weather Information
Description: Display local weather information
Version: 1.0
Author: Mat Gargano
Author URI: http://www.matgargano.com
*/

/**
 *
 * Weather Information Widget, includes necessary classes and instantiates widget.
 *
 * Plugin utilizes:
 * Web Service from CDYNE Professional REST and SOAP API Provider (http://cdyne.com , http://wsf.cdyne.com/WeatherWS/Weather.asmx)
 * Class by Nicky Eomon (http://www.nickyeoman.com/) for zipcode validation
 *
 * @package WordPress_Weather_Information_plugin
 */

require_once("inc/class-weather-widget.php");
require_once("inc/class-weather-information.php");
require_once("inc/class-zipcode-validator.php");

add_action('widgets_init', create_function('', 'return register_widget("weather_information_widget");'));