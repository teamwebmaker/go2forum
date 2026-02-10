<?php

namespace App\Livewire;

use App\Support\ChatAttachmentRules;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class UploadField extends Component
{
    use WithFileUploads;

    #[Modelable]
    public $value = null;

    public string $name = 'files';
    public string $label = 'Upload';
    public bool $multiple = false;
    public string $accept = '';
    public int $maxSize = 0;
    public bool $disabled = false;
    public ?string $helpText = null;
    public bool $previewImages = true;
    public ?string $removedEvent = null;
    public array $docMimes = [];

    public string $inputId;

    public function mount(): void
    {
        $this->inputId = 'upload-' . Str::uuid()->toString();
        if ($this->accept === '') {
            $this->accept = ChatAttachmentRules::acceptAttribute();
        }
        if ($this->maxSize <= 0) {
            $this->maxSize = ChatAttachmentRules::maxKb();
        }
        if (empty($this->docMimes)) {
            $this->docMimes = ChatAttachmentRules::documentMimes();
        }
        $this->normalizeValue();
    }

    protected function rules(): array
    {
        $fileRule = [
            'nullable',
            'file',
            'max:' . $this->maxSize,
            function (string $attribute, mixed $value, callable $fail): void {
                if (!$value instanceof TemporaryUploadedFile) {
                    return;
                }

                $mime = $value->getMimeType() ?? '';
                if (!ChatAttachmentRules::isSupportedMime($mime, $this->docMimes)) {
                    $fail('The selected file type is not supported.');
                }
            },
        ];

        if ($this->multiple) {
            return [
                'value' => ['nullable', 'array', 'max:' . ChatAttachmentRules::maxCount()],
                'value.*' => $fileRule,
            ];
        }

        return [
            'value' => $fileRule,
        ];
    }

    protected function messages(): array
    {
        return ChatAttachmentRules::messages('value');
    }

    public function updatedValue(): void
    {
        $this->normalizeValue();

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            throw $exception;
        }
    }

    public function removeFile(int $index = 0): void
    {
        if ($this->multiple) {
            $files = $this->normalizeToArray($this->value);
            if (!isset($files[$index])) {
                return;
            }
            unset($files[$index]);
            $this->value = array_values($files);
        } else {
            $this->value = null;
        }

        $this->resetErrorBag();

        if ($this->removedEvent) {
            $this->dispatch($this->removedEvent, index: $index, name: $this->name);
        }
    }

    protected function normalizeValue(): void
    {
        $value = TemporaryUploadedFile::unserializeFromLivewireRequest($this->value);

        if ($this->multiple) {
            if ($value === null) {
                return;
            }
            if (!is_array($value)) {
                $value = [$value];
            }
            $this->value = array_values(array_filter($value));
            return;
        }

        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        $this->value = $value;
    }

    protected function normalizeToArray(mixed $value): array
    {
        $value = TemporaryUploadedFile::unserializeFromLivewireRequest($value);

        if ($value === null) {
            return [];
        }
        if (is_array($value)) {
            return array_values(array_filter($value));
        }
        return [$value];
    }

    protected function buildDisplayItems(): array
    {
        $items = [];
        foreach ($this->normalizeToArray($this->value) as $index => $file) {
            if (!$file instanceof TemporaryUploadedFile) {
                continue;
            }
            $name = $file->getClientOriginalName();
            $extension = strtolower(pathinfo($name ?? '', PATHINFO_EXTENSION));
            $mime = $file->getMimeType() ?? '';
            $isImage = str_starts_with($mime, 'image/');
            $previewUrl = null;

            if ($isImage && $this->previewImages) {
                try {
                    $previewUrl = $file->temporaryUrl();
                } catch (\Throwable $e) {
                    $previewUrl = null;
                }
            }

            $items[] = [
                'index' => $index,
                'name' => $name,
                'extension' => $extension,
                'size' => $this->formatBytes($file->getSize() ?? 0),
                'mime' => $mime,
                'is_image' => $isImage,
                'preview_url' => $previewUrl,
                'icon' => $this->iconFor($extension, $isImage),
            ];
        }

        return $items;
    }

    protected function iconFor(string $extension, bool $isImage): string
    {
        if ($isImage) {
            return 'photo';
        }

        return match ($extension) {
            'pdf' => 'document-text',
            'doc', 'docx' => 'document-text',
            'xls', 'xlsx' => 'table-cells',
            'txt' => 'document-text',
            default => 'paper-clip',
        };
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }
        $kb = $bytes / 1024;
        if ($kb < 1024) {
            return number_format($kb, 1) . ' KB';
        }
        return number_format($kb / 1024, 1) . ' MB';
    }

    public function render()
    {
        $items = $this->buildDisplayItems();
        $imageItems = $this->previewImages
            ? array_values(array_filter($items, fn(array $item) => $item['is_image']))
            : [];
        $fileItems = $this->previewImages
            ? array_values(array_filter($items, fn(array $item) => !$item['is_image']))
            : $items;

        return view('livewire.upload-field', [
            'items' => $items,
            'imageItems' => $imageItems,
            'fileItems' => $fileItems,
        ]);
    }
}
