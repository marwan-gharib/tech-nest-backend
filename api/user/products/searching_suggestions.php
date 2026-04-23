<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

$q = isset($_GET['search_query']) ? trim($_GET['search_query']) : null;

if ($q === null || $q === '') {
    sendResponse(400, t('search_required'));
}

if (strlen($q) < 2) {
    sendResponse(400, t('search_min_length'));
}

$q = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');

try {
    $name_col = ($lang === 'ar') ? "COALESCE(pt.name, p.name)" : "p.name";

    // Search in both English and translated names; return localised name
    $stmt = $conn->prepare("
        SELECT DISTINCT $name_col AS name
        FROM products p
        LEFT JOIN products_translations pt ON pt.product_id = p.id
        WHERE p.name LIKE ? OR pt.name LIKE ?
        ORDER BY $name_col ASC
        LIMIT 10
    ");

    $stmt->execute(["%$q%", "%$q%"]);

    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$results) {
        sendResponse(200, t('no_suggestions'), []);
    }

    sendResponse(200, t('suggestions_retrieved'), $results);
} catch (Exception $e) {
    sendResponse(500, t('suggestions_failed'));
}
