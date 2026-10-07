<?php

namespace Database\Factories;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\ResourcePlacement;
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

    /**
     * A resource only shows to students once it has a placement, so every factory-made
     * resource gets one: its lesson (forLesson), its unit (forUnit) or the general area.
     * Pass ->unplaced() to skip it.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (SubjectResource $resource) {
            if ($resource->placements()->exists()) {
                return;
            }

            ResourcePlacement::create([
                'subject_resource_id' => $resource->id,
                'educational_lesson_id' => $resource->educational_lesson_id,
                'educational_unit_id' => null,
                'sort_order' => $resource->sort_order,
                'is_active' => true,
            ]);
        });
    }

    public function forLesson(EducationalLesson $lesson): static
    {
        return $this->state(fn () => [
            'subject_id' => $lesson->unit->stage->subject_id,
            'educational_lesson_id' => $lesson->id,
        ]);
    }

    /** Placed directly under a unit (no lesson). */
    public function forUnit(EducationalUnit $unit): static
    {
        return $this->state(fn () => ['subject_id' => $unit->stage->subject_id])
            ->afterCreating(function (SubjectResource $resource) use ($unit) {
                $resource->placements()->delete();
                ResourcePlacement::create([
                    'subject_resource_id' => $resource->id,
                    'educational_unit_id' => $unit->id,
                    'sort_order' => 1,
                    'is_active' => true,
                ]);
            });
    }

    /** Placed nowhere: invisible until somebody places it. */
    public function unplaced(): static
    {
        return $this->afterCreating(fn (SubjectResource $resource) => $resource->placements()->forceDelete());
    }
}
