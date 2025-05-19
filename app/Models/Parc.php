<?php
// app/Models/Parc.php

class Parc {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM parcs ORDER BY date_creation DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM parcs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO parcs (nom, description, date_creation, prix_entree, latitude, longitude, nom_contact, email_contact, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['nom'],
            $data['description'],
            $data['date_creation'],
            $data['prix_entree'],
            $data['latitude'],
            $data['longitude'],
            $data['nom_contact'],
            $data['email_contact'],
            $data['photo']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE parcs SET nom = ?, description = ?, date_creation = ?, prix_entree = ?, latitude = ?, longitude = ?, nom_contact = ?, email_contact = ?, photo = ? WHERE id = ?");
        return $stmt->execute([
            $data['nom'],
            $data['description'],
            $data['date_creation'],
            $data['prix_entree'],
            $data['latitude'],
            $data['longitude'],
            $data['nom_contact'],
            $data['email_contact'],
            $data['photo'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM parcs WHERE id = ?");
        return $stmt->execute([$id]);
    }
}