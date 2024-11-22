<?php
session_start();
$db = new PDO('sqlite:banco.db');

function flash($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'register') {
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $senha = $_POST['senha'];
            $confirmar_senha = $_POST['confirmar_senha'];

            if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
                flash('Preencha todos os campos', 'error');
            } elseif ($senha !== $confirmar_senha) {
                flash('Senhas não conferem', 'error');
            } else {
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    flash('E-mail já cadastrado', 'error');
                } else {
                    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                    $stmt->execute([$nome, $email, $hashed_password]);
                    flash('Conta criada com sucesso', 'success');
                    header('Location: index.php?action=login');
                    exit;
                }
            }
        } elseif ($_POST['action'] === 'login') {
            $email = $_POST['email'];
            $senha = $_POST['senha'];

            if (empty($email) || empty($senha)) {
                flash('Preencha todos os campos', 'error');
            } else {
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$usuario || !password_verify($senha, $usuario['senha'])) {
                    flash('E-mail ou senha incorretos', 'error');
                } else {
                    flash('Login realizado com sucesso', 'success');
                    // Set user session here if needed
                    header('Location: index.php');
                    exit;
                }
            }
        }
    }
}

$flash = get_flash();
?>