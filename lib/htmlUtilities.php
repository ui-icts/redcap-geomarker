<?php
/**
 * @brief Handy HTML utilities
 *
 * @file htmlUtilities.php
 * $Revision: 210 $
 * $Author: fmcclurg $
 * @author Fred R. McClurg, University of Iowa
 * $Date:: 2013-03-27 13:09:28 #$
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/lib/htmlUtilities.php $
 */


/**
 * @brief Builds a drop down list from an associative array
 *
 * @param $listName      Name of the dropdown list control
 * @param $dropdownHash  Associative array with the key used for the value
 *                       and a value used for the label
 * @param $defaultKey    If form has not been submitted, set this item as the default
 * @param $isAutoSubmit  If true, submit form upon change
 * @retval $htmlStr      HTML of the drop-down list
 */
function BuildDropDownList( $listName,
                            $dropdownHash,
                            $isAutoSubmit = FALSE,
                            $defaultKey = "" )
{
   $onChange = "";

   if ( $isAutoSubmit )
   {
      // $onChange = "onChange='alert(\"Foo!\");'";
      $onChange = "onChange='this.form.submit()'";
   }

   $htmlStr = sprintf( "<select name='%s' %s>\n", $listName, $onChange );

   // the first menu option is blank
   $htmlStr .= "   <option value=''></option>\n";

   foreach ( $dropdownHash as $key => $label )
   {
      /*
      if ( isset( $_REQUEST[$listName] ) &&  // form has been submitted
         ( $_REQUEST[$listName] == $key ) )  // option selected previously
      {
         // make item "sticky" and remember previous submission
         $htmlStr .= sprintf( "   <option value=\"%s\" selected=\"selected\">%s</option>\n",
            $key, $label );
      }
      else
      {
         if ( $defaultKey == $key )  // option specified as default
         {
            // set it as the default selection
            $htmlStr .= sprintf( "   <option value=\"%s\" selected=\"selected\">%s</option>\n",
               $key, $label );
         }
         else
         {
            $htmlStr .= sprintf( "   <option value=\"%s\">%s</option>\n",
               $key, $label );
         }
      }
      */
      
      if ( ! isset( $_REQUEST[$listName] ) )  // form has not been submitted
      {
         if ( $defaultKey == $key )  // option specified as default
         {
            // set it as the default selection
            $htmlStr .= sprintf( "   <option value=\"%s\" selected=\"selected\">%s</option>\n",
                                 $key, $label );
         }
         else
         {
            $htmlStr .= sprintf( "   <option value=\"%s\">%s</option>\n",
               $key, $label );
         }
      }
      else  // form has been submitted
      {
         if ( $_REQUEST[$listName] == $key )  // option specified as default
         {
            // make item "sticky" and remember previous submission
            $htmlStr .= sprintf( "   <option value=\"%s\" selected=\"selected\">%s</option>\n",
                                 $key, $label );
         }
         else
         {
            $htmlStr .= sprintf( "   <option value=\"%s\">%s</option>\n",
               $key, $label );
         }
      }
   }

   $htmlStr .= sprintf( "</select>\n" );

   return( $htmlStr );

}  // function BuildDropDownList()


/**
 * @brief   Sets the value of the text widget to the previous submission value
 * @author  Fred R. McClurg
 * @date    February 6, 2010
 *
 * @param   $requestKey    Name of the text control
 * @param   $defaultValue  Fallback value if previous submission value is undefined (optional).
 * @retval  $returnedValue Returns previously submitted value or the default.
 *
 * An example call statement would be:
 *
 * @code
 * <input type="text"
 *        name="title"
 *        value="<?php echo SetStickyValue( 'title',
 *                                           'Default Title Here' ); ?>" />
 * @endcode
 */
function SetStickyValue( $requestKey, $defaultValue = "" )
{
   if ( array_key_exists( $requestKey, $_REQUEST ) )  // form submitted
   {
      $returnedValue = $_REQUEST[$requestKey];
   }
   else  // form not submitted
   {
      $returnedValue = $defaultValue;
   }

   return( $returnedValue );

}  //  function SetStickyValue()


/**
 * @brief   Sets the attribute of the checkbox widget to the previous submission setting
 * @author  Fred R. McClurg
 * @date    September 26, 2012
 *
 * @param   $requestKey    Name of the checkbox control
 * @param   $isChecked     Default value if previous submission value is undefined (optional).
 * @retval  $returnedValue Returns previously submitted value or the default.
 *
 * An example call statement would be:
 *
 * @code
 * <input type="checked"
 *        name="showHide"
 *        <?php echo SetStickyChecked( 'showHide', TRUE ); ?> />
 * @endcode
 *
 */
function SetStickyChecked( $requestKey, $isChecked = FALSE )
{
   $returnedValue = "";

   if ( array_key_exists( "doit", $_REQUEST ) )  // form submitted
   {
      if ( array_key_exists( $requestKey, $_REQUEST ) )  // checkbox is checked
      {
         $returnedValue = "checked='checked'";
      }
   }
   else  // form not submitted
   {
      if ( $isChecked )  // default value
      {
         $returnedValue = "checked='checked'";
      }
   }

   return( $returnedValue );

}  //  function SetStickyChecked()


/**
 * @brief   Returns a red star unless all of the required fields have been completed
 * @author  Fred R. McClurg
 * @date    October 5, 2012
 *
 * @param   $keyArray    Array of names of the required fields
 *
 * Possible Scenarios:  "X" means requirement has not been satisfied
 *    X Unsubmitted (button not pressed)
 *    X $_REQUEST['business'] = '';  // value not set
 *    $_REQUEST['business'] = 'Value Set';
 */
function RequiredFieldNotice( $keyArray = array() )
{
   $returnedValue = "";

   foreach ( $keyArray as $requestKey )
   {
      if ( ! array_key_exists( $requestKey, $_REQUEST ) ||  // form not submitted
           strlen( $_REQUEST[$requestKey] ) == 0 )  // option has been set
      {
            $returnedValue = sprintf( "<span style='color: %s;'>*</span>\n", "red" );
            break;  // requirement has not been met
      }
   }

   echo( $returnedValue );

}  //  function RequiredFieldNotice()


/**
 * @brief   Concatinates passed hash values and returns the record as the key
 * @author  Fred R. McClurg
 * @date    November 19, 2012
 * @code
 *    $hash{'17695'} = "Picayune Airport, Iowa City, US-IA";
 *    $hash{'20182'} = "Iowa City Municipal Airport, Iowa City, US-IA";
 *    $hash{'8330'} = "University of Iowa Hospitals & Clinic Heliport, Iowa City, US-IA";
 * @endcode
 *
 * @param   $geoHashArray  Array of hashes to be concatenated
 *
 * Possible Data Array:
 *    array (
 *       'record' => '17769',
 *       'airport_name' => 'University of Iowa Hospitals & Clinic No2 Heliport',
 *       'municipality' => 'Iowa City',
 *       'iso_region' => 'US-IA' );
 */
function ConcatHashValues( $geoHashArray )
{
   $geoHash = array();
   
   foreach ( $geoHashArray as $hash )
   {
      $record = array_shift( $hash );
      $geoString = implode( ", ", $hash );
      // $geoHash[$record] = $geoString;
      
      array_push( $geoHash, $geoString );
   }

   return( $geoHash );

}  //  function ConcatHashValues()


/**
 * @brief Builds a query string from an array of $_REQUEST[] keys
 *
 * @param  $keyArray     Array of $_REQUEST[] keys
 * @retval $queryString  Returns "&" delimited key=value pairs
 */
function BuildQueryString( $keyArray )
{
   $hash = array();

   foreach ( $keyArray as $key )
   {
      $hash[$key] = $_REQUEST[$key];
   }

   $queryString = http_build_query( $hash );

   return( $queryString );

}  // function BuildQueryString()

?>
