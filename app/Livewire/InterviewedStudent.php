<?php

namespace App\Livewire;

use Carbon\Carbon;
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
        $count = $user->studentScores()->whereDate('created_at', now()->format('Y-m-d'))->count();
        return view('livewire.interviewed-student',[
            'count' => $count
        ]);
    }
}
