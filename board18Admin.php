<?php
/*
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
//Function to sanitize values received from POST. 
//Prevents SQL injection
function clean( $conn, $str ) {
  $str = @trim($str);
  return mysqli_real_escape_string( $conn, $str );
}

require_once('php/auth.php');
require_once('php/config.php');

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  error_log('Failed to connect to server: ' . mysqli_connect_error());
  die('Connect error: (' . mysqli_connect_errno() . ') ' .
          mysqli_connect_error());
  exit; // just in case
}

//Sanitize the force value
$forcex = clean( $link, $_REQUEST['force']);
// $force must be 'yes' or 'no'.
$force = ($forcex  === 'yes') ? : 'no';

//Create query
$qry = "SELECT * FROM players WHERE player_id='$loggedinplayer'";

$result = mysqli_query($link, $qry);
//Check whether the query was successful or not
if ($result) {
  if (mysqli_num_rows($result) == 1) {
    //Query Successful
    $playerrow = mysqli_fetch_assoc($result);
    $firstname = $playerrow['firstname'];
    $lastname = $playerrow['lastname'];
    $email = $playerrow['email'];
    $login = $playerrow['login'];
    $passwd = $playerrow['passwd'];
  } else {
    //Player not found
    header("location: access-denied.html");
  }
} else {
  error_log("Query failed");
  header("location: access-denied.html");
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18Admin.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18Admin.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        $('.error').hide();

        if ('<?php echo "$force"; ?>' === 'yes') {
          $('#passwd form').show();
        } else {
          $('#admin form').show();
        }
        $("#passwd").submit(function() {  
          forceChange('<?php echo $passwd; ?>');
          return false;
        }); // end passwd
        $("#admin").submit(function() {  
          administrate('<?php echo $passwd; ?>');
          return false;
        }); // admin
        $("#button2").click(function(){
          $('.error').hide(); 
          $('#admin form #pname').val('<?php echo $login; ?>');
          $('#admin form #email').val('<?php echo $email; ?>');
          $('#admin form #fname').val('<?php echo $firstname; ?>');
          $('#admin form #lname').val('<?php echo $lastname; ?>');
          $('#admin form #oldpw1').val('');
          $('#admin form #passwrd1').val('');
          $('#admin form #passwrd2').val('');
          return false;
        }); // end button2 click
        $("#button4").click(function(){
          window.location = "board18Main.php";
          return false;
        }); // end button2 click
      }); // end ready
    </script>
  </head>
  <body>

    <div id="topofpage">
      <div id="logo">
        <img src="images/logo.png" alt="Logo"/> 
      </div>
      <div id="heading">
        <h1>BOARD18 - Remote Play Tool For 18xx Style Games</h1>
      </div>
      <div>
        <p id="lognote"><?php echo "$welcomename: $headermessage"; ?>
          <span style="font-size: 70%">
            Click <a href="index.html">here</a> 
            if you are not <?php echo "$welcomename"; ?>.
          </span>
        </p>
      </div>
    </div>

    <div id="leftofpage">
    </div>

    <div id="rightofpage"> 
      <div id="content">    
        <div id="admin">
          <form name="admin" class="hideform" action="">
            <fieldset>
              <p>
                <label for="pname">Player ID:</label>
                <input type="text" name="pname" id="pname" class="reg"
                       value="<?php echo $login; ?>">
                <label class="error" for="pname" id="pname_error">
                  This field is required.</label>
              </p>
              <p>
                <label for="oldpw1">Enter Current Password:</label>
                <input type="password" name="oldpw1" id="oldpw1" 
                       value="" autocomplete="off">
                <label class="error" for="oldpw1" id="oldpw1_error">
                  This field is required.</label><br>
                The current Password is required to change any field.
              </p>
              <p>
                <label for="passwrd1">Enter New Password: </label>
                <input type="password" name="passwrd1" id="passwrd1">
                <label class="error" for="passwrd1" id="passwrd1_error">
                  This field is required.</label>
              </p>
              <p>
                <label for="passwrd2">Reenter New Password: </label>
                <input type="password" name="passwrd2" id="passwrd2">
                <label class="error" for="passwrd2" id="passwrd2_error">
                  Password field mismatch.</label> <br>
                The <span style="font-weight:bold">same</span> 
                new Password must be entered both times.
              </p>
              <p>
                <label for="email">Change Email Address: </label>
                <input type="text" name="email" id="email" class="reg"
                       value="<?php echo $email; ?>">
                <label class="error" for="email" id="email_error">
                  This field is required.</label>
              </p>

              <p>
                <label for="fname">Change First Name: </label>
                <input type="text" name="fname" id="fname" class="reg"
                       value="<?php echo $firstname; ?>">
              </p>
              <p>
                <label for="lname">Change Last Name: </label>
                <input type="text" name="lname" id="lname" class="reg"
                       value="<?php echo $lastname; ?>">
              </p>
              <p>
                <input type="submit" name="adminbutton" class="pwbutton"  
                       id="button1" value="Submit" >
                <input type="button" name="canbutton" class="pwbutton"  
                       id="button2" value="Reset Form" >
                <input type="button" name="canbutton" class="pwbutton"  
                       id="button4" value="Exit" >
              </p>
            </fieldset>
          </form>
        </div>

        <div id="passwd">
          <form name="passwd" class="hideform" action="">
            <fieldset>
              <p style="font-size: 110%">
                Please change your temporary password before proceeding.
              </p>
              <p>
                <label for="oldpw2">Enter Player ID:</label>
                <input type="password" name="oldpw2" id="oldpw2">
                <label class="error" for="oldpw2" id="oldpw2_error">
                  This field is required.</label>
              </p>
              <p>
                <label for="passwrd3">Enter Password: </label>
                <input type="password" name="passwrd3" id="passwrd3">
                <label class="error" for="passwrd3" id="passwrd3_error">
                  This field is required.</label>
              </p>
              <p>
                <label for="passwrd4">Reenter Password: </label>
                <input type="password" name="passwrd4" id="passwrd4">
                <label class="error" for="passwrd4" id="passwrd4_error">
                  Password field mismatch.</label>
              </p>
              <p>
                <input type="submit" name="forcebutton" class="pwbutton"  
                       id="button3" value="Submit" >
              </p>
            </fieldset>
          </form>
        </div>

      </div> 
    </div>  
  </body>
</html>
