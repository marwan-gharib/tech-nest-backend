<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

$stmt = $conn->prepare(
    "SELECT c.id, c.image_url,
            COALESCE(t.name, c.name) AS name
     FROM categories c
     LEFT JOIN categories_translations t
            ON t.category_id = c.id AND t.lang = ?
     ORDER BY c.id ASC"
);
$stmt->execute([$lang]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

sendResponse(200, t('categories_retrieved'), $categories);
