<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TotalAttachmentRule implements ValidationRule
{
    protected $existingFiles;

    public function __construct(array $existingFiles)
    {
        $this->existingFiles = $existingFiles;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $newFilesCount = is_array($value) ? count($value) : 0;
        $existingFilesCount = count($this->existingFiles);
        
        $totalFiles = $newFilesCount + $existingFilesCount;

        if ($totalFiles > 10) {
            $fail('Jumlah total lampiran tidak boleh melebihi 10.');
        }
    }
}
