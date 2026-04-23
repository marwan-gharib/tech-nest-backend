<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

$name_col = ($lang === 'ar') ? "COALESCE(t.name, c.name)" : "c.name";

$stmt = $conn->prepare(
    "SELECT c.id, c.image_url,
            $name_col AS name
     FROM categories c
     LEFT JOIN categories_translations t
            ON t.category_id = c.id
     ORDER BY c.id ASC"
);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

sendResponse(200, t('categories_retrieved'), $categories);
