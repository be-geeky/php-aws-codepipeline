<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<body>
<?php

$hostname = "localhost";
$username = "root";
$password = "Kodinar@123";
$db = "magento";

$dbconnect = mysqli_connect($hostname, $username, $password, $db);

if ($dbconnect->connect_error) {
	die("Database connection failed: " . $dbconnect->connect_error);
}

?>

<table border="1" align="center">
<tr>
  <td>ID</td>
  <td>Name</td>
  <td>Email</td>
</tr>

<?php

$query = mysqli_query($dbconnect, "SELECT * FROM user")
or die(mysqli_error($dbconnect));

while ($row = mysqli_fetch_array($query)) {
	echo
		"<tr>
    <td>{$row['id']}</td>
    <td>{$row['name']}</td>
    <td>{$row['email']}</td>
   </tr>\n";

}

?>
</table>
</body>
</html>