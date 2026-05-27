<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>DevHire Admin Login</title>

<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>

<script src="../assets/js/firebase-config.js"></script>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link
href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Inter',sans-serif;
}

body{

min-height:100vh;

background:
linear-gradient(
135deg,
#0f172a,
#312e81,
#7c3aed
);

display:flex;

justify-content:center;

align-items:center;

overflow:hidden;

}

.card{

width:480px;

padding:50px;

border-radius:30px;

background:
rgba(255,255,255,.08);

backdrop-filter:
blur(30px);

border:
1px solid rgba(255,255,255,.15);

box-shadow:
0 25px 80px rgba(0,0,0,.4);

color:white;

}

.logo{

font-size:42px;

font-weight:800;

text-align:center;

color:#a855f7;

}

.subtitle{

margin-top:10px;

text-align:center;

color:#d1d5db;

}

.btn{

width:100%;

padding:18px;

border:none;

border-radius:18px;

margin-top:18px;

font-size:16px;

font-weight:700;

cursor:pointer;

display:flex;

justify-content:center;

align-items:center;

gap:12px;

transition:.3s;

}

.google{

background:white;

color:#111827;

}

.google:hover{

transform:translateY(-4px);

}

.github{

background:#111827;

color:white;

}

.github:hover{

transform:translateY(-4px);

}

.back{

display:block;

margin-top:20px;

text-align:center;

color:#d8b4fe;

text-decoration:none;

}

.footer{

margin-top:25px;

text-align:center;

color:#94a3b8;

font-size:13px;

}

</style>

</head>

<body>

<div class="card">

<div class="logo">

&lt;/&gt; DevHire

</div>

<p class="subtitle">

Admin Dashboard Authentication

</p>

<button
class="btn google"
onclick="loginGoogle()">

<i class="fab fa-google"></i>

Continue with Google

</button>

<button
class="btn github"
onclick="loginGithub()">

<i class="fab fa-github"></i>

Continue with GitHub

</button>

<a
class="back"
href="../index.php">

← Back to Website

</a>

<div class="footer">

Secure Access • DevHire Admin

</div>

</div>

<script>

function saveUser(user)
{

fetch(
'firebase-save.php',
{

method:'POST',

headers:{
'Content-Type':
'application/json'
},

body:
JSON.stringify({

uid:user.uid,

name:user.displayName,

email:user.email,

photo:user.photoURL

})

}

)

.then(()=>{

window.location=
'../admin/dashboard.php';

});

}

function loginGoogle()
{

var provider=
new firebase.auth.GoogleAuthProvider();

firebase
.auth()
.signInWithPopup(provider)

.then((result)=>{

saveUser(result.user);

})

.catch(console.error);

}

function loginGithub()
{

var provider=
new firebase.auth.GithubAuthProvider();

firebase
.auth()
.signInWithPopup(provider)

.then((result)=>{

saveUser(result.user);

})

.catch(console.error);

}

</script>

</body>

</html>