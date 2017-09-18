<?
	class Location extends DataObject
	{
		private static $db = array( 
			"SortOrder" => "Int",
			"Title" => "Varchar(255)",
			"Address" => "Varchar(255)",
			"MapLatitude" => "Varchar(255)",
			"MapLongitude" => "Varchar(255)"
		);
		
		private static $default_sort = "SortOrder";
		
		private static $summary_fields = array(
			"Title" => "Title",
			"Address" => "Address"
		);
		
		private static $has_one = array(
			"LocationsPage" => "LocationsPage"
		); 		
		
		public function getCMSFields()
        {
			$fields = parent::getCMSFields();
			$fields->push( new HiddenField('SortOrder',null,$fields->dataFieldByName('SortOrder')->Value()) );
			$fields->push( new HiddenField('MapLatitude',null,$fields->dataFieldByName('MapLatitude')->Value()) );
			$fields->push( new HiddenField('MapLongitude',null,$fields->dataFieldByName('MapLongitude')->Value()) );
			return $fields;
        }
		
		public function getLocation($address=false){
			$google = "https://maps.google.com/maps/api/geocode/json?sensor=false&address=";
			$url = $google.urlencode($address);
			
			$resp_json = $this->curl_file_get_contents($url);
			$resp = json_decode($resp_json, true);
	
			if($resp['status']='OK'){
				return $resp['results'][0]['geometry']['location'];
			}else{
				return false;
			}
		}
		
		private function curl_file_get_contents($URL)
		{
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_URL, $URL);
			$contents = curl_exec($c);
			curl_close($c);
	
			if ($contents) return $contents;
				else return FALSE;
		}
		
		function onAfterWrite()
		{
			parent::onAfterWrite();
			
			$location = $this->getLocation($this->Address);
			if ($location)
			{
				$this->MapLatitude = $location['lat'];
				$this->MapLongitude = $location['lng'];
				$this->write();
			}
		}
		
		public function canCreate($member = null) { return true; }
		public function canDelete($member = null) { return true; }
		public function canEdit($member = null)   { return true; }
		public function canView($member = null)   { return true; }
	}
	
	class LocationsPage extends Page
	{
		private static $icon = "themes/mysite/images/icons/icon-locations";
		
		private static $db = array(
			"MapType" => "Varchar(255)",
			"MapDirections" => "Boolean"
		);
		
		private static $has_many = array(
			"Locations" => "Location"
		);
		
		public function getCMSFields()
		{
			$fields = parent::getCMSFields();
			$fields->addFieldToTab("Root.MapDetails", new DropdownField("MapType", "Map Display Type", array("ROADMAP"=>"Roadmap","SATELLITE"=>"Satellite","HYBRID"=>"Hybrid","TERRAIN"=>"Terrain"),"Roadmap"));
			$locations_config = GridFieldConfig::create()->addComponents(				
				new GridFieldSortableRows('SortOrder'),
				new GridFieldToolbarHeader(),
				new GridFieldAddNewButton('toolbar-header-right'),
				new GridFieldSortableHeader(),
				new GridFieldDataColumns(),
				new GridFieldPaginator(10),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldDetailForm()				
			);
			$fields->addFieldToTab('Root.MapDetails', new GridField('Locations','Locations',$this->Locations(),$locations_config));
			$fields->addFieldToTab("Root.MapDetails", new CheckboxField("MapDirections", "Display Directions Widget?"));
			return $fields;
		}			
	}
	
	class LocationsPage_Controller extends Page_Controller
	{
		
		private static $allowed_actions = array(
			"directions",
			"printview"			
		);	
		
		public function init()
		{
			Requirements::javascript("https://maps.googleapis.com/maps/api/js?key=AIzaSyAXy4BLGXyLMakRQbrMVrFxS2KiXSj51cM&sensor=false");
			parent::init();
		}
		
		function PageCSS()
		{
			$dir = ViewableData::ThemeDir();
			return array_merge(
				parent::PageCSS(),
				array(
					$dir."/css/form.css"
				)
			);
		}
		
		function CustomJS()
		{
			$js = parent::CustomJS();
			$js .= 'var MapType = "'.$this->MapType.'";
					var address_objects = [];';
			if($locations = $this->Locations()){
				foreach($locations as $key => $l){
					$js .= 'address_objects['.$key.'] = {"Title":"'.$l->Title.'","Address":"'.$l->Address.'","LatLng":['.$l->MapLatitude.','.$l->MapLongitude.']};';
				}
			}
			$js .= 'var Avgs = '.$this->Avgs().';
					var PageLink = "'.$this->Link().'";';
			
			
			return $js;
		}
		
		public function Avgs(){			
			$TotalLat = 0;
			$TotalLong = 0;
			$Total = 0;
			if($locations = $this->Locations())
			{
				foreach($locations as $l){
					$TotalLat += $l->MapLatitude;
					$TotalLong += $l->MapLongitude;
					$Total++;
				}
				if($Total)return "[".$TotalLat/$Total.",".$TotalLong/$Total."]";
			}
			return false;
		}
		
		public function directionsAPI()
		{
			$to_addy = urlencode($this->request->param('ID'));
			$from_addy = urlencode($this->request->param('OtherID'));
			$path = "http://maps.googleapis.com/maps/api/directions/json?origin=".$from_addy."&destination=".$to_addy."&sensor=false";
			$rows = file_get_contents($path,0,null,null);
			$directions_output = json_decode($rows, true);
			$ajax_data = false;
			
			if($directions_output['routes']){
				$data = $directions_output['routes'][0]['legs'][0];  //assumes best route and no waypoints
				$i = 1;
				$steps = "";
				foreach($data['steps'] as $step){
					$steps .= "<div class='step'><span class='step_number'>".$i.".</span><span class='step_text'>".$step['html_instructions']."</span><span class='step_distance'>".$step['distance']['text']."</span></div>";	
					$i++;
				}
				
				$ajax_data = array(
					"StartAddress" => $data['start_address'],
					"EndAddress" => $data['end_address'],
					"Distance" => $data['distance']['text'],
					"Duration" => $data['duration']['text'],
					"GoogleLink" => "https://maps.google.com/maps?q=".$from_addy."+to+".$to_addy,
					"PrintLink" => $this->AbsoluteLink()."printview/".$from_addy."/".$to_addy,
					"PageLink" => $this->AbsoluteLink()."directions/".$from_addy."/".$to_addy,
					"Steps" => $steps
				);
				
			}
			
			return $ajax_data;
		}
		
		public function directions()
		{
			$ajax_data = $this->directionsAPI();
			
			if($ajax_data){
				return Director::is_ajax() ? $this->Customise($ajax_data)->renderWith("ContactPage_directions") : $this->Customise($ajax_data);
			} else {
				return "<p>No routes were found from that destination address.</p>";	
			}
		}
		
		public function printview()
		{
			$ajax_data = $this->directionsAPI();
			
			if($ajax_data){
				return $this->Customise($ajax_data)->renderWith("ContactPage_printview");
			} else {
				return "<p>No routes were found from that destination address.</p>";	
			}
		}
		
		public function NeedLocationsSelect(){
			return $this->Locations()->Count() > 1;	
		}
	}