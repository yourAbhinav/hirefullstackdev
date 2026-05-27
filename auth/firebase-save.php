<?php

require '../config/db.php';

$data=
json_decode(
file_get_contents(
"php://input"
),
true
);

$sql=
"
INSERT IGNORE INTO admin_users
(
firebase_uid,
name,
email,
photo,
provider
)

VALUES
(
?,
?,
?,
?,
'google'
)
";

$stmt=
$conn->prepare($sql);

$stmt->bind_param(
"ssss",
$data['uid'],
$data['name'],
$data['email'],
$data['photo']
);

$stmt->execute();

session_start();

$_SESSION['admin']=
$data['email'];

echo "success";