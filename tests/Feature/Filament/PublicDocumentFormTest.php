<?php

use App\Filament\Resources\PublicDocuments\Pages\CreatePublicDocument;
use App\Filament\Resources\PublicDocuments\Pages\EditPublicDocument;
use App\Models\PublicDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create([
        'role' => 'admin',
        'surname' => 'Administrator',
    ]));
});

test('duplicate public document names and links are reported as validation errors', function () {
    PublicDocument::query()->create([
        'name' => 'Privacy policy',
        'link' => 'https://example.com/privacy',
        'order' => 1,
        'visibility' => true,
        'requires_auth_to_view' => false,
    ]);

    Livewire::test(CreatePublicDocument::class)
        ->fillForm([
            'name' => 'Privacy policy',
            'link' => 'https://example.com/privacy',
            'order' => 2,
            'visibility' => true,
            'requires_auth_to_view' => false,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'unique',
            'link' => 'unique',
        ]);

    expect(PublicDocument::query()->count())->toBe(1);
});

test('an existing public document can retain its own unique values', function () {
    $document = PublicDocument::query()->create([
        'name' => 'Privacy policy',
        'link' => 'https://example.com/privacy',
        'order' => 1,
        'visibility' => true,
        'requires_auth_to_view' => false,
    ]);

    Livewire::test(EditPublicDocument::class, ['record' => $document->getRouteKey()])
        ->fillForm([
            'name' => $document->name,
            'link' => $document->link,
            'order' => $document->order,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});

test('the stored PDF filename matches the filename saved in the database', function () {
    Storage::fake(PublicDocument::STORAGE_DISK);

    Livewire::test(CreatePublicDocument::class)
        ->fillForm([
            'name' => '2025 წ - სრული კაც-საათის სარეკომენდაციო ღირებულება',
            'document' => UploadedFile::fake()->create('recommendation.pdf', 100, 'application/pdf'),
            'order' => 1,
            'visibility' => true,
            'requires_auth_to_view' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = PublicDocument::query()->sole();
    $expectedFilename = '2025-ts-sruli-kats-saatis-sarekomendatsio-ghirebuleba.pdf';

    expect($document->document)->toBe($expectedFilename);
    Storage::disk(PublicDocument::STORAGE_DISK)
        ->assertExists(PublicDocument::STORAGE_DIR."/{$expectedFilename}");
});

test('an existing PDF gets a unique filename that is also saved in the database', function () {
    Storage::fake(PublicDocument::STORAGE_DISK);

    $baseFilename = '2025-ts-sruli-kats-saatis-sarekomendatsio-ghirebuleba.pdf';
    Storage::disk(PublicDocument::STORAGE_DISK)->put(
        PublicDocument::STORAGE_DIR."/{$baseFilename}",
        'existing PDF'
    );

    Livewire::test(CreatePublicDocument::class)
        ->fillForm([
            'name' => '2025 წ - სრული კაც-საათის სარეკომენდაციო ღირებულება',
            'document' => UploadedFile::fake()->create('recommendation.pdf', 100, 'application/pdf'),
            'order' => 1,
            'visibility' => true,
            'requires_auth_to_view' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = PublicDocument::query()->sole();
    $expectedFilename = '2025-ts-sruli-kats-saatis-sarekomendatsio-ghirebuleba-2.pdf';

    expect($document->document)->toBe($expectedFilename);
    Storage::disk(PublicDocument::STORAGE_DISK)
        ->assertExists(PublicDocument::STORAGE_DIR."/{$expectedFilename}");
    Storage::disk(PublicDocument::STORAGE_DISK)
        ->assertExists(PublicDocument::STORAGE_DIR."/{$baseFilename}");
});

test('replacing a PDF removes the previous file after the document is updated', function () {
    Storage::fake(PublicDocument::STORAGE_DISK);

    $previousFilename = 'recommendation.pdf';
    Storage::disk(PublicDocument::STORAGE_DISK)->put(
        PublicDocument::STORAGE_DIR."/{$previousFilename}",
        'previous PDF'
    );

    $document = PublicDocument::query()->create([
        'name' => '2025 წ - სრული კაც-საათის სარეკომენდაციო ღირებულება',
        'document' => $previousFilename,
        'order' => 1,
        'visibility' => true,
        'requires_auth_to_view' => false,
    ]);

    Livewire::test(EditPublicDocument::class, ['record' => $document->getRouteKey()])
        ->fillForm([
            'document' => null,
        ])
        ->fillForm([
            'document' => [UploadedFile::fake()->create('replacement.pdf', 100, 'application/pdf')],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $expectedFilename = '2025-ts-sruli-kats-saatis-sarekomendatsio-ghirebuleba.pdf';

    expect($document->refresh()->document)->toBe($expectedFilename);
    Storage::disk(PublicDocument::STORAGE_DISK)
        ->assertExists(PublicDocument::STORAGE_DIR."/{$expectedFilename}");
    Storage::disk(PublicDocument::STORAGE_DISK)
        ->assertMissing(PublicDocument::STORAGE_DIR."/{$previousFilename}");
});
