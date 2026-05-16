function proximaEtapa(etapaAtual) {

    const etapa = document.getElementById(
        "etapa" + etapaAtual
    );

    const campos = etapa.querySelectorAll(
        "input, textarea, select"
    );

    let formularioValido = true;

    campos.forEach((campo) => {

        campo.classList.remove(
            "border-red-500"
        );

        if (campo.type === "hidden") {
            return;
        }

        if (
            campo.hasAttribute("required") &&
            !campo.value.trim()
        ) {

            formularioValido = false;

            campo.classList.add(
                "border-red-500"
            );
        }
    });

    if (!formularioValido) {

        alert(
            "Preencha todos os campos obrigatórios."
        );

        return;
    }

    etapa.classList.add("hidden");

    document
        .getElementById(
            "etapa" + (etapaAtual + 1)
        )
        .classList.remove("hidden");
}

function voltarEtapa(etapaAtual) {

    document
        .getElementById(
            "etapa" + etapaAtual
        )
        .classList.add("hidden");

    document
        .getElementById(
            "etapa" + (etapaAtual - 1)
        )
        .classList.remove("hidden");
}