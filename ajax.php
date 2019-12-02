<html>
<head>
<title>Ajax Example</title>

<script type="text/javascript" src="jquery/jquery-1.7.1.js"></script>

<script type="text/javascript">
   $(function(){
      
      $('#helloButton').click(function(){
         
         var fname = $('#fname').val();
         
         $.get(
            'get.php', {name:fname},
            function(data) { $('#result').html( data ); }
         );
      });
   });
</script>
</head>

<body>
<form>
   Name: <input type="text" name="fname" id="fname" />
   <button type="button" id="helloButton">Say Hello</button>
</form>
<div id="result"> </div>
Entered value: <?php echo $_GET['fname'] ?> <br />
Entered value: <?php echo $value ?>
</body>
</html>
