<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'gestion_ticket');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$message = ""; // Variable pour stocker les messages d'erreur ou de succès

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users_auth WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "Cet email est déjà utilisé.";
    } else {
        // Hacher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insérer l'utilisateur dans la base de données
        $stmt = $conn->prepare("INSERT INTO users_auth (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            $message = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            $message = "Erreur lors de la création du compte.";
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="POST" action="">
        <h1>Créer un compte</h1>
        <label for="name">Nom :</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Créer un compte</button>
    </form>

    <!-- Affichage du message -->
    <?php if (!empty($message)): ?>
        <p style="color: red; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</body>
</html>