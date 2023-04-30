<?php
include '../sql/config.php';

$user_id = $_GET["resume_id"];

$sql = "SELECT CIN FROM demandeur_cv WHERE pseudo ='" . $user_id . "'";

$result = mysqli_query($link, $sql);

if (!$result) {
    die('Error executing query: ' . mysqli_error($link));
}

$cin = mysqli_fetch_assoc($result);
$cin = $cin["CIN"];
$result = $link->query("SELECT * FROM candidature WHERE CIN = $cin");

?>
<table class="job-offers">
    <?php
    if ($result->num_rows > 0) {
        echo "<tr>";
        echo "<th>Societé</th>";
        echo "<th>Titre</th>";
        echo "<th>Description</th>";
        echo "<th>Diplome</th>";
        echo "<th>Competence</th>";
        echo "<th>Anneés Experience</th>";
        echo "<th>Salary</th>";
        echo "<th>Status</th>";
        echo " </tr>";
        while ($row = $result->fetch_assoc()) {
            $status = $row['etat_candidature'];
            $query = "SELECT * FROM offre_emploi WHERE code_offre_emploi = " . $row['code_offre_emploi'];
            $resultt = mysqli_query($link, $query);
            $row = mysqli_fetch_assoc($resultt);
            if ($row) {
                $sql2 = "SELECT libelle_diplome FROM diplome WHERE code_diplome = '" . $row['code_diplome'] . "'";
                $result2 = mysqli_query($link, $sql2);
                $row2 = mysqli_fetch_assoc($result2);
                $libelle_diplome = $row2['libelle_diplome'];

                $competence_field = 'code_competence';

                $sql5 = "SELECT GROUP_CONCAT(DISTINCT $competence_field SEPARATOR ',') AS competence_values FROM offre_competence WHERE code_offre_emploi = '" . $row['code_offre_emploi'] . "'";
                $result5 = mysqli_query($link, $sql5);
                if (!$result5) {
                    die('Error executing query: ' . mysqli_error($link));
                }
                $row5 = mysqli_fetch_assoc($result5);
                $competence_offre_values = $row5['competence_values'];

                $competence_ar = explode(',', $competence_offre_values);
                $stmt = $link->prepare("SELECT libelle_competence FROM competence WHERE code_competence = ?");
                if (!$stmt) {
                    echo "Error: " . $link->error;
                } else {
                    $competence_labels = array();
                    foreach ($competence_ar as $competence_code) {
                        $stmt->bind_param('s', $competence_code);
                        $stmt->execute();
                        $result5 = $stmt->get_result();
                        $row5 = $result5->fetch_assoc();
                        $competence_labels[] = $row5['libelle_competence'];
                    }
                    // Concatenate the competence labels with a comma separator
                    $competence_arr = implode(',', $competence_labels);
                }

                $job_offer_title = $row['Titre'];
                $applicant = $link->query("SELECT * FROM demandeur_cv WHERE CIN = $cin")->fetch_assoc();
                $applicant_name = $applicant['prenom'] . ' ' . $applicant['nom'];
                $applicant_email = $applicant['email'];
                $sql3 = "SELECT code_registre_commerce FROM employeur_offre WHERE code_offre_emploi = '" . $row['code_offre_emploi'] . "'";
                $result3 = mysqli_query($link, $sql3);

                $row3 = mysqli_fetch_assoc($result3);
                $registre = $row3['code_registre_commerce'];


                $sql4 = "SELECT nom_entreprise FROM employeur WHERE code_registre_commerce = $registre";
                $result4 = mysqli_query($link, $sql4);

                $row4 = mysqli_fetch_assoc($result4);
                $company = $row4['nom_entreprise'];
                ?>
                <style>
                    .job-offers {
                        width: 100%;
                        border-collapse: collapse;
                    }

                    .job-offers th {
                        font-weight: bold;
                        background-color: antiquewhite;
                        border: 1px solid #ccc;
                        padding: 8px;
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
                </style>


                <tr>
                    <td>
                        <?php echo $company; ?>
                    </td>
                    <td>
                        <?php echo $row['Titre']; ?>
                    </td>
                    <td>
                        <?php echo $row['description']; ?>
                    </td>
                    <td>
                        <?php echo $libelle_diplome; ?>
                    </td>
                    <td>
                        <?php echo $competence_arr; ?>
                    </td>
                    <td>
                        <?php echo $row['nombre_annees_experience']; ?> years
                    </td>
                    <td>
                        <?php echo $row['salaire_propose']; ?> USD
                    </td>
                    <td style="color: <?php
                    if ($status == '0') {
                        echo 'orange';
                    } elseif ($status == '1') {
                        echo 'green';
                    } elseif ($status == '2') {
                        echo 'red';
                    }
                    ?>">
                        <?php 
                        
                        if ($status == '0') {
                            echo 'Pending';
                        } elseif ($status == '1') {
                            echo 'Accepted';
                        } elseif ($status == '2') {
                            echo 'Rejected';
                        }
                        
                        ?>
                    </td>
                </tr>

                <?php

            } else {

            }


        }
    } else {
        echo 'No job Applications found , Try Applying in List Job Offers';
    }
    ?>
</table>