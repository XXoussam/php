<?php
include '..\sql\config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['id'];
$result10 = $link->query("SELECT * FROM employeur_offre WHERE code_registre_commerce = ".$user_id);
    if ($result10->num_rows == 0) {
        echo "No job offers found";
    } else{
        ?>
        <h1>List of Job Offers</h1>

        <table class="job-offers">

    <?php
        echo "<tr>";
        echo "<th>Titre</th>";
        echo "<th>Description</th>";
        echo "<th>Diploma</th>";
        echo "<th>Skills</th>";
        echo "<th>Experience</th>";
        echo "<th>Salary</th>";
        echo "<th>Delete</th>";
        echo "<th>View Potential Candidates</th>";
        echo " </tr>";
        while ($row10 = $result10->fetch_assoc()) {
        $result = $link->query("SELECT * FROM offre_emploi WHERE code_offre_emploi = ".$row10["code_offre_emploi"]);


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
</style>


    <?php
        while ($row = $result->fetch_assoc()) {
            $sql = "SELECT libelle_diplome FROM diplome WHERE code_diplome = '" . $row['code_diplome'] . "'";
            $result2 = mysqli_query($link, $sql);
            $row2 = mysqli_fetch_assoc($result2);
            $libelle_diplome = $row2['libelle_diplome'];

            $competence_field = 'code_competence';
            $sql = "SELECT GROUP_CONCAT(DISTINCT $competence_field SEPARATOR ',') AS competence_values FROM offre_competence WHERE code_offre_emploi = '" . $row['code_offre_emploi'] . "'";
            $result2 = mysqli_query($link, $sql);
            if (!$result2) {
                die('Error executing query: ' . mysqli_error($link));
            }
            $row3 = mysqli_fetch_assoc($result2);
            $competence_values = $row3['competence_values'];

            $competence_ar = explode(',', $competence_values);
            $stmt = $link->prepare("SELECT libelle_competence FROM competence WHERE code_competence = ?");
            if (!$stmt) {
                echo "Error: " . $link->error;
            } else {
                $competence_labels = array();
                foreach ($competence_ar as $competence_code) {
                    $stmt->bind_param('s', $competence_code);
                    $stmt->execute();
                    $result3 = $stmt->get_result();
                    $row4 = $result3->fetch_assoc();
                    $competence_labels[] = $row4['libelle_competence'];
                }
                // Concatenate the competence labels with a comma separator
                $competence_arr = implode(',', $competence_labels);
            }
            ?>
            <tr>
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
                    <?php echo $row['nombre_annees_experience']; ?>
                </td>
                <td>
                    <?php echo $row['salaire_propose']; ?> USD
                </td>
                
                <td><a style="background-color: #d72222;" href="dashboard.php?delete_id=<?php echo $row['code_offre_emploi']; ?>">Delete</a></td>
                </td>
                
                <td> <a style="background-color: #1a69c3;" href="#" id="view-app" class="view-app" data-application-id="<?php echo $row['code_offre_emploi']; ?>">View</a></td>
            </tr>
            <?php
        }
    }}
    ?>
</table>