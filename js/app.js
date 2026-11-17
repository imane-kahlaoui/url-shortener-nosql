document.addEventListener('DOMContentLoaded', () => {
    // 1. Copier le lien court dans le presse-papiers
    const copyButtons = document.querySelectorAll('.btn-copy-link');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const urlElement = document.getElementById(targetId);
            const urlText = urlElement ? (urlElement.value || urlElement.innerText.trim()) : btn.getAttribute('data-url');

            if (urlText) {
                navigator.clipboard.writeText(urlText).then(() => {
                    const originalText = btn.innerHTML;
                    btn.innerHTML = 'Copié !';
                    btn.classList.replace('btn-outline-primary', 'btn-success');

                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.classList.replace('btn-success', 'btn-outline-primary');
                    }, 2000);
                });
            }
        });
    });

    // 2. Télécharger l'image du QR Code
    const downloadQrBtn = document.getElementById('btnDownloadQr');
    if (downloadQrBtn) {
        downloadQrBtn.addEventListener('click', async () => {
            const qrImg = document.getElementById('qrCodeImg');
            if (!qrImg) return;

            try {
                const response = await fetch(qrImg.src);
                const blob = await response.blob();
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'qrcode-lien-court.png';
                link.click();
            } catch (err) {
                window.open(qrImg.src, '_blank');
            }
        });
    }

    // 3. Afficher / Masquer les champs mot de passe
    document.querySelectorAll('input[type="password"]').forEach(input => {
        const wrapper = document.createElement('div');
        wrapper.className = 'input-group';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'btn btn-outline-secondary';
        toggleBtn.textContent = 'Afficher';
        toggleBtn.addEventListener('click', () => {
            const isPwd = input.type === 'password';
            input.type = isPwd ? 'text' : 'password';
            toggleBtn.textContent = isPwd ? 'Masquer' : 'Afficher';
        });
        wrapper.appendChild(toggleBtn);
    });

    // 4. Filtrage en temps réel dans les tableaux (Dashboard / Admin)
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', () => {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});