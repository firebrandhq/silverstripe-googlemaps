<?php

namespace ilateral\SilverStripe\GoogleMaps\Model;

use BetterBrief\GoogleMapField;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Google Map Objects represent a google map that needs to be rendered into a
 * page.
 *
 */
class GoogleMap extends DataObject
{
    private static $table_name = "GoogleMap";

    private $api_key;

    private static $db = array(
        'Title'             => 'Varchar',
        'Address'           => 'Text',
        'PostCode'          => 'Varchar',
        'Latitude'          => 'Varchar',
        'Longitude'         => 'Varchar',
        'Zoom'              => 'Int',
        'Sort'              => 'Int'
    );

    private static $has_one = array(
        'Parent' => SiteTree::class
    );

    private static $defaults = array(
        'Zoom' => 10
    );

    private static $casting = array(
        'FullAddress'   => 'HTMLText',
        'Location'      => 'Text',
        'Link'          => 'Text',
        'ImgURL'        => 'Text'
    );

    private static $summary_fields = array(
        'Title',
        'Address',
        'PostCode',
        'Latitude',
        'Longitude'
    );

    private static $default_sort = 'Sort';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByname('ParentID');
        $fields->removeByname('Latitude');
        $fields->removeByname('Longitude');
        $fields->removeByname('Zoom');
        $fields->removeByname('Sort');

        $fields->addFieldsToTab(
            "Root.Map",
            array(
                HeaderField::create(
                    "MapHeader",
                    _t("GoogleMaps.MapHeader", "Generate your map")
                ),
                $mapField = GoogleMapField::create($this, "Find the location"),
                ReadonlyField::create("Latitude"),
                ReadOnlyField::create("Longitude"),
                ReadOnlyField::create("Zoom")
            )
        );
        
        // set API key if available
        $config = SiteConfig::current_site_config();
        if ($config->APIKey) {
            $mapField->setOption('api_key', $config->APIKey);
        }

        return $fields;
    }

    private function url_safe_address()
    {
        $address  = str_replace('/n', ',', $this->Address);
        $address .= ',' . $this->PostCode;

        return urlencode($address);
    }

    /**
     * Get the location for this map, either address / postcode or lat / long
     *
     * @return String
     */
    public function getLocation()
    {
        $location = false;

        if ($this->Address && $this->PostCode) {
            $location = $this->url_safe_address();
        }

        if ($this->Latitude && $this->Longitude) {
            $location = $this->Latitude . ',' . $this->Longitude;
        }

        return $location;
    }

    /**
     * Get a XML rendered version of the text address and post code
     *
     * @return String
     */
    public function getFullAddress()
    {
        return Convert::raw2xml($this->Address . '/n' . $this->PostCode);
    }

    /**
     * Link to Google Maps for directions etc
     *
     * @return String
     */
    public function Link()
    {
        $link = false;
        $location = $this->getLocation();

        if ($location) {
            $link  = '//maps.google.com/maps?q=';
            $link .= $location;
            $link .= '&amp;z='.$this->Zoom;
        }

        return $link;
    }

    /**
     * URL for static map image
     *
     * @return String
     */
    public function ImgURL($width = 256, $height = 256)
    {
        $link = false;
        $location = $this->getLocation();

        if ($location) {
            $link = '//maps.googleapis.com/maps/api/staticmap?';
            $link .= 'center=' . $location;
            $link .= '&zoom=' . $this->Zoom;
            $link .= '&size=' . $width . 'x' . $height . '';
            $link .= '&maptype=roadmap';
            $link .= '&markers=color:red%7C' . $location;
            $link .= '&sensor=false';
        }

        return $link;
    }

    public function canCreate($member = null, $context = array())
    {
        return $this->Parent()->canCreate($member,$context);
    }

    public function canView($member = null)
    {
        return $this->Parent()->canView();
    }

    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit();
    }

    public function canDelete($member = null)
    {
        return $this->Parent()->canDelete();
    }
}
