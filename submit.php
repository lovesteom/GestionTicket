<?php
// ...existing code...

function saveToDatabase($name, $email) {
    $conn = new mysqli('localhost', 'root', '', 'gestion_ticket');
    if ($conn->connect_error) {
        die("Erreur de connexion : " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}

function sendEmail($name, $email) {
    $to = $email;
    $subject = "Confirmation de soumission";
    $message = "Bonjour $name,\n\nMerci pour votre soumission.";
    $headers = "From: no-reply@gestionticket.com";

    return mail($to, $subject, $message, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (saveToDatabase($name, $email)) {
        if (sendEmail($name, $email)) {
            echo "Données enregistrées et email envoyé.";
        } else {
            echo "Données enregistrées mais échec de l'envoi de l'email.";
        }
    } else {
        echo "Erreur lors de l'enregistrement des données.";
    }
}
?>
