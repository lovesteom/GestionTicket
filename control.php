<?php
/* session_start();
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers login.php si l'utilisateur n'est pas connecté
    header("Location: login.php");
    exit();
} */
include './env/data.php'; // Inclure le fichier de connexion à la base de données
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

// Vérifier si le code récupéré dans le localstorage est dans la base de données

//Récupérer le code dans le localStorage


    if (isset($_GET['code'])) {
        $code = filter_var($_GET['code'], FILTER_SANITIZE_STRING);

        // Récupérer l'email et le nombre de tickets depuis la base de données pour ce code
        $sql = "SELECT email, nbr_ticket FROM users WHERE code = '$code'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc(); // Récupérer la ligne une seule fois
            $max_tickets = (int)$row['nbr_ticket']; // Limiter les options à nbr_ticket
            $pre_email = htmlspecialchars($row['email']); // Préremplir l'email
            $pre_nbr_ticket = (int)$row['nbr_ticket']; // Préremplir le nombre de tickets
            if($pre_nbr_ticket===0){
                //afficher le message d'erreur en rouge
                echo "<script>alert('Vous avez atteint le nombre de tickets maximum');</script>";
            }
        } else {
            // Gérer le cas où le code n'existe pas dans la base de données
            $message = "Code invalide ou non trouvé.";
        }
    } /* else {
        // Rediriger vers la page de connexion si le code n'est pas présent dans l'URL
        header("Location: login.php");
        exit();
    } */
    // Vérifier si le nombre de tickets est passé en paramètre GET

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
        <script>
             // Vérifier si "is_loginf" existe dans le localStorage
            if (localStorage.getItem('is_loginf') === null) {
                // Si "is_loginf" n'existe pas, l'initialiser à "false"
                localStorage.setItem('is_loginf', 'false');
                console.log('"is_loginf" initialisé à false');
            } else {
                console.log('"is_loginf" existe déjà dans le localStorage');
            }
            
            // Vérifier si "is_loginf" dans le localStorage est défini sur "true"
            if (localStorage.getItem('is_loginf') !== 'true' && localStorage.getItem('is_loginf') !== null) {
                // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
                window.location.href = 'login.php';
               console.log("Vous n'êtes pas connecté !");
            }
            //Récupérer le code dans l'url et le stocker temporairement dans le localStorage
            const urlParams = new URLSearchParams(window.location.search);

            if(urlParams.get('code') !== null){ 
                const code = urlParams.get('code');
                localStorage.setItem('code', code);
            }else{
                //Récupérer le code dans le localStorage
                const code = localStorage.getItem('code');
                //passer le code dans l'url
                window.location.href = 'control.php?code=' + code;
            }

            
        </script>
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