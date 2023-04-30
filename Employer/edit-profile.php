
<?php
session_start();
include '../sql/config.php';
$user_id = $_SESSION['id'];

$sql = "SELECT * FROM employeur WHERE code_registre_commerce = " . mysqli_real_escape_string($link, $user_id);
$result = mysqli_query($link, $sql);
$user = mysqli_fetch_assoc($result);

$email_err = $ennom_err = $password_err = $fname_err = $lname_err = "";


$ennom = $user['nom_entreprise'];
$fname = $user['prenom_gerant'];
$lname = $user['nom_gerant'];
$email = $user['email'];




?>

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
    <label for="password" class="form-label">New Password</label>
    <input type="password" class="form-control" name="password" id="password" value="">
    <small class="text-danger"><?= $password_err; ?></small>
  </div>
  <div class="d-grid gap-2 col-6 mx-auto">
    <button type="submit" class="btn btn-primary" name = "update-profile">Save Changes</button>
  </div>
</form>