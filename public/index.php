<?php 
include_once "../src/includes/bloqueio.php";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JoJo's Bizarre Adventure</title>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    font-family: Arial, Helvetica, sans-serif;
    background: linear-gradient(180deg, #fff7f0, #f8f0ff);
    color: #24113d;
}

header {
    width: 100%;
    padding: 24px 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #d89b2b;
    background: #fffaf4;
}

header h1 {
    font-size: 42px;
    color: #b83280;
    font-weight: 900;
}

main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 60px 32px;
    text-align: center;
}

main > h1 {
    font-size: 52px;
    font-weight: 900;
    text-transform: uppercase;
    color: #24113d;
    margin-bottom: 20px;
}

main > p {
    max-width: 700px;
    margin: 0 auto 50px;
    font-size: 18px;
    line-height: 1.6;
    color: #5c4a70;
}

main > div:first-of-type {
    margin-bottom: 40px;
}

main > div:first-of-type h1 {
    font-size: 28px;
    color: #b83280;
}

main > div:last-of-type {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

main > div:last-of-type > a {
    text-decoration: none;
}

main > div:last-of-type > a div {
    min-height: 190px;
    border: 2px solid #d8b05c;
    border-radius: 22px;
    background: linear-gradient(135deg, #ffffff, #fff3df);
    box-shadow: 0 10px 25px rgba(72, 35, 116, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    transition: 0.25s;
}

main > div:last-of-type > a div::before {
    content: "★";
    position: absolute;
    top: 18px;
    right: 24px;
    color: #d89b2b;
    font-size: 28px;
}

main > div:last-of-type > a div::after {
    content: "ゴゴゴ";
    position: absolute;
    bottom: 12px;
    left: 20px;
    color: rgba(184, 50, 128, 0.18);
    font-size: 34px;
    font-weight: 900;
}

main > div:last-of-type > a div:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 35px rgba(72, 35, 116, 0.22);
    border-color: #b83280;
}

main > div:last-of-type h2 {
    font-size: 34px;
    font-weight: 900;
    color: #4c1d95;
    text-transform: uppercase;
}

a[href="logout.php"] {
    grid-column: 1 / -1;
    justify-self: center;
    margin-top: 24px;
    padding: 14px 34px;
    border: 2px solid #b83280;
    border-radius: 14px;
    color: #b83280;
    font-weight: 800;
    background: #fff;
    text-decoration: none;
    transition: 0.25s;
}

a[href="logout.php"]:hover {
    background: #b83280;
    color: #fff;
}

@media (max-width: 900px) {
    main > div:last-of-type {
        grid-template-columns: repeat(2, 1fr);
    }

    main > h1 {
        font-size: 38px;
    }
}

@media (max-width: 600px) {
    header {
        padding: 20px;
    }

    header h1 {
        font-size: 30px;
    }

    main {
        padding: 40px 20px;
    }

    main > div:last-of-type {
        grid-template-columns: 1fr;
    }

    main > h1 {
        font-size: 30px;
    }
}
</style>
</head>
<body>
    <header>
        <h1>Logo</h1>
        <a href="logout.php">Logout</a>
    </header>
    <main>
        <h1>
            Bem-vindo ao JoJo's Bizarre Adventure - CRUD
        </h1>
        <p>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusamus nobis culpa delectus pariatur vitae dolorum reprehenderit corrupti dicta unde modi.
        </p>
        <div>
            <a href="partes/visualizar.php?id=1">
                <div>
                    <h2>Parte 1</h2>
                </div>
            </a>
            <a href="partes/visualizar.php?id=2">
                <div>
                    <h2>Parte 2</h2>
                </div>
            </a>
            <a href="partes/visualizar.php?id=3">
                <div>
                    <h2>Parte 3</h2>
                </div>
            </a>
            <a href="partes/visualizar.php?id=4">
                <div>
                    <h2>Parte 4</h2>
                </div>
            </a>
            <a href="partes/visualizar.php?id=5">
                <div>
                    <h2>Parte 5</h2>
                </div>
            </a>
            <a href="partes/visualizar.php?id=6">
                <div>
                    <h2>Parte 6</h2>
                </div>
            </a>
            <a href="partes/visualizar.php?id=7">
                <div>
                    <h2>Parte 7</h2>
                </div>
            </a>
            <a href="partes/visualizar.php?id=8">
                <div>
                    <h2>Parte 8</h2>
                </div>
            </a>
            <a href="partes/visualizar.php?id=9">
                <div>
                    <h2>Parte 9</h2>
                </div>
            </a>
        </div>
    </main>
    <footer>
        <p>JoJo's Bizarre Adventure - CRUD @ 2026</p>
    </footer>
</body>
</html>