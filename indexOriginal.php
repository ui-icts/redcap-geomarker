<?php
/**
 * @brief REDCap plugin that generates a google map with a marker and infowindow for data
 *
 * @file index.php
 * $Revision: 210 $
 * $Author: fmcclurg $
 * @author Fred R. McClurg, University of Iowa
 * $Date:: 2013-03-27 13:09:28 #$
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/indexOriginal.php $
 * @ref $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/indexOriginal.php $
 */

# Display verbose error reporting
require_once ('lib/errorReporting.php');

# HTML utility functions
require_once('lib/htmlUtilities.php');

# JSON utility functions
require_once('lib/redcapUtilities.php');

// Call the REDCap Connect file in the main "redcap" directory
require_once ('../redcap_connect.php');

// Restrict this plugin to a specific REDCap project (in case user's randomly find the plugin's URL)
if ( PROJECT_ID != $_REQUEST['pid'] )
{
   $exitMessage = sprintf( "This plugin is only accessible to users from project \"%s\".", app_title );
   exit( $exitMessage );
}

// OPTIONAL: Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

// Your HTML page content goes here
?>

<style type="text/css">
   html { height: 100% }
   body { height: 100%; margin: 0; padding: 0 }
   #mapCanvas { height: 100% }
</style>

<!-- jQuery -->
<!-- <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script> -->
<script type="text/javascript" src="http://code.jquery.com/jquery.js"></script>

<!-- jQuery UI -->
<!-- <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script> -->
<script type="text/javascript" src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>

<!-- Note: Please replace the client value "client=gme-universityofiowa" with the 
     the map API key value issued from google if you have a premier license -->

<!-- Google Maps API Premier license -->

<script type="text/javascript"
        src="http://maps.google.com/maps?file=api&v=2.x&client=gme-universityofiowa">
        // src="https://maps.googleapis.com/maps/api/js?&client=gme-universityofiowa&sensor=false">
        
   /* Note: If you don't have a premier license, specify your "key=keyValueHere" 
      issued from Google similar to the following below */
     
   /* Google Maps API Key */
   // src="http://maps.google.com/maps?file=api&v=2.x&key=ABQIAAAAjU0EJWnWPMv7oQ-jjS7dYxT8bemrB74QmF-ljSt0r6xw5vxKjRRisAno3SZU83rSdUS3zR_JADzEUA" type="text/javascript">
</script>

<!-- MarkerManager to handle many markers -->
<!-- <script type="text/javascript" src="https://gmaps-utility-library.googlecode.com/svn/trunk/markermanager/release/src/markermanager.js"></script> -->
<!-- <script type="text/javascript" src="https://google-maps-utility-library-v3.googlecode.com/svn/tags/markermanager/1.0/src/markermanager.js"></script> -->
<!-- <script type="text/javascript" src="https://google-maps-utility-library-v3.googlecode.com/svn/trunk/markermanager/src/markermanager.js"></script> -->
<script type="text/javascript" src="https://gmaps-utility-library-dev.googlecode.com/svn/tags/markermanager/1.1/src/markermanager.js"></script>
    

<!-- jQuery plugin colorpicker -->
<script type="text/javascript" src="jquery/colorpicker/js/evol.colorpicker.js"></script>

<!-- JavaScript required by the following graph to image utilities -->
<script type="text/javascript" src="https://canvg.googlecode.com/svn/trunk/rgbcolor.js"></script>
<script type="text/javascript" src="https://canvg.googlecode.com/svn/trunk/canvg.js"></script>

<!-- Keep selected cookie state persistant -->
<script type="text/javascript" src="jquery-ui/development-bundle/external/jquery.cookie.js"></script>

<!-- jQuery tab customization -->
<link rel="stylesheet" type="text/css" href="jquery-ui/css/custom-theme/jquery-ui-1.8.24.custom.css" />

<!-- colorpicker CSS customization -->
<link rel="stylesheet" href="jquery/colorpicker/css/evol.colorpicker.css" />

<script type="text/javascript">
   $("#geoMarkerForm").submit(function() {
      alert( "Submit handler called." );
      console.log("Form submitted handled.");
      event.preventDefault();
      // return false;
   });
      
      /*
      $.get('<?php echo $_SERVER['PHP_SELF']; ?>'), function(data) {
         $('#result').html(data);
         alert('Submit was performed:' . data );
         console.log('Form get performed.');
      }
      */
</script>

<script type="text/javascript">
   $(document).ready( function() {
      // initialize();
      // init();
      setupMap();
      
      $("#tabs").tabs({
         cookie: {
            // store cookie for a day
            expires: 5
         }
      });
   });
      
    // global variables
    var IMAGES = [ "sun", "rain", "snow", "storm" ];
    var ICONS = [];
    var map = null;
    var mgr = null;

    function setupMap() {
      console.log('function setupMap');
      
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("mapCanvas"));
        map.addControl(new GLargeMapControl());
        map.setCenter(new GLatLng(48.25, 11.00), 4);
        map.enableDoubleClickZoom();
        window.setTimeout(setupWeatherMarkers, 0);
      }
    }  // function setupMap()

    function getWeatherIcon() {
       console.log( "function getWeatherIcon()" );
      var i = Math.floor(IMAGES.length*Math.random());
      if (!ICONS[i]) {
        var icon = new GIcon();
        icon.image = "images/" 
            + IMAGES[i] + ".png";
        icon.iconAnchor = new GPoint(16, 16);
        icon.infoWindowAnchor = new GPoint(16, 0);
        icon.iconSize = new GSize(32, 32);
        icon.shadow = "images/" 
            + IMAGES[i] + "-shadow.png";
        icon.shadowSize = new GSize(59, 32);
        ICONS[i] = icon;
      }
      return ICONS[i];
    }  // function getWeatherIcon()

    function getRandomPoint() {
       console.log( "function getRandomPoint()" );
      var lat = 48.25 + (Math.random() - 0.5)*14.5;
      var lng = 11.00 + (Math.random() - 0.5)*36.0;
      return new GLatLng(Math.round(lat*10)/10, Math.round(lng*10)/10);
    } // function getRandomPoint()

    function getWeatherMarkers(n) {
       console.log( "function getWeatherMarkers()" );
      var batch = [];
      for (var i = 0; i < n; ++i) {
        batch.push(new GMarker(getRandomPoint(), { icon: getWeatherIcon() }));
      }
      return batch;
    }  // function getWeatherMarkers()

    function setupWeatherMarkers() {
       console.log( "function setupWeatherMarkers()" );
      mgr = new MarkerManager(map);
      mgr.addMarkers(getWeatherMarkers(20));
      // mgr.addMarkers(getWeatherMarkers(200), 6);
      // mgr.addMarkers(getWeatherMarkers(1000), 8);
      mgr.refresh();
    }  // function setupWeatherMarkers()
    
      /*
      $("form#geoMarkerForm").submit(function(e) {
         console.log( "form submission was handled!" );
         
         var lat = $('select[name=lat] option:selected').val();
         var lng = $('select[name=lng] option:selected').val();
         var business = $('select[name=business] option:selected');
         var address = $('select[name=address] option:selected').val();
         var city = $('select[name=city] option:selected').val();
         var state = $('select[name=state] option:selected').val();
         var zip = $('select[name=zip] option:selected').val();
         var hoverText = $('select[name=hoverText] option:selected').val();
         var mapType = $('select[name=mapType] option:selected').val();
         var width = $('input[name=width]').val();
         var height = $('input[name=height]').val();
         var latLong = $('input[name=latLong]').val();
         
         $.get(
            'get.php', {
               business:business, 
               address:address,
               city:city,
               state:state,
               zip:zip,
               hoverText:hoverText,
               mapType:mapType,
               width:width,
               height:height
            },
            function(data) { $('#result').html( data ); }
         );
         
         e.preventDefault();
         // return false;
      });
      */
      
   // define global variables
   // var map;
   // var geocoder;
   // var markerMgr;
   // var latLngList = [];
   // var bounds = new google.maps.LatLngBounds();
      
<?php
   if ( strlen( $_REQUEST['mapType'] ) > 0 )
   {
      $mapType = sprintf( "google.maps.MapTypeId.%s", $_REQUEST['mapType'] );
   }
   else
   {
      // the fallback map type
      $mapType = "google.maps.MapTypeId.ROADMAP";
   }
?>
      
   function init()
   {
      console.log( "function init() ..." );
      
      var latLngCenter = new google.maps.LatLng(39.828182, -98.57955);  // Geographic Center of the Conterminous United States
      
      // Creating a map
      var mapOptions = {
         zoom: 4,
         scrollwheel: false,  // turn off the scroll wheel for zooming
         center: latLngCenter,
         mapTypeId: <?php echo $mapType; ?>
      };
      
      map = new google.maps.Map(document.getElementById('mapCanvas'), mapOptions);
      
<?php
      // if ( isset( $_REQUEST['doit'] ) )
      // {
?>
         displayMarkers();
<?php
      // }
?>
   }  // function init()
    
   function displayMarkers()
   {
      console.log( "function displayMarkers()" );
      
      // Creating a new MarkerManager object
      var mgr = new MarkerManager(map);
    
      // Creating an array that will contain all of the markers
      var markers = [];

      // Setting the boundaries within where the markers will be created
      var southWest = new google.maps.LatLng(24, -126);
      var northEast = new google.maps.LatLng(50, -60);
      var lngSpan = northEast.lng() - southWest.lng();
      var latSpan = northEast.lat() - southWest.lat();
    
      /*
      var weatherData = 
         {'weather': [
            {
               'lat': 40.75,
               'lng': -73.98
            },
            {
               'lat': 47.62,
               'lng': -122.34
            },
            {
               'lat': 37.75,
               'lng': -122.41
            },
         ]};
      
      for ( var i = 0; i < weatherData.weather.length; i++ )
      {
         var weather = weatherData.weather[i];
         var latlng = new google.maps.LatLng(weather.lat, weather.lng);
         
         // Creating a marker
         var marker = new google.maps.Marker({
            position: latlng
         });
         
         // Adding the marker to the array
         markers.push(marker);
      }
      */
      
      // Creating markers at random locations
      for (var i = 0; i < 100; i++) 
      {
         // Calculating a random location
         var lat = southWest.lat() + latSpan * Math.random();
         var lng = southWest.lng() + lngSpan * Math.random();
         var latlng = new google.maps.LatLng(lat, lng);
         
         // Creating a marker
         var marker = new google.maps.Marker({
            position: latlng
         });
         
         // Adding the marker to the array
         markers.push(marker);
      }
    
      // Making sure the MarkerManager is properly loaded before we use it
      google.maps.event.addListener(mgr, 'loaded', function() {
         console.log( "addListener loaded ..." );
      
         // Adding the markers to the MarkerManager
         mgr.addMarkers(markers, 1);
         
         // Adding the markers to the map
         mgr.refresh();
      });
   }  // function displayMarkers()
   
   
   function initialize() 
   {
      console.log( "function initialize()" );
      
      geocoder = new google.maps.Geocoder();
      var latLngCenter = new google.maps.LatLng(39.828182, -98.57955);  // Geographic Center of the Conterminous United States
      
      var mapOptions = {
         zoom: 4,
         center: latLngCenter,
         scrollwheel: false,
         mapTypeId: <?php echo $mapType; ?> };
         
      map = new google.maps.Map(document.getElementById('mapCanvas'), mapOptions);
      
<?php
      if ( isset( $_REQUEST['doit'] ) )
      {
?>
         // MultiAddress();
         
         markerMgr = new MarkerManager(map);
         
         var listener = google.maps.event.addListener(markerMgr, 'loaded', function(){
               markerMgr.addMarkers(getMarkers(200), 3);
               markerMgr.refresh();
         });
<?php
      }
?>
   }  // function initialize
         
   function getMarkers(n)
   {
      console.log( "function getMarkers()" );
      
      var batch = [];
      
      for ( var i = 0; i < n; i++ )
      {
         batch.push(new google.maps.Marker({
            postion: getRandomPoint(),
            title: "Marker #" + i
            })
         );
      }
      
      return( batch );
   }
   
   function getRandomPoint()
   {
      // console.log( "function getRandomPoint()" );
      
      var lat = Math.random() * 180 - 90;
      var lng = Math.random() * 360 - 180;
      
      return new google.maps.LatLng( lat, lng );
   }
      

function codeAddress( address ) 
{
   // var address = document.getElementById('address').value;
     
      if ( address.length != 0 )
      {
         geocoder.geocode( { 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) 
            {
               // map.setCenter(results[0].geometry.location);
               var latLong = results[0].geometry.location;
                                                     
               // alert( "latLong: " + latLong );
               
               var marker = new google.maps.Marker({
                     map: map,
                     position: latLong,
                     title: address,
                     url: 'http://google.com'
               });
               
               // var infowindow = new google.maps.InfoWindow({
                  // content: address
               // });

               // bind click event to each marker
               google.maps.event.addListener(marker, 'click', function() {
                  // infowindow.open(marker.get('map'), marker);
                  window.location.href = this.url;
               });
               
               console.log( "latLong: " + latLong );
               bounds.extend(latLong);
               map.fitBounds(bounds);
               console.log( "bounds: " + bounds );
               latLngList.push(latLong);
            } 
            else 
            {
               alert('Geocode was not successful for the following reason: ' + status);
            }
         });
      }
   }
   
<?php
   if ( isset( $_REQUEST['doit'] ) )
   {
      // initialize arrays
      $selectedGeoKeys = array();
      
      // list of all possible geolocation fields
      $geoLocationKeys = array( 'business', 'address', 'city', 'state', 'zip' );
   
      foreach ( $geoLocationKeys as $key )
      {
         if ( strlen( $_REQUEST[$key] ) > 0 )
         {
            // obtain a list of all selected geolocation columns
            array_push( $selectedGeoKeys, $_REQUEST[$key] );
         }
      }
      
      /*
       * array( 
       *    231 => 
       *    array (
       *       'record' => '20182',
       *       'airport_name' => 'Iowa City Municipal Airport',
       *       'iso_region' => 'US-IA',
       *       'municipality' => 'Iowa City',
       *    ),
       *    308 => 
       *    array (
       *       'record' => '8330',
       *       'airport_name' => 'University of Iowa Hospitals & Clinic Heliport',
       *       'iso_region' => 'US-IA',
       *       'municipality' => 'Iowa City',
       *    ),
       * )
       */
      // get the value of the selected location fields
      $arrayOfHashes = GetMultipleFieldValues( $_REQUEST['pid'],
                                               $selectedGeoKeys );
   
      $geolocationHash = ConcatHashValues( $arrayOfHashes );
      
      printf( "/*\n" );
      
       var_export( $geolocationHash );
       
      // foreach ( $arrayOfHashes as $array )
      // {
         // printf( "// \$arrayOfHashes[%s]: %s\n", $key, $arrayOfHashes[$key] );
      // }
      
      printf( "*/\n" );
      
   }
?>

   function MultiAddress()
   {
      // var cities = [ "Pickering, MO",
                     // "Maryville, MO",
                     // "Los Alamos, NM",
                     // "Idaho Falls, ID",
                     // "Marion, IL",
                     // "Cedar Rapids, IA",
                     // "North Liberty, IA",
                     // "Iowa City Municipal Airport, Iowa City, US-IA",
                     // "University of Iowa Hospitals & Clinic Heliport, Iowa City, US-IA" ];

      var cities = <?php echo json_encode( $geolocationHash ) ?>;
      
      for ( city in cities )
      {
         console.log( "cities[" + city + "]: " + cities[city] );
         
         // this works for a few markers
         codeAddress( cities[city] );
      }
      
      // console.log( "bounds: " + bounds );
      
      // map.setZoom(20);
      // map.fitBounds(bounds);
      // map.panToBounds(bounds);
      
      // for ( var i = 0; i < latLngList.length; i++ )
      // {
         // bounds.extend(latLngList[i]);
      // }
      
      // console.log( "before fitBounds" );
      
      // if ( latLngList.length >= cities.length )
      // {
         // map.fitBounds(bounds);
         // map.panToBounds(bounds);
         // map.getZoom(20);
      // }
      
      // the default zoom is not enough
      var listener = google.maps.event.addListener(map, "idle", function() { 
         var zoomLevel = map.getZoom();
         console.log( "zoomLevel: " + zoomLevel );
         map.setZoom( zoomLevel + 1 );
         google.maps.event.removeListener(listener); 
      });
      
      // console.log( "after fitBounds" );
      console.log( "bounds: " + bounds );
   }
</script>

<?php
   $mapStyle = sprintf( "margin: 5px auto; width: %dpx; height: %dpx;", SetStickyValue( 'width', 600 ),
                                                      SetStickyValue( 'height', 400 ) );
                                                      
?>

<div id="mapCanvas" style="<?php echo $mapStyle ?>"></div>

<p />

<!-- <form id="geoMarkerForm" method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>"> -->
<form id="geoMarkerForm" method="get">

<?php
   if ( isset( $_REQUEST['doit'] ) )  // submit button was pressed
   {
      $settings = isset( $_REQUEST['settings'] ) ? "Show" : "Hide";
?>

   <input type="checkbox" name="settings" value="hide" id="settings"
          <?php echo SetStickyChecked( 'settings', TRUE ); ?> />

   <label for="settings">
      <?php echo $settings; ?> Settings
   </label>

<?php
   }  // submit button was pressed
?>

<?php
   $settingsDisplay = '';
      
   // if ( isset( $_REQUEST['settings'] ) )
   // {
      // $settingsDisplay = 'style="display: none;"';
   // }
?>

   <div id="settingsDiv" <?php echo $settingsDisplay; ?> >
   
<p />

      <div id="tabs">
         <ul>
<?php
   if ( ! isset( $_REQUEST['latLong'] ) )
   {
?>
            <li>
               <a href="#geoCodeTab">Geolocation
                  <?php RequiredFieldNotice( array( 'business' ) ); ?>
               </a>
            </li>

<?php
   }
   else // if ( isset( $_REQUEST['latLong'] ) )
   {
?>
            <li>
               <a href="#latLongTab">Latitude/Longitude
                  <?php RequiredFieldNotice( array( 'lat', 'long' ) ); ?>
               </a>
            </li>
<?php
   }
?>
            <li>
               <a href="#markerTab">Data Marker
                  <?php RequiredFieldNotice( array( 'hoverText' ) ); ?>
               </a>
            </li>
            <li>
               <a href="#mapSettings">Map Settings
                  <!-- <?php RequiredFieldNotice( array( 'hoverText' ) ); ?> -->
               </a>
            </li>
         </ul>

<?php
   // build the sql statement to display the string variables
   $geolocationList = GetGeoFieldNames( $_REQUEST['pid'] );
   
   if ( ! isset( $_REQUEST['latLong'] ) )
   {
?>
      <div id="geoCodeTab">  <!-- Geolocation Tab -->
         <table border="1">
            <tr>
               <th class="fieldHeader">
                  1st Geo Field:<br />
                  (e.g. Name)
                  <?php RequiredFieldNotice( array( 'business' ) ); ?>
               </th>
               <td>
                  <?php echo BuildDropDownList( "business", $geolocationList ); ?>
               </td>
            </tr>

            <tr>
               <th class="fieldHeader">
                  2nd Geo Field:<br />
                  (e.g. Address)
               </th>
               <td>
                  <?php echo BuildDropDownList( "address", $geolocationList ); ?>
               </td>
            </tr>

            <tr>
               <th class="fieldHeader">
                  3rd Geo Field:<br />
                  (e.g. City)
               </th>
               <td>
                  <?php echo BuildDropDownList( "city", $geolocationList ); ?>
               </td>
            </tr>

            <tr>
               <th class="fieldHeader">
                  4th Geo Field:<br />
                  (e.g. State)
               </th>
               <td>
                  <?php echo BuildDropDownList( "state", $geolocationList ); ?>
               </td>
            </tr>

            <tr>
               <th class="fieldHeader">
                  5th Geo Field:<br />
                  (e.g. Zip)
               </th>
               <td>
                  <?php echo BuildDropDownList( "zip", $geolocationList ); ?>
               </td>
            </tr>
         </table>
      </div>  <!-- Geolocation Tab -->
<?php
   }
   else  // if ( isset( $_REQUEST['latLong'] ) )
   {
      // build the sql statement to display the integer, float and calc variables
      $numberList = GetNumberFieldNames( $_REQUEST['pid'] );
?>
      <div id="latLongTab">  <!-- Latitude/Longitude Tab -->
         <table>
            <tr>
               <th class="fieldHeader">
                  Latitude:
                  <?php RequiredFieldNotice( array( 'lat' ) ); ?>
               </th>
               <td>
                  <?php echo BuildDropDownList( "lat", $numberList ); ?>
               </td>
            </tr>

            <tr>
               <th class="fieldHeader">
                  Longitude:
                  <?php RequiredFieldNotice( array( 'long' ) ); ?>
               </th>
               <td>
                  <?php echo BuildDropDownList( "long", $numberList ); ?>
               </td>
            </tr>
         </table>
      </div>  <!-- Latitude/Longitude Tab -->
<?php
   }
?>

      <div id="markerTab">  <!-- Data Marker Tab -->
         <table border="1">
            <tr>
               <th class="fieldHeader">
                  Hover Text:
                  <?php RequiredFieldNotice( array( 'hoverText' ) ); ?>
               </th>
               <td>
                  <?php echo BuildDropDownList( "hoverText", $geolocationList ); ?>
               </td>
            </tr>
         </table>
      </div>  <!-- Data Marker Tab -->

      <div id="mapSettings">  <!-- Map Settings Tab -->
         <table border="1">
            <tr>
               <td>
                  Map Type:
               </td>
               <td colspan="2">
<?php
   $mapTypeList = array( "ROADMAP" => "Roadmap", 
                         "SATELLITE" => "Satellite", 
                         "HYBRID" => "Hybrid", 
                         "TERRAIN" => "Terrain" );

   echo BuildDropDownList( "mapType", $mapTypeList, FALSE, "ROADMAP" );
?>
               </td>
               <td>
                  &nbsp;
               </td>
            </tr>

            <tr>
               <th class="fieldHeader" width="15">
                  Width:
                     <?php RequiredFieldNotice( array( 'width' ) ); ?>
               </th>
               <td class="grp2" width="15">
                  <input type="text" name="width" size="2"
                         value="<?php echo SetStickyValue( 'width', 600); ?>" />
               </td>
         
               <th class="fieldHeader" width="15">
                  Height:
                     <?php RequiredFieldNotice( array( 'height' ) ); ?>
               </th>
               <td width="15">
                  <input type="text" name="height" size="2"
                         value="<?php echo SetStickyValue('height', 400); ?>" />
               </td>
            </tr>
         </table>
      </div>  <!-- Map Settings Tab -->
   </div>  <!-- tabs -->

   <p />

   <table width="100%" style="background-color: #EEEEEE; border: 1px solid #AAAAAA; border-radius: 4px;">
      <tr class="even">
         <th class="fieldHeader">
<?php
   $latLong = isset( $_REQUEST['latLong'] ) ? "Hide" : "Show";
?>
            <input type="checkbox" name="latLong" value="show"
                   id="latLong" onClick="this.form.submit()"
                   <?php echo SetStickyChecked( 'latLong' ); ?> />

            <label for="latLong">
               <?php echo $latLong; ?>
               Latitude/Longitude
            </label>
         </th>
      </tr>

      <tr class="even">
         <th style="text-align: center; padding: 10px;" nowrap>
            <input type="submit" 
                   name="doit" 
                   value="Generate Map" 
                   onclick="displayMarkers()"
                   class="buttonClass" />

<?php
   if ( isset( $_REQUEST['doit'] ) )
   {
?>
            <button type="button"div
                    onclick="javascript:saveAsImg(document.getElementById('markersMapDiv'), 'markersMapDiv' )"
                    class="buttonClass">Download PNG Image</button>

            <button type="button"
                    onclick="container = document.getElementById('markersMapDiv'); javascript:toImg(container, container);"
                    class="buttonClass">Display Plot as Image</button>
<?php
   }
?>
         </th>
      </tr>
   </table>

   <p style="color: red;">
      * Required Fields
   </p>
   </div>  <!-- settingsDiv -->
      
   <!-- remember current project upon next submission -->
   <input type="hidden" name="pid" value="<?php echo $_REQUEST['pid'] ?>" />
</form>

<div id="result"></div>

<?php
   // OPTIONAL: Display the project footer
   require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
?>
