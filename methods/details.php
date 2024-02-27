<?php
    include('header.php');
    session_start();
?>

<!DOCTYPE html>
<html>

    <head>

        <title>Mutual Fund Details</title>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="../css/bootstrap@5.3.2.min.css" crossorigin="anonymous">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600&family=Playfair+Display&family=Poppins:wght@200;300;400;500;600;700;800&display=swap');
            body
            {
                font-family: 'Poppins', sans-serif;
            }
        </style>

    </head>

    <body style="background-color: #C0C0C0;">
        
        <div class="row">

            <div class="col-2">
                <?php include('side_menu.php'); ?>
            </div>

            <div class="col-10">
                <h2 class="pt-2 shadow">Mutual Fund Details</h2>

                <div class="row">
                    <div  class="col-4">
                        <form method="GET" action="">
                            <label class="form-label">Your Name:</label>
                            <input type="text" class="form-control w-75" name="name"><br>
                            <label for="search" class="form-label">Mutual Fund Name:</label>
                            <input type="text" class="form-control w-75" name="search" id="search" required><br>
                            <button type="submit" value="search" class="btn btn-primary">Search</button>
                        </form>
                    </div>
                    <div class="col-8">
                        <?php
                            // Function to search for mutual funds by name
                            function searchMutualFundsByName($mutualFunds, $searchTerm)
                            {
                                $matchingFunds = array();
                                foreach ($mutualFunds as $fund) {
                                    if (stripos($fund['schemeName'], $searchTerm) !== false) {
                                        $matchingFunds[] = $fund;
                                    }
                                }
                                return $matchingFunds;
                            }

                            // Function to paginate an array
                            function paginateArray($array, $perPage, $currentPage)
                            {
                                $offset = ($currentPage - 1) * $perPage;
                                return array_slice($array, $offset, $perPage);
                            }

                            // Check if the API data and timestamp are stored in the session
                            if (!isset($_SESSION['mutual_funds']) || !isset($_SESSION['api_last_call']) || time() - $_SESSION['api_last_call'] > 6 * 60 * 60)
                            {
                                // If not, make the API call and store the data and timestamp in the session
                                $api_url = 'https://api.mfapi.in/mf';
                                $json_data = file_get_contents($api_url);
                                $data_array = json_decode($json_data, true);

                                if ($data_array !== null)
                                {
                                    $_SESSION['mutual_funds'] = $data_array;
                                    $_SESSION['api_last_call'] = time(); // Store the timestamp of the API call
                                }
                                else
                                {
                                    echo 'Failed to fetch data from the API.';
                                    exit; // Exit if API call fails
                                }
                            }
                            else
                            {
                                // If the data is already in the session and it's within the 24-hour limit, retrieve it
                                $data_array = $_SESSION['mutual_funds'];
                            }

                            // Handle user search
                            $matchingFunds = array();
                            if (isset($_GET['search']))
                            {
                                $searchTerm = $_GET['search'];
                                $matchingFunds = searchMutualFundsByName($data_array, $searchTerm);
                            }

                            // Pagination
                            $perPage = 20;
                            $totalPages = ceil(count($matchingFunds) / $perPage);
                            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $currentPage = max(1, min($totalPages, $currentPage));

                            // Display matching mutual funds if a search is performed
                            if (!empty($matchingFunds))
                            {
                                echo '<h3>Matching Mutual Funds:</h3>';

                                // Paginate the matching funds
                                $paginatedFunds = paginateArray($matchingFunds, $perPage, $currentPage);

                                echo '<ul>';
                                foreach ($paginatedFunds as $fund)
                                {
                                    $schemeName = $fund['schemeName'];
                                    $schemeCode = $fund['schemeCode'];
                                    echo '<li><a href="https://api.mfapi.in/mf/'. $schemeCode . '/latest" style="text-decoration: none;" class="text-secondary">' . $schemeName . ' - ' . $schemeCode . '</a></li>';
                                }
                                echo '</ul>';

                                // Display pagination links
                                echo '<div style="text-align: center;">';
                                for ($i = 1; $i <= $totalPages; $i++)
                                {
                                    echo '<a style="text-decoration: none;" class="text-black" href="?search=' . urlencode($searchTerm) . '&page=' . $i . '">' . $i . "&nbsp&nbsp" . '</a> ';
                                }
                                echo '</div>';
                            }
                            else
                            {
                                // Display the top 20 mutual funds if no search is performed
                                echo '<h3>Top 20 Mutual Funds:</h3>';
                                $first_20_mutual_funds = array_slice($data_array, 0, 20);

                                echo '<ul>';
                                foreach ($first_20_mutual_funds as $fund)
                                {
                                    $schemeName = $fund['schemeName'];
                                    $schemeCode = $fund['schemeCode'];
                                    echo '<li><a href="https://api.mfapi.in/mf/'. $schemeCode . '/latest" style="text-decoration: none;" class="text-secondary">' . $schemeName . ' - ' . $schemeCode . '</a></li>';
                                }
                                echo '</ul>';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>