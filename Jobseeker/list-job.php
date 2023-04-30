<?php
include '../sql/config.php';

echo "<h1>List of Job Offers Suited to your Resume</h1>";
$user_id = $_GET["resume_id"];
$result = $link->query("SELECT * FROM offre_emploi");
echo "<table class='job-offers' id='myTable'>";
echo "<tr>";
echo "<th>Societé</th>";
echo "<th>Titre</th>";
echo "<th>Description</th>";
echo "<th>Diplome</th>";
echo "<th>Competence</th>";
echo "<th>Anneés Experience</th>";
echo "<th>Salaire</th>";
echo "<th>Score</th>";
echo "<th>Postulé</th>";
echo " </tr>";
##### DATA

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
$sql = "SELECT GROUP_CONCAT(DISTINCT $diploma_field SEPARATOR ',') AS diploma_values FROM diplome_demandeur WHERE CIN = '$cin'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$diploma_values = $row['diploma_values'];




$competence_field = 'code_competence';
$sql = "SELECT GROUP_CONCAT(DISTINCT $competence_field SEPARATOR ',') AS competence_values FROM competence_demandeur WHERE CIN = '$cin'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$competence_values = $row['competence_values'];


#########
$sql1 = "SELECT * FROM offre_emploi";
$result1 = $link->query($sql1);

while ($row1 = $result1->fetch_assoc()) {
    $sql = "SELECT libelle_diplome FROM diplome WHERE code_diplome = '" . $row1['code_diplome'] . "'";
    $result2 = mysqli_query($link, $sql);
    $row2 = mysqli_fetch_assoc($result2);
    $libelle_diplome = $row2['libelle_diplome'];
    $sql = "SELECT GROUP_CONCAT(DISTINCT $competence_field SEPARATOR ',') AS competence_values FROM offre_competence WHERE code_offre_emploi = '" . $row1['code_offre_emploi'] . "'";
    $result = mysqli_query($link, $sql);
    if (!$result) {
        die('Error executing query: ' . mysqli_error($link));
    }
    $row = mysqli_fetch_assoc($result);
    $competence_offre_values = $row['competence_values'];

    $competence_ar = explode(',', $competence_offre_values);
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
            $competence_labels[] = $row['libelle_competence'];
        }
        // Concatenate the competence labels with a comma separator
        $competence_arr = implode(',', $competence_labels);
        $competence_array = explode(',', $competence_arr);
    }







    $sql2 = "SELECT * FROM `demandeur_cv` WHERE pseudo = '$user_id'";
    $result2 = $link->query($sql2);
    if ($result2->num_rows > 0) {
        $row2 = $result2->fetch_assoc();





        $skills_score = 0;
        $salaire_score = intval($row1['salaire_propose'] / 100);
        $diploma_match = 0;
        $score = 0;


        foreach ($competence_ar as $job_offer_skill) {
            if (stripos($competence_values, $job_offer_skill) !== false) {
                $skills_score += 5;
            }
        }



        $job_offer_diplomas = explode(',', $row1['code_diplome']);
        foreach ($job_offer_diplomas as $job_offer_diploma) {
            if (stripos($diploma_values, $job_offer_diploma) !== false) {
                $diploma_match = 1;
            }
        }


        $score = ($skills_score + $salaire_score) * $diploma_match;

        $sql3 = "SELECT code_registre_commerce FROM employeur_offre WHERE code_offre_emploi = '" . $row1['code_offre_emploi'] . "'";
        $result3 = mysqli_query($link, $sql3);

        $row3 = mysqli_fetch_assoc($result3);
        $registre = $row3['code_registre_commerce'];


        $sql4 = "SELECT nom_entreprise FROM employeur WHERE code_registre_commerce = $registre";
        $result4 = mysqli_query($link, $sql4);

        $row4 = mysqli_fetch_assoc($result4);
        $company = $row4['nom_entreprise'];

        echo "<tr>";
        echo "<td>" . $company . "</td>";
        echo "<td>" . $row1['Titre'] . "</td>";
        echo "<td>" . $row1['description'] . "</td>";
        echo "<td>" . $libelle_diplome . "</td>";
        echo "<td>" . $competence_arr . "</td>";
        echo "<td>" . $row1['nombre_annees_experience'] . "</td>";
        echo "<td>" . $row1['salaire_propose'] . "</td>";
        echo "<td>" . $score . "</td>";



        
         $query = "SELECT * FROM candidature WHERE CIN = $cin AND code_offre_emploi = " . $row1['code_offre_emploi'] ;
         $resultt = mysqli_query($link, $query);
         if (mysqli_num_rows($resultt) > 0) {
             echo '<td><p style="color:red">cant applay twice</p></td>';
         } else {
             echo '<td><a href="dashboard.php?apply_id=' . $row1['code_offre_emploi'] . '" >Apply</a></td>';
          }
          echo "</tr>";
        

    }
}
echo "</table>";

?>


<style>
    .job-offers {
        width: 100%;
        border-collapse: collapse;
    }

    .job-offers th {
        font-weight: bold;
        background-color: #f5f5f5;
        border: 1px solid #ccc;
        padding: 8px;
        background-color: antiquewhite;
    }

    .job-offers td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;

    }

    .job-offers tbody tr:nth-child(even) {
        background-color: #f5f5f5;
    }

    .job-offers a {
        display: inline-block;
        padding: 8px 16px;
        background-color: #333;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
    }

    .job-offers button {
        display: inline-block;
        padding: 8px 16px;
        background-color: red;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
    }
</style>