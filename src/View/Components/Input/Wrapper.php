<?php

namespace Iperamuna\View\Components\Input;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Livewire\Attributes\On;

class Wrapper extends Component
{
    public $record;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {

    }

    #[On('reveal-secret')]
    public function getRevealSecret()
    {
        $record = $this->record;
        dd($record->getRevealValue());
        //$this->state($record->getRevealValue());
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('filament-secret::components.input.wrapper');
    }
}
