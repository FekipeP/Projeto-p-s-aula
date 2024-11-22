<?php
session_start();
$dbhost = 'localhost';
$dbUsername = 'magatsu';
$dbPassword = 'Felps_1324';
$dbName = 'info-user';

$conexao = new mysqli($dbhost, $dbUsername, $dbPassword, $dbName);

if ($conexao->connect_error) {
    die("Erro: " . $conexao->connect_error);
}

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
                // Check if email already exists
                $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    flash('E-mail já cadastrado', 'error');
                } else {
                    // Hash the password
                    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $nome, $email, $hashed_password);
                    if ($stmt->execute()) {
                        flash('Conta criada com sucesso', 'success');
                        header('Location: index.php?action=login');
                        exit;
                    } else {
                        flash('Erro ao criar conta', 'error');
                    }
                }
                $stmt->close();
            }
        } elseif ($_POST['action'] === 'login') {
            $email = $_POST['email'];
            $senha = $_POST['senha'];

            if (empty($email) || empty($senha)) {
                flash('Preencha todos os campos', 'error');
            } else {
                // Check email and password
                $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $usuario = $result->fetch_assoc();

                if (!$usuario || !password_verify($senha, $usuario['senha'])) {
                    flash('E-mail ou senha incorretos', 'error');
                } else {
                    flash('Login realizado com sucesso', 'success');
                    // Set user session here if needed
                    header('Location: index.php');
                    exit;
                }
                $stmt->close();
            }
        }
    }
}

$flash = get_flash();
?>