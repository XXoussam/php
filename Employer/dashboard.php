<?php
session_start();
include '../sql/config.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}
$user_id = $_SESSION['id'];


if (isset($_POST['update-profile'])) {



    $stmt = mysqli_prepare($link, "UPDATE employeur SET email = ?, pass_word = ?, nom_entreprise = ?, prenom_gerant = ?, nom_gerant = ? WHERE code_registre_commerce = ?");
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "sssssi", $_POST['email'], $password_hash, $_POST['ennom'], $_POST['fname'], $_POST['lname'], $user_id);
    mysqli_stmt_execute($stmt);

    // Redirect to the profile page
    header("location: ../logout.php?reason=1");
    exit;
}

if (isset($_GET['delete_id'])) {

    $delete_id = mysqli_real_escape_string($link, $_GET['delete_id']);
    $delete = mysqli_query($link, "DELETE FROM offre_emploi WHERE code_offre_emploi = $delete_id");

    if ($delete) {
        header('location:dashboard.php');
    } else {
        echo '<div class="error">Error in deleting job offer!</div>';
        echo mysqli_error($link);
    }
}

if (isset($_GET['accept'])) {
    $applicant_id = mysqli_real_escape_string($link, $_GET['id']);
    $offer_id = mysqli_real_escape_string($link, $_GET['accept']);

    $update = mysqli_query($link, "UPDATE candidature SET etat_candidature = '1' WHERE code_offre_emploi = '$offer_id' AND CIN = '$applicant_id'");
    echo mysqli_error($link);
    if ($update) {
    } else {
        echo '<div class="error">Error in updating job offer status!</div>';
        echo mysqli_error($link);
    }
}

if (isset($_GET['reject'])) {
    $applicant_id = mysqli_real_escape_string($link, $_GET['id']);
    $offer_id = mysqli_real_escape_string($link, $_GET['reject']);


    $update = mysqli_query($link, "UPDATE candidature SET etat_candidature = '2' WHERE code_offre_emploi = '$offer_id' AND CIN = '$applicant_id'");

    if ($update) {
    } else {
        echo '<div class="error">Error in updating job offer status!</div>';
        echo mysqli_error($link);
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['newoffer'])) {

        $job_title = mysqli_real_escape_string($link, $_POST['job_title']);
        $description = mysqli_real_escape_string($link, $_POST['job_description']);
        $diploma_id = mysqli_real_escape_string($link, $_POST['diploma_id']);
        $years_of_experience = mysqli_real_escape_string($link, $_POST['years_of_experience_required']);
        $proposed_salary = mysqli_real_escape_string($link, $_POST['proposed_salary']);

        if (isset($_POST['skill_id']) && !empty($_POST['skill_id'])) {
            $skills = $_POST['skill_id'];
            $skills_str = implode(',', $skills);
        }

        $query = "INSERT INTO offre_emploi (Titre, description, code_diplome, nombre_annees_experience, salaire_propose)
              VALUES ('$job_title', '$description', '$diploma_id', '$years_of_experience', '$proposed_salary')";
        if (mysqli_query($link, $query)) {
            $offer_id = mysqli_insert_id($link);
            mysqli_query($link, "INSERT INTO employeur_offre (code_registre_commerce, code_offre_emploi) VALUES ($user_id, $offer_id)");
            foreach ($skills as $skill_id) {
                $insert_offer_skill = mysqli_query($link, "INSERT INTO offre_competence (code_offre_emploi	, code_competence) VALUES ($offer_id, $skill_id)");
            }
            header('location:dashboard.php');
            echo "New job offer created successfully.";
        } else {
            echo "Error: " . $query . "<br>" . mysqli_error($link);
        }
    }
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Job Portal Dashboard</title>
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
    <div> 
<!--*****************************************************************************************-->
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
          <a class="nav-link" href="#" id="list-job-link">List job offers</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" id="add-job-link">Add Job Offre</a>
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








        <div class="container-fluid"  style="max-width: 60%;">
            <div id="shown-content">


                
<?php

$sql = "SELECT * FROM employeur WHERE code_registre_commerce = " . mysqli_real_escape_string($link, $user_id);
$result = mysqli_query($link, $sql);
$user = mysqli_fetch_assoc($result);

$email_err = $ennom_err = $password_err = $fname_err = $lname_err = "";


$ennom = $user['nom_entreprise'];
$fname = $user['prenom_gerant'];
$lname = $user['nom_gerant'];
$email = $user['email'];




?>
<h3>Edit your Profile</h3>

<form method="post" action="dashboard.php">
  <div class="mb-2">
    <label for="email" class="form-label">Email</label>
    <input type="text" class="form-control" name="email" id="email" value="<?= $email; ?>">
    <small class="text-danger"><?= $email_err; ?></small>
  </div>
  
  <div class="mb-3">
    <label for="noment" class="form-label">Nom de l'entreprise</label>
    <input type="text" class="form-control" name="ennom" id="ennom" value="<?= $ennom; ?>">
    <small class="text-danger"><?= $ennom_err; ?></small>
  </div>
  <div class="mb-3">
    <label for="fname" class="form-label">Nom Gérant</label>
    <input type="text" class="form-control" name="fname" id="fname" value="<?= $fname; ?>">
    <small class="text-danger"><?= $fname_err; ?></small>
  </div>
  <div class="mb-3">
    <label for="lname" class="form-label">Prénom Gérant</label>
    <input type="text" class="form-control" name="lname" id="lname" value="<?= $lname; ?>">
    <small class="text-danger"><?= $lname_err; ?></small>
  </div>
  <div class="mb-2">
    <label required for="password" class="form-label">New Password</label>
    <input type="password" class="form-control" name="password" id="password" value="">
    <small class="text-danger"><?= $password_err; ?></small>
  </div>
  <div class="d-grid gap-2 col-6 mx-auto">
    <button style="width: 140px;
    margin-left: 90%;
    margin-top: 5%;" type="submit" class="btn btn-primary" name = "update-profile">Save Changes</button>
  </div>
</form>


            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-info mt-3">
                    <?php echo htmlentities($message); ?>
                </div>
            <?php endif; ?>
        </div>
   
</body>

<script>
    $(document).ready(function () {
        // Dashboard link
        $("#dashboard-link").click(function (e) {
            e.preventDefault();
            $("body").load("dashboard.php");
        });

        // Add Job link
        $("#add-job-link").click(function (e) {
            e.preventDefault();
            $("#shown-content").load("add-job.php");
        });

        // List job offre link
        $("#list-job-link").click(function (e) {
            e.preventDefault();
            $("#shown-content").load("list-job.php", function () {
                $(".view-app").click(function (e) {
                    console.log();
                    var applicationId = $(this).data().applicationId;
                    e.preventDefault();
                    $("#shown-content").load("relevant.php?id=" + $(this).data().applicationId, function () {
                        sortTable(2);
                    });
                });

            });
        });

        // List job application link
        $("#list-app-link").click(function (e) {
            e.preventDefault();
            $("#shown-content").load("list-app.php");
        });
        // Update profile link
        $("#update-profile-link").click(function (e) {
            e.preventDefault();
            $("#shown-content").load("edit-profile.php");
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