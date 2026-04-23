<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class InterviewedStudent extends Component
{
    protected $listeners = ['refreshInterviewedStudent' => '$refresh'];

    #[On('close-modal')]
    public function getTotalCount(): void
    {
        $this->dispatch('refreshInterviewedStudent');
    }

    public function render(): View
    {
        $user = auth()->user();
        $count = $user?->studentScores()->whereDate('created_at', now()->format('Y-m-d'))->count() ?? 0;

        return view('livewire.interviewed-student', [
            'count' => $count,
        ]);
    }
}
