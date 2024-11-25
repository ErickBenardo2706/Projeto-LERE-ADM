document.addEventListener("DOMContentLoaded", () => {
    const cancelarButtons = document.querySelectorAll(".btn_cancelar");
    const modal = document.getElementById("modal_cancelar");
    const btnSim = document.getElementById("btn_sim");
    const btnNao = document.getElementById("btn_nao");
    let currentAgendamentoId = null;

    cancelarButtons.forEach(button => {
        button.addEventListener("click", () => {
            // Capturar o ID do agendamento
            currentAgendamentoId = button.dataset.id;
            // Abrir o modal
            modal.showModal();
        });
    });

    // Confirmação no modal
    btnSim.addEventListener("click", () => {
        fetch('cancelar_agendamento_adm.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentAgendamentoId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);

                }

            })
            .catch(error => console.error("Erro:", error));

        modal.close(); // Fechar o modal
    });

    // Cancelar no modal
    btnNao.addEventListener("click", () => modal.close());
});
