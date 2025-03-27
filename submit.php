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

    // Définir une valeur par défaut pour nbr_ticket
    $defaultNbrTicket = 6;

    // Requête d'insertion avec nbr_ticket
    $stmt = $conn->prepare("INSERT INTO users (name, email, nbr_ticket) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $email, $defaultNbrTicket);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}

function generatePDF($name, $email) {
    $dompdf = new Dompdf();
    $qrCodePath = generateQRCode( $email, $name);


    // Contenu HTML pour le PDF
    $html = "
        <style>
            .bee-row,
            .bee-row-content {
                position: relative
            }

            .bee-row-6,
            body {
                background-color: #ffffff
            }

            body {
                color: #000000;
                font-family: Arial, Helvetica, sans-serif
            }

            a {
                color: #7747FF
            }

            * {
                box-sizing: border-box
            }

            body,
            h1 {
                margin: 0
            }

            .bee-row-content {
                max-width: 1280px;
                margin: 0 auto;
                display: flex
            }

            .bee-row-content .bee-col-w6 {
                flex-basis: 50%
            }

            .bee-row-content .bee-col-w12 {
                flex-basis: 100%
            }

            .bee-icon .bee-icon-label-right a {
                text-decoration: none
            }

            .bee-image {
                overflow: auto
            }

            .bee-image .bee-center {
                margin: 0 auto
            }

            .bee-row-1 .bee-col-1 .bee-block-1 {
                width: 100%
            }

            .bee-icon {
                display: inline-block;
                vertical-align: middle
            }

            .bee-icon .bee-content {
                display: flex;
                align-items: center
            }

            .bee-image img {
                display: block;
                width: 100%
            }

            @media (max-width:768px) {
                .bee-row-content:not(.no_stack) {
                    display: block
                }
            }

            .bee-row-1,
            .bee-row-2,
            .bee-row-3,
            .bee-row-4,
            .bee-row-5 {
                background-repeat: no-repeat
            }

            .bee-row-1 .bee-row-content {
                background-repeat: no-repeat;
                border-radius: 0;
                color: #000000
            }

            .bee-row-1 .bee-col-1,
            .bee-row-2 .bee-col-1,
            .bee-row-6 .bee-col-1 {
                padding-bottom: 5px;
                padding-top: 5px
            }

            .bee-row-2 .bee-row-content,
            .bee-row-6 .bee-row-content {
                background-repeat: no-repeat;
                color: #000000
            }

            .bee-row-2 .bee-col-1 .bee-block-1 {
                padding: 10px;
                text-align: center;
                width: 100%
            }

            .bee-row-3 .bee-row-content,
            .bee-row-4 .bee-row-content,
            .bee-row-5 .bee-row-content {
                background-repeat: no-repeat;
                border-radius: 6px;
                color: #000000;
                padding: 5px
            }

            .bee-row-3 .bee-col-1,
            .bee-row-4 .bee-col-1,
            .bee-row-5 .bee-col-1 {
                border-right: 1px dotted #000000;
                border-top: 0 solid #000000;
                padding-bottom: 5px;
                padding-right: 1px;
                padding-top: 5px;
                display: flex;
                flex-direction: column;
                justify-content: center
            }

            .bee-row-3 .bee-col-1 .bee-block-1,
            .bee-row-3 .bee-col-2 .bee-block-1,
            .bee-row-4 .bee-col-1 .bee-block-1,
            .bee-row-4 .bee-col-2 .bee-block-1,
            .bee-row-5 .bee-col-1 .bee-block-1,
            .bee-row-5 .bee-col-2 .bee-block-1 {
                padding: 5px;
                width: 100%
            }

            .bee-row-3 .bee-col-2,
            .bee-row-4 .bee-col-2,
            .bee-row-5 .bee-col-2 {
                padding-bottom: 5px;
                padding-top: 5px;
                display: flex;
                flex-direction: column;
                justify-content: center
            }

            .bee-row-6 {
                background-repeat: no-repeat
            }

            .bee-row-6 .bee-col-1 .bee-block-1 {
                color: #1e0e4b;
                font-family: Inter, sans-serif;
                font-size: 15px;
                padding-bottom: 5px;
                padding-top: 5px;
                text-align: center
            }

            .bee-row-2 .bee-col-1 .bee-block-1 h1 {
                color: #000000;
                direction: ltr;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 32px;
                font-weight: 700;
                letter-spacing: normal;
                line-height: 120%;
                text-align: center
            }

            .bee-row-6 .bee-col-1 .bee-block-1 .bee-icon-image {
                padding: 5px 6px 5px 5px
            }

            .bee-row-6 .bee-col-1 .bee-block-1 .bee-icon:not(.bee-icon-first) .bee-content {
                margin-left: 0
            }

            .bee-row-6 .bee-col-1 .bee-block-1 .bee-icon::not(.bee-icon-last) .bee-content {
                margin-right: 0
            }

            .bee-row-6 .bee-col-1 .bee-block-1 .bee-icon-label a {
                color: #1e0e4b
            }
        </style>
</head>
<body>
	<div class='bee-page-container'>
		<div class='bee-row bee-row-1'>
			<div class='bee-row-content'>
				<div class='bee-col bee-col-1 bee-col-w12'>
					<div class='bee-block bee-block-1 bee-image'><img alt='' class='bee-center bee-fixedwidth' src='./asset/festival-logo.png' style='max-width:256px;' /></div>
				</div>
			</div>
		</div>
		<div class='bee-row bee-row-2'>
			<div class='bee-row-content'>
				<div class='bee-col bee-col-1 bee-col-w12'>
					<div class='bee-block bee-block-1 bee-heading'>
						<h1>Veuillez utiliser ces tickets pour manger gratuitement<br />Mr/Mme .$name.  </h1>
						<p>Veuillez trouver ci-joint un QR code pour votre soumission :</p>
						<p><img src=''.$qrCodePath.'' alt='QR Code'></p>
					</div>
				</div>
			</div>
		</div>
		<div class='bee-row bee-row-3'>
			<div class='bee-row-content'>
				<div class='bee-col bee-col-1 bee-col-w6'>
					<div class='bee-block bee-block-1 bee-image'><img alt='' class='bee-center bee-autowidth' src='./asset/ticket-festival.jpg' style='max-width:623px;' /></div>
				</div>
				<div class='bee-col bee-col-2 bee-col-w6'>
					<div class='bee-block bee-block-1 bee-image'><img alt='' class='bee-center bee-autowidth' src='./asset/ticket-festival.jpg' style='max-width:625px;' /></div>
				</div>
			</div>
		</div>
		<div class='bee-row bee-row-4'>
			<div class='bee-row-content'>
				<div class='bee-col bee-col-1 bee-col-w6'>
					<div class='bee-block bee-block-1 bee-image'><img alt='' class='bee-center bee-autowidth' src='./asset/ticket-festival.jpg' style='max-width:623px;' /></div>
				</div>
				<div class='bee-col bee-col-2 bee-col-w6'>
					<div class='bee-block bee-block-1 bee-image'><img alt='' class='bee-center bee-autowidth' src='./asset/ticket-festival.jpg' style='max-width:625px;' /></div>
				</div>
			</div>
		</div>
		<div class='bee-row bee-row-5'>
			<div class='bee-row-content'>
				<div class='bee-col bee-col-1 bee-col-w6'>
					<div class='bee-block bee-block-1 bee-image'><img alt='' class='bee-center bee-autowidth' src='./asset/ticket-festival.jpg' style='max-width:623px;' /></div>
				</div>
				<div class='bee-col bee-col-2 bee-col-w6'>
					<div class='bee-block bee-block-1 bee-image'><img alt='' class='bee-center bee-autowidth' src='./asset/ticket-festival.jpg' style='max-width:625px;' /></div>
				</div>
			</div>
		</div>
		
	</div>
</body>
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




function generateQRCode($email, $name) {
    $writer = new PngWriter();
    $url =  __DIR__."/control.php?email=".$email;
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
