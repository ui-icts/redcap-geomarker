<?php
/**
 * @brief REDCap plugin that generates a google map with a marker and infowindow for data
 *
 * @file index.php
 * $Revision: 213 $
 * $Author: fmcclurg $
 * @author Fred R. McClurg, University of Iowa
 * $Date:: 2013-04-12 14:10:58 #$
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/geomarker.php $
 * @ref $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/geomarker.php $
 */

# Display verbose error reporting
require_once('lib/errorReporting.php');

# HTML utility functions
require_once('lib/htmlUtilities.php');

# JSON utility functions
require_once('lib/redcapUtilities.php');

// Call the REDCap Connect file in the main "redcap" directory
require_once( '../redcap_connect.php' );

// Display the stand-alone page header
# $HtmlPage = new HtmlPage();
# $HtmlPage->PrintHeaderExt();

// Restrict this plugin to a specific REDCap project (in case user's randomly find the plugin's URL)
if ( PROJECT_ID != $_REQUEST['pid'] )
{
   $exitMessage = sprintf( "This plugin is only accessible to users from project \"%s\".", app_title );
   exit( $exitMessage );
}
// allowProjects( $_REQUEST["pid"] );

// OPTIONAL: Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

// Your HTML page content goes here
?>

<!-- jQuery -->
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

<!-- jQuery UI -->
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>

<!-- JavaScript required by geochart -->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<!-- Google Maps API Premier license -->
<script type="text/javascript"
        src="http://maps.googleapis.com/maps/api/js?client=gme-universityofiowa&sensor=false">
   <!-- Google Maps API Key -->
   <!-- src="http://maps.googleapis.com/maps/api/js?key=AIzaSyC2fqTrPJJKDnqjKiPLj4rKiuLU08KdNCc&sensor=false" -->
</script>

<!-- jQuery plugin colorpicker -->
<script type="text/javascript" src="jquery/colorpicker/js/evol.colorpicker.js"></script>

<!-- JavaScript required by the following graph to image utilities -->
<script type="text/javascript" src="http://canvg.googlecode.com/svn/trunk/rgbcolor.js"></script>
<script type="text/javascript" src="http://canvg.googlecode.com/svn/trunk/canvg.js"></script>

<!-- JavaScript functions to convert google charts to images -->
<script type="text/javascript" src="js/googleGraphUtilities.js"></script>

<!-- Handy JavaScript utility functions -->
<script type="text/javascript" src="js/javaScriptUtilities.js"></script>

<!-- Keep selected cookie state persistant -->
<script type="text/javascript" src="jquery-ui/development-bundle/external/jquery.cookie.js"></script>

<!-- jQuery tab customization -->
<link rel="stylesheet" type="text/css" href="jquery-ui/css/custom-theme/jquery-ui-1.8.24.custom.css" />

<!-- colorpicker CSS customization -->
<link rel="stylesheet" href="jquery/colorpicker/css/evol.colorpicker.css" />

<!-- local CSS customization -->
<link rel="stylesheet" type="text/css" href="css/style.css" />

<!-- Create your custom javascript code to construct the scatter plot on this page -->
<script type="text/javascript">
   $(document).ready( function() {
      $("#startColor").colorpicker({
         // color: "<?php echo SetStickyValue( 'startColor', '#00ff00'); ?>"
      });

      $("#endColor").colorpicker({
         // color: "<?php echo SetStickyValue( 'endColor', '#ff0000'); ?>"
      });

      $("#bgColor").colorpicker({
         // color: "<?php echo SetStickyValue( 'bgColor', '#f5f5f5'); ?>"
      });

      $("#settings").click(function() {
         $("div#settingsDiv").fadeToggle("slow", "linear");
      });

      $("#tabs").tabs({
         cookie: {
            // store cookie for a day
            expires: 5
         }
      });

   });
</script>

<?php
   if ( isset( $_REQUEST['doit'] ) )  // submit button was pressed
   {
      // list of all possible fields
      if ( isset( $_REQUEST['latLong'] ) )
      {
         $allFieldKeys = array( 'lat', 'long', 'colorMarkerData', 'sizeMarkerData' );
      }
      else
      {
         $allFieldKeys = array( 'business', 'address', 'city', 'state', 'zip', 'colorMarkerData', 'sizeMarkerData' );
      }

      // list of all possible geolocation fields
      $geoLocationKeys = array( 'business', 'address', 'city', 'state', 'zip' );

      $selectedKeys = array();
      $selectedGeoKeys = array();
      $selectedDataKeys = array();

      // determine which fields have been selected
      foreach ( $allFieldKeys as $key )
      {
         if ( strlen( $_REQUEST[$key] ) > 0 )  // user must have selected this option
         {
            // obtain a list of all selected columns
            array_push( $selectedKeys, $_REQUEST[$key] );

            if ( in_array( $key, $geoLocationKeys ) )
            {
               // obtain a list of all selected geolocation columns
               array_push( $selectedGeoKeys, $_REQUEST[$key] );
            }
            else
            {
               // obtain a list of all selected data columns
               array_push( $selectedDataKeys, $_REQUEST[$key] );
            }
         }
      }

      $selectedFilterHash = array();

      // list of all possible filter fields
      $selectedFilterKeys = array( 'fieldFilter1' => 'valueFilter1',
                                   'fieldFilter2' => 'valueFilter2',
                                   'fieldFilter3' => 'valueFilter3',
                                   'fieldFilter4' => 'valueFilter4',
                                   'fieldFilter5' => 'valueFilter5' );

      foreach ( $selectedFilterKeys as $key => $value )
      {
         if ( ( strlen( $_REQUEST[$key] ) > 0 ) &&   // user must have selected the filter
              ( strlen( $_REQUEST[$value] ) > 0 ) )  // user must have selected the value also
         {
            $hashKey = $_REQUEST[$key];
            $hashValue = $_REQUEST[$value];

            $selectedFilterHash[$hashKey] = $hashValue;
         }
      }

      // get the value of the selected location fields
      $arrayOfHashes = GetMultipleFieldValues( $_REQUEST['pid'],
                                               $selectedKeys,
                                               $selectedFilterHash );

      // initialize the data headers with labels
      $geoChartMatrix = array();

      if ( isset( $_REQUEST['latLong'] ) )
      {
         $geoChartHeaders = array();
         $geoChartLabels = array( 'latLabel', 'longLabel', 'colorDataLabel', 'sizeDataLabel' );
      }
      else
      {
         $geoChartHeaders = array( "City" );  // dummy header required
         $geoChartLabels = array( 'colorDataLabel', 'sizeDataLabel' );
      }

      foreach ( $geoChartLabels as $key )
      {
         if ( isset( $_REQUEST[$key] ) &&
              strlen( $_REQUEST[$key] ) > 0 )
         {
            array_push( $geoChartHeaders, $_REQUEST[$key] );
         }
      }

      if ( $_REQUEST['geoMode'] == 'regions' )
      {
         // store the record ID in the last column to make the chart clickable
         array_push( $geoChartHeaders, "ID" );
      }

      // the first column is an array of data labels
      array_push( $geoChartMatrix, $geoChartHeaders );

      foreach ( $arrayOfHashes as $record )
      {
         $geoChartRecords = array();

         // inialize variables
         if ( ( $_REQUEST['geoMode'] == 'regions' ) ||
              ( isset( $_REQUEST['latLong'] ) ) )        // Latitude/Longitude data
         {
            $geoLocationRecords = array();
         }
         else  // if ( $_REQUEST['geoMode'] == 'markers' )
         {
            // embed the study_id in the geolocation record so we can
            // drill down to the record upon clicking on the marker
            $markerId = sprintf( "#%s", $record['record'] );
            $geoLocationRecords = array( $markerId );
         }

         foreach ( $selectedGeoKeys as $key )
         {
            array_push( $geoLocationRecords, $record[$key] );
         }

         if ( ! isset( $_REQUEST['latLong'] ) )     // Latitude/Longitude data
         {
            // create a comma delimited geolocation string
            $geoLocationStr = implode( ", ", $geoLocationRecords );

            // define first geochart column with geolocation data
            array_push( $geoChartRecords, $geoLocationStr );
         }

         foreach ( $selectedDataKeys as $key )
         {
            // define the geochart column with data
            array_push( $geoChartRecords, $record[$key] );
         }

         if ( ( $_REQUEST['geoMode'] == 'regions' ) &&
              ( ! isset( $_REQUEST['latLong'] ) ) )     // Latitude/Longitude data
         {
            # use the "study_id" as the last field in order to make a link to the record
            array_push( $geoChartRecords, $record['record'] );
         }

         // build an array of arrays of data
         array_push( $geoChartMatrix, $geoChartRecords );
      }

      /*
      foreach( $geoChartMatrix as $geoChartRecords )
      {
         foreach( $geoChartRecords as $value )
         {
            printf( "%s\n", $value );
         }

         printf( "<br />\n" );
      }
      */
?>

<script type="text/javascript">
   // Load the Visualization API and the geochart package.
   google.load('visualization', '1', {'packages':['geochart']});

   // Set a callback to run when the Google Visualization API is loaded.
   google.setOnLoadCallback(drawMarkersMap);

   function drawMarkersMap()
   {
      // Create and populate the scatter chart
      var data = google.visualization.arrayToDataTable( <?php echo json_encode( $geoChartMatrix, JSON_NUMERIC_CHECK ); ?> );

      var options = {
         width: <?php echo $_REQUEST['width']; ?>,
         height: <?php echo $_REQUEST['height']; ?>,
         datalessRegionColor: "<?php echo $_REQUEST['bgColor']; ?>",
         region: "<?php echo $_REQUEST['region']; ?>",
         resolution: "<?php echo $_REQUEST['resolution']; ?>",
         displayMode: "<?php echo $_REQUEST['geoMode']; ?>",
         magnifyingGlass: { enable: false },
         colorAxis: {colors:
            [ "<?php echo $_REQUEST['startColor']; ?>",
              "<?php echo $_REQUEST['endColor']; ?>" ]},
<?php
   if ( ! isset( $_REQUEST['legend'] ) )
   {
?>
         legend: "none",
<?php
   }
?>
      };

      var chart = new google.visualization.GeoChart(document.getElementById('markersMapDiv'));
      chart.draw(data, options);

      <?php
         $redcapFormName = GetFormName( $_REQUEST['pid'], $selectedGeoKeys[0] );

         # before: /redcap/redcap_v4.14.0/
         $pattern = '/\/$/';  // strip off trailing slashes

         # after: redcap_v4.14.0
         $path = preg_replace( $pattern, '', APP_PATH_WEBROOT );

         $appPathWebRoot = array_pop( explode( "/", $path ) );

         $recordUrl = sprintf( "%s%s/DataEntry/index.php?pid=%d&page=%s&id=",
                                  APP_PATH_WEBROOT_FULL,
                                  $appPathWebRoot,
                                  $_REQUEST['pid'],
                                  $redcapFormName );

         $lastElementCount = count( $geoChartHeaders ) - 1;
      ?>

      google.visualization.events.addListener(chart, 'select', function() {
         var selection = chart.getSelection();

         // if (typeof selection[0] !== "undefined" )
         // {
<?php
      if ( $_REQUEST['geoMode'] == 'regions' )
      {
?>
         // the study_id is the last field
         var value = data.getValue(selection[0].row, <?php echo $lastElementCount; ?> );
<?php
      }
      else  // if ( $_REQUEST['geoMode'] == 'markers' )
      {
?>
         var firstField = data.getValue(selection[0].row, 0 );

         // the study_id is embedding in the first field
         // #15026, SAPP BROS, COUNCIL BLUFFS, 51501
         pieces = firstField.split(/[# ,]/);
         value = pieces[1];
<?php
      }
?>

            // redirect to specific REDCap record
            window.location.assign('<?php echo $recordUrl; ?>' + value );
         // }
      });

   }  // function drawMarkersMap()


<?php
   }  // submit button was pressed
?>
</script>


<?php
   if ( isset( $_REQUEST['doit'] ) )  // submit button was pressed
   {
?>
<!-- Div that will contain the geo chart -->
<div id="markersMapDiv" style="margin-left: auto;
                               margin-right: auto;"></div>

<br />

<?php
   }  // submit button was pressed
?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get"
      name="geoChartForm" id="geoChartForm" >

<?php
   if ( isset( $_REQUEST['doit'] ) )  // submit button was pressed
   {
      $settings = isset( $_REQUEST['settings'] ) ? "Show" : "Hide";
?>

   <input type="checkbox" name="settings" value="hide" id="settings"
          <?php echo SetStickyChecked( 'settings' ); ?> />

   <label for="settings">
      <?php echo $settings; ?> Settings
   </label>

<?php
   }  // submit button was pressed
?>

   <div id="settingsDiv"
<?php
   if ( $_REQUEST['settings'] == "hide" )
   {
?>
      style="display: none;"
<?php
   }
?>
      >  <!-- closing tag of settings div -->

      <!-- Display main page -->
      <h1>
         Geochart Builder
      </h1>

      <p>
         Geochart Builder can generate a custom map of your geographic
         data.&nbsp; Select the data fields containing the location data,
         select the optional fields containing marker data and enter the
         desired graph parameters in order to build a geochart plot.
      </p>

      <?php
         // build the sql statement to display all the field names
         $variableHash = GetAllFieldNames( $_REQUEST['pid'] );

         // build the sql statement to display the integer, float and calc variables
         $numberHash = GetNumberFieldNames( $_REQUEST['pid'] );

         // build the sql statement to display the string variables
         $geolocationHash = GetGeoFieldNames( $_REQUEST['pid'] );

         // build the sql statement to display the data variables
         $dataHash = GetDataFieldNames( $_REQUEST['pid'] );
      ?>

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
               <a href="#dataColorTab">Data Color
<?php
   if ( ! isset( $_REQUEST['latLong'] ) )
   {
      RequiredFieldNotice( array( 'colorDataLabel',
                                  'startColor',
                                  'endColor',
                                  'colorMarkerData' ) );
   }
?>
               </a>
            </li>
<?php
   if ( $_REQUEST['geoMode'] == 'markers' )
   {
?>
            <li>
               <a href="#markerSizeTab">Marker Size
               </a>
            </li>
<?php
   }
?>
            <li>
               <a href="#mapSettingsTab">Map Settings
                  <?php RequiredFieldNotice( array( 'region',
                                                    'resolution',
                                                    'geoMode',
                                                    'bgColor',
                                                    'width',
                                                    'height' ) ); ?>
               </a>
            </li>

            <li>
               <a href="#dataFilterTab">Data Filter
               </a>
            </li>
         </ul>

<?php
   if ( ! isset( $_REQUEST['latLong'] ) )
   {
?>
         <div id="geoCodeTab">  <!-- Geolocation Tab -->
            <table>
               <tr>
                  <th class="fieldHeader">
                     1st Geo Field:<br />
                     (e.g. Name)
                     <?php RequiredFieldNotice( array( 'business' ) ); ?>
                  </th>
                  <td>
                     <?php echo BuildDropDownList( "business", $geolocationHash ); ?>
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     2nd Geo Field:<br />
                     (e.g. Address)
                  </th>
                  <td>
                     <?php echo BuildDropDownList( "address", $geolocationHash ); ?>
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     3rd Geo Field:<br />
                     (e.g. City)
                  </th>
                  <td>
                     <?php echo BuildDropDownList( "city", $geolocationHash ); ?>
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     4th Geo Field:<br />
                     (e.g. State)
                  </th>
                  <td>
                     <?php echo BuildDropDownList( "state", $geolocationHash ); ?>
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     5th Geo Field:<br />
                     (e.g. Zip)
                  </th>
                  <td>
                     <?php echo BuildDropDownList( "zip", $geolocationHash ); ?>
                  </td>
               </tr>
            </table>
         </div>
<?php
   }
   else  // if ( isset( $_REQUEST['latLong'] ) )
   {
?>
         <div id="latLongTab">  <!-- Latitude/Longitude Tab -->
            <table>
               <tr>
                  <th class="fieldHeader">
                     Latitude:
                     <?php RequiredFieldNotice( array( 'lat' ) ); ?>
                  </th>
                  <td>
                     <?php echo BuildDropDownList( "lat", $numberHash ); ?>
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     Longitude:
                     <?php RequiredFieldNotice( array( 'long' ) ); ?>
                  </th>
                  <td>
                     <?php echo BuildDropDownList( "long", $numberHash ); ?>
                  </td>
               </tr>
            </table>
         </div>
<?php
   }
?>

         <div id="dataColorTab">  <!-- Data Color Tab -->
            <table>
               <tr>
                  <th class="fieldHeader">
                     Data Color Label:
<?php
   if ( ! isset( $_REQUEST['latLong'] ) )
   {
      RequiredFieldNotice( array( 'colorDataLabel' ) );
   }
?>
                  </th>
                  <td colspan="4">
                     <input type="text" name="colorDataLabel" size="35"
                            value="<?php echo SetStickyValue( 'colorDataLabel' ); ?>" />
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     Start Data Color:
                        <?php RequiredFieldNotice( array( 'startColor' ) ); ?>
                  </th>
                  <td width="15">
                     <input type="input" size="6"
                            name="startColor" id="startColor"
                            value="<?php echo SetStickyValue( 'startColor', '#0000ff'); ?>" />
                  </td>

                  <th class="fieldHeader">
                     End Data Color:
                        <?php RequiredFieldNotice( array( 'endColor' ) ); ?>
                  </th>
                  <td width="15">
                     <input type="input" size="6"
                            name="endColor" id="endColor"
                            value="<?php echo SetStickyValue( 'endColor', '#ff0000'); ?>" />
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     Data Color Data:
                  </th>
                  <td colspan="4">
                     <?php echo BuildDropDownList( "colorMarkerData", $dataHash ); ?>
                  </td>
               </tr>

            </table>
         </div>

<?php
   if ( $_REQUEST['geoMode'] == 'markers' )
   {
?>
         <div id="markerSizeTab">  <!-- Marker Size Tab -->
            <table>
               <tr>
                  <th class="fieldHeader">
                     Marker Size Label:
                  </th>
                  <td colspan="3">
                     <input type="text" name="sizeDataLabel" size="35"
                            value="<?php echo $_REQUEST['sizeDataLabel'] ?>" />
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     Marker Size Data:
                  </th>
                  <td colspan="3">
                     <?php echo BuildDropDownList( "sizeMarkerData", $dataHash ); ?>
                  </td>
               </tr>
            </table>
         </div>
<?php
   }
?>

         <div id="mapSettingsTab">  <!-- Map Settings Tab -->
            <table>
               <tr>
                  <th class="fieldHeader" width="15">
                     Map Region:
                        <?php RequiredFieldNotice( array( 'region' ) ); ?>
                  </th>
                  <td colspan="2">
<?php
   $regionHash = array( /* "world" => "World", */
                        /* "CA" => "Canada", */
                        "US" => "United States",
                        "US-AL" => "Alabama, US",
                        "US-AK" => "Alaska, US",
                        "US-AZ" => "Arizona, US",
                        "US-AR" => "Arkansas, US",
                        "US-CA" => "California, US",
                        "US-CO" => "Colorado, US",
                        "US-CT" => "Connecticut, US",
                        "US-DE" => "Delaware, US",
                        "US-FL" => "Florida, US",
                        "US-GA" => "Georgia, US",
                        "US-HI" => "Hawaii, US",
                        "US-ID" => "Idaho, US",
                        "US-IL" => "Illinois, US",
                        "US-IN" => "Indiana, US",
                        "US-IA" => "Iowa, US",
                        "US-KS" => "Kansas, US",
                        "US-KY" => "Kentucky, US",
                        "US-LA" => "Louisiana, US",
                        "US-ME" => "Maine, US",
                        "US-MD" => "Maryland, US",
                        "US-MA" => "Massachusetts, US",
                        "US-MI" => "Michigan, US",
                        "US-MN" => "Minnesota, US",
                        "US-MS" => "Mississippi, US",
                        "US-MO" => "Missouri, US",
                        "US-MT" => "Montana, US",
                        "US-NE" => "Nebraska, US",
                        "US-NV" => "Nevada, US",
                        "US-NH" => "New Hampshire, US",
                        "US-NJ" => "New Jersey, US",
                        "US-NM" => "New Mexico, US",
                        "US-NY" => "New York, US",
                        "US-NC" => "North Carolina, US",
                        "US-ND" => "North Dakota, US",
                        "US-OH" => "Ohio, US",
                        "US-OK" => "Oklahoma, US",
                        "US-OR" => "Oregon, US",
                        "US-PA" => "Pennsylvania, US",
                        "US-RI" => "Rhode Island, US",
                        "US-SC" => "South Carolina, US",
                        "US-SD" => "South Dakota, US",
                        "US-TN" => "Tennessee, US",
                        "US-TX" => "Texas, US",
                        "US-UT" => "Utah, US",
                        "US-VT" => "Vermont, US",
                        "US-VA" => "Virginia, US",
                        "US-WA" => "Washington, US",
                        "US-WV" => "West Virginia, US",
                        "US-WI" => "Wisconsin, US",
                        "US-WY" => "Wyoming, US",
                        /* "CA" => "Canada",
                        "CA-AB" => "Alberta, CA",
                        "CA-BC" => "British Columbia, CA",
                        "CA-MB" => "Manitoba, CA",
                        "CA-NB" => "New Brunswick, CA",
                        "CA-NL" => "Newfoundland and Labrador, CA",
                        "CA-NT" => "Northwest Territories, CA",
                        "CA-NS" => "Nova Scotia, CA",
                        "CA-NU" => "Nunavut, CA",
                        "CA-ON" => "Ontario, CA",
                        "CA-PE" => "Prince Edward Island, CA",
                        "CA-QC" => "Quebec, CA",
                        "CA-SK" => "Saskatchewan, CA",
                        "CA-YT" => "Yukon, CA" */ );

   echo BuildDropDownList( "region", $regionHash );
?>
                  </td>

                  <th class="fieldHeader" colspan="2">
                     Map Resolution:
                        <?php RequiredFieldNotice( array( 'resolution' ) ); ?>
                  </th>
                  <td width="15">
                     <?php
                        $resolution = array( "countries" => "Country",
                                             "provinces" => "Province",
                                             "metros" => "Metro" );

                        echo BuildDropDownList( "resolution", $resolution );
                     ?>
                  </td>
               </tr>

               <tr>
                  <th class="fieldHeader">
                     Geochart Mode:
                        <?php RequiredFieldNotice( array( 'geoMode' ) ); ?>
                  </th>
                  <td colspan="2">
                     <?php
                        $modeHash = array( "markers" => "Markers",
                                           "regions" => "Regions" );

                        echo BuildDropDownList( "geoMode", $modeHash, TRUE );
                     ?>
                  </td>

                  <th class="fieldHeader" colspan="2">
                     Background Color:
                        <?php RequiredFieldNotice( array( 'bgColor' ) ); ?>
                  </th>
                  <td width="15">
                     <input type="input" size="6"
                            name="bgColor" id="bgColor"
                            value="<?php echo SetStickyValue('bgColor', '#f5f5f5'); ?>" />
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

                  <th class="fieldHeader" style="text-align: center;" colspan="2">
                     <input type="checkbox" name="legend" value="show" id="legend"
                        <?php echo SetStickyChecked( 'legend', TRUE ); ?> />

                     <label for="legend">
                        Display Legend
                     </label>
                  </th>
               </tr>
            </table>
         </div>

         <div id="dataFilterTab">  <!-- Data Filter Tab -->
            <table width="100%" style="background-color: #EEEEEE; border: 1px solid #AAAAAA; border-radius: 4px;">
               <tr>
                  <th>
                     <table>
                        <tr>
                           <th class="fieldHeader">
                              Field Filter 1:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "fieldFilter1",
                                                            $variableHash, TRUE ); ?>
                           </td>
                        </tr>

                        <tr>
                           <th class="fieldHeader">
                              Value Filter 1:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "valueFilter1",
                                                            GetDistinctFieldValues( $_REQUEST['pid'],
                                                                                    $_REQUEST['fieldFilter1'] ) ); ?>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>

            <table width="100%" style="background-color: #EEEEEE; border: 1px solid #AAAAAA; border-radius: 4px;">
               <tr>
                  <th>
                     <table>
                        <tr>
                           <th class="fieldHeader">
                              Field Filter 2:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "fieldFilter2",
                                                            $variableHash, TRUE ); ?>
                           </td>
                        </tr>

                        <tr>
                           <th class="fieldHeader">
                              Value Filter 2:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "valueFilter2",
                                                            GetDistinctFieldValues( $_REQUEST['pid'],
                                                                                    $_REQUEST['fieldFilter2'] ) ); ?>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>

            <table width="100%" style="background-color: #EEEEEE; border: 1px solid #AAAAAA; border-radius: 4px;">
               <tr>
                  <th>
                     <table>
                        <tr>
                           <th class="fieldHeader">
                              Field Filter 3:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "fieldFilter3",
                                                            $variableHash, TRUE ); ?>
                           </td>
                        </tr>

                        <tr>
                           <th class="fieldHeader">
                              Value Filter 3:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "valueFilter3",
                                                            GetDistinctFieldValues( $_REQUEST['pid'],
                                                                                    $_REQUEST['fieldFilter3'] ) ); ?>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>

            <table width="100%" style="background-color: #EEEEEE; border: 1px solid #AAAAAA; border-radius: 4px;">
               <tr>
                  <th>
                     <table>
                        <tr>
                           <th class="fieldHeader">
                              Field Filter 4:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "fieldFilter4",
                                                            $variableHash, TRUE ); ?>
                           </td>
                        </tr>

                        <tr>
                           <th class="fieldHeader">
                              Value Filter 4:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "valueFilter4",
                                                            GetDistinctFieldValues( $_REQUEST['pid'],
                                                                                    $_REQUEST['fieldFilter4'] ) ); ?>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>

            <table width="100%" style="background-color: #EEEEEE; border: 1px solid #AAAAAA; border-radius: 4px;">
               <tr>
                  <th>
                     <table>
                        <tr>
                           <th class="fieldHeader">
                              Field Filter 5:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "fieldFilter5",
                                                            $variableHash, TRUE ); ?>
                           </td>
                        </tr>

                        <tr>
                           <th class="fieldHeader">
                              Value Filter 5:
                           </th>
                           <td>
                              <?php echo BuildDropDownList( "valueFilter5",
                                                            GetDistinctFieldValues( $_REQUEST['pid'],
                                                                                    $_REQUEST['fieldFilter5'] ) ); ?>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
            </table>
         </div>

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
                  <input type="submit" name="doit" value="Generate Map" class="buttonClass" />

<?php
   if ( isset( $_REQUEST['doit'] ) )
   {
?>
                  <button type="button"
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
      </div>  <!-- id="tabs" -->

   <!-- define labels for latitude and longitude -->
   <input type="hidden" name="latLabel" value="Latitude" />
   <input type="hidden" name="longLabel" value="Longitude" />

   <!-- remember current project upon next submission -->
   <input type="hidden" name="pid" value="<?php echo $_REQUEST['pid'] ?>" />
</form>

<?php
   // OPTIONAL: Display the project footer
   require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
?>
