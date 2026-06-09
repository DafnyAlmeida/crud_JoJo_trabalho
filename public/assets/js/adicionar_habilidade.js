let contadorHabilidades = window.habilidadesIniciais || 0;
let modoFormulario = window.modoFormulario || "adicionar";

function adicionarHabilidade() {
    contadorHabilidades++;

    const container = document.getElementById("habilidades");
    const div = document.createElement("div");

    div.className = "habilidade-item rounded-2xl border border-jojo-border bg-white/85 p-5 shadow-sm";

    const requiredArquivo = modoFormulario === "adicionar" ? "required" : "";

    div.innerHTML = `
        <div class="mb-5 flex items-center justify-between gap-4 border-b border-jojo-border pb-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-jojo-purple">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </span>

                <div>
                    <h3 class="font-title text-lg font-bold text-jojo-purple">
                        Habilidade ${contadorHabilidades}
                    </h3>
                    <p class="text-xs text-slate-500">
                        Preencha as informações da habilidade do Stand.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="removerHabilidade(this)"
                class="inline-flex items-center gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-bold text-red-500 transition hover:bg-red-100"
            >
                <i class="fa-solid fa-trash"></i>
                Remover
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-[#473267]">
                    Nome da habilidade <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="habilidade_nome[]"
                    required
                    placeholder="Ex.: Manipulação do tempo"
                    class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                >
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-[#473267]">
                    Descrição da habilidade <span class="text-red-500">*</span>
                </label>

                <textarea
                    name="habilidade_descricao[]"
                    required
                    rows="4"
                    placeholder="Descreva como essa habilidade funciona..."
                    class="w-full resize-none rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] leading-6 text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                ></textarea>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-[#473267]">
                    Imagem da habilidade ${modoFormulario === "adicionar" ? '<span class="text-red-500">*</span>' : ''}
                </label>

                <input
                    type="file"
                    name="habilidade_imagem[]"
                    accept="image/*"
                    ${requiredArquivo}
                    class="w-full cursor-pointer rounded-xl border border-jojo-border bg-white px-3 py-2 text-[13px] text-[#433366] outline-none transition file:mr-3 file:rounded-lg file:border-0 file:bg-purple-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-jojo-purple hover:file:bg-purple-100 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                >
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-[#473267]">
                    Diagrama de força ${modoFormulario === "adicionar" ? '<span class="text-red-500">*</span>' : ''}
                </label>

                <input
                    type="file"
                    name="habilidade_diagrama_imagem[]"
                    accept="image/*"
                    ${requiredArquivo}
                    class="w-full cursor-pointer rounded-xl border border-jojo-border bg-white px-3 py-2 text-[13px] text-[#433366] outline-none transition file:mr-3 file:rounded-lg file:border-0 file:bg-purple-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-jojo-purple hover:file:bg-purple-100 focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                >
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-[#473267]">
                    Tipo de Stand
                </label>

                <select
                    name="habilidade_tipo[]"
                    class="w-full rounded-xl border border-jojo-border bg-white px-3 py-2.5 text-[13px] text-[#433366] outline-none transition focus:border-jojo-lilac focus:ring-4 focus:ring-purple-100"
                >
                    <option value="Stands de Curto Alcance">Stands de Curto Alcance</option>
                    <option value="Stands de Longa Distancia">Stands de Longa Distância</option>
                    <option value="Stands Automáticos">Stands Automáticos</option>
                </select>
            </div>
        </div>
    `;

    container.appendChild(div);
}

function removerHabilidade(botao) {
    botao.closest(".habilidade-item").remove();
}