<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $csvFilePath = 'names.csv';

    if (strpos($name, ',') !== false) {
        $error = 'Voer alstublieft slechts één naam in.';
    } else {
        $names = file($csvFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lowercaseNames = array_map('strtolower', $names);
        if (in_array(strtolower($name), $lowercaseNames)) {
            $error = 'This name already exists.';
        } else {
            file_put_contents($csvFilePath, $name . PHP_EOL, FILE_APPEND);
            $success = 'Name added successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Name</title>
    <link rel="stylesheet" href="game.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Submit Your Name</h1>
        <form method="POST" action="">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <button type="submit">Submit</button>
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