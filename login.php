<!DOCTYPE html>

<html>
    <head>
    </head>

    <body>
        <?php

        if ($_GET["name"] != null)
        {   
            echo "You are ", $_GET["name"], ". <br/> Fun name, right?<br/>";
            $username = json_encode($_GET["name"]);
        }

        ?>

        <form method="GET">
            <input type="text" name="name"></input>
            <input type="submit" />
        </form>
    </body>
</html>