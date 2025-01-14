<?php
class User {
    public static function getAll() {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM ims_user ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }
}