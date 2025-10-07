<?php
    echo "Boilerplate here <br/> I think he wants us to use Bootstrap for this? Or at least it's recommended <br>";
    echo "Anyway";

    $members = ["AJ Schulte", "Joseph Gallucci", "Riley Fitzgerald"];
?>

<!DOCTYPE html>

<html>
    <body>
        <br>
        <a href="login.php">User Login</a>
    </body>

    <footer>
        Developed by <?php for($i=0;$i<3;$i++) {
			echo $members[$i], "<br>";
		} ?>
    </footer>
</html>