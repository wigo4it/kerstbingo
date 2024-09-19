<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $csvFilePath = 'namen.csv';

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
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        button {
            font-family: 'Anderson Four Feather Falls', sans-serif;
            color: #005452;
            font-size: 20px;
            border-radius: 20px;
            background-color: azure;
        }

        input[type="text"] {
            font-family: 'Anderson Four Feather Falls', sans-serif;
            height: 50px;
            width: 100%;
            /* Change width to 100% for better scaling */
            max-width: 200px;
            /* Set a maximum width */
            border-radius: 20px;
            color: #005452;
            border-width: 4px;
            font-size: 25px;
            box-sizing: border-box;
            text-align: center;
            /* Ensure padding and border are included in the element's total width and height */
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
</head>

<body>
    <div class="container">
        <h1>Voer je naam in</h1>
        <form method="POST" action="">
            <label for="name">Naam:</label>
            <input type="text" id="name" name="name" required>
            <button type="Versturen">Submit</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
    </div>
</body>

</html>