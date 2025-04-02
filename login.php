<?php
session_start(); // Démarrer la session

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'gestion_ticket');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$message = ""; // Variable pour stocker les messages d'erreur ou de succès

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Vérifier si l'utilisateur existe dans la base de données
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        // Vérifier le mot de passe
        if (password_verify($password, $hashed_password)) {
            // Mot de passe correct
            // Enregistrer les informations dans la session
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['email'] = $email;

            // Rediriger vers la page d'accueil ou une autre page
            header("Location: control.php");
            exit();
        } else {
            // Mot de passe incorrect
            $message = "Mot de passe incorrect.";
        }
    } else {
        $message = "Aucun utilisateur trouvé avec cet email.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="POST" action="">
        <h1>Connexion</h1>
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Se connecter</button>
    </form>

    <!-- Affichage du message -->
    <?php if (!empty($message)): ?>
        <p style="color: red; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</body>
</html>