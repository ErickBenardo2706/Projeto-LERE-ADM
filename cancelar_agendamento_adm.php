<?php
// Iniciar a sessão
session_start();

// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lere";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexão falhou: " . $conn->connect_error]));
}

// Verificar o método da requisição
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['id'])) {
        $idAgendamento = intval($data['id']);

        // Excluir o agendamento do banco de dados
        $sql = "DELETE FROM agendamentos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idAgendamento);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Agendamento cancelado com sucesso!"]);
        } else {
            echo json_encode(["error" => "Erro ao cancelar o agendamento: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "ID do agendamento não fornecido."]);
    }
}

$conn->close();
