<?php
function GenerateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function FormatTimeSince($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function FormatSeconds($seconds, $minimal = false)
{
    // To deduct when going down
    $total = $seconds;

    // Calculate the years
    $years = floor($total / 3.154e+7);
    $total -= $years*3.154e+7; // Removing from total

    // Months
    $month = floor($total / 2628336);
    $total -= $month*2628336; // Removing from total

    // Weeks
    $weeks = floor($total / 604800);
    $total -= $weeks*604800; // Removing from total

    // Days
    $days = floor($total / 86400);
    $total -= $days*86400; // Removing from total

    // Hours
    $hours = floor($total / 3600);
    $total -= $hours*3600; // Removing from total

    // Minutes
    $mins = floor($total / 60);
    $total -= $mins*60; // Removing from total

    $secs = floor($total);


    if ($minimal) {
        return $years."y ".$month."m ".$weeks."w ".$days."d ".$hours."h ".$mins."m ".$secs."s";
    } else {
        if (($years == 0) and ($month == 0) and ($weeks == 0) and ($days == 0)) {
            return $hours . "h " . $mins . "m " . $secs . "s";
        } elseif (($years == 0) and ($month == 0) and ($weeks == 0)) {
            return $days."d ".$hours."h ".$mins."m";
        } elseif (($years == 0) and ($month == 0)) {
            return $weeks."w ".$days."d ".$hours."h";
        } elseif (($years == 0)) {
            return $month."m ".$weeks."w ".$days."d";
        } else { // If there's weeks
            return $years."y ".$month."m ".$weeks."w";
        }
    }
}
