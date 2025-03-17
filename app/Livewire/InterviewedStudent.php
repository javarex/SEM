<?php

namespace App\Livewire;

use Illuminate\Foundation\Events\Dispatchable;
use Livewire\Attributes\On;
use Livewire\Component;

class InterviewedStudent extends Component
{

    protected $listeners = ['refreshInterviewedStudent' => '$refresh'];

    #[On('close-modal')]
    public function getTotalCount()
    {
        $this->dispatch('refreshInterviewedStudent');
    }

    public function render()
    {
        $user = auth()->user();
        // dd($user);

        $count = $user->studentScores->count();
        return view('livewire.interviewed-student',[
            'count' => $count
        ]);
    }
}
