// Função para copiar a chave PIX
function copyPix() {
    let pixKey = document.getElementById("pixKey").value;
    let copyBtn = document.getElementById("copyBtn");

    navigator.clipboard.writeText(pixKey).then(() => {
        copyBtn.innerHTML = "✅ Copiado!";
        setTimeout(() => {
            copyBtn.innerHTML = "📋 Copiar";
        }, 2000);
    }).catch(err => {
        alert("Erro ao copiar: " + err);
    });
}