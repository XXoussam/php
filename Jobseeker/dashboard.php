<?php
# Initialize the session
session_start();
include '../sql/config.php';


if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}
$user_id = $_SESSION['username'];
$sql = "SELECT CIN FROM demandeur_cv WHERE pseudo ='" . $user_id . "'";

$result = mysqli_query($link, $sql);

if (!$result) {
    die('Error executing query: ' . mysqli_error($link));
}

$cin = mysqli_fetch_assoc($result);
$cin = $cin["CIN"];

# VALUES
$cin = mysqli_real_escape_string($link, $cin);


$diploma_field = 'code_diplome';

$sql = "SELECT GROUP_CONCAT(DISTINCT $diploma_field SEPARATOR ',') AS diplome_values FROM diplome_demandeur WHERE CIN = '$cin'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$diploma_values = $row['diplome_values'];

$competence_field = 'code_competence';
$sql = "SELECT GROUP_CONCAT(DISTINCT $competence_field SEPARATOR ',') AS competence_values FROM competence_demandeur WHERE CIN = '$cin'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$competence_values = $row['competence_values'];
#####

if (isset($_GET['apply_id'])) {

    $apply_id = mysqli_real_escape_string($link, $_GET['apply_id']);

    echo $cin;
    $insert = mysqli_query($link, "INSERT INTO `candidature` (`CIN`,`code_offre_emploi`, `etat_candidature`) VALUES ('$cin','$apply_id', '0')");


    if ($insert) {

        header('location:dashboard.php');
        echo '<div class="success">Job offer added successfully!</div>';
    } else {
        echo '<div class="error">Error in adding job offer!</div>';
        echo mysqli_error($link);
    }

}



if (isset($_POST['update_resume'])) {
    // Get the new competence values from the $_POST array
    if(isset($_POST['competence'])) {
    $new_competence_values = $_POST['competence'];

    // Get the corresponding code values from the competence table
    $codes = array();
    foreach ($new_competence_values as $competence) {
        $sql = "SELECT code_competence FROM competence WHERE libelle_competence = '$competence'";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            $codes[] = $row['code_competence'];
        }
    }

    // Update the competence_demandeur table with the new competence values and codes
    $sql = "DELETE FROM competence_demandeur WHERE CIN = $cin";
    if (mysqli_query($link, $sql)) {
        $stmt = mysqli_prepare($link, "INSERT INTO competence_demandeur (CIN, code_competence) VALUES (?, ?)");
        
        if ($stmt) {
            // Bind the parameters and execute the statement for each code
            foreach ($codes as $code) {
                mysqli_stmt_bind_param($stmt, "si", $cin, $code);
                if (mysqli_stmt_execute($stmt)) {
                } else {
                    echo "Error inserting record: " . mysqli_error($link);
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error preparing statement: " . mysqli_error($link);
        }
    } else {
        echo "Error deleting old codes: " . mysqli_error($link);
    }

}}


if (isset($_POST['update_resume'])) {
    if(isset($_POST['Diploma'])) {
        $new_diplome_values = $_POST['Diploma'];

        $codes = array();
        foreach ($new_diplome_values as $diplome) {
            $sql = "SELECT code_diplome FROM diplome WHERE libelle_diplome = '$diplome'";
            $result = mysqli_query($link, $sql);
            $row = mysqli_fetch_assoc($result);
            if ($row) {
                $codes[] = $row['code_diplome'];
            }
        }
    // Update the diplome_demandeur table with the new competence values and codes
    $sql = "DELETE FROM diplome_demandeur WHERE CIN = $cin";
    if (mysqli_query($link, $sql)) {
        $stmt = mysqli_prepare($link, "INSERT INTO diplome_demandeur (CIN, code_diplome) VALUES (?, ?)");

        if ($stmt) {
            // Bind the parameters and execute the statement for each code
            foreach ($codes as $code) {
                mysqli_stmt_bind_param($stmt, "si", $cin, $code);
                if (mysqli_stmt_execute($stmt)) {
                } else {
                    echo "Error inserting record: " . mysqli_error($link);
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error preparing statement: " . mysqli_error($link);
        }
    } else {
        echo "Error deleting old codes: " . mysqli_error($link);
    }
}
}

if (isset($_POST['update_resume'])) {
    $university = $_POST['university'];

    if (empty($university)) {
        $errors[] = 'Please select your university.';

    }


    $FirstN = $_POST['prenom'];
    $LastN = $_POST['nom'];
    $Birthday = $_POST['dateNaissance'];
    $Experience = $_POST['Experience'];
    $Phone = $_POST['Phone'];
    $university = $_POST['university'];
    $marital_status = $_POST['marital_status'];
    $Adress = $_POST['Adress'];

    $query = "UPDATE demandeur_cv SET prenom='$FirstN', nom='$LastN',date_naissance = '$Birthday',etat_civil = $marital_status,adresse = '$Adress', numero_telephone=$Phone, code_universite=$university, nombre_annee_experience=$Experience WHERE CIN=" . $cin;

    $result = mysqli_query($link, $query);
    echo mysqli_error($link);
    if ($result) {
        $_SESSION['success'] = 'Resume updated successfully.';
    } else {
        echo $FirstN;
        $_SESSION['error'] = 'Something went wrong. Please try again.';
    }

    if ($_FILES['pdfcv']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['pdfcv']['tmp_name'])) {
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
            $query = "SELECT cv FROM `demandeur_cv` WHERE CIN = '$cin'";
            $result = mysqli_query($link, $query);
            $row = mysqli_fetch_assoc($result);
            $oldFileName = $row['cv'];
            $oldFileName = "./cv/".$oldFileName;
            unlink($oldFileName);
            $query = "UPDATE `demandeur_cv` SET cv = '$newFileName' WHERE CIN = '$cin'";


            if (mysqli_query($link, $query)) {
                echo 'Registration successful!';
                header('location:dashboard.php');
            } else {
                echo 'Error: ' . mysqli_error($link);
            }
        } else {
            echo 'Error: Failed to move uploaded file.';
        }
    } else {
       
    }


}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Job Seeker Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding: 1rem;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
        }

        .sidebar a:hover {
            color: #fff;
        }

        .container-fluid {
            padding: 1rem;
        }

        .profile-container {
            position: relative;
        }

        .profile-container img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin-left: 10px;
        }

        .profile-edit {
            position: absolute;
            top: 0;
            right: 0;
            margin-top: 20px;
            margin-right: 20px;
        }
    </style>
</head>

<body>
    
        <!--********************************************************************************-->
        <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #0083ff4d;">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
      <h2 class="navbar-brand">Employer Portal</h2>
      <ul class="navbar-nav mr-auto">
        <li class="nav-item">
          <a class="nav-link" href="#" id="dashboard-link">Dashboard</a>
        </li>
        <li class="nav-item">
        <a class="nav-link" href="#" id="resume-link">Modifier CV</a>
        </li>
        <li class="nav-item">
        <a class="nav-link" href="#" id="list-job-link">List job offre</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" id="list-app-link">List job applications</a>
        </li>
       <li class="nav-item">
                <a href="../logout.php" class="nav-link logout-btn">logout</a>
                </li>
      </ul>
    </div>
  </div>
</nav>






        <div class="container-fluid">
            <div class="profile-container">
                <img src="../img/blank-avatar.jpg" alt="Profile Picture">
            
            </div>


            <div class="alert alert-info mt-3">
                Welcome,
                <?php echo $_SESSION['username']; ?>!
            </div>

            <div id="shown-content"></div>


            <?php if (isset($message)): ?>
                <div class="alert alert-info mt-3">
                    <?php echo htmlentities($message); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

<script>
    $(document).ready(function () {
        // Dashboard link
        $("#dashboard-link").click(function (e) {
            e.preventDefault();
            $("body").load("dashboard.php");
        });

        // Resume link
        $("#resume-link").click(function (e) {
            e.preventDefault();
            var resume_id = '<?php echo $_SESSION["username"]; ?>';
            $("#shown-content").load("resume.php?resume_id=" + resume_id);
        });

        // List job offre link
        $("#list-job-link").click(function (e) {
            e.preventDefault();
            var resume_id = '<?php echo $_SESSION["username"]; ?>';
            $("#shown-content").load("list-job.php?resume_id=" + resume_id, function () {
                // Execute JS function after loading list-job.php
                sortTable(7);
            });
        });


        // List job application link
        $("#list-app-link").click(function (e) {
            e.preventDefault();
            var resume_id = '<?php echo $_SESSION["username"]; ?>';
            $("#shown-content").load("list-app.php?resume_id=" + resume_id);
        });
    });

    function sortTable(columnIndex) {
        var table, rows, switching, i, x, y, shouldSwitch;
        table = document.getElementById("myTable");
        switching = true;
        while (switching) {
            switching = false;
            rows = table.rows;
            for (i = 1; i < (rows.length - 1); i++) {
                shouldSwitch = false;
                x = rows[i].getElementsByTagName("td")[columnIndex].innerHTML;
                y = rows[i + 1].getElementsByTagName("td")[columnIndex].innerHTML;
                if (parseInt(x) < parseInt(y)) {
                    shouldSwitch = true;
                    break;
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
            }
        }
        // remove rows with a value of 0
        rows = table.rows;
        let j = 1;
        do {
            const x = rows[j].getElementsByTagName("td")[columnIndex].innerHTML;
            if (parseInt(x) === 0) {
                rows[j].parentNode.removeChild(rows[j]);
                j--;
            }
            j++;
        } while (j < rows.length);


    }
</script>

</html>