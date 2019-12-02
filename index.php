<?php
/**
 * @brief REDCap plugin that generates a google map with a marker and infowindow for data
 *
 * @file index.php
 * $Revision: 213 $
 * $Author: fmcclurg $
 * @author Fred R. McClurg, University of Iowa
 * $Date:: 2013-04-12 14:10:58 #$
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/index.php $
 * @ref $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/index.php $
 */

// stuff to include in each file
require_once('lib/errorReporting.php');

// stuff to include in each file
require_once('lib/debugUtilities.php');

// HTML utility functions
require_once('lib/htmlUtilities.php');

// REDCap utility functions
require_once('lib/redcapUtilities.php');

// Call the REDCap Connect file in the main "redcap" directory
require_once "../redcap_connect.php";

// Restrict this plugin to a specific REDCap project (in case user's randomly find the plugin's URL)
if (PROJECT_ID != $_REQUEST['pid'])
{
   $exitMessage = sprintf( "This plugin is only accessible to users from project \"%s\".", app_title );
   exit( $exitMessage );
}
?>

<!-- jQuery UI Theme -->
<link rel="stylesheet" href="jquery/jquery-ui-1.9.2.custom/css/smoothness/jquery-ui-1.9.2.custom.min.css" />
<!-- <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" /> -->

<!-- Google Maps -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&client=gme-universityofiowa&sensor=true&channel=geomarker"></script>
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA1GWvNJmHgLeOpixVBgXwZ6_oSXBeLcGA&sensor=true"></script> -->
<!-- <script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script> -->

<!-- Marker Manager -->
<script type="text/javascript" src="js/markerManager.js"></script>
<!-- <script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markermanager/src/markermanager.js"></script> -->

<!-- Local Map Function -->
<script type="text/javascript" src="js/googleMapUtilities.js"></script>

<!-- jQuery -->
<script type="text/javascript" src="jquery/jquery-1.8.3.min.js"></script>
<!-- <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.js"></script> -->

<!-- jQuery UI -->
<script type="text/javascript" src="jquery/jquery-ui-1.9.2.custom/js/jquery-ui-1.9.2.custom.min.js"></script>
<!-- <script type="text/javascript" src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script> -->

<!-- jQuery UI Cookie (persistant tab) -->
<!-- <script type="text/javascript" src="jquery/jquery-ui-1.9.2.custom/development-bundle/external/jquery.cookie.js"></script> -->
<!-- <script type="text/javascript" src="https://github.com/carhartl/jquery-cookie/blob/master/jquery.cookie.js"></script> -->

<!-- Local Theme -->
<link rel="stylesheet" href="css/style.css" />

<script type="text/javascript">
   $(document).ready( function() {
      $(window).load( function() {

<?php
   if ( isset( $_REQUEST['doit'] ) )
   {
      if ( $_REQUEST['dataType'] == 'latLng' )
      {
         if ( isset( $_REQUEST['hoverText'] ) &&
              isset( $_REQUEST['latField'] ) &&
              isset( $_REQUEST['lngField'] ) )

         {
            $json = GetJsonLatLngMarkerData( $_REQUEST['pid'],
                                             $_REQUEST['hoverText'],
                                             $_REQUEST['latField'],
                                             $_REQUEST['lngField'] );
         }
      }
      elseif ( $_REQUEST['dataType'] == 'geoCode' )
      {
         if ( isset( $_REQUEST['hoverText'] ) &&
              isset( $_REQUEST['business'] ) )
         {
            $keys = array( 'business', 'address', 'city', 'state', 'zip' );
            $geoCodeList = array();

            foreach ( $keys as $key )
            {
               if ( isset( $_REQUEST[$key] ) &&
                    strlen( $_REQUEST[$key] ) > 0 )
               {
                  array_push( $geoCodeList, $_REQUEST[$key] );
               }
            }

            $json = GetJsonGeoCodeMarkerData( $_REQUEST['pid'],
                                              $_REQUEST['hoverText'],
                                              $geoCodeList );
         }
      }
   }

   if ( strlen( $json ) == 0 )
   {
      $json = "[]";
   }
?>

      var json = <?php echo $json; ?> ;

<?php
   $maxMinZoom = 'true';

   if ( isset( $_REQUEST['doit'] ) )  // page has been submitted
   {
      $dataType = $_REQUEST['dataType'];

      if ( ! isset( $_REQUEST['maxMinZoom'] ) )  // checkbox not checked
      {
         $maxMinZoom = 'false';
      }
   }
   else  // default pre-submission values
   {
      $dataType = "latLng";
   }

?>

      setupMap( "mapCanvas",
                google.maps.MapTypeId.<?php echo SetStickyValue('mapType', 'ROADMAP'); ?>,
                <?php echo SetStickyValue('latCtr', 39.828182); ?>,
                <?php echo SetStickyValue('lngCtr', -98.579547); ?>,
                <?php echo SetStickyValue('zoom', 4); ?>,
                <?php echo $maxMinZoom; ?>,
                <?php printf( "'%s'", $dataType ); ?>,
                json );

      $("#tabs").tabs();

      $( "input[type=submit], button" )
         .button()
         // .click(function( event ) {
            // event.preventDefault();
         // });
      });
   });
</script>

<?php
   // Display the project header
   require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
?>

<div id="mapCanvas" style="width: <?php echo SetStickyValue('width', 600); ?>px; height: <?php echo SetStickyValue('height', 400); ?>px"></div>

<p />

<?php
   $settingsDisplay = '';

   if ( $_REQUEST['settings'] == "hide" )
   {
      $settingsDisplay = 'style="display: none;"';
   }
?>

<form id="geoMarkerForm" method="get">

<?php
   if ( isset( $_REQUEST['doit'] ) )  // submit button was pressed
   {
      $settings = isset( $_REQUEST['settings'] ) ? "Show" : "Hide";
?>

   <input type="checkbox" name="settings" value="hide" id="settings"
          <?php echo SetStickyChecked( 'settings' ); ?> />

   <label for="settings" class="rprt" style="border-top: none;">
      <?php echo $settings; ?> Settings
   </label>

<?php
   }  // submit button was pressed
?>

   <p />

   <div id="settingsDiv" <?php echo $settingsDisplay; ?> >

      <div id="tabs">
         <ul>
<?php
   if ( $_REQUEST['dataType'] == 'geoCode' )
   {
?>
            <li>
               <a href="#geoCodeTab">Geocoding
                  <?php RequiredFieldNotice( array( 'business' ) ); ?>
               </a>
            </li>
<?php
   }
   else  // if ( $_REQUEST['dataType'] == 'latLng' )
   {
?>
            <li>
               <a href="#latLongTab">Latitude/Longitude
                  <?php RequiredFieldNotice( array( 'latField', 'lngField' ) ); ?>
               </a>
            </li>
<?php
   }
?>
            <li>
               <a href="#dataTab">Data Settings
                  <?php RequiredFieldNotice( array( 'hoverText', 'dataType' ) ); ?>
               </a>
            </li>
            <li>
               <a href="#mapSettings">Map Settings
                  <?php RequiredFieldNotice( array( 'hoverText' ) ); ?>
               </a>
            </li>
         </ul>

<?php
   // build the sql statement to display the string variables
   $geolocationList = GetGeoFieldNames( $_REQUEST['pid'] );

   if ( $_REQUEST['dataType'] == 'geoCode' )
   {
?>
         <div id="geoCodeTab">  <!-- Geocoding Tab -->
            <table class="dt2">
               <tbody>
                  <tr class="odd">
                     <th class="rprt fieldHeader">
                        1st Geo Field:<br />
                        (e.g. Name)
                        <?php RequiredFieldNotice( array( 'business' ) ); ?>
                     </th>
                     <td class="rprt">
                        <?php echo BuildDropDownList( "business", $geolocationList ); ?>
                     </td>
                  </tr>

                  <tr class="even">
                     <th class="rprt fieldHeader">
                        2nd Geo Field:<br />
                        (e.g. Address)
                     </th>
                     <td class="rprt">
                        <?php echo BuildDropDownList( "address", $geolocationList ); ?>
                     </td>
                  </tr>

                  <tr class="odd">
                     <th class="rprt fieldHeader">
                        3rd Geo Field:<br />
                        (e.g. City)
                     </th>
                     <td class="rprt">
                        <?php echo BuildDropDownList( "city", $geolocationList ); ?>
                     </td>
                  </tr>

                  <tr class="even">
                     <th class="rprt fieldHeader">
                        4th Geo Field:<br />
                        (e.g. State)
                     </th>
                     <td class="rprt">
                        <?php echo BuildDropDownList( "state", $geolocationList ); ?>
                     </td>
                  </tr>

                  <tr class="odd">
                     <th class="rprt fieldHeader">
                        5th Geo Field:<br />
                        (e.g. Zip)
                     </th>
                     <td class="rprt">
                        <?php echo BuildDropDownList( "zip", $geolocationList ); ?>
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>  <!-- Geocoding Tab -->
<?php
   }
   else  // if ( $_REQUEST['dataType'] == 'latLng' )
   {
      // build the sql statement to display the integer, float and calc variables
      $numberList = GetNumberFieldNames( $_REQUEST['pid'] );
?>
         <div id="latLongTab">  <!-- Latitude/Longitude Tab -->
            <table class="dt2">
               <tbody>
                  <tr class="odd">
                     <th class="rprt fieldHeader">
                        Lat Field:
                        <?php RequiredFieldNotice( array( 'latField' ) ); ?>
                     </th>
                     <td class="rprt">
                        <?php echo BuildDropDownList( "latField", $numberList ); ?>
                     </td>
                  </tr>

                  <tr class="even">
                     <th class="rprt fieldHeader">
                        Lng Field:
                        <?php RequiredFieldNotice( array( 'lngField' ) ); ?>
                     </th>
                     <th class="rprt">
                        <?php echo BuildDropDownList( "lngField", $numberList ); ?>
                     </th>
                  </tr>
               </tbody>
            </table>
         </div>  <!-- Latitude/Longitude Tab -->
<?php
   }
?>
         <div id="dataTab">  <!-- Data Settings Tab -->
            <table class="dt2">
               <tbody>
                  <tr class="odd">
                     <th class="rprt fieldHeader">
                        Hover Text:
                        <?php RequiredFieldNotice( array( 'hoverText' ) ); ?>
                     </th>
                     <td class="rprt">
                        <?php echo BuildDropDownList( "hoverText", $geolocationList ); ?>
                     </td>
                  </tr>
                  <tr class="even">
                     <th class="rprt fieldHeader">
                        Data Type:
                        <?php RequiredFieldNotice( array( 'dataType' ) ); ?>
                     </th>
                     <td class="rprt">
<?php
   $dataTypeList = array( // "geoCode" => "Geocoding",  // implemented at a future date
                          "latLng" => "Latitude/Longitude" );

   echo BuildDropDownList( "dataType", $dataTypeList, TRUE, "latLng" );
?>
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>  <!-- Data Settings Tab -->

         <div id="mapSettings">  <!-- Map Settings Tab -->
            <table class="dt2">
               <tbody>
                  <tr class="odd">
                     <th class="rprt fieldHeader">
                        Map Type:
                     </th>
                     <td class="rprt">
<?php
   $mapTypeList = array( "ROADMAP" => "Roadmap",
                         "SATELLITE" => "Satellite",
                         "HYBRID" => "Hybrid",
                         "TERRAIN" => "Terrain" );

   echo BuildDropDownList( "mapType", $mapTypeList, FALSE, "ROADMAP" );
?>
                     </td>
                     <th class="rprt fieldHeader" width="15">
                        Zoom:
                           <?php RequiredFieldNotice( array( 'zoom' ) ); ?>
                     </th>
                     <td width="15" class="rprt">
                        <input type="text" name="zoom" id="zoom" size="2"
                               value="<?php echo SetStickyValue('zoom', 4); ?>" />
                     </td>
                  </tr>

                  <tr class="even">
                     <th class="rprt fieldHeader" width="15">
                        Width:
                           <?php RequiredFieldNotice( array( 'width' ) ); ?>
                     </th>
                     <td class="rprt" width="15">
                        <input type="text" name="width" size="2"
                               value="<?php echo SetStickyValue( 'width', 600); ?>" />
                     </td>
                     <th class="rprt fieldHeader" width="15">
                        Height:
                           <?php RequiredFieldNotice( array( 'height' ) ); ?>
                     </th>
                     <td width="15" class="rprt">
                        <input type="text" name="height" size="2"
                               value="<?php echo SetStickyValue('height', 400); ?>" />
                     </td>
                  </tr>

                  <tr class="odd">
                     <th class="rprt fieldHeader" width="15">
                        Lat Center:
                           <?php RequiredFieldNotice( array( 'latCtr' ) ); ?>
                     </th>
                     <td class="rprt" width="15">
                        <input type="text" name="latCtr" id="latCtr" size="7"
                               value="<?php echo SetStickyValue( 'latCtr', 39.828182); ?>" />
                     </td>
                     <th class="rprt fieldHeader" width="15">
                        Lng Center:
                           <?php RequiredFieldNotice( array( 'lngCtr' ) ); ?>
                     </th>
                     <td width="15" class="rprt">
                        <input type="text" name="lngCtr" id="lngCtr" size="8"
                               value="<?php echo SetStickyValue('lngCtr', -98.579547); ?>" />
                     </td>
                  </tr>

                  <tr class="even">
                     <th class="rprt" colspan="4">
                        <input type="checkbox" name="maxMinZoom" value="true" id="maxMinZoom"
                               <?php echo SetStickyChecked( 'maxMinZoom', TRUE ); ?> />

                        <label for="maxMinZoom" class="rprt" style="border-top: none;">
                           Auto Max/Min &amp; Zoom
                        </label>
                     </th>
                  </tr>
               </tbody>
            </table>
         </div>  <!-- Map Settings Tab -->

         <table width="100%" style="border: 1px solid #AAAAAA; border-radius: 4px;">
            <tr>
               <th style="text-align: center; padding: 10px;" nowrap>
                  <input type="submit" name="doit" value="Generate Map" class="buttonClass" />

<?php
   if ( isset( $_REQUEST['doit'] ) )
   {
?>
<!-- The following button does not currently function -->
<!-- Leaving the code in place in order to resolve for a future version -->
                  <!--
                  <button type="button"
                          onclick="javascript:saveAsImg(document.getElementById('markersMapDiv'), 'markersMapDiv' )"
                          class="buttonClass">Download PNG Image</button>
                  -->

<!-- The following button does not currently function -->
<!-- Leaving the code in place in order to resolve for a future version -->
                  <!--
                  <button type="button"
                          onclick="container = document.getElementById('markersMapDiv'); javascript:toImg(container, container);"
                          class="buttonClass">Display Plot as Image</button>
                  -->
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
   </div>  <!-- id="settingsDiv" -->

   <input type="hidden" name="pid" value="<?php echo $_REQUEST['pid'] ?>" />
</form>

<?php
   // Display the project footer
   require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
?>
