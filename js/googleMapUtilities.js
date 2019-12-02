/**
 * @brief Set of handy google map functions
 *
 * @file googleMapUtilties.js
 * @version 1.0
 * $Revision: 210 $
 * $Author: fmcclurg $
 * @author Fred R. McClurg, University of Iowa
 * $Date:: 2013-03-27 13:09:28 #$: Date of last commit
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/js/googleMapUtilities.js $
 */


/**
 * @brief global variables
 */
var IMAGES = [ 'sun', 'rain', 'snow', 'storm' ];
var ICONS = [];
var map = null;
var mgr = null;
 

/**
 * @brief Set initial settings of map
 *
 * @param  mapId       ID of the map canvas
 * @param  mapType     Default map type (ROADMAP, SATELLITE, HYBRID, TERRAIN)
 * @param  lat         Default latitude of the map center
 * @param  lng         Default longitude of the map center
 * @param  zoom        Default zoom level of the map
 * @param  maxMinZoom  If true auto max/min and zoom to marker boundary
 * @param  dataType    If value = "geoCode", data requires geocoded addresses.  
 *                     If value = "latLng", data is lat/lng
 * @param  json        JSON structure containing the marker data
 */
function setupMap( mapId, 
                   mapType, 
                   lat, 
                   lng, 
                   zoom, 
                   maxMinZoom, 
                   dataType, 
                   json ) 
{
   console.log( "Function: setupMap()" );
   
   var latLngCenter = new google.maps.LatLng(lat, lng);  // Geographic Center of the Conterminous United States
   
   var options = {
     zoom: zoom,
     scrollwheel: false,  // turn off the scroll wheel for zooming
     center: latLngCenter,
     mapTypeId: mapType
   };
   
   map = new google.maps.Map(document.getElementById( mapId ), options);

   if ( json.length )
   {
      var boundsListener = google.maps.event.addListener(map, 'bounds_changed', function(){
         // setupWeatherMarkers();
         setupDataMarkers( maxMinZoom, dataType, json );
         google.maps.event.removeListener(boundsListener);
      });
      
      var dataListener = google.maps.event.addListener(map, 'idle', function(){
         setLatLngZoomText();
         // google.maps.event.removeListener(dataListener);
      });
   }

   // var mapListener = google.maps.event.addListener(map, 'idle', function(){
       // setLatLngZoomText();
   // });
}  // function setupMap() {


/**
 * @brief Sets the text fields with the latitude, longitude, and zoom
 */
function setLatLngZoomText()
{
   console.log( "Function: setLatLngZoomText()" );
   
   // obtain the current map center
   var latLng = map.getCenter();
   var lat = latLng.lat();
   var lng = latLng.lng();
   
   var zoom = map.getZoom();
   
   var latText = document.getElementById('latCtr');
   var lngText = document.getElementById('lngCtr');
   var zoomText = document.getElementById('zoom');
   
   // set the text fields with the current values
   latText.value = lat;
   lngText.value = lng;
   zoomText.value = zoom;
   
}  // function setLatLngZoomText()



/**
 * @brief Retrieves an icon
 *
 * @return An array of icons
 */
function getWeatherIcon() {
   var i = Math.floor(IMAGES.length*Math.random());
   if (!ICONS[i]) {          
     var iconImage = new google.maps.MarkerImage('images/' + IMAGES[i] + '.png',
         new google.maps.Size(32, 32),
         new google.maps.Point(0,0),
         new google.maps.Point(0, 32)
     );
     
     var iconShadow = new google.maps.MarkerImage('images/' + IMAGES[i] + '.png',
         new google.maps.Size(32, 59),
         new google.maps.Point(0,0),
         new google.maps.Point(0, 32)
     );
     
     var iconShape = {
         coord: [1, 1, 1, 32, 32, 32, 32, 1],
         type: 'poly'
     };

     ICONS[i] = { 
       icon : iconImage,
       shadow: iconShadow,
       shape : iconShape
     };
     
   }
   return ICONS[i];
}  // function getWeatherIcon()



/**
 * @brief Retrieves an icon
 *
 * @return An array of icons
 */
function getWeatherIcon() {
   console.log( "Function: getWeatherIcon()" );
   
   var i = Math.floor(IMAGES.length*Math.random());
   if (!ICONS[i]) {          
     var iconImage = new google.maps.MarkerImage('images/' + IMAGES[i] + '.png',
         new google.maps.Size(32, 32),
         new google.maps.Point(0,0),
         new google.maps.Point(0, 32)
     );
     
     var iconShadow = new google.maps.MarkerImage('images/' + IMAGES[i] + '.png',
         new google.maps.Size(32, 59),
         new google.maps.Point(0,0),
         new google.maps.Point(0, 32)
     );
     
     var iconShape = {
         coord: [1, 1, 1, 32, 32, 32, 32, 1],
         type: 'poly'
     };

     ICONS[i] = { 
       icon : iconImage,
       shadow: iconShadow,
       shape : iconShape
     };
     
   }
   return ICONS[i];
 }  // function getWeatherIcon()

function getRandomPoint() {
   console.log( "Function: getRandomPoint()" );
   
   var lat = 48.25 + (Math.random() - 0.5) * 14.5;
   var lng = 11.00 + (Math.random() - 0.5) * 36.0;
   return new google.maps.LatLng(Math.round(lat * 10) / 10, Math.round(lng * 10) / 10);
 }

 function getWeatherMarkers(n) {
   console.log( "Function: getWeatherMarkers()" );
   
   var batch = [];
   for (var i = 0; i < n; ++i) {
     var tmpIcon = getWeatherIcon();  
     
     batch.push(new google.maps.Marker({
         position: getRandomPoint(),
         shadow: tmpIcon.shadow,
         icon: tmpIcon.icon,
         shape: tmpIcon.shape,
         title: 'Weather marker'
         })
     );        
   }
   return batch;
 }

 function setupWeatherMarkers() {
   console.log( "Function: setupWeatherMarkers()" );
   
   mgr = new MarkerManager(map);
   
   google.maps.event.addListener(mgr, 'loaded', function(){
       mgr.addMarkers(getWeatherMarkers(20), 3);
       mgr.addMarkers(getWeatherMarkers(200), 6);
       mgr.addMarkers(getWeatherMarkers(1000), 8);
       
       mgr.refresh();          
   });      
 }
 

/**
 * @brief Retrieve lat/lng data points from JSON data
 *
 * @param  dataType    If value = "geoCode", data requires geocoded addresses.  
 *                     If value = "latLng", data is lat/lng
 * @param  json        JSON structure containing the marker data
 * @retval batch       An array of lat/lng points
 */
 function getJsonData( dataType, json ) 
 {
    console.log( "Function: getJsonData()" );
    
    var batch = [];
    
    // Example:
    /*
    var json = 
       [
          {
             "title": "Stockholm",
             "lat": 59.3,
             "lng": 18.1,
             "description": "Stockholm is the capital and the largest city of Sweden and constitutes the most populated urban area in Scandinavia with a population of 2.1 million in the metropolitan area (2010)"
          },
          {
             "title": "Oslo",
             "lat": 59.9,
             "lng": 10.8,
             "description": "Oslo is a municipality, and the capital and most populous city of Norway with a metropolitan population of 1,442,318 (as of 2010)."
          },
          {
             "title": "Copenhagen",
             "lat": 55.7,
             "lng": 12.6,
             "description": "Copenhagen is the capital of Denmark and its most populous city, with a metropolitan population of 1,931,467 (as of 1 January 2012)."
          }
       ];
    */
    
    for (var i = 0; i < json.length; ++i) 
    {
       var data = json[i];
       var latLng = new google.maps.LatLng(data.lat, data.lng);
       
       // console.log( "lat: " + data.lat + " lng: " + data.lng );
       
       // var tmpIcon = getWeatherIcon();

       var marker = new google.maps.Marker({
           position: latLng,
           // shadow: tmpIcon.shadow,
           // icon: tmpIcon.icon,
           // shape: tmpIcon.shape,
           title: data.title,
           url: data.url
       });
       
       google.maps.event.addListener(marker, 'click', function() {
          window.location.href = this.url;
       });
       
       batch.push( marker );
    }
    
    return batch;
 }  // function getJsonData() 


/**
 * @brief Create the data markers
 *
 * @param  maxMinZoom  If true auto max/min and zoom to marker boundary
 * @param  json        JSON structure containing the marker data
 */
 function setupDataMarkers( maxMinZoom, dataType, json ) 
 {
    mgr = new MarkerManager(map);
   
    google.maps.event.addListener(mgr, 'loaded', function(){
       mgr.addMarkers(getJsonData( dataType, json ), 1);
       
       if ( maxMinZoom )
       {
          setupMarkerBounds( json );
       }
       
       mgr.refresh();
    });      
 }  // function setupDataMarkers() 


/**
 * @brief Determine the bounds of a set of markers
 *
 * @param  json        JSON structure containing the marker data
 */
function setupMarkerBounds( json )
{
   console.log( "Function: setupMarkerBounds()" );
   
   // initialize variables
   var latLngBounds = new google.maps.LatLngBounds();
      
   for (var i = 0; i < json.length; ++i)  // first one already in bounds
   {
      var lat = json[i].lat;
      var lng = json[i].lng;
      
      // data = json[i];
      var latLng = new google.maps.LatLng(lat, lng);
      
      latLngBounds.extend( latLng );
      
      // sw = latLngBounds.getSouthWest();
      // ne = latLngBounds.getNorthEast();
      
      // console.log( "Lat/Lng: " + lat + ", " + lng + "\n" +
                   // "SW Lat/Lng: " + sw.lat() + ", " + sw.lng() + "\n" +
                   // "NE Lat/Lng: " + ne.lat() + ", " + ne.lng() );
   }
   
   
   // alert( "Lat/Lng: " + lat + ", " + lng + "\n" +
          // "SW Lat/Lng: " + sw.lat() + ", " + sw.lng() + "\n" +
          // "NE Lat/Lng: " + ne.lat() + ", " + ne.lng() );
   
   map.fitBounds( latLngBounds );
   
   // because generally, the fit bounds does not zoom quite enough
   var zoomLevel = map.getZoom();
   map.setZoom( zoomLevel + 1 );
   
   // visualize the bounding box (for debugging)
   // new google.maps.Rectangle( {
      // bounds: latLngBounds,
      // map: map,
      // fillColor: "#000000",
      // fillOpacity: 0.2,
      // strokeWeight: 0
    // } );
    
}  // function setupMarkerBounds()
