<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Biodata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class BiodataApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_get_all_biodata()
    {
        $response = $this->getJson('/api/biodata');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar biodata berhasil diambil.',
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_get_single_biodata_success_and_not_found()
    {
        $response = $this->getJson('/api/biodata/1');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'nama' => 'rizki eka prasodjo',
                ]
            ]);

        $notFound = $this->getJson('/api/biodata/9999');
        $notFound->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Biodata tidak ditemukan.',
            ]);
    }

    public function test_store_biodata_without_photo()
    {
        $payload = [
            'nama' => 'Budi Santoso',
            'nim' => '22.11.1234',
            'deskripsi' => 'Pengembang aplikasi mobile dan tester.',
        ];

        $response = $this->postJson('/api/biodata', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Biodata berhasil ditambahkan.',
                'data' => [
                    'nama' => 'Budi Santoso',
                    'nim' => '22.11.1234',
                    'foto' => null,
                    'foto_url' => null,
                ]
            ]);

        $this->assertDatabaseHas('biodata', [
            'nama' => 'Budi Santoso',
            'nim' => '22.11.1234',
        ]);
    }

    public function test_store_biodata_with_photo()
    {
        $file = UploadedFile::fake()->image('avatar.png', 200, 200)->size(500);

        $response = $this->post('/api/biodata', [
            'nama' => 'Citra Lestari',
            'nim' => '22.11.5678',
            'deskripsi' => 'UI/UX Designer',
            'foto' => $file,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Biodata berhasil ditambahkan.',
            ]);

        $data = $response->json('data');
        $this->assertNotNull($data['foto']);
        $this->assertStringContainsString('uploads/', $data['foto_url']);

        // Pastikan file tersimpan di public/uploads
        $savedPath = public_path('uploads/' . $data['foto']);
        $this->assertFileExists($savedPath);

        // Cleanup
        if (File::exists($savedPath)) {
            File::delete($savedPath);
        }
    }

    public function test_validation_rejects_missing_fields()
    {
        $response = $this->postJson('/api/biodata', [
            'nama' => '',
            'nim' => '',
            'deskripsi' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Nama wajib diisi.',
            ]);
    }

    public function test_validation_rejects_invalid_file_and_oversize()
    {
        $fakeDoc = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post('/api/biodata', [
            'nama' => 'Dedi',
            'nim' => '22.11.9999',
            'deskripsi' => 'DevOps',
            'foto' => $fakeDoc,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'File foto harus berupa gambar valid.',
            ]);

        // File gambar > 5MB (misal 6MB = 6144 KB)
        $largeImage = UploadedFile::fake()->image('big.png')->size(6000);
        $responseLarge = $this->post('/api/biodata', [
            'nama' => 'Dedi',
            'nim' => '22.11.9999',
            'deskripsi' => 'DevOps',
            'foto' => $largeImage,
        ], ['Accept' => 'application/json']);

        $responseLarge->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Ukuran file foto maksimal adalah 5MB.',
            ]);
    }

    public function test_update_biodata_with_and_without_new_photo()
    {
        // 1. Update tanpa ganti foto
        $response = $this->post('/api/biodata/1', [
            '_method' => 'PUT',
            'nama' => 'Rizki Eka Prasodjo (Updated)',
            'nim' => '12.11.5866',
            'deskripsi' => 'Deskripsi baru setelah update',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Biodata berhasil diperbarui.',
                'data' => [
                    'id' => 1,
                    'nama' => 'Rizki Eka Prasodjo (Updated)',
                ]
            ]);

        // 2. Update dengan foto baru
        $file = UploadedFile::fake()->image('new_avatar.png', 100, 100)->size(300);
        $responsePhoto = $this->post('/api/biodata/1', [
            '_method' => 'PUT',
            'nama' => 'Rizki Eka Prasodjo (Photo Updated)',
            'nim' => '12.11.5866',
            'deskripsi' => 'Deskripsi baru dengan foto',
            'foto' => $file,
        ], ['Accept' => 'application/json']);

        $responsePhoto->assertStatus(200);
        $savedFile = $responsePhoto->json('data.foto');
        $this->assertNotNull($savedFile);
        $savedPath = public_path('uploads/' . $savedFile);
        $this->assertFileExists($savedPath);

        // 3. Update lagi dengan foto kedua -> foto lama harus terhapus
        $file2 = UploadedFile::fake()->image('new_avatar2.png', 100, 100)->size(300);
        $responsePhoto2 = $this->post('/api/biodata/1', [
            '_method' => 'PUT',
            'nama' => 'Rizki Eka Prasodjo (Photo 2)',
            'nim' => '12.11.5866',
            'deskripsi' => 'Deskripsi',
            'foto' => $file2,
        ], ['Accept' => 'application/json']);

        $responsePhoto2->assertStatus(200);
        $savedFile2 = $responsePhoto2->json('data.foto');
        $this->assertFileDoesNotExist($savedPath);
        $this->assertFileExists(public_path('uploads/' . $savedFile2));

        // Cleanup
        if (File::exists(public_path('uploads/' . $savedFile2))) {
            File::delete(public_path('uploads/' . $savedFile2));
        }
    }

    public function test_destroy_biodata_removes_record_and_file()
    {
        // Buat record dengan file
        $file = UploadedFile::fake()->image('to_delete.png', 50, 50)->size(100);
        $createRes = $this->post('/api/biodata', [
            'nama' => 'Akan Dihapus',
            'nim' => '99.99.9999',
            'deskripsi' => 'Tes hapus',
            'foto' => $file,
        ], ['Accept' => 'application/json']);

        $id = $createRes->json('data.id');
        $photoName = $createRes->json('data.foto');
        $filePath = public_path('uploads/' . $photoName);
        $this->assertFileExists($filePath);

        // Hapus
        $deleteRes = $this->deleteJson('/api/biodata/' . $id);
        $deleteRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Biodata berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('biodata', ['id' => $id]);
        $this->assertFileDoesNotExist($filePath);
    }
}
