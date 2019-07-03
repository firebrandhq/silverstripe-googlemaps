<?php

namespace ilateral\SilverStripe\GoogleMaps\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

/**
 * @author morven
 *
 */

class SiteConfigExtension extends DataExtension
{
    private static $db = array(
        'APIKey'   => 'Varchar(100)'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', TextField::create('APIKey', 'Google Maps API Key'));
    }
}
