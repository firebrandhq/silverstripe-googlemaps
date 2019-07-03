<?php

namespace ilateral\SilverStripe\GoogleMaps\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Requirements;

/**
 * Inject our map data into the Content Controller
 * 
 */
class ControllerExtension extends Extension
{

    public function onAfterInit()
	{
        // load static requirements
        Requirements::css('i-lateral/silverstripe-googlemaps: client/dist/css/GoogleMaps.css');

        // load dynamic maps and requirements
        if (
            $this->owner->ShowMap
            && !$this->owner->StaticMap
        ) {

            // load requirements
            $config = SiteConfig::current_site_config();
            $key = ($config->APIKey) ? "?key={$config->APIKey}" : '';
            Requirements::javascript('silverstripe/admin: thirdparty/jquery/jquery.min.js');
            Requirements::javascript("//maps.googleapis.com/maps/api/js" . $key);
            Requirements::javascript('i-lateral/silverstripe-googlemaps: client/thirdparty/javascript/gmap3.min.js');

            // load maps
            if ($this->owner->OnlyOneMap == false) {
                foreach($this->owner->Maps() as $map) {
                    $vars = array(
                        'MapID'         => "google-map-dynamic-{$map->ID}",
                        'Address'       => ($map->Address) ? str_replace('/n', ',', $map->Address) . ',' . $map->PostCode : 'false',
                        'Latitude'      => ($map->Latitude) ? $map->Latitude : 'false',
                        'Longitude'     => ($map->Longitude) ? $map->Longitude : 'false',
                        'Zoom'          => $map->Zoom
                    );

                    Requirements::javascriptTemplate(
                        'i-lateral/silverstripe-googlemaps: client/dist/javascript/GoogleMap.js',
                        $vars
                    );
                }
            } else {
                $centerAdress = "";
                $centerLatitude;
                $centerLongitude;
                $markerValues = "";
                $zoom = 10;
                $mapID = "";
                $autofit =  ($this->owner->AutoFit) ? ',"autofit"' : null;
                $markers = array();

                for ($i = 0; $i < $this->owner->Maps()->count(); $i++) {
                    $map = $this->owner->Maps();
                    $map = $map[$i];
                    $address = ($map->Address) ? str_replace('/n', ',', $map->Address) . ',' . $map->PostCode : null;

                    if ($i == 0) {
                        $centerAdress = ($map->Address) ? "address: '".str_replace('/n', ',', $map->Address) . ',' . $map->PostCode ."'," : '';
                        $zoom = $map->Zoom;
                        $mapID = "google-map-dynamic-{$map->ID}";
                        $centerLatitude = ($map->Latitude) ? $map->Latitude : 'false';
                        $centerLongitude = ($map->Longitude) ? $map->Longitude : 'false';
                    }


                    $markerValue = "{";

                    if ($address) {
                        $markerValue .= ' address:"'.$address.'"';
                    } else {
                        $markerValue .= " position: [";
                        $markerValue .= ($map->Latitude) ? $map->Latitude : 'false';
                        $markerValue .= ",";
                        $markerValue .= ($map->Longitude) ? $map->Longitude : 'false';
                        $markerValue .= "]";
                    }

                    $markerValue .= "}";
                    $markers[] = $markerValue;
                }

                $js = '(function($){$(document).ready(function() {
                $(".'.$mapID.'").gmap3({
                    center: ['.$centerLatitude.','.$centerLongitude.'],
                    zoom: '.$zoom.'
                })';

                $js .= '.marker([' . implode(",", $markers) . '])';

                if ($this->owner->AutoFit) {
                    $js .= ".fit();";
                } else {
                    $js .= ";";
                }

                $js .= "});}(jQuery));";

                Requirements::customScript($js);
            }
        }
    }

    public function GoogleMaps()
	{
        if ($this->owner->Maps()->exists() && $this->owner->ShowMap) {
            $config = SiteConfig::current_site_config();
            $vars = array(
                'Maps' => $this->owner->Maps()
            );

            return $this->owner->renderWith('ilateral\Silverstripe\GoogleMaps\Includes\GoogleMaps',$vars);
        } else {
            return false;
		}
    }
}