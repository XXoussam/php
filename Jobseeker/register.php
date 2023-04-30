<?php
# Include connection
require_once "../sql/config.php";

# Define variables and initialize with empty values
$cin_err = $username_err = $email_err = $password_err = $lname_err = $daten_err = $fname_err = $cv_err = "";
$username = $email = $password = $nom = $prenom = $datenais = $cin = "";

# Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  # Validate username
  if (empty(trim($_POST["username"]))) {
    $username_err = "Please enter a username.";
  } else {
    $username = trim($_POST["username"]);
    if (!ctype_alnum(str_replace(array("@", "-", "_"), "", $username))) {
      $username_err = "Username can only contain letters, numbers and symbols like '@', '_', or '-'.";
    } else {
      # Prepare a select statement
      $sql2 = "SELECT * FROM demandeur_cv WHERE pseudo = ?";

      if ($stmt2 = mysqli_prepare($link, $sql2)) {
        # Bind variables to the statement as parameters
        mysqli_stmt_bind_param($stmt2, "s", $param_username);

        # Set parameters
        $param_username = $username;

        # Execute the prepared statement 
        if (mysqli_stmt_execute($stmt2)) {
          # Store result
          mysqli_stmt_store_result($stmt2);
          echo mysqli_stmt_num_rows($stmt2);
          # Check if username is already registered
          if (mysqli_stmt_num_rows($stmt2) > 0) {
            $username_err = "This username is already registered.";
          }
        } else {
          echo "<script>" . "alert('Oops! Something went wrong. Please try again later.')" . "</script>";
        }

        # Close statement 
        mysqli_stmt_close($stmt2);
      }
    }
  }

  #Validate CIN
  if (empty(trim($_POST["cin"]))) {
    $cin_err = "Please enter a cin number.";
  } else {
    $cin = trim($_POST["cin"]);
    if (!ctype_digit($cin)) {
      $cin_err = "CIN can only contain numbers.";

    } else {
      # Prepare a select statement
      $sql = "SELECT * FROM demandeur_cv WHERE cin = ?";

      if ($stmt = mysqli_prepare($link, $sql)) {
        # Bind variables to the statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_cin);

        # Set parameterscin
        $param_cin = $cin;

        # Execute the prepared statement 
        if (mysqli_stmt_execute($stmt)) {
          # Store result
          mysqli_stmt_store_result($stmt);

          # Check if username is already registered
          if (mysqli_stmt_num_rows($stmt) == 1) {
            $cin_err = "This CIN is already registered.";
          }
        } else {
          echo "<script>" . "alert('Oops! Something went wrong. Please try again later.')" . "</script>";
        }

        # Close statement 
        mysqli_stmt_close($stmt);
      }
    }
  }

  # Validate email 
  if (empty(trim($_POST["email"]))) {
    $email_err = "Please enter an email address";
  } else {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $email_err = "Please enter a valid email address.";
    } else {
      # Prepare a select statement
      $sql = "SELECT * FROM demandeur_cv WHERE email = ?";

      if ($stmt = mysqli_prepare($link, $sql)) {
        # Bind variables to the statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_email);

        # Set parameters
        $param_email = $email;

        # Execute the prepared statement 
        if (mysqli_stmt_execute($stmt)) {
          # Store result
          mysqli_stmt_store_result($stmt);

          # Check if email is already registered
          if (mysqli_stmt_num_rows($stmt) > 0) {
            $email_err = "This email is already registered.";
          }
        } else {
          echo "<script>" . "alert('Oops! Something went wrong. Please try again later.');" . "</script>";
        }

        # Close statement
        mysqli_stmt_close($stmt);
      }
    }
  }

  # Validate password
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter a password.";
  } else {
    $password = trim($_POST["password"]);
    if (strlen($password) < 8) {
      $password_err = "Password must contain at least 8 or more characters.";
    }
  }

  if (empty(trim($_POST["lname"]))) {
    $lname_err = "Please enter a last name.";
  }

  if (empty(trim($_POST["fname"]))) {
    $fname_err = "Please enter a first name.";
  }

  if (empty(trim($_POST["daten"]))) {
    $daten_err = "Please enter a birthdate name.";
  }

  $nom = trim($_POST["lname"]);
  $prenom = trim($_POST["fname"]);
  $datenais = trim($_POST["daten"]);
  if (isset($_FILES['pdfcv']) && is_uploaded_file($_FILES['pdfcv']['tmp_name'])) {
    $fileTmpPath = $_FILES['pdfcv']['tmp_name'];
    $fileName = $_FILES['pdfcv']['name'];
    $fileSize = $_FILES['pdfcv']['size'];
    $fileType = $_FILES['pdfcv']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    $uploadFileDir = './cv/';
    $dest_path = $uploadFileDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $dest_path)) {
      # Check input errors before inserting data into database
      if (empty($username_err) && empty($email_err) && empty($password_err) && empty($cin_err) && empty($lname_err) && empty($fname_err) && empty($daten_err)) {
        # Prepare an insert statement
        $sql = "INSERT INTO demandeur_cv(CIN, nom, prenom,pseudo,email,pass_word,date_naissance,code_universite,cv) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
          # Bind varibales to the prepared statement as parameters
          $uni = 1;
          mysqli_stmt_bind_param($stmt, "sssssssss", $param_cin, $param_nom, $param_prenom, $param_pseudo, $param_email, $param_password, $param_datenais, $uni, $param_file);

          # Set parameters
          $param_nom = $_POST["lname"];
          $param_prenom = $_POST["fname"];
          $param_email = $email;
          $param_pseudo = $username;
          $param_datenais = $_POST["daten"];
          $param_password = password_hash($password, PASSWORD_DEFAULT);
          $param_file = $newFileName;

          # Execute the prepared statement
          if (mysqli_stmt_execute($stmt)) {
            echo "<script>" . "alert('Registration completed successfully. Login to continue.');" . "</script>";
            echo "<script>" . "window.location.href='./login.php';" . "</script>";
            exit;
          } else {
            $error_message = mysqli_stmt_error($stmt);
            echo "<script>" . "alert('Oops! Something went wrong. Please try again later. Error message: " . $error_message . "');" . "</script>";
          }


          # Close statement
          mysqli_stmt_close($stmt);
        }
      }
    } else {
      echo 'Error: Failed to move uploaded file.';


    }
  } else {
    $cv_err = "upload cv";
  }

  # Close connection
  mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User login system</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/main.css">
  <link rel="shortcut icon" href="../img/favicon-16x16.png" type="image/x-icon">
  <script defer src="../js/script.js"></script>
</head>

<body>
  <div class="container">
    <div class="row min-vh-100 justify-content-center align-items-center" >
      <div class="col-lg-5" >
        <div class="form-wrap border rounded p-4"  style="background-color: #0000ff1a;">
          <h1>Sign up as Job Seeker</h1>
          <!-- form starts here -->
          <form action="register.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="cin" class="form-label"></label>
              <input placeholder="CIN" type="text" class="form-control" name="cin" id="cin" value="<?= $cin; ?>">
              <small class="text-danger">
                <?= $cin_err; ?>
              </small>
            </div>
            <div class="mb-3">
              <label for="fname" class="form-label"></label>
              <input placeholder="First Name" type="text" class="form-control" name="fname" id="fname" value="<?= $prenom; ?>">
              <small class="text-danger">
                <?= $fname_err; ?>
              </small>
            </div>
            <div class="mb-3">
              <label for="lname" class="form-label"></label>
              <input placeholder="Last Name" type="text" class="form-control" name="lname" id="lname" value="<?= $nom; ?>">
              <small class="text-danger">
                <?= $lname_err; ?>
              </small>
            </div>
            <div class="mb-3">
              <label for="daten" class="form-label"></label>
              <input placeholder="Date Naissance" type="date" class="form-control" name="daten" id="daten" value="<?= $datenais; ?>">
              <small class="text-danger">
                <?= $daten_err; ?>
              </small>
            </div>

            <div class="mb-3">
              <label for="username" class="form-label"></label>
              <input placeholder="Username" type="text" class="form-control" name="username" id="username" value="<?= $username; ?>">
              <small class="text-danger">
                <?= $username_err; ?>
              </small>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label"></label>
              <input placeholder="Email Address" type="email" class="form-control" name="email" id="email" value="<?= $email; ?>">
              <small class="text-danger">
                <?= $email_err; ?>
              </small>
            </div>
            <div class="mb-2">
              <label for="password" class="form-label"></label>
              <input placeholder="Password" type="password" class="form-control" name="password" id="password" value="<?= $password; ?>">
              <small class="text-danger">
                <?= $password_err; ?>
              </small>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="togglePassword">
              <label for="togglePassword" class="form-check-label">Show Password</label>
            </div>
            <div class="mb-3" style="display:flex">
              <label for="pdfcv" class="form-label fw-bold">Deposit your CV</label>
              <input style="
              margin-left: 3%;
              height: 37px;
              background-color: aquamarine;"
               class="form-control" name="pdfcv" type="file" id="pdfcv" accept="application/pdf">
              <small class="text-danger">
                <?= $cv_err; ?>
              </small>
            </div>
            <div class="mb-3" style="display:flex">
              <input style="width:120px;background-color: #008000ba;" type="submit" class="btn btn-primary form-control" name="submit" value="Sign Up">
              <p style="margin-left:3%" class="mb-0">Already have an account ? <a href="./login.php">Log In</a></p>
            </div>
            
          </form>
          <!-- form ends here -->
        </div>
      </div>
    </div>
  </div>
</body>

</html>