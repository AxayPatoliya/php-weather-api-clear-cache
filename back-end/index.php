<?php

// function for db connection and entry check
function record_exist($city_name){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "allevents";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $result = $conn->query("SELECT city FROM city WHERE city = '$city_name'");
    if($result->num_rows == 0) {
        // not exist
        $api_key = 'a256aa72018b05d973f2193ac7a2c336';
        $api_url = 'http://api.openweathermap.org/data/2.5/weather?q='.$city_name.'&appid='.$api_key;
        $weather_data = json_decode(file_get_contents($api_url), true);
        $temp = $weather_data['main']['temp']-273.15;

        // inserting
        $sql = "INSERT INTO city (city, temp)
        VALUES ('$city_name', '$temp')";

        if ($conn->query($sql) === TRUE) {
          echo "<h1>City-$city_name<br>Temperature-$temp &#8451</h1>";
        } else {
          echo "Error: " . $sql . "<br>" . $conn->error;
        }

        // deleting
        $sql_del = "CREATE EVENT myevent
            ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 HOUR
            DO
          DELETE FROM city WHERE city = '$city_name'";

        if ($conn->query($sql_del) === TRUE) {
          echo "Deleting in process...";
        } else {
          echo "Error: " . $sql_del . "<br>" . $conn->error;
        }
    } else {
        // do exist
        $temp_db = $conn->query("SELECT temp FROM city WHERE city = '$city_name'");
        $temp_db = $temp_db->fetch_assoc();
        echo("<h1>City-");
        print_r($city_name);
        echo("<br>Temperature-");
        print_r($temp_db['temp']);
        echo("&#8451</h1>");
   }
    $conn->close();
}



if (isset($_POST['submit'])) {
    // getting city name from the form
    $city = $_POST['city'];
    record_exist($city);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Info</title>
</head>
<body>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
        <input type="text" name="city">
        <input type="submit" name="submit">
    </form>
</body>
</html>
