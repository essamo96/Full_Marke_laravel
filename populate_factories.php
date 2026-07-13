<?php

$factories = [
    'ProgramFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Program;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'name_ar' => $this->faker->words(2, true),
            'name_en' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug,
            'type' => $this->faker->randomElement(['عادي', 'أطفال', 'تأهيلي']),
            'short_description' => $this->faker->sentence,
            'long_description' => $this->faker->paragraph,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
EOT,

    'SubjectFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Subject;
use App\Models\Program;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'name_ar' => $this->faker->words(2, true),
            'name_en' => $this->faker->words(2, true),
            'fee' => $this->faker->randomFloat(2, 50, 500),
            'min_payment' => $this->faker->randomFloat(2, 10, 50),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
EOT,

    'GroupFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'name' => $this->faker->word,
            'days' => ['Monday', 'Wednesday'],
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'max_capacity' => 20,
            'is_active' => true,
        ];
    }
}
EOT,

    'TeacherFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'password' => Hash::make('password'),
            'status' => 1,
        ];
    }
}
EOT,

    'GuardianFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Guardian;
use Illuminate\Support\Facades\Hash;

class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'national_id' => $this->faker->unique()->numerify('##########'),
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'is_active' => 1,
        ];
    }
}
EOT,

    'StudentFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'full_name_ar' => $this->faker->name,
            'full_name_en' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'guardian_id' => null,
            'phone' => $this->faker->phoneNumber,
            'password' => Hash::make('password'),
            'status' => 1,
        ];
    }
}
EOT,

    'RegistrationFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Group;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'registration_number' => $this->faker->unique()->numerify('REG-####'),
            'student_id' => Student::factory(),
            'group_id' => Group::factory(),
            'fee_snapshot' => 100.00,
            'total_fee' => 100.00,
            'amount_paid' => 0.00,
            'status' => 'pending',
        ];
    }
}
EOT,

    'PaymentFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Payment;
use App\Models\Student;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_number' => $this->faker->unique()->numerify('PAY-####'),
            'invoice_number' => null,
            'student_id' => Student::factory(),
            'method' => '1',
            'amount' => 100.00,
            'status' => 'pending',
        ];
    }
}
EOT,

    'PaymentRegistrationFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PaymentRegistration;
use App\Models\Payment;
use App\Models\Registration;

class PaymentRegistrationFactory extends Factory
{
    protected $model = PaymentRegistration::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'registration_id' => Registration::factory(),
            'allocated_amount' => 100.00,
        ];
    }
}
EOT,

    'PaymentStatusLogFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PaymentStatusLog;
use App\Models\Payment;

class PaymentStatusLogFactory extends Factory
{
    protected $model = PaymentStatusLog::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'action' => 'approved',
            'at' => now(),
        ];
    }
}
EOT,

    'EmailVerificationCodeFactory' => <<<'EOT'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EmailVerificationCode;
use App\Models\Student;

class EmailVerificationCodeFactory extends Factory
{
    protected $model = EmailVerificationCode::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'code' => $this->faker->numerify('######'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ];
    }
}
EOT,
];

foreach ($factories as $name => $content) {
    file_put_contents(__DIR__ . '/database/factories/' . $name . '.php', $content);
}
echo "Factories populated successfully.\n";
