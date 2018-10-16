<?php

namespace IQnection\LocationsPage;

use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use SilverStripe\Forms;

class LocationsPage extends \Page
{
	private static $icon = "iqnection-pages/locationspage:images/icons/icon-locations-file.gif";
	private static $table_name = 'LocationsPage';
	
	private static $db = [
		"MapType" => "Varchar(255)",
		"MapDirections" => "Boolean"
	];
	
	private static $has_many = [
		"Locations" => Model\LocationsPageLocation::class
	];
	
	private static $defaults = [
		'MapType' => 'ROADMAP'
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.MapDetails", Forms\DropdownField::create("MapType", "Map Display Type")
			->setSource([
				"ROADMAP" => "Roadmap",
				"SATELLITE" => "Satellite",
				"HYBRID" => "Hybrid",
				"TERRAIN" => "Terrain"
			]));

		$fields->addFieldToTab('Root.MapDetails', Forms\GridField\GridField::create(
			'Locations',
			'Locations',
			$this->Locations(),
			Forms\GridField\GridFieldConfig_RecordEditor::create(30)
				->addComponent(new GridFieldSortableRows('SortOrder'))
		));
		$fields->addFieldToTab("Root.MapDetails", Forms\CheckboxField::create("MapDirections", "Display Directions Widget?"));
		return $fields;
	}			
}

