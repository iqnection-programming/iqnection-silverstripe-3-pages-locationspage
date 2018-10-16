<?php

namespace IQnection\LocationsPage;

use SilverStripe\View\Requirements;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Director;

class LocationsPageController extends \PageController
{	
	private static $allowed_actions = array(
		"_directions",
		'_printview',
		'printview'
	);
	
	private static $url_handlers = [
		'_printview' => 'printview'
	];
	
	public function init()
	{
		$gMapsApiUrl = "https://maps.googleapis.com/maps/api/js?";
		if ($key = SiteConfig::current_site_config()->GoogleMapsApiKey)
		{
			$gMapsApiUrl .= "key=".$key;
		}
		Requirements::javascript($gMapsApiUrl);
		parent::init();
	}
		
	public function CustomJS()
	{
		$js = parent::CustomJS();
		$js .= "var MapType = '".$this->MapType."';";
		$addresses = [];
		foreach($this->Locations() as $key => $l)
		{
			$addresses[$key] = [
				'Title' => $l->Title,
				'Address' => $l->Address,
				'LatLng' => [
					$l->Latitude,
					$l->Longitude
				]
			];
		}
		$js .= "\nvar address_objects = ".json_encode($addresses).";";
		$js .= "\nvar Avgs = ".json_encode($this->Avgs()).";";
		$js .= "\nvar PageLink = '".$this->Link()."';";		
		return $js;
	}
	
	public function Avgs(){			
		$TotalLat = 0;
		$TotalLong = 0;
		$Total = 0;
		foreach($this->Locations() as $l)
		{
			$TotalLat += $l->Latitude;
			$TotalLong += $l->Longitude;
			$Total++;
		}
		if ( ($Total) && (abs($TotalLat)) && (abs($TotalLong)) )
		{
			return [$TotalLat/$Total,$TotalLong/$Total];
		}
		return [];
	}
	
	public function directionsAPI()
	{
		if (!$to_addy = urlencode($this->request->getVar('to')))
		{
			return "I don't have a destination'";
		}
		if (!$from_addy = urlencode($this->request->getVar('from')))
		{
			return "I don't have a starting location";
		}
		$params = [
			'origin' => preg_replace('/\W/','+',$from_addy),
			'destination' => preg_replace('/\W/','+',$to_addy)
		];
		if ($key = SiteConfig::current_site_config()->GoogleMapsApiKey)
		{
			$params["key"] = $key;
		}
		$gMapsApiUrl = "https://maps.googleapis.com/maps/api/directions/json?".http_build_query($params);
		$rows = file_get_contents($gMapsApiUrl,0,null,null);
		$directions_output = json_decode($rows, true);
		$ajax_data = false;
		
		if($directions_output['routes'])
		{
			$data = $directions_output['routes'][0]['legs'][0];  //assumes best route and no waypoints
			$i = 1;
			$steps = "";
			foreach($data['steps'] as $step)
			{
				$steps .= "<div class='step'><span class='step_number'>".$i.".</span><span class='step_text'>".$step['html_instructions']."</span><span class='step_distance'>".$step['distance']['text']."</span></div>";	
				$i++;
			}
			
			$ajax_data = array(
				"StartAddress" => $data['start_address'],
				"EndAddress" => $data['end_address'],
				"Distance" => $data['distance']['text'],
				"Duration" => $data['duration']['text'],
				"GoogleLink" => "https://maps.google.com/maps?q=".$from_addy."+to+".$to_addy,
				"PrintLink" => $this->AbsoluteLink()."_printview/?from=".$from_addy."&to=".$to_addy,
				"PageLink" => $this->AbsoluteLink()."_directions/?from=".$to_addy."&to=".$from_addy,
				"Steps" => $steps
			);
		}
		
		return $ajax_data;
	}
	
	public function _directions()
	{
		$ajax_data = $this->directionsAPI();
		
		if($ajax_data)
		{
			return Director::is_ajax() ? $this->Customise($ajax_data)->renderWith("IQnection/LocationsPage/Includes/directions") : $this->Customise($ajax_data);
		} 
		else 
		{
			return "<p>No routes were found from that destination address.</p>";	
		}
	}
	
	public function printview()
	{
		Requirements::customScript("window.print();window.close()");
		$ajax_data = $this->directionsAPI();
		
		if ($ajax_data)
		{
			return $this->Customise($ajax_data);
		} 
		else 
		{
			return "<p>No routes were found from that destination address.</p>";	
		}
	}
	
}