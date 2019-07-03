<?php

namespace ilateral\SilverStripe\GoogleMaps\Extensions;

use ilateral\SilverStripe\GoogleMaps\Model\GoogleMap;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\DataExtension;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * This class adds maps support to the CMS, allowing you to tick "Show maps"
 * under the settings pane. This then adds the Maps gridfield to the content
 * fields.
 *
 * @author nicolaas[at] sunnysideup.co.nz
 * @author morven [at] i-lateral.com
 *
 **/
class GoogleMapsExtension extends DataExtension
{

    private static $db = array(
        'ShowMap'   => 'Boolean',
        'StaticMap' => 'Boolean',
        'OnlyOneMap' => 'Boolean',
        'AutoFit' => 'Boolean'
    );

    private static $has_many = array(
        'Maps' => GoogleMap::class
    );

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->ShowMap) {
            $maps_field = new GridField(
                'Maps',
                '',
                $this->owner->Maps(),
                $config = GridFieldConfig_RecordEditor::create()
            );

            $config->addComponent(new GridFieldOrderableRows('Sort'));

            // Add creation button if member has create permissions
            if ($this->owner->canCreate()) {
                $config->removeComponentsByType(GridFieldAddNewButton::class);
                $add_button = new GridFieldAddNewButton('toolbar-header-left');
                $add_button->setButtonName(_t("GoogleMaps.AddGoogleMap", "Add Google Map"));
                $config->addComponent($add_button);
            }

            $fields->addFieldToTab('Root.Maps', $maps_field);
        }

        return $fields;
    }

    public function updateSettingsFields(FieldList $fields)
    {
        $maps_group = FieldGroup::create(
            CheckboxField::create("ShowMap", "Enable maps on this page?"),
            CheckboxField::create("StaticMap", "Render maps as images?"),
            CheckboxField::create("OnlyOneMap", "Show only one map?"),
            CheckboxField::create("AutoFit", "Set autofit?")
        )->setTitle('Google Maps');

        $fields->addFieldToTab("Root.Settings", $maps_group);

        return $fields;
    }
}