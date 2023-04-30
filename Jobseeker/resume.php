<?php

include '../sql/config.php';

$user_id = $_GET["resume_id"];

$user_id = mysqli_real_escape_string($link, $user_id);
$sql = "SELECT * FROM demandeur_cv WHERE pseudo = '$user_id'";
$result = mysqli_query($link, $sql);
$fetch = mysqli_fetch_assoc($result);
$university_code = $fetch["code_universite"];
$Email = $fetch["email"];
$marital_status = $fetch["etat_civil"];
$Experience = $fetch["nombre_annee_experience"];
$Adress = $fetch["adresse"];
$cin = $fetch["CIN"];
$phone = $fetch["numero_telephone"];


$university_code = mysqli_real_escape_string($link, $fetch["code_universite"]);
$stmt = $link->prepare("SELECT libelle_universite FROM universite WHERE code_universite = ?");
if (!$stmt) {
    echo "Error: " . $link->error;
} else {
    $university_code = mysqli_real_escape_string($link, $fetch["code_universite"]);
    $stmt->bind_param('s', $university_code);
    $stmt->execute();
    // ...

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// Store the name of the university in a variable
$university = $row['libelle_universite'];





$cin = mysqli_real_escape_string($link, $cin);

$diploma_field = 'code_diplome';

$sql = "SELECT GROUP_CONCAT(DISTINCT $diploma_field SEPARATOR ',') AS diplome_values FROM diplome_demandeur WHERE CIN = '$cin'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$diploma_values = $row['diplome_values'];


$diploma_ar = explode(',', $diploma_values);
// Fetch the labels of the diplomas from the database
$stmt = $link->prepare("SELECT libelle_diplome FROM diplome WHERE code_diplome = ?");
$diploma_labels = array();

foreach ($diploma_ar as $diploma_code) {
    $stmt->bind_param('s', $diploma_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (empty($row)) {
        // $row is empty
    } else {
        $diploma_labels[] = $row['libelle_diplome'];
    }
    
}
$diploma_arr = implode(',', $diploma_labels);
$diploma_array = explode(',', $diploma_arr);


$competence_field = 'code_competence';
$sql = "SELECT GROUP_CONCAT(DISTINCT $competence_field SEPARATOR ',') AS competence_values FROM competence_demandeur WHERE CIN = '$cin'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$competence_values = $row['competence_values'];

$competence_ar = explode(',', $competence_values);
$stmt = $link->prepare("SELECT libelle_competence FROM competence WHERE code_competence = ?");
if (!$stmt) {
    echo "Error: " . $link->error;
} else {
    $competence_labels = array();
    foreach ($competence_ar as $competence_code) {
        $stmt->bind_param('s', $competence_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (empty($row)) {
            // $row is empty
        } else {
        $competence_labels[] = $row['libelle_competence'];
        }
    }
    // Concatenate the competence labels with a comma separator
    $competence_arr = implode(',', $competence_labels);
    $competence_array = explode(',', $competence_arr);
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>update resume</title>


</head>

<body>
<div class="container mt-5">
    <div class="row" >
        <div class="col-md-6 mx-auto" style="background-color: deepskyblue;">
            <div class="card">
                <div class="card-body">

                    <?php
                    $select = mysqli_query($link, "SELECT * FROM `demandeur_cv` WHERE pseudo = '$user_id'") or die('query failed');
                    if (mysqli_num_rows($select) > 0) {
                        $fetch = mysqli_fetch_assoc($select);
                    }
                    ?>

                    <form action="dashboard.php" method="post" enctype="multipart/form-data">

                        <?php if (isset($messagee)): ?>
                                    <div class="alert alert-danger">
                                        <?php foreach ($messagee as $message): ?>
                                                    <?php echo $message; ?><br>
                                        <?php endforeach; ?>
                                    </div>
                        <?php endif; ?>

                        <fieldset>

                            <legend>Informations Générales</legend>

                            <div class="form-group">
                                <label for="prenom">Prénom :</label>
                                <input type="text" name="prenom" id="prenom" class="form-control" placeholder="prénom" value="<?php echo $fetch['prenom']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="nom">Nom:</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder="nom" value="<?php echo $fetch['nom']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone:</label>
                                <input type="text" name="Phone" id="phone" class="form-control" placeholder="phone number" value="<?php echo $fetch['numero_telephone']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="dateNaissance">Date de Naissance :</label>
                                <input type="date" name="dateNaissance" id="dateNaissance" class="form-control" value="<?php echo $fetch['date_naissance']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="adresse">Adresse :</label>
                                <textarea name="Adress" id="Adress" class="form-control" rows="1" placeholder="adresse"><?php echo $fetch['adresse']; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Etat Civil :</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="married" name="marital_status" value="1" <?php if ($marital_status == '1')
                                        echo 'checked'; ?>>
                                    <label class="form-check-label" for="married">Marié(e)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="single" name="marital_status" value="0" <?php if ($marital_status == '0')
                                        echo 'checked'; ?>>
                                    <label class="form-check-label" for="single">Célibataire</label>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset>

                        <fieldset>
    <legend class="fs-4">Cursus et compétences</legend>
    <div class="row">
        <div class="col-md-6">
            <label for="competences" class="form-label fw-bold">Compétences</label>
            <?php
            $languages = [
                'JavaScript' => 'Programming',
                'Python' => 'Programming',
                'HTML' => 'Programming',
                'CSS' => 'Programming',
                'C#' => 'Programming',
                'C++' => 'Programming',
                'PHP' => 'Programming'
            ];
            $categories = array_unique(array_values($languages));
            echo '<select name="competence[]" multiple size="' . 10 . '" class="form-select">'; foreach ($categories as $category) {
                echo "<optgroup label=\"$category\">";
                foreach ($languages as $language => $cat) {
                    if ($cat == $category) {
                        $selected = in_array($language, $competence_array) ? 'selected' : '';
                        echo "<option value=\"$language\" $selected>$language</option>";
                    }
                }
                echo "</optgroup>";
            }
            echo '</select>';
            ?>
        </div>
        <div class="col-md-6">
            <label for="university" class="form-label fw-bold">Université</label>
            <select id="university" name="university" class="form-select">
                <option value="">Choisir Université</option>
                <option value="1" <?php if ($university == 'ISG')
                    echo 'selected'; ?>>ISG</option>
                <option value="2" <?php if ($university == 'FST')
                    echo 'selected'; ?>>FST</option>
            </select>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="Diploma" class="form-label fw-bold">Cochez vos diplômes</label>
            <?php
            $diplomas = [
                'Baccalaureat',
                'License',
                'Mastere',
                'Ingenieure',
                'Doctorat'
            ];

            echo '<select name="Diploma[]" multiple class="form-select">';
            foreach ($diplomas as $diploma) {
                $selected = in_array($diploma, $diploma_array) ? 'selected' : '';
                echo "<option value=\"$diploma\" $selected>$diploma</option>";
            }
            echo '</select>';
            ?>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="Experience" class="form-label fw-bold">Années d'expérience</label>
                <input type="number" name="Experience" id="experience" placeholder="Année d'expérience" size="30"
                    maxlength="40" value="<?php echo $fetch['nombre_annee_experience']; ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label for="pdfcv" class="form-label fw-bold">CV</label>
                <input class="form-control" name="pdfcv" type="file" id="pdfcv" accept="application/pdf">
            </div>
        </div>
    </div>
    <button style="margin-left: 55%;
    background-color: #008000c4;" type="submit" class="btn btn-primary" name="update_resume">Update Resume</button>
    <a href="dashboard.php" class="btn btn-secondary">Go Back</a>
</fieldset>

        </form>

    </div>

</body>

</html>