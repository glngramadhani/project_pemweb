document.addEventListener("DOMContentLoaded", () => {
    // 1. DOM ANIMATION: Angka berjalan (Count-Up) pada kartu statistik dashboard
    const statNumbers = document.querySelectorAll(".stat-card p");
    statNumbers.forEach((el) => {
        const rawText = el.textContent.trim();
        const isCurrency = rawText.startsWith("Rp");
        const numericMatch = rawText.replace(/\./g, "").match(/\d+/);

        if (!numericMatch) return;

        const targetValue = parseInt(numericMatch[0], 10);
        if (targetValue === 0) return;

        const suffix = isCurrency ? "" : rawText.replace(/[\d.\s]+/, " ");
        const duration = 900;
        const startTime = performance.now();

        function updateCounter(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out cubic agar melambat halus di akhir
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentVal = Math.floor(easeOut * targetValue);

            if (isCurrency) {
                el.textContent = "Rp " + currentVal.toLocaleString("id-ID");
            } else {
                el.textContent = currentVal + suffix;
            }

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                el.textContent = rawText;
            }
        }

        requestAnimationFrame(updateCounter);
    });

    // 2. DOM ANIMATION: Efek gelombang (Ripple) saat tombol diklik
    document.querySelectorAll(".btn").forEach((button) => {
        button.addEventListener("click", function (e) {
            const circle = document.createElement("span");
            const diameter = Math.max(this.clientWidth, this.clientHeight);
            const radius = diameter / 2;
            const rect = this.getBoundingClientRect();

            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${e.clientX - rect.left - radius}px`;
            circle.style.top = `${e.clientY - rect.top - radius}px`;
            circle.classList.add("ripple");

            const existingRipple = this.querySelector(".ripple");
            if (existingRipple) existingRipple.remove();

            this.appendChild(circle);
        });
    });

    // 3. DOM: Live Search Filter Tabel + Pesan Kosong Dinamis
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            const kataKunci = this.value.toLowerCase();
            const tbody = document.querySelector("#tabelPinjam tbody");
            const barisTabel = tbody.querySelectorAll("tr:not(#emptySearchRow)");
            let jumlahCocok = 0;

            barisTabel.forEach((baris) => {
                const isiTeks = baris.textContent.toLowerCase();
                const cocok = isiTeks.includes(kataKunci);
                baris.style.display = cocok ? "" : "none";
                if (cocok) jumlahCocok++;
            });

            let emptyRow = document.getElementById("emptySearchRow");
            if (jumlahCocok === 0 && barisTabel.length > 0) {
                if (!emptyRow) {
                    emptyRow = document.createElement("tr");
                    emptyRow.id = "emptySearchRow";
                    emptyRow.innerHTML = `<td colspan="9" class="text-center">Data dengan kata kunci "<b>${this.value}</b>" tidak ditemukan.</td>`;
                    tbody.appendChild(emptyRow);
                } else {
                    emptyRow.innerHTML = `<td colspan="9" class="text-center">Data dengan kata kunci "<b>${this.value}</b>" tidak ditemukan.</td>`;
                }
            } else if (emptyRow) {
                emptyRow.remove();
            }
        });
    }

    // 4. DOM + Cookie: Ubah Tema Gelap / Terang
    const btnTema = document.getElementById("btnTema");
    if (btnTema) {
        btnTema.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");
            const modeGelap = document.body.classList.contains("dark-mode");
            document.cookie = "tema=" + (modeGelap ? "dark" : "light") + "; path=/; max-age=" + (86400 * 30);
        });
    }

    // 5. DOM: Konfirmasi Hapus Data
    const tombolHapus = document.querySelectorAll(".btn-hapus");
    tombolHapus.forEach((tombol) => {
        tombol.addEventListener("click", (e) => {
            if (!confirm("Apakah Anda yakin ingin menghapus data peminjaman ini?")) {
                e.preventDefault();
            }
        });
    });

    // 6. DOM: Validasi Tanggal Kembali tidak boleh lebih awal dari Tanggal Pinjam
    const formPinjam = document.getElementById("formPinjam");
    if (formPinjam) {
        formPinjam.addEventListener("submit", (e) => {
            const tglPinjam = document.getElementById("tgl_pinjam").value;
            const tglKembali = document.getElementById("tgl_kembali").value;
            const domAlert = document.getElementById("domAlert");

            if (tglKembali < tglPinjam) {
                e.preventDefault();
                domAlert.innerHTML = '<div class="alert alert-danger">Tanggal jatuh tempo kembali tidak boleh lebih awal dari tanggal pinjam!</div>';
            }
        });
    }
});