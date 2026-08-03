<?php

namespace App\Support;

/**
 * Shared constants for the student single-device lock, used by both the
 * login controller (which sets/checks the cookie) and the
 * EnforceStudentDeviceLock middleware (which re-checks it on every
 * subsequent request) so the cookie name/lifetime can't drift apart.
 */
class StudentDeviceLock
{
    public const COOKIE = 'fma_student_device_id';

    // 10 years — effectively "until the student clears their browser data".
    public const COOKIE_MINUTES = 60 * 24 * 365 * 10;
}
