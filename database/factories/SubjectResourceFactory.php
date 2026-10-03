<?php

namespace Database\Factories;

use App\Models\EducationalLesson;
use App\Models\Subject;
use App\Models\SubjectResource;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectResourceFactory extends Factory
{
    protected $model = SubjectResource::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'educational_lesson_id' => null,
            'category' => 'video',
            'title' => $this->faker->sentence(3),
            'type' => 'video',
            'url' => 'resources/'.$this->faker->uuid().'.mp4',
            'description' => null,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 50),
            'processing_status' => 'ready',
            'allow_download' => false,
            'is_shared' => false,
            'original_filename' => 'video.mp4',
        ];
    }

    public function forLesson(EducationalLesson $lesson): static
    {
        return $this->state(fn () => [
            'subject_id' => $lesson->unit->stage->subject_id,
            'educational_lesson_id' => $lesson->id,
        ]);
    }
}
