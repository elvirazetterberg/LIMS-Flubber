<!DOCTYPE html>
<html>

<head>
    <title>Drug Page</title>
    <link href="../images/SIDES_head_icon.png" rel="icon">
</head>
<style>
           /* START STYLE PRESENTATION SLIDES */
           .slides_button {
            background-color: #9510AC;
            border: none;
            color: white;
            position: absolute;
            top: 40%;
            border-radius: 50%;
            padding: 25px;
            width: 100px;
            height: 100px;
        }
    /* Start slide overlay */
    #overlay {
    position: fixed;
    display: none;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 10, 0.5);
    /*this is 757CB3 */
    z-index: 2;
    cursor: pointer;
        }
        #outerContainer {
            background-color: #ffffff;
            border: 2px solid #256e8a;
            border-radius: 15px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
            padding: 20px;

            max-height: 95vh;
            /* Set maximum height for the container */
            overflow-y: auto;
            /* Enable vertical scrolling if content overflows */

            position: absolute;
            top: 50%;
            left: 50%;

            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
        }
    </style>

<body>
    <header>
        <?php
        include "../navigation.php";
        ?>
    </header>
    <div class="white">
        <h2>Drug Details</h2>
        <?php
        // Retrieve the drug information based on the drug_id query parameter
        $servername = "localhost";
        $username = "root";
        $password = "root";
        $dbname = "sides";
        $drug_id = $_GET['drug_id'];

        // Create connection
        $link = mysqli_connect($servername, $username, $password, $dbname);

        if (mysqli_connect_error()) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // ¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤   
        // Fetching general drug information based on drug_id
        // ¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤
        $sql = "SELECT drug_brand, drug_class, drug_active_ingredient, drug_inactive_ingredient
            FROM drugs
            WHERE drug_id = $drug_id";
        // Executing the SQL query that gathers drug details based on drug_id and storing the result in $result
        $result = $link->query($sql);

        // ¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤  
        // Fetching common side effects from Fass
        // ¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤ 
        $drug_side_effects_fass = "SELECT GROUP_CONCAT(side_effects.se_name SEPARATOR ', ') AS fass_side_effects
    FROM side_effects
    JOIN drug_association_fass ON side_effects.se_id = drug_association_fass.F_se_fk_id
    WHERE drug_association_fass.F_drug_fk_id = $drug_id";

        // Executing the second SQL query that gathers common Fass side effects and stores the result in $fass_side_effects_result
        $fass_side_effects_result = $link->query($drug_side_effects_fass);

        // ¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤
        // Fetching top user-reported side effects
        // ¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤
        $query1 = "CREATE TEMPORARY TABLE occurrences AS
    SELECT side_effects.se_name, side_effects.se_id, report.drugid
    FROM report
    INNER JOIN side_effects ON side_effects.se_id=report.side_effect";

        // Creating a temp table with drug IDs, side effect names, and count of identical occurrences of that unique combo (drug ID+side effect name)
        $query2 = "CREATE TEMPORARY TABLE running_tallies AS
    SELECT drugid, se_name, COUNT(*) AS occurrence_count
    FROM occurrences
    GROUP BY drugid, se_name
    ORDER BY drugid, occurrence_count DESC";

        // Temporary table of just the top 3 side effects for each drug, listed by drug ID
        $query3 = "CREATE TEMPORARY TABLE top_reported_sides AS
    SELECT drugid, se_name
    FROM running_tallies 
    LIMIT 3";

        // Fetching drug info and the top side effects
        $query4 = "SELECT drugs.drug_brand, drugs.drug_class, drugs.drug_active_ingredient, drugs.drug_inactive_ingredient, GROUP_CONCAT(top_reported_sides.se_name SEPARATOR ', ') AS user_side_effects
    FROM top_reported_sides
    INNER JOIN drugs ON drugs.drug_id=top_reported_sides.drugid
    WHERE drug_id = $drug_id -- from a click, made to align with 'drug_page' syntax";


        // ¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤  
        // ¤ Displaying the results ¤
        // ¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Display drug information
            echo "<p>Active Ingredients: " . $row["drug_active_ingredient"] . "</p>";
            echo "<p>Brand: " . $row["drug_brand"] . "</p>";
            echo "<p>Class: " . $row["drug_class"] . "</p>";
            echo "<p>Inactive Ingredients: " . $row["drug_inactive_ingredient"] . "</p>";

            // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
            // ~~ Displaying Fass side effects ~~
            // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
            $row_fass = $fass_side_effects_result->fetch_assoc();
            # if $row_fass is not an empty string & not NULL, then the side effects gets displayed
            if ($row_fass["fass_side_effects"] !== "" && $row_fass["fass_side_effects"] !== null) {
                echo "<p><strong>Fass side effects: </strong>" . $row_fass["fass_side_effects"] . "</p>";
            } else {
                echo "<p>No Fass side effects found</p>";
            }

            // ~~~~~~~~~~~~~~~~~~~~~~~~~~~        
            // ~~ Displaying User reported side effects ~~
            // ~~~~~~~~~~~~~~~~~~~~~~~~~~~
            if ($link->query($query1)) {
                $query2;
                if ($link->query($query2)) {
                    $query3;
                    if ($link->query($query3)) {
                        $query4;
                        $top_sides_result = $link->query($query4);
                        if ($top_sides_result) {
                            if ($top_sides_result->num_rows > 0) {
                                $row4 = $top_sides_result->fetch_assoc();
                                if ($row4["user_side_effects"] !== "" && $row4["user_side_effects"] !== null) {
                                    echo "<p><strong>Top user-reported side effects: </strong>" . $row4["user_side_effects"] . "</p>";
                                } else {
                                    echo "<p>No side effects yet reported by users.</p>";
                                }
                            }
                        } else {
                            echo "Error with 4th query: " . $link->error;
                        }
                    } else {
                        echo "Error with 3rd query: " . $link->error;
                    }
                } else {
                    echo "Error with 2nd query: " . $link->error;
                }
            } else {
                echo "Error with 1st query: " . $link->error;
            }
        } else {
            echo "<p>Drug not found</p>"; // Added formatting for error message
        }
        ?>

        <form action="../Analytics/compare_analytics.php" method="POST">
            <input type="submit" value="See more about this drug">
        </form>
        <!-- <form action="../Analytics/compare_analytics.php" method="POST">
    <input type="submit" value="Compare any two drugs">
    </form> -->

    
<div>
        <div id="overlay">
            <div id="outerContainer">
            <img src="../images/ceci_flows/topsides.png" alt="review database flowchart" width = 500 height = auto>
            </div>
        </div>
        <button type="button" class="slides_button" style="right:10%" onclick="overlay_on()">Say more!</button>
       

<script>

function overlay_on() {
    document.getElementById("overlay").style.display = "block";
}

function overlay_off() {
    document.getElementById("overlay").style.display = "none";
}
document.addEventListener("keydown", function (event) {// to allow for esc closing 
    if (event.key === "Escape") {
        overlay_off();
        overlay2_off(); y
    }
});

</script>

        <?php
        // Close the database connection
        mysqli_close($link);
        ?>
    </div>
    <?php
    include "../footer.php";
    include "../Logging_and_posts/process_form.php";
    ?>
</body>

</html>