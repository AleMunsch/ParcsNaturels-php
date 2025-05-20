<?php
// app/Controllers/ParcController.php

require_once __DIR__ . '/../Models/Parc.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Spipu\Html2Pdf\Html2Pdf;

class ParcController extends Controller {
    private $model;

    public function __construct() {
        parent::__construct();

        if (empty($_SESSION['admin_logged'])) {
            header('Location: /ParcsNaturels-php/public/?url=auth/login');
            exit;
        }

        $this->model = new Parc($GLOBALS['pdo']);
    }

    public function index() {
        $parcs = $this->model->getAll();
        $this->renderJsonOrView('parc/index', ['parcs' => $parcs]);
    }

    public function show($id) {
        $parc = $this->model->getById($id);
        if ($parc) {
            $this->renderJsonOrView('parc/show', ['parc' => $parc]);
        } else {
            http_response_code(404);
            echo "Parc non trouvé.";
        }
    }

    public function create() {
        $this->renderJsonOrView('parc/create');
    }

    public function store() {
        $data = $_POST;
        $photoName = null;
        if (!empty($_FILES['photo']['name'])) {
            $photoName = uniqid() . '_' . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/../../public/uploads/' . $photoName);
        }
        $data['photo'] = $photoName;

        if ($this->model->create($data)) {
            $this->sendConfirmationEmail($data);
            header('Location: /ParcsNaturels-php/public/?url=parc/index');
            exit;
        } else {
            echo "Erreur lors de l'ajout du parc.";
        }
    }

    private function sendConfirmationEmail($data) {
    $config = require __DIR__ . '/../../config/config.php';
    $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->Port = $config['smtp']['port'];
            $mail->SMTPAuth = false; // Aucune auth pour MailHog

            // Vérifie si l'encryption est définie
            if (!empty($config['smtp']['encryption'])) {
                $mail->SMTPSecure = $config['smtp']['encryption'];
            }

            $mail->setFrom('no-reply@parcs-naturels.local', 'Parcs Naturels');
            $mail->addAddress($data['email_contact'], $data['nom_contact']);

            $mail->isHTML(true);
            $mail->Subject = "Confirmation d'enregistrement du parc naturel";
            $mail->Body = '<h2>Votre parc a bien été enregistré</h2>' .
                        '<p><strong>Nom :</strong> ' . htmlspecialchars($data['nom']) . '</p>' .
                        '<p><strong>Description :</strong> ' . htmlspecialchars($data['description']) . '</p>' .
                        '<p><strong>Date :</strong> ' . htmlspecialchars($data['date_creation']) . '</p>' .
                        '<p><strong>Prix :</strong> ' . htmlspecialchars($data['prix_entree']) . ' €</p>' .
                        '<p><strong>Coordonnées :</strong> ' . htmlspecialchars($data['latitude']) . ', ' . htmlspecialchars($data['longitude']) . '</p>' .
                        '<p><a href="' . $config['base_url'] . 'parc/show/' . $GLOBALS['pdo']->lastInsertId() . '">Voir les détails du parc</a></p>';

            $mail->send();
        } catch (Exception $e) {
            echo 'Erreur lors de l\'envoi du mail : ' . $mail->ErrorInfo;
        }
    }


    public function exportPdf($id) {
        $parc = $this->model->getById($id);
        if (!$parc) {
            http_response_code(404);
            echo "Parc non trouvé.";
            return;
        }

        ob_start();
        echo '<h1>' . $parc['nom'] . '</h1>';
        echo '<p><strong>Description :</strong> ' . $parc['description'] . '</p>';
        echo '<p><strong>Date de création :</strong> ' . $parc['date_creation'] . '</p>';
        echo '<p><strong>Prix :</strong> ' . $parc['prix_entree'] . ' €</p>';
        echo '<p><strong>Coordonnées :</strong> ' . $parc['latitude'] . ', ' . $parc['longitude'] . '</p>';
        echo '<p><strong>Contact :</strong> ' . $parc['nom_contact'] . ' (' . $parc['email_contact'] . ')</p>';
        if ($parc['photo']) {
            echo '<p><img src="../public/uploads/' . $parc['photo'] . '" width="300"></p>';
        }
        $html = ob_get_clean();

        $pdf = new Html2Pdf();
        $pdf->writeHTML($html);
        $pdf->output('parc_' . $id . '.pdf');
    }

    public function edit($id) {
        $parc = $this->model->getById($id);
        $this->renderJsonOrView('parc/edit', ['parc' => $parc]);
    }

    public function update($id) {
        $data = $_POST;
        if (!empty($_FILES['photo']['name'])) {
            $photoName = uniqid() . '_' . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/../../public/uploads/' . $photoName);
            $data['photo'] = $photoName;
        } else {
            $parc = $this->model->getById($id);
            $data['photo'] = $parc['photo'] ?? null;
        }

        if ($this->model->update($id, $data)) {
            header('Location: /ParcsNaturels-php/public/?url=parc/index');
            exit;
        } else {
            echo "Erreur lors de la mise à jour du parc.";
        }
    }

    public function delete($id) {
        $this->model->delete($id);
        header('Location: /ParcsNaturels-php/public/parc/index');
        exit;
    }
}