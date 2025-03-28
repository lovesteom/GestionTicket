<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_ticket";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$message = ""; // Variable pour stocker le message
$pre_email = ""; // Variable pour préremplir l'email
$pre_nbr_ticket = ""; // Variable pour préremplir le nombre de tickets
$max_tickets = 6; // Valeur par défaut pour limiter les options

// Vérification des paramètres GET pour préremplissage
if (isset($_GET['email'])) {
    $pre_email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);

    // Récupérer le nombre de tickets depuis la base de données pour cet email
    $sql = "SELECT nbr_ticket FROM users WHERE email = '$pre_email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $max_tickets = (int)$row['nbr_ticket']; // Limiter les options à nbr_ticket
    }
}

if (isset($_GET['nbr_ticket'])) {
    $pre_nbr_ticket = filter_var($_GET['nbr_ticket'], FILTER_SANITIZE_NUMBER_INT);
}

// Vérifier si les données sont envoyées via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['nbr_ticket'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $nbr_ticket = (int) $_POST['nbr_ticket'];

    // Mettre à jour la valeur de nbr_ticket pour l'utilisateur correspondant
    $sql = "UPDATE users SET nbr_ticket = $nbr_ticket WHERE email = '$email'";

    if ($conn->query($sql) === TRUE) {
        $message = "Le nombre de tickets a été mis à jour avec succès.";
    } else {
        $message = "Erreur lors de la mise à jour : " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mise à jour des tickets</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <form method="POST" action="">
        <h1 style="text-align: center;">Mise à jour des tickets</h1>
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" 
               value="<?php echo htmlspecialchars($pre_email); ?>" 
               required>
        <br>
        <label for="nbr_ticket">Nombre de tickets :</label>
        <select style="padding: 8px; width: 100%; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;" id="nbr_ticket" name="nbr_ticket" required>
            <option value="">Sélectionnez le nombre de tickets</option>
            <?php
            // Générer dynamiquement les options en fonction de $max_tickets
            for ($i = 0; $i < $max_tickets; $i++) {
                $selected = ($pre_nbr_ticket == $i) ? 'selected' : '';
                echo "<option value=\"$i\" $selected>$i ticket(s)</option>";
            }
            ?>
        </select>
        <br>
        <button type="submit">Mettre à jour</button>
        <p style="text-align: center;" id="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    </form>
</body>
</html>