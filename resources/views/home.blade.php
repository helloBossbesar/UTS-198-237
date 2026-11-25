<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>UTS Pem Web - Dashboard Biodata</title>
    <!-- Bootstrap 5.3 CSS via jsDelivr -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <!-- 1. NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">UTS Pem Web</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active fw-medium" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Features</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link disabled text-muted">(Masukkan Nama)</span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link disabled text-muted">(Masukkan NIM)</span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 2. MAIN CONTENT & ALERT -->
    <main class="py-4">
        <div class="container">
            <!-- Alert Notifikasi -->
            <div id="alertContainer"></div>

            <!-- 3. GRID KARTU -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="cardsGrid">
                <!-- Kartu Tambah (Pertama / Selalu paling kiri) -->
                <div class="col" id="colAddCard">
                    <div class="card card-team card-add h-100" id="cardAddTrigger" role="button" tabindex="0" title="Tambah Biodata Baru">
                        <div class="btn-add-circle">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Kartu Biodata akan dirender dinamis di sini oleh app.js -->
            </div>
        </div>
    </main>

    <!-- 4. FOOTER -->
    <footer class="py-4 mt-auto border-top bg-white">
        <div class="container text-center">
            <p class="mb-2">
                <a href="#" class="text-secondary px-2">Home</a>
                <a href="#" class="text-secondary px-2">Features</a>
                <a href="#" class="text-secondary px-2">Pricing</a>
                <a href="#" class="text-secondary px-2">FAQs</a>
                <a href="#" class="text-secondary px-2">About</a>
            </p>
            <hr class="my-3 text-secondary opacity-25">
            <p class="text-secondary small mb-0">
                &copy; 2025 Dibuat Sejujurjurnya Oleh Team (Masukkan Nama ketua) dan (Masukkan Nama Anggota) Tanpa Mencotek dari Tim Lain
            </p>
        </div>
    </footer>

    <!-- MODAL FORM (TAMBAH / EDIT BIODATA) -->
    <div class="modal fade" id="modalBiodata" tabindex="-1" aria-labelledby="modalBiodataLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formBiodata" enctype="multipart/form-data" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalBiodataLabel">Tambah Biodata</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Alert Error di Dalam Modal -->
                        <div id="modalAlert" class="alert alert-danger d-none" role="alert"></div>

                        <!-- ID Biodata (Hidden untuk mode edit) -->
                        <input type="hidden" id="biodataId" name="id">

                        <div class="mb-3">
                            <label for="inputNama" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inputNama" name="nama" placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div class="mb-3">
                            <label for="inputNim" class="form-label fw-semibold">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inputNim" name="nim" placeholder="Contoh: 12.11.5866" required>
                        </div>

                        <div class="mb-3">
                            <label for="inputDeskripsi" class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="inputDeskripsi" name="deskripsi" rows="3" placeholder="Masukkan deskripsi atau peran tugas" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="inputFoto" class="form-label fw-semibold">Foto <span class="text-muted fw-normal">(Opsional, Maks 5 MB)</span></label>
                            <input class="form-control" type="file" id="inputFoto" name="foto" accept="image/*">
                        </div>

                        <!-- Preview Foto -->
                        <div class="mb-2 d-none" id="previewWrapper">
                            <label class="form-label small text-muted">Preview Foto:</label>
                            <div class="preview-container border p-1">
                                <img id="imagePreview" src="" alt="Preview Foto">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitForm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div class="modal fade" id="modalDelete" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="deleteMessage">Yakin ingin menghapus biodata ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 Bundle JS via jsDelivr -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Custom Application JS -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
