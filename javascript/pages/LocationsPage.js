var map;
var bounds;
(function($){
	"use strict";
	$(window).load(function(){
		if((typeof google === 'object')&&(typeof address_objects === 'object')&&(address_objects.length)){
			bounds = new google.maps.LatLngBounds();
			
			var myOptions = {
			  zoom: 12,
			  center: new google.maps.LatLng(window.Avgs[0],window.Avgs[1]),
			  zoomControl:true,
			  streetViewControl:false,
			  mapTypeControl:false,
			  mapTypeId: google.maps.MapTypeId.MapType
			};
			map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
				
			for(var i = 0; i < address_objects.length; i++){
				addMarker(address_objects[i]);
			}	
			if(address_objects.length>1){
				map.fitBounds(bounds);
			}
		}
	});
	
	window.addMarker = function(object) {
		var latlng = new google.maps.LatLng(object.LatLng[0], object.LatLng[1]);
		bounds.extend(latlng);
		var marker = new google.maps.Marker({
			map: map,
			position: latlng,
			title:object.Title,
		});
		var heading = object.Title ? "<h5>"+object.Title+"</h5>" : "";
		var contentString = '<div id="map-popup">'+heading+'<p>'+object.Address+'</p><p><a href="https://maps.google.com/maps?q='+object.Address+'" target="_blank">Get Driving Directions</a></p></div>';
		var infowindow = new google.maps.InfoWindow({
			content: contentString
		});
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map,marker);
		});
	}
	
	window.getDirections = function(){
		$("#directions_ajax").empty();
		$.ajax({
			url: PageLink+"_directions/",
			data: {
				'to': $('#to_address').val(),
				'from': $('#from_address').val()
			},
			global: false,
			dataType: "html",
			async: true,
			cache: true,
			success: function(data) {
				$("#directions_ajax").html(data);
				jScroll('directions_top');
			}
		});
	}
}(jQuery));