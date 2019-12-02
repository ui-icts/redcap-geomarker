<?php
/**
 * @brief REDCap specific utilities
 *
 * @file redcapUtilities.php
 * $Revision: 210 $
 * $Author: fmcclurg $
 * @author Fred R. McClurg, University of Iowa
 * $Date:: 2013-03-27 13:09:28 #$
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/lib/redcapUtilities.php $
 */


/**
 * @brief Build a hash variable of all the field names
 *
 * @param  $projectId      Id number of the project
 * @retval $variableHash   Hash with the key as the field name
 *                         and the value containing the label
 */
function GetAllFieldNames( $projectId = PROJECT_ID )
{
   // build the sql statement to display all the field names
   $sql = sprintf( "
      SELECT field_name, form_name, element_type,
             element_label, element_validation_type, element_enum
         FROM redcap_metadata
         WHERE project_id = %d AND
               element_type != 'descriptive'
         ORDER BY field_name",
                       $projectId );

   // execute the sql statement
   $result = mysql_query( $sql );

   if ( ! $result )  // sql failed
   {
      die( "Error 38: Could not execute SQL:
            <pre>$sql</pre> <br />" .
            mysql_error() );
   }

   $variableHash = array();

   while ($record = mysql_fetch_assoc( $result ))
   {
      $key = $record['field_name'];

      $value = sprintf( "%s, %s (%s)",
                  $record['field_name'],     // field variable name
                  $record['element_label'],  // field label
                  $record['form_name'] );    // the form where the field is located

      $variableHash[$key] = $value;
   }

   return( $variableHash );

}  // function GetAllFieldNames()


/**
 * @brief Build a hash variable of all the field names containing
 *        integer and float values
 *
 * @param  $projectId    Id number of the project
 * @retval $numberHash   Hash with the key as the field name
 *                       and the value containing the label
 */
function GetNumberFieldNames( $projectId = PROJECT_ID )
{
   // build the sql statement to display the integer, float and calc variables
   $sql = sprintf( "
      SELECT field_name, form_name, element_type,
             element_label, element_validation_type
         FROM redcap_metadata
         WHERE project_id = %d AND
            ( ( element_type = 'calc' ) OR
               ( ( element_type = 'text' ) AND
                  ( ( element_validation_type = 'float' ) OR
                    ( element_validation_type = 'int' ) ) ) )
         ORDER BY field_name",
                       $_REQUEST['pid'] );

   // execute the sql statement
   $result = mysql_query( $sql );

   if ( ! $result )  // sql failed
   {
      die( "Error 90: Could not execute SQL:
            <pre>$sql</pre> <br />" .
            mysql_error() );
   }

   $numberHash = array();

   while ($record = mysql_fetch_assoc( $result ))
   {
      $key = $record['field_name'];

      $value = sprintf( "%s, %s (%s)",
                  $record['field_name'],
                  $record['element_label'],
                  $record['form_name'] );

      $numberHash[$key] = $value;
   }

   return( $numberHash );

}  // function GetNumberFieldNames()


/**
 * @brief Build a hash variable of all the field names containing
 *        fields that might be used for geolocation (e.g. strings and zipcode)
 *
 * @param  $projectId        Id number of the project
 * @retval $geolocationHash  Hash with the key as the field name
 *                           and the value containing the label
 */
function GetGeoFieldNames( $projectId = PROJECT_ID )
{
   // build the sql statement to display the string variables
   $sql = sprintf( "
      SELECT field_name, form_name, element_type,
         element_label, element_validation_type
         FROM redcap_metadata
         WHERE project_id = %d AND
            ( ( element_validation_type = 'zipcode' ) OR
              ( ( element_type = 'text' ) AND
                  ( element_validation_type IS null ) ) )
         ORDER BY field_name",
                       $_REQUEST['pid'] );

   // execute the sql statement
   $result = mysql_query( $sql );

   if ( ! $result )  // sql failed
   {
      die( "Error 141: Could not execute SQL:
            <pre>$sql</pre> <br />" .
            mysql_error() );
   }

   $geolocationHash = array();

   while ($record = mysql_fetch_assoc( $result ))
   {
      $key = $record['field_name'];

      $value = sprintf( "%s, %s (%s)",
                  $record['field_name'],
                  $record['element_label'],
                  $record['form_name'] );

      $geolocationHash[$key] = $value;
   }

   return( $geolocationHash );

}  // function GetGeoFieldNames()


/**
 * @brief Build a hash variable of all the field names containing
 *        fields that might be used for data (e.g. integer, float, select, yesno)
 *
 * @param  $projectId  Id number of the project
 * @retval $dataHash   Hash with the key as the field name
 *                     and the value containing the label
 */
function GetDataFieldNames( $projectId = PROJECT_ID )
{
   // build the sql statement to display the string variables
   $sql = sprintf( "
SELECT field_name, form_name, element_type,
       element_label, element_validation_type, element_enum
   FROM redcap_metadata
   WHERE project_id = %d
      AND
      ( ( element_type = 'calc' ) OR
        ( element_type = 'select' ) OR
        ( element_type = 'yesno' ) OR
        ( ( element_type = 'text' ) AND
          ( ( element_validation_type = 'float' ) OR
            ( element_validation_type = 'int' ) ) ) )
   ORDER BY field_name", $_REQUEST['pid'] );

   // execute the sql statement
   $result = mysql_query( $sql );

   if ( ! $result )  // sql failed
   {
      die( "Error 195: Could not execute SQL:
            <pre>$sql</pre> <br />" .
            mysql_error() );
   }

   $geolocationHash = array();

   while ($record = mysql_fetch_assoc( $result ))
   {
      $key = $record['field_name'];

      $value = sprintf( "%s, %s (%s)",
                  $record['field_name'],
                  $record['element_label'],
                  $record['form_name'] );

      $geolocationHash[$key] = $value;
   }

   return( $geolocationHash );

}  // function GetGeoFieldNames()


/**
 * @brief Build a hash variable of all the distinct values of a field
 *
 * @param  $projectId   Id number of the project
 * @param  $fieldName   The field name containing the to retrieve
 * @retval $valueHash   Hash with the unique values contained as
 *                      key and the value pairs
 */
function GetDistinctFieldValues( $projectId, $fieldName )
{
   // initialize variables
   $valueHash = array();

   if ( strlen( $fieldName ) > 0 )
   {
      $sql = sprintf( "
SELECT DISTINCT value
   FROM redcap_data
   WHERE project_id = %d AND
      field_name = '%s'
   ORDER BY value;",
                       $projectId,
                       $fieldName );

      // for debugging purposes
      // printf( "<pre>%s</pre> <br />", $sql );

      // execute the sql statement
      $result = mysql_query( $sql );

      if ( ! $result )  // sql failed
      {
         die( "Error 251: Could not execute SQL:
               <pre>$sql</pre> <br />" .
               mysql_error() );
      }

      // get label values to use for choice options
      $choiceHash = GetElementEnumChoiceValues( $projectId, $fieldName );

      while ($record = mysql_fetch_assoc( $result ))
      {
         $key = $record['value'];

         if ( count( $choiceHash ) > 0 )
         {
            $label = $choiceHash[$key];
         }
         else
         {
            $label = $key;
         }

         $valueHash[$key] = $label;
      }
   }

   return( $valueHash );

}  // function GetDistinctFieldValues()


/**
 * @brief Build a hash variable containing the key as the choice
 *        value and the value as the choice label
 *
 * @param  $projectId   Id number of the project
 * @param  $fieldName   The field name containing the to retrieve
 * @retval $valueHash   Hash with the unique values contained as
 *                      key and the value pairs
 */
function GetElementEnumChoiceValues( $projectId, $fieldName )
{
   // initialize variable
   $valueHash = array();

   if ( strlen( $fieldName ) > 0 )
   {
      $sql = sprintf( "
SELECT field_name, form_name, element_type,
       element_label, element_validation_type, element_enum
   FROM redcap_metadata
   WHERE ( project_id = %d ) AND
         ( field_name = '%s' ) AND
         ( ( element_type = 'select' ) OR
           ( element_type = 'yesno' ) )
   ORDER BY field_name",
                          $projectId,
                          $fieldName );

      // for debugging purposes
      // printf( "<pre>%s</pre> <br />", $sql );

      // execute the sql statement
      $result = mysql_query( $sql );

      if ( ! $result )  // sql failed
      {
         die( "Error 317: Could not execute SQL:
               <pre>$sql</pre> <br />" .
               mysql_error() );
      }

      while ($record = mysql_fetch_assoc( $result ))
      {
         $elementEnum = $record['element_enum'];
         $elementType = $record['element_type'];
      }

      if ( $elementType == 'select' )
      {
         // Information embedded in the following format:
         // 1, Balloonport \n 0, Closed \n 2, Heliport \n 6, Large Airport \n 5, Medium Airport \n 3, Seaplane Base \n 4, Small Airport

         $pattern = '/\s*\\\\n\s*/';  // split on carriage return
         $choiceOptions = preg_split( $pattern, $elementEnum );

         $pattern = '/\s*,\s*/';  // split on first comma

         foreach ( $choiceOptions as $choice )
         {
            // Information embedded in the following format:
            // 1, Balloonport
            list( $number , $label ) = preg_split( $pattern, $choice, 2 );
            $valueHash[$number] = $label;
         }
      }
      elseif ( $elementType == 'yesno' )
      {
         $choiceOptions = array( "1" => "Yes", "0" => "No" );

         foreach ( $choiceOptions as $key => $value )
         {
            $valueHash[$key] = $value;
         }
      }
   }

   return( $valueHash );

}  // function GetElementEnumChoiceValues()


/**
 * @brief Performs a REDCap query and returns values from the database
 *
 * @param  $projectId        Id number of the project
 * @param  $fieldNameArray   Array of field names of the columns to retrieve
 * @param  $filterNameHash   Array of field names and values to filter upon
 * @retval $fieldValueArray  Array of hashes containing the results of the query.
 *                           The keys represent the column names and the value
 *                           is the column value returned from the database.
 *                           The folllowing example represents one data row:
 *                      @code
 *                         $valueHash[0] = array( 'diesel_retail_price' => 3.959,
 *                                                'ifta_tax_rate' => 0.19,
 *                                                'diesel_discount_price' => 3.588, );
 *                      @endcode
 */
function GetMultipleFieldValues( $projectId,
                                 $fieldNameArray,
                                 $filterNameHash = array() )
{
   // intialize variables
   $count = 0;
   $aliasArray = range( "a", "z" );

   // initialize sql statement
   $sql = "
SELECT a.record";  // first column is the "study_id"

   // build the select columns in the sql statement
   // Example: SELECT a.record, a.xvalue, b.yvalue, c.zvalue
   foreach ( $fieldNameArray as $fieldName )
   {
      $sql .= sprintf( ", %s.%s", $aliasArray[$count], $fieldName );
      $count++;
   }

   // reset counter flag
   $count = 0;

   foreach ( $fieldNameArray as $fieldName )
   {
      // initialize variables
      // $filterStr = "";

      // if ( array_key_exists( $fieldName, $filterNameHash ) &&
           // strlen( $filterNameHash[$fieldName] ) > 0 )
      // {
         // $filterStr = sprintf( "AND
         // value = '%s'\n", $filterNameHash[$fieldName] );
      // }

      if ( $count == 0 )
      {
         $sql .= sprintf( "
FROM (
   SELECT record, value AS %s
   FROM redcap_data
   WHERE project_id = %d AND
         field_name = '%s'
) AS %s", $fieldName, $projectId, $fieldName, $aliasArray[$count] );
      }
      else
      {
         $sql .= sprintf( "
JOIN (
   SELECT record, value AS %s
   FROM redcap_data
   WHERE project_id = %d AND
         field_name = '%s'
) AS %s ON %s.record = %s.record", $fieldName,
                                   $projectId,
                                   $fieldName,
                                   $aliasArray[$count],
                                   $aliasArray[$count - 1],
                                   $aliasArray[$count] );
      }

      $count++;
   }

   foreach ( $filterNameHash as $fieldName => $fieldValue )
   {
      $sql .= sprintf( "
JOIN (
   SELECT record, value AS %s
   FROM redcap_data
   WHERE project_id = %d AND
         field_name = '%s' AND
         value = '%s'
) AS %s ON %s.record = %s.record", $fieldName,
                                   $projectId,
                                   $fieldName,
                                   $fieldValue,
                                   $aliasArray[$count],
                                   $aliasArray[$count - 1],
                                   $aliasArray[$count] );

      $count++;
   }

   $sql .= sprintf( "
ORDER BY %s.record", $aliasArray[0] );  // sort by the "study_id"

   // for debugging, display the generated SQL
   // printf( "<pre>%s</pre> <br />", $sql );

   // execute the sql statement
   $result = mysql_query( $sql );

   if ( ! $result )  // sql failed
   {
      die( "Error 471: Could not execute SQL:
            <pre>$sql</pre> <br />" .
            mysql_error() );
   }

   // initialize array
   $fieldValueArray = array();

   while ($record = mysql_fetch_assoc( $result ))
   {
      array_push( $fieldValueArray, $record );
   }

   return( $fieldValueArray );

}  // function GetMultipleFieldValues()


/**
 * @brief Performs a REDCap query and determines which data collection
 *        instrument form the field resides
 *
 * @param $projectId   Id number of the project
 * @param $fieldName   The field name containing values to retrieve
 * @retval $formName   The name for the form
 */
function GetFormName( $projectId, $fieldName )
{
   $sql = sprintf( "
      SELECT form_name
         FROM redcap_metadata
         WHERE redcap_metadata.project_id = %d AND
               redcap_metadata.field_name = '%s' ",
                  $projectId, $fieldName );

   // execute the sql statement
   $result = mysql_query( $sql );

   if ( ! $result )  // sql failed
   {
      die( "Error 510: Could not execute SQL:
            <pre>$sql</pre> <br />" .
            mysql_error() );
   }

   while ($record = mysql_fetch_assoc( $result ))
   {
      $key = "form_name";
      $formName = $record[$key];
   }

   return( $formName );

}  // function GetFormName()


/**
 * @brief Performs a REDCap query and determines the event id
 *
 * @param $projectId   Id number of the project
 * @param $fieldName   The field name containing values to retrieve
 * @retval $eventId    The event id
 */
function GetDataEventId( $projectId, $fieldName )
{
   $sql = sprintf( "
      SELECT DISTINCT event_id
         FROM redcap_data
            WHERE project_id = %d AND
                  field_name = '%s'
            ORDER BY event_id",
               $projectId, $fieldName );

   // execute the sql statement
   $result = mysql_query( $sql );

   if ( ! $result )  // sql failed
   {
      die( "Error 548: Could not execute SQL:
            <pre>$sql</pre> <br />" .
            mysql_error() );
   }

   while ($record = mysql_fetch_assoc( $result ))
   {
      $key = "event_id";
      $eventId = $record[$key];
   }

   return( $eventId );

}  // function GetDataEventId()


/**
 * @brief Retrieves the lat/lng marker data in JSON format
 *
 * @param $pid        Project id
 * @param $titleField The field name that will be used as the hover text
 * @param $latField   The field name containing the latitude values
 * @param $lngField   The field name containing the longitude values
 * @retval $json      JSON structure to be processed by JavaScript
 */
function GetJsonLatLngMarkerData( $pid,
                                  $titleField,
                                  $latField,
                                  $lngField )
{
   $json = "";

   // if ( isset( $pid ) &&
        // isset( $titleField ) &&
        // isset( $latField ) &&
        // isset( $lngField ) )
   // {
      $fieldNameArray = array( $titleField, $latField, $lngField );

      $fieldValueArray = GetMultipleFieldValues( $pid, $fieldNameArray );

      $newArray = array();  // initialize variable

      // obtain the name of the data collection instrument form
      $pageName = GetFormName( $_REQUEST['pid'],
                               $_REQUEST['hoverText'] );

      foreach ( $fieldValueArray as $fieldValueHash )
      {
         $newHash = array();  // reset variable

         foreach ( $fieldValueHash as $key => $value )
         {
            if ( $key == $titleField )
            {
               $newKey = "title";
            }
            elseif ( $key == $latField )
            {
               $newKey = "lat";
            }
            elseif ( $key == $lngField )
            {
               $newKey = "lng";
            }
            else  // if ( $key == "record" )
            {
               $newKey = $key;
            }

            $newHash[$newKey] = $value;
         }

         // build the URL to the record
         // Example: /redcap/redcap_v4.14.0/DataEntry/index.php?pid=57&page=airports&id=3375");
         $url = sprintf( "%sDataEntry/index.php?pid=%d&page=%s&id=%s",
                         APP_PATH_WEBROOT, $_REQUEST['pid'], $pageName, $newHash['record'] );

         $newHash['url'] = $url;

         array_push( $newArray, $newHash );
      }

      $json = json_encode( $newArray );
   // }

   return( $json );

}  // function GetJsonLatLngMarkerData()


/**
 * @brief Retrieves the geocode marker data in JSON format
 *
 * @param $pid            Project id
 * @param $titleField     Field name to be used as the hover text
 * @param $geoCodeFields  An array of the geocode fields selected
 * @retval $json          JSON structure processed by JavaScript
 */
function GetJsonGeoCodeMarkerData( $pid,
                                   $titleField,
                                   $geoCodeFields )
{
   $json = "";
   $fieldNameArray = $geoCodeFields;

   if ( ! in_array( $titleField, $geoCodeFields ) )
   {
      array_unshift( $fieldNameArray, $titleField );
   }

   $fieldValueArray = GetMultipleFieldValues( $pid, $fieldNameArray );

   $newArray = array();  // initialize variable

   // obtain the name of the data collection instrument form
   $pageName = GetFormName( $_REQUEST['pid'],
                            $_REQUEST['hoverText'] );

   foreach ( $fieldValueArray as $fieldValueHash )
   {
      $newHash = array();  // reset variable

      foreach ( $fieldValueHash as $key => $value )
      {
         if ( $key == $titleField )
         {
            $newKey = "title";
            $newHash[$newKey] = $value;
         }

         if ( $key == "record" )
         {
            $newKey = "record";
            $newHash[$newKey] = $value;

            // build the URL to the record
            // Example: /redcap/redcap_v4.14.0/DataEntry/index.php?pid=57&page=airports&id=3375");
            $url = sprintf( "%sDataEntry/index.php?pid=%d&page=%s&id=%s",
                  APP_PATH_WEBROOT, $_REQUEST['pid'], $pageName, $newHash['record'] );

            $newKey = "url";
            $newHash[$newKey] = $url;
         }
         elseif ( in_array( $key, $geoCodeFields ) )
         {
            $newKey = "geocode";

            if ( strlen( $newHash[$newKey] ) > 0 )
            {
               $newHash[$newKey] .= ", ";
            }

            $newHash[$newKey] .= $value;
         }
      }

      array_push( $newArray, $newHash );
   }

   $json = json_encode( $newArray );

   return( $json );

}  // function GetJsonGeoCodeMarkerData()
?>
