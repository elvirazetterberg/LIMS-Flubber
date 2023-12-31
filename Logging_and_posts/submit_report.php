<?php
    include "../DB_connect.php";
    session_start();

    if (isset($_SESSION['username']) && isset($_SESSION["id"])) {
        $userid = $_SESSION['id'];
    }




    if(isset($_POST['selectDate'])) {
        $review_date = mysqli_real_escape_string($link, $_POST['selectDate']); //sanitize? 
    } else {
    }

    
//    if (isset($_POST['side_effects']) && is_array($_POST['side_effects'])) {
        $sideEffects = $_POST['side_effects'];
        $sideEffectsIntensities = $_POST['side_effects_intensity'];
        $drug_id = $_POST['drugid'];

        // Prepare the statement outside the loop
        $stmt = $link->prepare("INSERT INTO report (userid, drugid, side_effect, intensity, review_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiids", $userid, $drug_id, $sideEffectId, $sideEffectInt, $review_date);

        // Iterate through side effects and their intensities simultaneously
        foreach ($sideEffects as $index => $sideEffectId) {
            $sideEffectInt = $sideEffectsIntensities[$index]; // Get corresponding intensity

            // Bind the current side effect ID and intensity from the loop
            $stmt->execute();
        }

       // echo "Report submitted successfully!"; // this should be printed over the screen for a second 
  //  } else {
      //  echo "No side effects selected!";
    //}



    $link->close();

?>
