<?php

require_once __DIR__.'/jdf.php'; //jdf library

/**
 * Convert a Jalali date and time to Unix timestamp (UTC).
 *
 * @param string $jalaliDate Format: YYYY/MM/DD (e.g., "1404/07/12")
 * @param string $time Format: HH:MM (e.g., "14:00")
 * @return int Unix timestamp in UTC
 */
function jalaliToUnix(string $jalaliDate, string $time): int
{
    // Set the timezone to Iran
    date_default_timezone_set('Asia/Tehran');

    // Split the Jalali date into year, month, and day
    list($jy, $jm, $jd) = explode('/', $jalaliDate);

    // Split the time into hours and minutes
    list($hour, $minute) = explode(':', $time);

    // Convert Jalali date to Gregorian
    list($gy, $gm, $gd) = jalali_to_gregorian((int)$jy, (int)$jm, (int)$jd);

    // Create a DateTime object in Iran timezone
    $dateTime = new DateTime("$gy-$gm-$gd $hour:$minute:00", new DateTimeZone('Asia/Tehran'));

    // Convert to UTC and get the Unix timestamp
    $dateTime->setTimezone(new DateTimeZone('UTC'));

    return $dateTime->getTimestamp();
}

// Example Usage
// $jalaliDate = "1403/10/23";
// $time = "10:50";

// $unixTimestamp = jalaliToUnix($jalaliDate, $time);
// echo "$unixTimestamp";