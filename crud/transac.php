<?php
       
 include('connection.php');
       
        $filename = $_FILES["img"]["name"];
        $tempname = $_FILES["img"]["tmp_name"];
        $folder = "images/".$filename;
        move_uploaded_file($tempname, $folder);


$image= $_POST['img'];
$fname = $_POST['firstname'];
$lname = $_POST['lastname'];
$mname = $_POST['Middlename'];
$address = $_POST['Address'];
$contct = $_POST['Contact'];
$comment = $_POST['comment'];

switch ($_GET['action'])
{
    case 'add':
        $query = "INSERT INTO people
                                (img,first_name, last_name, mid_name, address,contact, comment)
                                VALUES (' $folder ','" . $fname . "','" . $lname . "','" . $mname . "','" . $address . "','$contct','" . $comment . "')";
        mysqli_query($link, $query) or die('Error in updating Database');

    break;

}
?>
        <script type="text/javascript">
            alert("Successfully added.");
            window.location = "index.php";
    </script>
