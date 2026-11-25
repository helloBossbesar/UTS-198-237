/**
 * Frontend logic untuk Dashboard Biodata Tim UTS Pemrograman Web Service
 */

document.addEventListener('DOMContentLoaded', () => {
    // Referensi Elemen DOM
    const alertContainer = document.getElementById('alertContainer');
    const cardsGrid = document.getElementById('cardsGrid');
    const colAddCard = document.getElementById('colAddCard');
    const cardAddTrigger = document.getElementById('cardAddTrigger');

    // Modal Form Elements
    const modalBiodataEl = document.getElementById('modalBiodata');
    const modalBiodata = new bootstrap.Modal(modalBiodataEl);
    const formBiodata = document.getElementById('formBiodata');
    const modalBiodataLabel = document.getElementById('modalBiodataLabel');
    const modalAlert = document.getElementById('modalAlert');
    const biodataId = document.getElementById('biodataId');
    const inputNama = document.getElementById('inputNama');
    const inputNim = document.getElementById('inputNim');
    const inputDeskripsi = document.getElementById('inputDeskripsi');
    const inputFoto = document.getElementById('inputFoto');
    const previewWrapper = document.getElementById('previewWrapper');
    const imagePreview = document.getElementById('imagePreview');
    const btnSubmitForm = document.getElementById('btnSubmitForm');

    // Modal Delete Elements
    const modalDeleteEl = document.getElementById('modalDelete');
    const modalDelete = new bootstrap.Modal(modalDeleteEl);
    const deleteMessage = document.getElementById('deleteMessage');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');

    // State
    let currentDeleteId = null;
    let autoDismissTimer = null;

    // SVG Placeholder Siluet Abu-abu (Inline Data URI)
    const placeholderSvg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20300%20415%22%20width%3D%22300%22%20height%3D%22415%22%20fill%3D%22%23e9ecef%22%3E%3Crect%20width%3D%22300%22%20height%3D%22415%22%20fill%3D%22%23e9ecef%22%2F%3E%3Cpath%20d%3D%22M150%20165a50%2050%200%201%200-50-50%2050%2050%200%200%200%2050%2050zm0%2025c-45%200-95%2025-95%2065v35h190v-35c0-40-50-65-95-65z%22%20fill%3D%22%23adb5bd%22%2F%3E%3C%2Fsvg%3E";

    // Helper untuk CSRF Token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Tampilkan Alert Global (Success / Danger)
    function showAlert(message, type = 'success') {
        if (autoDismissTimer) {
            clearTimeout(autoDismissTimer);
            autoDismissTimer = null;
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
        alertDiv.setAttribute('role', 'alert');

        // Escape teks untuk cegah XSS
        const textSpan = document.createElement('span');
        textSpan.textContent = message;
        alertDiv.appendChild(textSpan);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close';
        closeBtn.setAttribute('data-bs-dismiss', 'alert');
        closeBtn.setAttribute('aria-label', 'Close');
        alertDiv.appendChild(closeBtn);

        alertContainer.innerHTML = '';
        alertContainer.appendChild(alertDiv);

        // Auto dismiss setelah 4 detik
        autoDismissTimer = setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertDiv);
            if (bsAlert) {
                bsAlert.close();
            } else {
                alertDiv.remove();
            }
        }, 4000);
    }

    // Tampilkan Pesan Error di Dalam Modal
    function showModalError(message) {
        modalAlert.textContent = message;
        modalAlert.classList.remove('d-none');
    }

    // Sembunyikan Error di Dalam Modal
    function hideModalError() {
        modalAlert.textContent = '';
        modalAlert.classList.add('d-none');
    }

    // Reset Form Modal
    function resetForm() {
        formBiodata.reset();
        biodataId.value = '';
        hideModalError();
        previewWrapper.classList.add('d-none');
        imagePreview.src = '';
        btnSubmitForm.disabled = false;
        btnSubmitForm.textContent = 'Simpan';
    }

    // Buka Modal Mode Tambah
    function openAddModal() {
        resetForm();
        modalBiodataLabel.textContent = 'Tambah Biodata';
        modalBiodata.show();
    }

    // Buka Modal Mode Edit
    async function openEditModal(id) {
        resetForm();
        modalBiodataLabel.textContent = 'Edit Biodata';
        btnSubmitForm.disabled = true;
        btnSubmitForm.textContent = 'Memuat...';
        modalBiodata.show();

        try {
            const response = await fetch(`/api/biodata/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                modalBiodata.hide();
                showAlert(result.message || 'Gagal mengambil data biodata.', 'danger');
                return;
            }

            const data = result.data;
            biodataId.value = data.id;
            inputNama.value = data.nama || '';
            inputNim.value = data.nim || '';
            inputDeskripsi.value = data.deskripsi || '';

            // Tampilkan foto saat ini sebagai preview jika ada
            if (data.foto_url) {
                imagePreview.src = data.foto_url;
                previewWrapper.classList.remove('d-none');
            } else {
                previewWrapper.classList.add('d-none');
                imagePreview.src = '';
            }

            btnSubmitForm.disabled = false;
            btnSubmitForm.textContent = 'Simpan';
        } catch (error) {
            modalBiodata.hide();
            showAlert('Terjadi kesalahan jaringan saat mengambil data.', 'danger');
        }
    }

    // Buka Modal Hapus
    function openDeleteModal(id, nama) {
        currentDeleteId = id;
        deleteMessage.textContent = `Yakin ingin menghapus biodata ${nama}?`;
        modalDelete.show();
    }

    // Preview File Foto Saat Dipilih
    inputFoto.addEventListener('change', () => {
        const file = inputFoto.files[0];
        if (file) {
            // Validasi tipe file
            if (!file.type.startsWith('image/')) {
                showModalError('File harus berupa gambar valid.');
                inputFoto.value = '';
                previewWrapper.classList.add('d-none');
                imagePreview.src = '';
                return;
            }

            // Validasi ukuran file (5MB = 5 * 1024 * 1024)
            if (file.size > 5 * 1024 * 1024) {
                showModalError('Ukuran file foto maksimal adalah 5MB.');
                inputFoto.value = '';
                previewWrapper.classList.add('d-none');
                imagePreview.src = '';
                return;
            }

            hideModalError();
            const previewUrl = URL.createObjectURL(file);
            imagePreview.src = previewUrl;
            previewWrapper.classList.remove('d-none');
        }
    });

    // Reset Form Saat Modal Ditutup
    modalBiodataEl.addEventListener('hidden.bs.modal', () => {
        resetForm();
    });

    // Event Trigger Klik Kartu Tambah
    cardAddTrigger.addEventListener('click', openAddModal);
    cardAddTrigger.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openAddModal();
        }
    });

    // Render Kartu Biodata ke DOM
    function renderBiodataList(biodataArray) {
        // Hapus semua kartu kecuali colAddCard
        const cards = cardsGrid.querySelectorAll('.biodata-card-col');
        cards.forEach(card => card.remove());

        biodataArray.forEach(item => {
            const col = document.createElement('div');
            col.className = 'col biodata-card-col';

            const card = document.createElement('div');
            card.className = 'card card-team h-100 flex-column';

            // Wrapper Gambar
            const imgWrapper = document.createElement('div');
            imgWrapper.className = 'card-img-wrapper';

            const img = document.createElement('img');
            img.src = item.foto_url ? item.foto_url : placeholderSvg;
            img.alt = item.nama;
            img.loading = 'lazy';
            img.onerror = () => {
                img.src = placeholderSvg;
            };
            imgWrapper.appendChild(img);
            card.appendChild(imgWrapper);

            // Card Body
            const cardBody = document.createElement('div');
            cardBody.className = 'card-body d-flex flex-column';

            const nameEl = document.createElement('h5');
            nameEl.className = 'card-team-name';
            nameEl.textContent = item.nama;
            cardBody.appendChild(nameEl);

            const descEl = document.createElement('p');
            descEl.className = 'card-team-desc mb-3';
            descEl.textContent = item.deskripsi;
            cardBody.appendChild(descEl);

            // Bagian Bawah (Separator & Buttons)
            const footerDiv = document.createElement('div');
            footerDiv.className = 'mt-auto';

            const hr = document.createElement('hr');
            hr.className = 'my-2 text-secondary opacity-25';
            footerDiv.appendChild(hr);

            const btnGroup = document.createElement('div');
            btnGroup.className = 'd-flex gap-2';

            // Tombol Edit
            const btnEdit = document.createElement('button');
            btnEdit.type = 'button';
            btnEdit.className = 'btn btn-outline-primary btn-sm';
            btnEdit.textContent = 'Edit';
            btnEdit.addEventListener('click', () => openEditModal(item.id));
            btnGroup.appendChild(btnEdit);

            // Tombol Hapus
            const btnDelete = document.createElement('button');
            btnDelete.type = 'button';
            btnDelete.className = 'btn btn-outline-danger btn-sm';
            btnDelete.textContent = 'Hapus';
            btnDelete.addEventListener('click', () => openDeleteModal(item.id, item.nama));
            btnGroup.appendChild(btnDelete);

            footerDiv.appendChild(btnGroup);
            cardBody.appendChild(footerDiv);
            card.appendChild(cardBody);

            col.appendChild(card);
            cardsGrid.appendChild(col);
        });
    }

    // Load Data Biodata dari Server
    async function loadBiodata() {
        try {
            const response = await fetch('/api/biodata', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                showAlert(result.message || 'Gagal memuat data.', 'danger');
                return;
            }

            renderBiodataList(result.data || []);
        } catch (error) {
            showAlert('Gagal memuat data dari server. Periksa koneksi backend.', 'danger');
        }
    }

    // Submit Form Tambah / Edit
    formBiodata.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideModalError();

        const namaVal = inputNama.value.trim();
        const nimVal = inputNim.value.trim();
        const deskripsiVal = inputDeskripsi.value.trim();

        // Validasi Klien
        if (!namaVal) {
            showModalError('Nama wajib diisi.');
            inputNama.focus();
            return;
        }
        if (!nimVal) {
            showModalError('NIM wajib diisi.');
            inputNim.focus();
            return;
        }
        if (!deskripsiVal) {
            showModalError('Deskripsi wajib diisi.');
            inputDeskripsi.focus();
            return;
        }

        const id = biodataId.value;
        const isEdit = !!id;

        const formData = new FormData();
        formData.append('nama', namaVal);
        formData.append('nim', nimVal);
        formData.append('deskripsi', deskripsiVal);

        if (inputFoto.files[0]) {
            formData.append('foto', inputFoto.files[0]);
        }

        let url = '/api/biodata';
        if (isEdit) {
            url = `/api/biodata/${id}`;
            formData.append('_method', 'PUT');
        }

        btnSubmitForm.disabled = true;
        btnSubmitForm.textContent = 'Menyimpan...';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: formData
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                // Tampilkan pesan error validasi atau server
                showModalError(result.message || 'Gagal menyimpan data.');
                btnSubmitForm.disabled = false;
                btnSubmitForm.textContent = 'Simpan';
                return;
            }

            // Sukses
            modalBiodata.hide();
            resetForm();
            await loadBiodata();

            const successMsg = isEdit ? 'Biodata berhasil diperbarui.' : 'Biodata berhasil ditambahkan.';
            showAlert(successMsg, 'success');
        } catch (error) {
            showModalError('Terjadi kesalahan jaringan atau server saat menyimpan data.');
            btnSubmitForm.disabled = false;
            btnSubmitForm.textContent = 'Simpan';
        }
    });

    // Konfirmasi Hapus
    btnConfirmDelete.addEventListener('click', async () => {
        if (!currentDeleteId) return;

        btnConfirmDelete.disabled = true;
        btnConfirmDelete.textContent = 'Menghapus...';

        try {
            const response = await fetch(`/api/biodata/${currentDeleteId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            const result = await response.json();
            modalDelete.hide();
            btnConfirmDelete.disabled = false;
            btnConfirmDelete.textContent = 'Ya, Hapus';

            if (!response.ok || !result.success) {
                showAlert(result.message || 'Gagal menghapus biodata.', 'danger');
                return;
            }

            currentDeleteId = null;
            await loadBiodata();
            showAlert('Biodata berhasil dihapus.', 'success');
        } catch (error) {
            modalDelete.hide();
            btnConfirmDelete.disabled = false;
            btnConfirmDelete.textContent = 'Ya, Hapus';
            showAlert('Terjadi kesalahan jaringan saat menghapus biodata.', 'danger');
        }
    });

    // Inisialisasi: Muat data saat halaman dibuka
    loadBiodata();
});
