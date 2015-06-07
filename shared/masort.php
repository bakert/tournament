<?php

//XXX this newfangled preserve_keys thing actually fucks up the sorting ...
//       see random-bids for an example.  We put it in for checking of get_stints
//       conversion so just remove it and do that some other way?
// Expects Multidimensional associative array for the first param.
// Second param is "field_a,field_d,field_a" as first param - _a for sort
// ascending, _d for sort descending.
function masort(&$data, $sort, $preserve_keys=true) {
    $function = create_sort_function($sort, $data);
    if ($preserve_keys) {
        return uasort($data, $function);
    } else {
        return usort($data, $function);
    }
}

function create_sort_function($sort, $data) {
    $f = '';
    foreach (explode(",", $sort) as $raw) {
        $ending = substr($raw, -strlen("_d"), strlen("_d"));
        if ($ending !== '_a' && $ending !== '_d') {
            $ending = '';
        }
        $key  = substr($raw, 0, strlen($raw) - strlen($ending));
        $desc = ($ending === "_d");
        $cmp  = get_comparison_function($key, $data);
        $f .= '$res = ' . $cmp . '($a["' . $key . '"], $b["' . $key . '"]); '
            . 'if ($res != 0) { '
                . 'return ' . ($desc ? '-$res' : '$res') . '; '
            . '} ';
    }
    $f .= 'return $a;';
    return create_function('$a, $b', $f);
}

//Look at the data and guess what the best comparator is.
function get_comparison_function($key, $data) {
    foreach ($data as $row) {
        $value = $row[$key];
        if (is_numeric($value)) {
            return 'numcmp';
        }
    }
    return 'strcasecmp';
}

/* test data
$data = array(
    array('A' => 'lemon',
          'B' => 'chicken'),
    array('A' => 'orange',
          'B' => 'duck'),
    array('A' => 'lemon',
          'B' => 'sherbert'),
    array('A' => 'orange',
          'B' => 'juice')
);

print "<pre>";
masort("A_a,B_a", $data);
print_r($data);
*/

function numcmp($a, $b) {
    if ($a > $b) {
        return 1;
    } elseif ($b > $a) {
        return -1;
    } else {
        return 0;
    }
}

?>
