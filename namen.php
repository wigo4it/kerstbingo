<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $csvFilePath = 'namen.csv';

    // Check if the cookie is set
    if (isset($_COOKIE['name_added'])) {
        $error = 'Je kunt slechts één naam toevoegen.';
    } else {
        if (strpos($name, ',') !== false) {
            $error = 'Voer alstublieft slechts één naam in.';
        } else {
            // Read the existing names from the CSV file
            $names = file_exists($csvFilePath) ? file_get_contents($csvFilePath) : '';
            $namesArray = $names ? explode(',', $names) : [];

            // Convert names to lowercase for case-insensitive comparison
            $lowercaseNames = array_map('strtolower', $namesArray);

            if (in_array(strtolower($name), $lowercaseNames)) {
                $error = 'Deze naam bestaat al!.';
            } else {
                // Append the new name to the list
                $namesArray[] = $name;

                // Write the updated list back to the CSV file in a comma-separated format
                file_put_contents($csvFilePath, implode(',', $namesArray));
                $success = 'Naam succesvol toegevoegd!';

                // Set a cookie to disallow adding more names for 1 day
                setcookie('name_added', '1', time() + 180); // 3 minutes
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voeg je naam toe</title>
    <link rel="stylesheet" href="game.css">
    <style>
        body {
            font-family: 'Anderson Four Feather Falls', sans-serif;
            margin: 20px;
            background-image: url('namen.png');
        }

        .container {
            max-width: 400px;
            margin: auto;
            color: #005452;
            text-align: center;
            font-size: 10px;
        }

        .error {
            color: red;
            text-align: center;
            font-size: 10px;
        }

        .success {
            color: green;
            text-align: center;
            font-size: 10px;
        }

        button {
            font-family: 'Anderson Four Feather Falls', sans-serif;
            color: #005452;
            font-size: 15px;
            border-radius: 10px;
            background-color: azure;
        }

        input[type="text"] {
            font-family: 'Anderson Four Feather Falls', sans-serif;
            height: 30px;
            width: 100%;
            max-width: 150px;
            border-radius: 20px;
            border-color: #005452;
            border-width: 5px;
            font-size: 20px;
            box-sizing: border-box;
            text-align: center;
            color: #005452;
        }

        @media (max-width: 600px) {
            input[type="text"] {
                height: 50px;
                /* Adjust height for smaller screens */
                font-size: 20px;
                /* Adjust font size for smaller screens */
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.4.0/dist/confetti.browser.min.js"></script>
    <script>
        function runConfetti() {
            confetti();
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Voer je naam in</h1>
        <form method="POST" action="">
            <input type="text" id="name" name="name" required>
            <button type="Versturen">Submit</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
            <script>
                runConfetti();
            </script>
        <?php endif; ?>
    </div>
</body>

</html>