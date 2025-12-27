<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'];
$name  = $data['name'];
$provider = $data['provider']; // google OR facebook
$social_id = $data['social_id'];

if (!$email || !$provider || !$social_id) {
    echo json_encode(["status"=>false,"message"=>"Invalid data"]);
    exit;
}

/* نشوف اليوزر موجود ولا لا */
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    /* Update social id لو مش متسجل */
    if ($provider == "google" && empty($user['google_id'])) {
        $conn->prepare("UPDATE users SET google_id=? WHERE id=?")
             ->execute([$social_id,$user['id']]);
    }

    if ($provider == "facebook" && empty($user['facebook_id'])) {
        $conn->prepare("UPDATE users SET facebook_id=? WHERE id=?")
             ->execute([$social_id,$user['id']]);
    }

    echo json_encode([
        "status"=>true,
        "message"=>"Login success",
        "user"=>[
            "id"=>$user['id'],
            "name"=>$user['name'],
            "email"=>$user['email'],
            "role"=>$user['role']
        ]
    ]);
    exit;
}

/* User جديد */
$stmt = $conn->prepare(
    "INSERT INTO users (name,email,role,google_id,facebook_id)
     VALUES (?,?,?,?,?)"
);

$stmt->execute([
    $name,
    $email,
    "user",
    $provider == "google" ? $social_id : null,
    $provider == "facebook" ? $social_id : null
]);

$user_id = $conn->lastInsertId();

echo json_encode([
    "status"=>true,
    "message"=>"User registered",
    "user"=>[
        "id"=>$user_id,
        "name"=>$name,
        "email"=>$email,
        "role"=>"user"
    ]
]);
