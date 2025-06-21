<?php

function getSAHolidays($year) {
    $holidays = [];

    // ✅ Fixed-date holidays (same every year)
    $fixed = [
        'New Year\'s Day' => "$year-01-01",
        'Human Rights Day' => "$year-03-21",
        'Freedom Day' => "$year-04-27",
        'Workers\' Day' => "$year-05-01",
        'Youth Day' => "$year-06-16",
        'National Women\'s Day' => "$year-08-09",
        'Heritage Day' => "$year-09-24",
        'Day of Reconciliation' => "$year-12-16",
        'Christmas Day' => "$year-12-25",
        'Day of Goodwill' => "$year-12-26",
    ];

    foreach ($fixed as $name => $date) {
        $holidays[] = [
            'name' => $name,
            'date' => $date,
            'is_recurring' => true,
            'calculated_by' => null
        ];
    }

    // ✅ Floating holidays (require calculation)

    // Good Friday = Friday before Easter Sunday
    $easter = easter_date($year);
    $goodFriday = date('Y-m-d', strtotime('-2 days', $easter));
    $familyDay = date('Y-m-d', strtotime('+1 day', $easter));

    $holidays[] = [
        'name' => 'Good Friday',
        'date' => $goodFriday,
        'is_recurring' => false,
        'calculated_by' => 'easter - 2 days'
    ];
    $holidays[] = [
        'name' => 'Family Day',
        'date' => $familyDay,
        'is_recurring' => false,
        'calculated_by' => 'easter + 1 day'
    ];

    // Father's Day = 3rd Sunday of June
    $fathersDay = date('Y-m-d', strtotime("third sunday of june $year"));
    $holidays[] = [
        'name' => 'Father\'s Day',
        'date' => $fathersDay,
        'is_recurring' => false,
        'calculated_by' => 'third sunday of june'
    ];

    // Mother's Day = 2nd Sunday of May (optional)
    $mothersDay = date('Y-m-d', strtotime("second sunday of may $year"));
    $holidays[] = [
        'name' => 'Mother\'s Day',
        'date' => $mothersDay,
        'is_recurring' => false,
        'calculated_by' => 'second sunday of may'
    ];

    return $holidays;
}
