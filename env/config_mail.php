<?php
$mail->isSMTP();
$mail->Host = 'localhost'; // MailDev écoute sur localhost
$mail->SMTPAuth = false; // Pas d'authentification nécessaire pour MailDev
$mail->Port = 1025; // Port par défaut de MailDev

// Configuration de l'email
$mail->setFrom('no-reply@gestionticket.com', 'Gestion Ticket');