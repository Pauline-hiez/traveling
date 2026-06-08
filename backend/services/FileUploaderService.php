<?php

class FileUploader
{
    /**
     * Gere un upload et retourne le chemin relatif ou null
     *
     * @param string $field  Nom du champ dans $_FILES
     * @param string $subdir Sous-dossier dans frontend/assets/img/ (ex: avatars, articles, bg)
     * @return ?string       Chemin relatif ou null
     */
    public static function upload(string $field, string $subdir): ?string
    {
        // Vérifie la présence du fichier
        if (empty($_FILES[$field]['tmp_name'])) {
            return null;
        }

        // Vérifie le type
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array(mime_content_type($_FILES[$field]['tmp_name']), $allowed, true)) {
            return null;
        }

        // Génére un nom unique
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $name = uniqid('img_', true) . '.' . $ext;
        $dir = ROOT . "/frontend/assets/img/$subdir/";

        // Crée le dossier si besoin
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Déplace le fichier
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir . $name)) {
            return null;
        }

        // Retourne le chemin relatif
        return "frontend/assets/img/$subdir/$name";
    }
}
