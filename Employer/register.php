<?php
# Include connection
require_once "../sql/config.php";

# Define variables and initialize with empty values
$username_err = $email_err = $password_err = $lname_err = $fname_err = $ennom_err = $cde_err = "";
$username = $email = $password = $cde = $fname = $lname = $ennom = "";

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
      $sql = "SELECT * FROM employeur WHERE pseudo = ?";

      if ($stmt = mysqli_prepare($link, $sql)) {
        # Bind variables to the statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_username);

        # Set parameters
        $param_username = $username;

        # Execute the prepared statement 
        if (mysqli_stmt_execute($stmt)) {
          # Store result
          mysqli_stmt_store_result($stmt);

          # Check if username is already registered
          if (mysqli_stmt_num_rows($stmt) > 0) {
            $username_err = "This username is already registered.";
          }
        } else {
          echo "<script>" . "alert('Oops! Something went wrong. Please try again later.')" . "</script>";
        }

        # Close statement 
        mysqli_stmt_close($stmt);
      }
    }
  }

    #Validate Code Entreprise
    if (empty(trim($_POST["cde"]))) {
      $cde_err = "Please enter a code registre commerce.";
    } else {
      $cde = trim($_POST["cde"]);
      if (!ctype_digit($cde)) {
        $cde_err = "cin can only contain numbers";
      } else {
        # Prepare a select statement
        $sql = "SELECT * FROM employeur WHERE code_registre_commerce = ?";
  
        if ($stmt = mysqli_prepare($link, $sql)) {
          # Bind variables to the statement as parameters
          mysqli_stmt_bind_param($stmt, "s", $param_cde);
  
          # Set parameterscin
          $param_cde = $cde;
  
          # Execute the prepared statement 
          if (mysqli_stmt_execute($stmt)) {
            # Store result
            mysqli_stmt_store_result($stmt);
  
            # Check if username is already registered
            if (mysqli_stmt_num_rows($stmt) > 0) {
              $cde_err = "This CRC is already registered.";
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
      $sql = "SELECT * FROM employeur WHERE email = ?";

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

  if (empty(trim($_POST["ennom"]))) {
    $ennom_err = "Please enter a entreprise name.";
  }


  # Check input errors before inserting data into database
  if (empty($username_err) && empty($email_err) && empty($password_err) && empty($cde_err) && empty($lname_err) && empty($fname_err) && empty($ennom_err)) {
    # Prepare an insert statement
    $sql = "INSERT INTO employeur(code_registre_commerce, nom_entreprise,email,nom_gerant	,prenom_gerant	,pseudo	, pass_word) VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
      # Bind varibales to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "sssssss", $param_cde,$param_enname, $param_email,$param_lname,$param_fname,$param_username, $param_password);

      # Set parameters
      $param_cde = $cde;
      $param_enname = $_POST["ennom"];
      $param_lname = $_POST["lname"];
      $param_fname = $_POST["fname"];
      $param_username = $username;
      $param_email = $email;
      $param_password = password_hash($password, PASSWORD_DEFAULT);

      # Execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        echo "<script>" . "alert('Registeration completed successfully. Login to continue.');" . "</script>";
        echo "<script>" . "window.location.href='./login.php';" . "</script>";
        exit;
      } else {
        echo "<script>" . "alert('Oops! Something went wrong. Please try again later.');" . "</script>";
      }

      # Close statement
      mysqli_stmt_close($stmt);
    }
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/main.css">
  <link rel="shortcut icon" href="../img/favicon-16x16.png" type="image/x-icon">
  <script defer src="../js/script.js"></script>
</head>

<body>
  <div class="container">
    <div class="row min-vh-100 justify-content-center align-items-center">
      <div class="col-lg-5">
        <div class="form-wrap border rounded p-4"  style="background-color: #0000ff1a;">
          <h1>Sign up as Employer</h1>          <!-- form starts here -->
          <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
          <div class="mb-3">
              <label for="cde" class="form-label"></label>
              <input placeholder="Code entreprise" type="text" class="form-control" name="cde" id="cde" value="<?= $cde; ?>">
              <small class="text-danger"><?= $cde_err; ?></small>
            </div>
            <div class="mb-3">
              <label for="noment" class="form-label"></label>
              <input placeholder="Nom de l'entreprise" type="text" class="form-control" name="ennom" id="ennom" value="<?= $ennom; ?>">
              <small class="text-danger"><?= $ennom_err; ?></small>
            </div>
            <div class="mb-3">
              <label for="fname" class="form-label"></label>
              <input placeholder = "code entreprise" type="text" class="form-control" name="fname" id="fname" value="<?= $fname; ?>">
              <small class="text-danger">
                <?= $fname_err; ?>
              </small>
            </div>
            <div class="mb-3">
              <label for="lname" class="form-label"></label>
              <input placeholder="Prénom Gérant" type="text" class="form-control" name="lname" id="lname" value="<?= $lname; ?>">
              <small class="text-danger">
                <?= $lname_err; ?>
              </small>
            </div>
            <div class="mb-3">
              <label for="username" class="form-label"></label>
              <input placeholder="Username" type="text" class="form-control" name="username" id="username" value="<?= $username; ?>">
              <small class="text-danger"><?= $username_err; ?></small>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label"></label>
              <input placeholder="Email Address" type="email" class="form-control" name="email" id="email" value="<?= $email; ?>">
              <small class="text-danger"><?= $email_err; ?></small>
            </div>
            <div class="mb-2">
              <label for="password" class="form-label"></label>
              <input placeholder="Password" type="password" class="form-control" name="password" id="password" value="<?= $password; ?>">
              <small class="text-danger"><?= $password_err; ?></small>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="togglePassword">
              <label for="togglePassword" class="form-check-label">Show Password</label>
            </div>
            <div class="mb-3" style="display:flex">
              <input style="width:120px;background-color: #008000ba;" type="submit" class="btn btn-primary form-control" name="submit" value="Sign Up">
              <p class="mb-0" style="    margin-left: 5%;">Already have an account ? <a href="./login.php">Log In</a></p>
            </div>
          </form>
          <!-- form ends here -->
        </div>
      </div>
    </div>
  </div>
</body>

</html>