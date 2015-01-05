<?php
require_once ("synch.class.php");
$x=new reservesSynch();

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
  <title>Synch Watzek Course Reserves</title>
	<style>
	body{margin:30px;}
.submit {

font-size:150%;
 width: 250px;
 height: 150px;
 border: none;
 margin-left: 100;
 margin-top:30;
 padding: 0;
 box-shadow: 10px 10px 5px #888888;

}

</style>
</head>
<body>
<?php
$x->controller();
?>
</body>
</html>