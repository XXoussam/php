<?php
include '../sql/config.php';
session_start();

if (!isset($_SESSION['id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['id'];

$result = $link->query("SELECT * FROM employeur_offre WHERE code_registre_commerce = $user_id");

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $result2 = $link->query("SELECT * FROM offre_emploi WHERE code_offre_emploi = " . $row['code_offre_emploi']);
    $row2 = $result2->fetch_assoc();


    $job_offer_id = $row['code_offre_emploi'];
    $job_offer_title = $row2['Titre'];

    echo '<h3 class="job-title">' . $job_offer_title . '</h3>';

    $applications = $link->query("SELECT * FROM candidature WHERE code_offre_emploi = $job_offer_id");

    if ($applications->num_rows > 0) {
      ?>
      <!--******************************************************************************-->
      <table class="table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>CV</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($application = $applications->fetch_assoc()) {
      $applicant_id = $application['CIN'];
      $status = $application['etat_candidature'];

      $applicant = $link->query("SELECT * FROM demandeur_cv WHERE CIN = $applicant_id")->fetch_assoc();
      $applicant_name = $applicant['prenom'] . ' ' . $applicant['nom'];
      $applicant_email = $applicant['email'];
      $cv = "../Jobseeker/cv/" . $applicant['cv'];

      if ($status == '0') {
    ?>
    <tr>
      <td><?php echo $applicant_name; ?></td>
      <td><?php echo $applicant_email; ?></td>
      <td><a href="#" class="job-application-view-cv" data-toggle="modal" data-target="#cvModal">View CV</a></td>
      <td>
        <form method="GET" action="dashboard.php" class="job-application-actions">
          <input type="hidden" name="id" value="<?php echo $applicant_id; ?>">
          <button type="submit" name="accept" value="<?php echo $job_offer_id; ?>"
            class="btn btn-success job-application-action-accept">Accept</button>
          <button type="submit" name="reject" value="<?php echo $job_offer_id; ?>"
            class="btn btn-danger job-application-action-reject">Reject</button>
        </form>
      </td>
    </tr>

    <!-- Modal -->
    <div class="modal fade" id="cvModal" tabindex="-1" role="dialog" aria-labelledby="cvModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="cvModalLabel">
              <?php echo $applicant_name; ?>'s CV
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <iframe src="<?php echo $cv; ?>" width="100%" height="620px"></iframe>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <?php
      }
    } ?>
  </tbody>
</table>

      <!--******************************************************************************-->
      <?php
    } else {
      echo '<p class="no-applications">No applications found for this job offer.</p>';
    }

    echo '<hr>';


  }
} else {
  echo 'No job applications found';
}
?>

<style>
  /* CSS for the job offer title */
  h3.job-title {
    font-size: 36px;
    font-weight: bold;
    margin-top: 50px;
    margin-bottom: 30px;
    color: #4F4F4F;
    text-align: center;
  }

  /* CSS for the list of applications */
  ul.list-group {
    margin-top: 30px;
    margin-bottom: 50px;
  }

  li.list-group-item {
    background-color: #F9F9F9;
    border: none;
    margin-bottom: 20px;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }

  li.list-group-item:hover {
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
  }

  li.list-group-item .mb-1 {
    color: #7D7D7D;
    font-size: 16px;
  }

  li.list-group-item .view-cv {
    color: #0056b3;
  }

  li.list-group-item form {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
  }

  li.list-group-item button {
    width: 100px;
    font-size: 14px;
    border-radius: 8px;
    box-shadow: none;
    transition: all 0.3s ease-in-out;
  }

  li.list-group-item button:hover {
    transform: translateY(-2px);
  }

  li.list-group-item button.btn-success {
    background-color: #1DC690;
    border-color: #1DC690;
    color: #fff;
  }

  li.list-group-item button.btn-danger {
    background-color: #FF5353;
    border-color: #FF5353;
    color: #fff;
  }

  .modal-header {
    background-color: #1DC690;
    color: #fff;
    border-bottom: none;
  }

  .modal-header h5.modal-title {
    font-size: 22px;
    font-weight: bold;
  }

  .modal-body {
    padding: 0;
  }

  .modal-footer {
    border-top: none;
  }

  .modal-footer button.btn-secondary {
    background-color: #EBEBEB;
    color: #000;
    border-radius: 8px;
  }

  .modal-footer button.btn-secondary:hover {
    background-color: #D4D4D4;
  }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function () {
    $('.view-cv').on('click', function (e) {
      e.preventDefault();
      var pdf_src = $(this).data('cv');
      if ($('#cv-iframe').length > 0 && $('#cv-iframe').attr('src') === pdf_src) {
        $('#cv-iframe-container').toggle();
      } else {
        $('#cv-iframe-container').html('<iframe id="cv-iframe" src="' + pdf_src + '" width="800" height="500"></iframe>');
        $('#cv-iframe-container').show();
      }
    });

    $('#hide-cv').on('click', function (e) {
      e.preventDefault();
      $('#cv-iframe-container').hide();
    });
  });

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css">