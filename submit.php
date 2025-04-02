<?php
require "vendor/autoload.php"; // Inclure l'autoloader de Composer pour PHPMailer, dompdf et Endroid QR Code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Endroid\QrCode\Builder\Builder;




function saveToDatabase($name, $email) {
    include './env/data.php'; // Inclure le fichier de connexion à la base de données

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // L'email existe déjà
        $stmt->close();
        $conn->close();
        return false; // Retourne false pour indiquer un échec
    }

    $stmt->close();
    $code = generer_code_aleatoire(6);    // Convertir les bytes en une chaîne hexadécimale

    // Définir une valeur par défaut pour nbr_ticket
    $defaultNbrTicket = 6;

    // Requête d'insertion avec nbr_ticket
    $stmt = $conn->prepare("INSERT INTO users (name, email, code, nbr_ticket) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $code, $defaultNbrTicket);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}

function generatePDF($name, $email) {
    $dompdf = new Dompdf();
    $qrCodePath = generateQRCode( $email, $name);

    $path_img ="http://". $_SERVER['SERVER_NAME']."/GestionTicket/file.php?file=".$name.".png";
    // Contenu HTML pour le PDF
    $html = "
            <div>
                
                <div class='bee-col bee-col-1 bee-col-w12'>
                    <div ><img style='max-width:156px; texte-align:center !important;' alt='logo' src='https://festival-nouvellejerusalem-save.bj/wp-content/uploads/2025/01/festival-logo.png' style='max-width:156px; texte-align:center !important;' />
                    </div>
                </div>
                <div style='texte-align:center !important;'>
                    <h1>Veuillez utiliser ces tickets pour manger gratuitement<br />Mr/Mme .$name.  </h1>
                    <p>Veuillez trouver ci-joint un QR code pour votre soumission :</p>

                    <p><img style='max-width:256px; texte-align:center !important;' src='$path_img' alt='QR Code'></p>
                    <p></p>
                    <p><img style='max-width:256px; texte-align:center !important;' src='https://festival-nouvellejerusalem-save.bj/wp-content/uploads/2025/03/ticket-festival.jpg' style='max-width:750px; width:100%;' /></p>
                    <p></p>
                    <p><img style='max-width:256px; texte-align:center !important;' src='https://festival-nouvellejerusalem-save.bj/wp-content/uploads/2025/03/ticket-festival.jpg'  style='max-width:750px; width:100%;' /></p>
                    <p></p>
                    <p><img style='max-width:256px; texte-align:center !important;' src='https://festival-nouvellejerusalem-save.bj/wp-content/uploads/2025/03/ticket-festival.jpg'  style='max-width:750px; width:100%;' /></p>
                    <p></p>
                    <p><img style='max-width:256px; texte-align:center !important;' src='https://festival-nouvellejerusalem-save.bj/wp-content/uploads/2025/03/ticket-festival.jpg'  style='max-width:750px; width:100%;' /></p>
                    <p></p>
                    <p><img style='max-width:256px; texte-align:center !important;' src='https://festival-nouvellejerusalem-save.bj/wp-content/uploads/2025/03/ticket-festival.jpg'  style='max-width:750px; width:100%;' /></p>
                    <p></p>
                    <p><img style='max-width:256px; texte-align:center !important;' src='https://festival-nouvellejerusalem-save.bj/wp-content/uploads/2025/03/ticket-festival.jpg'  style='max-width:750px; width:100%;'  /></p>
                </div>
                
            </div>
        </body>
            ";

    // Charger le HTML dans dompdf
    $dompdf->loadHtml($html);

    // (Optionnel) Configurer la taille et l'orientation de la page
    $dompdf->setPaper('A3', 'portrait');

    $dompdf->set_option('isRemoteEnabled', true);

    // Générer le PDF
    $dompdf->render();

    // Sauvegarder le PDF dans un fichier temporaire
    $output = $dompdf->output();
    $filePath = sys_get_temp_dir() . "/confirmation_$name.pdf";
    file_put_contents($filePath, $output);
   

    return $filePath; // Retourne le chemin du fichier PDF
}

function generer_code_aleatoire($longueur = 8, $inclure_minuscules = true, $inclure_majuscules = true, $inclure_chiffres = true) {
    // Création du pool de caractères
    $caracteres = "";
    
    if ($inclure_minuscules) {
        $caracteres .= "abcdefghijklmnopqrstuvwxyz";
    }
    if ($inclure_majuscules) {
        $caracteres .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }
    if ($inclure_chiffres) {
        $caracteres .= "0123456789";
    }
    
    // Vérifier qu'au moins un type de caractère est inclus
    if (empty($caracteres)) {
        throw new Exception("Vous devez inclure au moins un type de caractères");
    }
    
    // Générer le code aléatoire
    $code = "";
    $max = strlen($caracteres) - 1;
    
    for ($i = 0; $i < $longueur; $i++) {
        $code .= $caracteres[rand(0, $max)];
    }
    
    return $code;
}



function generateQRCode($email, $name ) {
    $writer = new PngWriter();
    //Récupérer le code de la base de données
    include 'data.php'; // Inclure le fichier de connexion à la base de données
    $sql = "SELECT code FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $code = $row['code']; // Récupérer le code de la base de données
    } else {
        // Gérer le cas où l'email n'existe pas dans la base de données
        return false;
    }
    $conn->close(); // Fermer la connexion à la base de données

    $url =  __DIR__."/control.php?code=".$code;
    // Create QR code
    $qrCode = new QrCode(
        data: $url, 
        size: 300,
        margin: 10,
        
    );


    $result = $writer->write($qrCode);

    // Validate the result
    //$writer->validateResult($result, 'Life is too short to be generating QR codes');

    // Save it to a file
    $file_path = __DIR__."/".$name.".png";
    $result->saveToFile($file_path);
    return $file_path;
}



function sendEmail($name, $email) {
    $mail = new PHPMailer(true);
    // Générer le QR code et l'ajouter en pièce jointe
    $qrCodePath = generateQRCode( $email, $name);
    $path_imgs ="http://". $_SERVER['SERVER_NAME']."/GestionTicket/file.php?file=".$name.".png";
    try {
        // Configuration du serveur SMTP pour MailDev
        include './env/config_mail.php'; // Inclure le fichier de configuration de l'email
        $mail->addAddress($email, $name);
        $mail->Subject = "Confirmation de soumission";
        
        $mail->Body = "Bonjour $name,\n\nMerci pour votre soumission. Veuillez trouver en pièce jointe un PDF de confirmation et un QR code.\n\n \n\nCordialement,\nL'équipe Gestion Ticket
        "; 

        // Générer le PDF et l'ajouter en pièce jointe
        $pdfPath = generatePDF($name, $email);
        $mail->addAttachment($pdfPath, "confirmation_$name.pdf");

        
        //$mail->addAttachment($qrCodePath, "qrcode_$name.png");

        // Envoyer l'email
        $mail->send();

        // Supprimer les fichiers temporaires après envoi
        unlink($pdfPath);
        //unlink($qrCodePath);

        return true;
    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo);
        return false;
    }
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
        echo "Erreur : Cet email est déjà enregistré.";
    }
}
?>
