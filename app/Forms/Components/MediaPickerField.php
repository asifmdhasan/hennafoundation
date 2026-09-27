<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class MediaPickerField extends Field
{
    protected string $view = 'forms.components.media-picker-field';

    protected bool $isMultiple = false;

    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }
}
