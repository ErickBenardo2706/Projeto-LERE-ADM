<?php
// Iniciar a sessão
session_start();

// Verificar se as variáveis de sessão existem
$nome_usuario = isset($_SESSION['nome_completo']) ? $_SESSION['nome_completo'] : null;
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// Verificar se o administrador está logado
if (!isset($_SESSION['id'])) {
    header("Location: pag_login_adm.html");
    exit();
}

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'lere');
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Consulta SQL para obter todos os agendamentos com informações dos clientes
$sql = "SELECT a.id AS agendamento_id, a.data_agendamento, a.hora_agendamento, 
               c.nome_completo, c.telefone, c.data_nasc, c.possui_doenca, c.descricao_doenca, 
               p.nome AS procedimento, p.valor
        FROM agendamentos a
        JOIN cadastro c ON a.usuario_id = c.id
        JOIN procedimentos p ON a.procedimento_id = p.id";

$result = $conn->query($sql);

// Armazenar os agendamentos em um array
$agendamentos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $agendamentos[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LERÊ - AGENDAMENTOS - CLIENTES</title>
    <link rel="stylesheet" href="./estilo_pagagendamentosADM.css">
    <script src="./agendamentos_adm.js"></script>
</head>

<body>

    <div class="cabecalho1">
        <div class="logo">
            <img src="./imagens/logo.png" alt="logo" id="img_logo">
        </div>

        <?php if ($nome_usuario): ?>
            <div class="logado_ou_nao">
                <img src="./imagens/login_icon.png" alt="ícone de login" id="login_icon" onclick="toggleDropdown()">
                <p id="nome_usuario">Seja bem-vindo(a), <?php echo htmlspecialchars($nome_usuario); ?>!</p>
                <div class="dropdown" id="menuDropdown">
                    <a href="./logout.php">Sair</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="corpo">
        <div id="user_id" data-id="<?php echo $user_id !== null ? htmlspecialchars($user_id) : ''; ?>"></div>

        <h1>Agendamentos</h1>


        <?php if (empty($agendamentos)): ?>
            <div class="sem_agendamento">
                <img src="./imagens/sem_agendamento.png" alt="" id="img_sem_agendamento">
                <h2>Nenhum cliente fez agendamento!</h2>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Data de Nasc.</th>
                        <th>Doença</th>
                        <th>Descrição</th>
                        <th>Procedimento</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $agendamento): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($agendamento['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($agendamento['telefone']); ?></td>
                            <td><?php echo htmlspecialchars($agendamento['data_nasc']); ?></td>
                            <td><?php echo $agendamento['possui_doenca'] ? 'Sim' : 'Não'; ?></td>
                            <td><?php echo htmlspecialchars($agendamento['descricao_doenca'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($agendamento['procedimento']); ?></td>
                            <td>R$<?php echo number_format($agendamento['valor'], 2, ',', '.'); ?></td>

                            <?php
                            // Formatando data e hora para exibição no padrão brasileiro
                            $data = new DateTime($agendamento['data_agendamento']);
                            $hora = new DateTime($agendamento['hora_agendamento']);

                            // Traduzindo a data para português
                            $dias = [
                                'Monday' => 'segunda-feira',
                                'Tuesday' => 'terça-feira',
                                'Wednesday' => 'quarta-feira',
                                'Thursday' => 'quinta-feira',
                                'Friday' => 'sexta-feira',
                                'Saturday' => 'sábado',
                                'Sunday' => 'domingo'
                            ];

                            $meses = [
                                'January' => 'janeiro',
                                'February' => 'fevereiro',
                                'March' => 'março',
                                'April' => 'abril',
                                'May' => 'maio',
                                'June' => 'junho',
                                'July' => 'julho',
                                'August' => 'agosto',
                                'September' => 'setembro',
                                'October' => 'outubro',
                                'November' => 'novembro',
                                'December' => 'dezembro'
                            ];

                            $data_formatada = $data->format('l, d \d\e F \d\e Y');
                            foreach ($dias as $en => $pt) {
                                $data_formatada = str_replace($en, $pt, $data_formatada);
                            }
                            foreach ($meses as $en => $pt) {
                                $data_formatada = str_replace($en, $pt, $data_formatada);
                            }

                            $data_formatada = ucfirst($data_formatada);
                            ?>

                            <td><?php echo $data_formatada; ?></td>
                            <td><?php echo $hora->format('H:i'); ?></td>
                            <td>
                                <button class="btn_cancelar"
                                    data-id="<?php echo $agendamento['agendamento_id']; ?>">Cancelar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>


    <!-- Modal de confirmação de cancelamento -->
    <dialog class="modal_cancelar" id="modal_cancelar">

        <h3>Você deseja cancelar este agendamento?</h3>
        <button id="btn_nao">Não!</button>
        <button id="btn_sim">Sim!</button>

    </dialog>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const cancelarButtons = document.querySelectorAll(".btn_cancelar");
            const modal = document.getElementById("modal_cancelar");
            const btnSim = document.getElementById("btn_sim");
            const btnNao = document.getElementById("btn_nao");

            cancelarButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const agendamentoId = this.dataset.id;

                    // Verificar se o modal existe e exibir
                    if (modal) {
                        modal.showModal(); // Abre o modal para confirmar o cancelamento
                    } else {
                        console.error("Modal não encontrado!");
                    }

                    // Quando o botão "Sim" for clicado, envia a requisição de cancelamento
                    btnSim.addEventListener("click", function () {
                        console.log("Enviando pedido de cancelamento para o PHP com ID: " + agendamentoId);

                        // Envia o ID do agendamento para o PHP
                        fetch('cancelar_agendamento_adm.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: agendamentoId })
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data); // Verifique se a resposta está correta
                                if (data.success) {
                                    // Exibe a mensagem de sucesso
                                    alert(data.success);
                                    // Atualiza a página duas vezes
                                    location.reload(); // Primeira vez
                                    setTimeout(() => location.reload(), 500); // Segunda vez
                                } else {
                                    alert(data.error || "Erro ao cancelar o agendamento.");
                                }
                            })
                            .catch(error => {
                                console.error("Erro na requisição:", error);
                                alert("Erro ao tentar cancelar o agendamento.");
                            });

                        // Fecha o modal
                        modal.close();
                    });

                    // Quando o botão "Não" for clicado, fecha o modal sem cancelar
                    btnNao.addEventListener("click", function () {
                        modal.close();
                    });
                });
            });
        });


    </script>
</body>

</html>