<?php

namespace App\Jobs;

use App\Models\GroupNote;
use App\Notifications\NewGroupNoteNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyGroupOfNewNote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $note;

    public function __construct(GroupNote $note)
    {
        $this->note = $note;
    }

    public function handle(): void
    {
        $students = $this->note->group->registrations()
            ->whereIn('status', ['partially_paid', 'fully_paid'])
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter();

        foreach ($students as $student) {
            $student->notify(new NewGroupNoteNotification($this->note, $student->id));
        }
    }
}
