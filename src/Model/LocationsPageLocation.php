<?php

namespace IQnection\LocationsPage\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;
use SilverStripe\SiteConfig\SiteConfig;

class LocationsPageLocation extends DataObject
{
	private static $table_name = 'LocationsPageLocation';
	private static $singular_name = 'Location';
	private static $plural_name = 'Locations';
	
	private static $db = [
		"SortOrder" => "Int",
		"Title" => "Varchar(255)",
		"Address" => "Varchar(255)",
		"Latitude" => "Varchar(255)",
		"Longitude" => "Varchar(255)"
	];
	
	private static $has_one = [
		"LocationsPage" => \IQnection\LocationsPage\LocationsPage::class
	]; 		
	
	private static $default_sort = "SortOrder";
	
	private static $summary_fields = [
		"Title" => "Title",
		"Address" => "Address"
	];
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName(['SortOrder','FileTracking','LinkTracking']);
		$fields->addFieldToTab('Root.Main', $coordsGroup = Forms\FieldGroup::create('Coordinates')
			->setDescription('Will auto populate upon save. Only change if values are known to be incorrect') );
		$coordsGroup->push(	$fields->dataFieldByName('Latitude') );
		$coordsGroup->push( $fields->dataFieldByName('Longitude') );

		return $fields;
	}

	public function canCreate($member = null,$context = []) { return true; }
	public function canDelete($member = null,$context = []) { return true; }
	public function canEdit($member = null,$context = [])   { return true; }
	public function canView($member = null,$context = [])   { return true; }
	
	public function validate()
	{
		$result = parent::validate();
		if ($location = $this->getLocation($this->Address))
		{
			$this->Latitude = $location['lat'];
			$this->Longitude = $location['lng'];
		}
		else
		{
			$result->addError('Could not locate address on Google Maps');
		}
		return $result;
	}
	
	public function getLocation($address=false){
		$gMapsApiUrl = "https://maps.google.com/maps/api/geocode/json?sensor=false&address=";
		$gMapsApiUrl .= urlencode($address);
		if ($key = SiteConfig::current_site_config()->GoogleMapsApiKey)
		{
			$gMapsApiUrl .= "&key=".$key;
		}
		
		$resp_json = $this->curl_file_get_contents($gMapsApiUrl);
		$resp = json_decode($resp_json, true);

		if($resp['status']='OK')
		{
			return $resp['results'][0]['geometry']['location'];
		}
		return false;
	}
	
	private function curl_file_get_contents($URL)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);

		if ($contents) 
		{
			return $contents;
		}
		return false;
	}	
}

