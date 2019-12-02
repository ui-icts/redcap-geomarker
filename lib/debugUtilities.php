<?php
/**
 * @brief Utilities useful for debugging code
 *
 * @file debugUtilities.php
 * $Revision: 200 $
 * $Author: fmcclurg $
 * @author Fred R. McClurg, University of Iowa
 * $Date:: 2012-10-16 10:55:45 #$
 * @since 2011-12-27
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/lib/debugUtilities.php $
 */

   /**
    * @brief Retrieves the function name of the calling function
    *
    * @return Name of the function
    */
   function GetFunctionName()
   {
      $backtrace = debug_backtrace();

      # echo "<pre>";
      # var_dump( $backtrace );
      # echo "</pre>";

      return( $backtrace[1]['function'] );
   }  // GetFunctionName()


   /**
    * @brief Retrieves the arguments passed to the calling function
    *
    * @return Argument values of the function
    */
   function GetFunctionArgs()
   {
      $backtrace = debug_backtrace();

      # echo "<pre>";
      # var_dump( $backtrace );
      # echo "</pre>";

      $argArray = $backtrace[1]['args'];

      $args = implode( ", ", $argArray );

      return( $args );
   }  // GetFunctionArgs()


   /**
    * @brief Displays the line number of the calling function
    *
    * @return Line number
    */
   function  GetLineNumber()
   {
      $backtrace = debug_backtrace();

      # echo "<pre>";
      # var_dump( $backtrace );
      # echo "</pre>";

      return( $backtrace[1]['line'] );
   }  // GetLineNumber()


   /**
    * @brief Displays the filename of the calling function
    *
    * @return Basename of the file
    */
   function GetFileName()
   {
      $backtrace = debug_backtrace();

      # echo "<pre>";
      # var_dump( $backtrace );
      # echo "</pre>";

      $file = basename( $backtrace[1]['file'] );
      return( $file );
   }  // GetFileName()


   /**
    * @brief Displays function, arguments, line number, and file of the calling function
    */
   function WhereAmI()
   {
      $func = getFunctionName();
      $args = getFunctionArgs();
      $line = getLineNumber();
      $file = getFileName();

      printf( "Function: %s(%s) &nbsp; Line: %d &nbsp; File: %s<br />\n",
              $func, $args, $line, $file );
   }  // WhereAmI()

   /**
    * @brief Dumps the value of a variable
    *
    * @param  $name   The variable name
    * @param  $value  The contents of the variable
    */
   function PrintDebug( $name, $value )
   {
      printf( "Variable %s: ", $name );
      printf( "<pre>%s</pre>", print_r( $value ) );

   }  // function PringDebug()

?>
