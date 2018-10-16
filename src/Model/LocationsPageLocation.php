<?php

namespace IQnection\LocationsPage\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms;

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
		$fields->removeByName('SortOrder');
		$fields->dataFieldByName('Latitude')->setDescription('Will auto populate upon save. Only change if values are known to be incorrect');
		$fields->dataFieldByName('Longitude')->setDescription('Will auto populate upon save. Only change if values are known to be incorrect');
		return $fields;
	}

	public function canCreate($member = null) { return true; }
	public function canDelete($member = null) { return true; }
	public function canEdit($member = null)   { return true; }
	public function canView($member = null)   { return true; }
	
	public function getLocation($address=false){
		$gMapsApiUrl = "https://maps.google.com/maps/api/geocode/json?sensor=false&address=";
		if ($key = SiteConfig::current_site_config()->GoogleMapsApiKey)
		{
			$gMapsApiUrl .= "&key=".$key;
		}
		$url = $google.urlencode($address);
		
		$resp_json = $this->curl_file_get_contents($url);
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
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		
		if ( (!$this->Latitude) || (!$this->Longitude) || ($this->isChanged('Address')) )
		{
			if ($location = $this->getLocation($this->Address))
			{
				$this->Latitude = $location['lat'];
				$this->Longitude = $location['lng'];
			}
		}
	}
	
}

