<?php

/*
 * Weather Information Class
 *
 * Usage:
 * $weather = new weather_information($zip);
 * $weather_info = $weather->parse_weather();
 *
 * @package WordPress_Weather_Information_plugin
 *
 */
  

class weather_information {
      public $zip_code;
      public $response;
      private $weather_by_zip             = "GetCityWeatherByZIP";
      private $get_weather_information    = "GetWeatherInformation";
      private $url_template               = "http://wsf.cdyne.com/WeatherWS/Weather.asmx/%s?%s=%s";

    /**
    * Constructor method - sets up functionality for the class
    *
    * @param  none
    * @return weather object
    */      
      
      public function __construct($zip_code) {
            $this->zip_code = $zip_code;
            return $this->get_weather();
      }

    /**
    * Method that initializes and sets up the main action, e.g. collecting the data
    *
    * @param  none
    * @return object containing weather information
    * @access public
    */      

      public function parse_weather(){
        return $this->get_weather();
      }
      
    /**
    * Constructor method - sets up functionality for the class
    *
    * @param  none
    * @return none
    * @access private
    */      
      
      private function get_weather(){
            $url = sprintf($this->url_template, $this->weather_by_zip, "ZIP", $this->zip_code);
            $this->response     = simplexml_load_file($url) or die("ERROR");
            $weather_request    = $this->response;
            $weather_object = new stdClass;
            $weather_object->success                = (string)$weather_request->Success;
            $weather_object->response_text          = (string)$weather_request->ResponseText;
            $weather_object->state                  = (string)$weather_request->State;
            $weather_object->city                   = (string)$weather_request->City;
            $weather_object->weather_station_city   = (string)$weather_request->WeatherStationCity;
            $weather_object->weather_id             = (string)$weather_request->WeatherID;
            $weather_object->weather_description    = (string)$weather_request->Description;
            $weather_object->temperature            = (string)$weather_request->Temperature;
            $weather_object->relative_humidity      = (string)$weather_request->RelativeHumidity;
            $weather_object->wind                   = (string)$weather_request->Wind;
            $weather_object->pressure               = (string)$weather_request->Pressure;
            $meta_information                       = $this->weather_meta($weather_object->weather_id);
            $weather_object->description            = (string)$meta_information->description;
            $weather_object->image                  = (string)$meta_information->image;
            return $weather_object;
      }

      /**
      * Weather Meta Information - grabs meta information including image and verbose description of current weather
      *
      * @param  int $weather_id
      * @return object containing description and image url for current weather condition
      * @access private
      */      

      
      private function weather_meta($weather_id){
            $url = sprintf($this->url_template, $this->get_weather_information, "WeatherID", "");
            $this->response                 = simplexml_load_file($url) or die("ERROR");
            $weather_request                = $this->response->WeatherDescription[2];
            $weather_response = new stdClass;
            $weather_response->description = $weather_request->Description;
            $weather_response->image = $weather_request->PictureURL;
            return $weather_response;
      }
}



?>