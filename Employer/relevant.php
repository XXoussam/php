<?php
include '../sql/config.php';
session_start();

if (!isset($_SESSION['id'])) {
  header("Location: login.php");
  exit();
}

$offer_id = $_GET['id'];
$result = $link->query("SELECT * FROM demandeur_cv");

echo "<table class='job-offers' id='myTable'>";
echo "<tr>";
echo "<th>Name</th>";
echo "<th>Email</th>";
echo "<th>Score</th>";
echo "<th>CV</th>";
echo " </tr>";

while ($row1 = $result->fetch_assoc()) {

    $cin = $row1["CIN"];

    # VALUES
    $cin = mysqli_real_escape_string($link, $cin);
    
    $diploma_field = 'code_diplome';
    $sqls = "SELECT GROUP_CONCAT(DISTINCT $diploma_field SEPARATOR ',') AS diploma_values FROM diplome_demandeur WHERE CIN = '$cin'";
    $results = mysqli_query($link, $sqls);
    $rows = mysqli_fetch_assoc($results);
    $diploma_values = $rows['diploma_values'];
    
    
    
    
    $competence_field = 'code_competence';
    $sqls = "SELECT GROUP_CONCAT(DISTINCT $competence_field SEPARATOR ',') AS competence_values FROM competence_demandeur WHERE CIN = '$cin'";
    $results = mysqli_query($link, $sqls);
    $rows = mysqli_fetch_assoc($results);
    $competence_values = $rows['competence_values'];


    $sql2 = "SELECT * FROM offre_emploi WHERE code_offre_emploi= '" . $offer_id . "'";
    $result2 = $link->query($sql2);

    $row2 = $result2->fetch_assoc();

    $sql3 = "SELECT libelle_diplome FROM diplome WHERE code_diplome = '" . $row2['code_diplome'] . "'";
    $result3 = mysqli_query($link, $sql3);
    $row3 = mysqli_fetch_assoc($result3);
    $libelle_diplome = $row3['libelle_diplome'];
    $sql3 = "SELECT GROUP_CONCAT(DISTINCT $competence_field SEPARATOR ',') AS competence_values FROM offre_competence WHERE code_offre_emploi = '" . $row2['code_offre_emploi'] . "'";
    $result3 = mysqli_query($link, $sql3);
    if (!$result3) {
        die('Error executing query: ' . mysqli_error($link));
    }
    $row3 = mysqli_fetch_assoc($result3);
    $competence_offre_values = $row3['competence_values'];

    $competence_ar = explode(',', $competence_offre_values);
    $stmt = $link->prepare("SELECT libelle_competence FROM competence WHERE code_competence = ?");
    if (!$stmt) {
        echo "Error: " . $link->error;
    } else {
        $competence_labels = array();
        foreach ($competence_ar as $competence_code) {
            $stmt->bind_param('s', $competence_code);
            $stmt->execute();
            $result4 = $stmt->get_result();
            $row4 = $result4->fetch_assoc();
            $competence_labels[] = $row4['libelle_competence'];
        }
        // Concatenate the competence labels with a comma separator
        $competence_arr = implode(',', $competence_labels);
        $competence_array = explode(',', $competence_arr);
       
    }

        $skills_score = 0;
        $diploma_match = 0;
        $experience_score = 0;
        $score = 0;

        foreach ($competence_ar as $job_offer_skill) {
            if (stripos($competence_values, $job_offer_skill) !== false) {
                $skills_score += 5;
            }
        }

        if ($row1['nombre_annee_experience'] >= $row2['nombre_annees_experience']) {
            $experience_score = ($row1['nombre_annee_experience'] - $row2['nombre_annees_experience']) * 2;
        }



        $job_offer_diplomas = explode(',', $row2['code_diplome']);
        foreach ($job_offer_diplomas as $job_offer_diploma) {
            if (stripos($diploma_values, $job_offer_diploma) !== false) {
                $diploma_match = 1;
            }
        }
       

        $score = ($skills_score + $experience_score) * $diploma_match;
        $cv = "../Jobseeker/cv/" . $row1['cv'];
        echo "<tr>";
        echo "<td>" . $row1['prenom']. " ".$row1['nom'] . "</td>";
        echo "<td>" . $row1['email'] . "</td>";
        echo "<td>" . $score . "</td>";
        echo '<td><p class="job-application-cv mb-1"> <a href="#" class="job-application-view-cv" data-toggle="modal" data-target="#cvModal">View CV</a></p></td>';
        echo '
    <!-- Modal -->
    <div class="modal fade" id="cvModal" tabindex="-1" role="dialog" aria-labelledby="cvModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
             <div class="modal-header">
                <h5 class="modal-title" id="cvModalLabel">' . $row1['prenom']. " ".$row1['nom'] . '\'s CV</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                </button>
             </div>
             <div class="modal-body">
                <iframe src="' . $cv . '" width="100%" height="620px"></iframe>
             </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
             </div>
          </div>
       </div>
    </div>';
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  $('.view-cv').on('click', function(e) {
    e.preventDefault();
    var pdf_src = $(this).data('cv');
    if ($('#cv-iframe').length > 0 && $('#cv-iframe').attr('src') === pdf_src) {
      $('#cv-iframe-container').toggle();
    } else {
      $('#cv-iframe-container').html('<iframe id="cv-iframe" src="' + pdf_src + '" width="800" height="500"></iframe>');
      $('#cv-iframe-container').show();
    }
  });
  
  $('#hide-cv').on('click', function(e) {
    e.preventDefault();
    $('#cv-iframe-container').hide();
  });
});

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css">
