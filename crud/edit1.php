<!DOCTYPE html>
<html >
<head>
</head>
<body>
<?php
			$zz = $_POST['id'];
			$fname = $_POST['firstname'];
		    $lname = $_POST['lastname'];
			$mname = $_POST['Middlename'];
			$address = $_POST['Address'];
			$contct = $_POST['Contact'];
			$comment = $_POST['comment'];
			
	   include('connection.php');
		
	 			$query = 'UPDATE people set first_name ="'.$fname.'",
					last_name ="'.$lname.'", mid_name="'.$mname.'",
					address="'.$address.'",contact='.$contct.', 
					comment="'.$comment.'" WHERE
					id ="'.$zz.'"';
					$result = mysqli_query($link, $query) or die(mysqli_error($link));
							
?>	
	<script type="text/javascript">
			alert("Update Successfull.");
			window.location = "index.php";
		</script>
 </body>
</html>