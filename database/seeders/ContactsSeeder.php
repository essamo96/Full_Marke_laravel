<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactsSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'طالب تجريبي',
                'email' => 'student@test.com',
                'phone' => '0591234567',
                'subject' => 'استفسار عن دورة الآيلتس',
                'message' => 'مرحباً، أود الاستفسار عن مواعيد دورات الآيلتس القادمة والتكلفة الإجمالية. شكراً لكم.',
                'status' => 0 // 0 means unread/pending
            ]
        ];

        foreach ($contacts as $contact) {
            Contact::firstOrCreate(
                ['email' => $contact['email']],
                $contact
            );
        }
    }
}
