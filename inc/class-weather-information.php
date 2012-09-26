<?php
/*
 * Weather Information Class
 *
 * Usage:
 *
 * <code>
 *    $weather = new weather_information($zip);
 *    $weather_info = $weather->parse_weather();
 * </code>
 * 
 * @package WordPress_Weather_Information_plugin
 * @todo create method for class to gracefully fail
 */
class weather_information {
      
      /**
      * Sets up functionality for the class along with url templates and API specific keywords
      *
      * @param  int United States zipcode
      * @return weather object
      */      
      public function __construct($zip_code) {
            
            /**
             * Response from CDYNE API call
             *
             * @since 1.0
             * @var object
             */
            $this->response                     = "";
            
            /**
             * United States zipcode
             *
             * @since 1.0
             * @var int
             */
            $this->zip_code                     = $zip_code;
            
            /**
             * API method for getting weather information
             *
             * @since 1.0
             * @var string
             */            
            $this->weather_by_zip               = "GetCityWeatherByZIP";
            
            /**
             * API method for getting weather meta information
             *
             * @since 1.0
             * @var string
             */
            $this->get_weather_information      = "GetWeatherInformation";
            
            /**
             * Template for building API URL
             *
             * @since 1.0
             * @var string
             */
            $this->url_template                 = "http://wsf.cdyne.com/WeatherWS/Weather.asmx/%s?%s=%s";
      }
      
      /**
      * Accessor method that allows public access to the private method that obtains the weather data
      *
      * @since 1.0
      * @param  none
      * @return object containing weather information
      * @access public
      */      
      public function parse_weather(){
            return $this->get_weather();
      }
      
      /**
      * Private method that calls the API call for the collection of weather data
      *
      * @since 1.0
      * @param  none
      * @return object containing weather information
      * @access private
      */      
      private function get_weather(){
            $url = sprintf($this->url_template, $this->weather_by_zip, "ZIP", $this->zip_code);
            $this->response                           = simplexml_load_file($url) or die("ERROR"); /*  Make external API call */
            $weather_request                          = $this->response;
            $weather_object                           = new stdClass;
            $weather_object->success                  = (string)$weather_request->Success;   /* cast responses as strings */
            $weather_object->response_text            = (string)$weather_request->ResponseText;
            $weather_object->state                    = (string)$weather_request->State;
            $weather_object->city                     = (string)$weather_request->City;
            $weather_object->weather_station_city     = (string)$weather_request->WeatherStationCity;
            $weather_object->weather_id               = (string)$weather_request->WeatherID;
            $weather_object->weather_description      = (string)$weather_request->Description;
            $weather_object->temperature              = (string)$weather_request->Temperature;
            $weather_object->relative_humidity        = (string)$weather_request->RelativeHumidity;
            $weather_object->wind                     = (string)$weather_request->Wind;
            $weather_object->pressure                 = (string)$weather_request->Pressure;
            $meta_information                         = $this->weather_meta($weather_object->weather_id);
            $weather_object->description              = (string)$meta_information->description;
            $weather_object->image                    = (string)$meta_information->image;
            return $weather_object;
      }
      
      /**
       * Private method that calls the API call for the collection of weather meta data (description and image)
       *
       * @since 1.0
       * @param  none
       * @return object containing description and image url for current weather condition
       * @access private
       */      
      private function weather_meta(){
            $url = sprintf($this->url_template, $this->get_weather_information, "WeatherID", "");
            $this->response                     = simplexml_load_file($url) or die("ERROR");
            $weather_request                    = $this->response->WeatherDescription[2];
            $weather_response                   = new stdClass;
            $weather_response->description      = $weather_request->Description;
            $weather_response->image            = $weather_request->PictureURL;
            return $weather_response;
      }
}



?>