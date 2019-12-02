<!DOCTYPE html>
<html>
<head>
  <script src="http://code.jquery.com/jquery-latest.js"></script>
</head>

<script>
   $("#myForm").submit(function() {
      alert( "Submit handler called." );
      event.preventDefault();
      // return false;
   });
   
   /*
   $.get("<?php echo $_SERVER['PHP_SELF'] ?>", function(data) {
      $('#result').html(data);
      alert('Submit performed.');
   });
   */
</script>
<body>
  
<h1>Silent submission</h1>

<form id="myForm" method="get" action="javascript:alert('default action');">
   <input type="text" name="name" value="Look Ma, no page reload!" />
   <input type="submit" name="doit" value="Look Ma, no page reload!" />
</form>

<p>

<?php
   foreach ( $_REQUEST as $key => $value )
   {
      printf( "\$_REQUEST[%s]: %s<br />\n", $key, $value );
   }
   
   if ( isset( $_REQUEST['doit'] ) )
   {
      echo $_REQUEST['doit'];
   }
?>

<p>

<div id="result"></div>

</body>
</html>