let contadorHabilidades = window.habilidadesIniciais || 0;
let modoFormulario = window.modoFormulario || "adicionar";

function adicionarHabilidade() {
    contadorHabilidades++;

    const container = document.getElementById("habilidades");

    const div = document.createElement("div");

    div.className = "habilidade-item border rounded-xl p-4 bg-gray-50 shadow space-y-3";

    const requiredArquivo = modoFormulario === "adicionar" ? "required" : "";

    div.innerHTML = `
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-bold">
                Habilidade ${contadorHabilidades}
            </h3>

            <button
                type="button"
                onclick="removerHabilidade(this)"
                class="bg-red-600 text-white px-3 py-1 rounded-lg"
            >
                Remover
            </button>
        </div>

        <div>
            <label class="block mb-1">Nome da habilidade</label>
            <input
                type="text"
                name="habilidade_nome[]"
                required
                class="w-full border rounded-lg p-2"
            >
        </div>

        <div>
            <label class="block mb-1">Descrição da habilidade</label>
            <textarea
                name="habilidade_descricao[]"
                required
                class="w-full border rounded-lg p-2"
            ></textarea>
        </div>

        <div>
            <label class="block mb-1">Imagem da habilidade</label>
            <input
                type="file"
                name="habilidade_imagem[]"
                ${requiredArquivo}
                class="w-full border rounded-lg p-2"
            >
        </div>

        <div>
            <label class="block mb-1">Diagrama de força</label>
            <input
                type="file"
                name="habilidade_diagrama_imagem[]"
                ${requiredArquivo}
                class="w-full border rounded-lg p-2"
            >
        </div>

        <label>Tipo de stand</label>
        <select name="habilidade_tipo[]">
            <option value="Stands de Curto Alcance">Stands de Curto Alcance</option>
            <option value="Stands de Longa Distancia">Stands de Longa Distancia</option>
            <option value="Stands Automáticos">Stands Automáticos</option>
        </select>
    `;

    container.appendChild(div);
}

function removerHabilidade(botao) {
    botao.closest(".habilidade-item").remove();
}