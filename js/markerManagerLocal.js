/**
 * @brief Code based on Google's MarkManager examples.
 *
 * @file index.php
 * $Revision: 205 $
 * $Author: fmcclurg $
 * @author Fred R. McClurg, University of Iowa
 * $Date:: 2013-01-03 13:34:19 #$
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/js/markerManagerLocal.js $
 * @ref $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/js/markerManagerLocal.js $
 * @see http://gmaps-utility-library-dev.googlecode.com/svn/trunk/markermanager/examples/weather_map.html
 */

   var IMAGES = [ "sun", "rain", "snow", "storm" ];
   var ICONS = [];
   var map = null;
   var mgr = null;

   function setupMap(mapId, mapType) 
   {
      var options = 
         {
            zoom: 4,
            center: new google.maps.LatLng(48.25, 11.00),
            mapTypeId: mapType
         };
      
      map = new google.maps.Map(document.getElementById( mapId ), options);

      var listener = google.maps.event.addListener(map, 'bounds_changed', function(){
         setupWeatherMarkers();
         // setupDataMarkers();
         google.maps.event.removeListener(listener);
      });

      // if (GBrowserIsCompatible()) 
      // {
         // map = new GMap2(document.getElementById(mapId));
         // map.addControl(new GLargeMapControl());
         // map.setCenter(new GLatLng(48.25, 11.00), 4);
         // map.enableDoubleClickZoom();
         // window.setTimeout(setupWeatherMarkers, 0);
         // window.setTimeout(setupDataMarkers, 0);
      // }
   }  // function setupMap() 


   function getWeatherIcon() 
   {
      var i = Math.floor(IMAGES.length*Math.random());
      
      if (!ICONS[i]) 
      {
         var icon = new GIcon();
         icon.image = "images/" + IMAGES[i] + ".png";
         icon.iconAnchor = new GPoint(16, 16);
         icon.infoWindowAnchor = new GPoint(16, 0);
         icon.iconSize = new GSize(32, 32);
         icon.shadow = "images/" + IMAGES[i] + "-shadow.png";
         icon.shadowSize = new GSize(59, 32);
         ICONS[i] = icon;
      }
      
      return ICONS[i];
   }  // function getWeatherIcon() 


   function getRandomPoint() 
   {
      var lat = 48.25 + (Math.random() - 0.5)*14.5;
      var lng = 11.00 + (Math.random() - 0.5)*36.0;
      return new GLatLng(Math.round(lat*10)/10, Math.round(lng*10)/10);
   }  // function getRandomPoint() 


   function getWeatherMarkers(n) 
   {
      var batch = [];
      
      for (var i = 0; i < n; ++i) 
      {
         batch.push(new GMarker(getRandomPoint(), { icon: getWeatherIcon() }));
      }
      
      return batch;
   }  // function getWeatherMarkers(n) 

   
   function getJsonData() 
   {
      var batch = [];
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
      
      for (var i = 0; i < json.length; ++i) 
      {
         var data = json[i];
         var latLng = new google.maps.LatLng(data.lat, data.lng);
         
         // alert( "lat: " + data.lat + " lng: " + data.lng );
         
         // var tmpIcon = getWeatherIcon();

         var marker = new google.maps.Marker({
             position: latLng,
             // shadow: tmpIcon.shadow,
             // icon: tmpIcon.icon,
             // shape: tmpIcon.shape,
             title: data.title
             });
         
         batch.push( marker );
      }
      
      return batch;
   }  // function getJsonData() 


   function setupDataMarkers() 
   {
      mgr = new MarkerManager(map);
      mgr.addMarkers(getJsonData(), 3);
      mgr.addMarkers(getJsonData(), 6);
      mgr.addMarkers(getJsonData(), 8);
      mgr.refresh();
   }  // function setupDataMarkers() 
   

   function setupWeatherMarkers() 
   {
      mgr = new MarkerManager(map);
      mgr.addMarkers(getWeatherMarkers(20), 3);
      mgr.addMarkers(getWeatherMarkers(200), 6);
      mgr.addMarkers(getWeatherMarkers(1000), 8);
      mgr.refresh();
   }  // function setupWeatherMarkers() 
   