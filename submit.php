<?php
require "vendor/autoload.php"; // Inclure l'autoloader de Composer pour PHPMailer, dompdf et Endroid QR Code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Endroid\QrCode\Builder\Builder;





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

function generatePDF($name, $email) {
    $dompdf = new Dompdf();
    $qrCodePath = generateQRCode($name, $email);


    // Contenu HTML pour le PDF
    $html = "
        <h1>Confirmation de Soumission</h1>
        <p>Bonjour <strong>$name</strong>,</p>
        <p>Merci pour votre soumission. Voici les détails :</p>
        <ul>
            <li><strong>Nom :</strong> $name</li>
            <li><strong>Email :</strong> $email</li>
        </ul>
        <p>Veuillez trouver ci-joint un QR code pour votre soumission :</p>
        <p><img src='".$qrCodePath."' alt='QR Code'></p>
        <p>Cordialement,<br>L'équipe Gestion Ticket</p>
    ";

    // Charger le HTML dans dompdf
    $dompdf->loadHtml($html);

    // (Optionnel) Configurer la taille et l'orientation de la page
    $dompdf->setPaper('A4', 'portrait');

    // Générer le PDF
    $dompdf->render();

    // Sauvegarder le PDF dans un fichier temporaire
    $output = $dompdf->output();
    $filePath = sys_get_temp_dir() . "/confirmation_$name.pdf";
    file_put_contents($filePath, $output);
   

    return $filePath; // Retourne le chemin du fichier PDF
}




function generateQRCode($name, $email) {
    $writer = new PngWriter();

// Create QR code
$qrCode = new QrCode(
    data: $email . " " . $name, 
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
    $qrCodePath = generateQRCode($name, $email);
    try {
        // Configuration du serveur SMTP pour MailDev
        $mail->isSMTP();
        $mail->Host = 'localhost'; // MailDev écoute sur localhost
        $mail->SMTPAuth = false; // Pas d'authentification nécessaire pour MailDev
        $mail->Port = 1025; // Port par défaut de MailDev

        // Configuration de l'email
        $mail->setFrom('no-reply@gestionticket.com', 'Gestion Ticket');
        $mail->addAddress($email, $name);
        $mail->Subject = "Confirmation de soumission";
        
        $mail->Body = "Bonjour $name,\n\nMerci pour votre soumission. Veuillez trouver en pièce jointe un PDF de confirmation et un QR code.\n\n <image src='".$qrCodePath ."'> \n\nCordialement,\nL'équipe Gestion Ticket
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
        echo "Erreur lors de l'enregistrement des données.";
    }
}
?>
