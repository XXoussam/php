<?php
include '../sql/config.php';
session_start();




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Job Offer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
          crossorigin="anonymous">
</head>
<body>
<div class="container" style="background-color: #0000ff21;">
    <h1>Add Job Offer</h1>
    <form method="POST" action="dashboard.php">
        <div class="form-group">
            <label for="job_title">Job Title</label>
            <input type="text" class="form-control" id="job_title" name="job_title" required>
        </div>
        <div class="form-group">
            <label for="job_description">Job Description</label>
            <textarea class="form-control" id="job_description" name="job_description" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label for="diploma_id">Diploma</label>
            <select class="form-control" id="diploma_id" name="diploma_id" required>
                <?php
                // Get diplomas from database
                $result = mysqli_query($link, "SELECT * FROM diplome");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<option value="' . $row['code_diplome'] . '">' . $row['libelle_diplome'] . '</option>';
                }

                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="skill_id">Skills</label>
            <select class="form-control" id="skill_id" name="skill_id[]" multiple required>
                <?php
                // Get skills from database
                $result = mysqli_query($link, "SELECT * FROM competence");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<option value="' . $row['code_competence'] . '">' . $row['libelle_competence'] . '</option>';
                }
                ?>
            </select>

        </div>
        <div class="form-group">
            <label for="years_of_experience_required">Years of Experience Required</label>
            <input type="number" class="form-control" id="years_of_experience_required" name="years_of_experience_required"
                   required>
        </div>
        <div class="form-group">
            <label for="proposed_salary">Proposed Salary</label>
            <input type="number" class="form-control" id="proposed_salary" name="proposed_salary" required>
        </div>
        <button style="margin-left: 90%;
    background-color: #008000bd;" type="submit" class="btn btn-primary" name ="newoffer">Submit</button>
  </form>
</div>