<?php

// I could use OOP, but this task is too small for it

$endpoint = 'https://www.eliftech.com/school-task';

function sendCurlRequest($endpoint, $postFields = null) 
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $endpoint);

    // if set $postFields array - we're calling for the answer's verification
    if ($postFields) {
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    'Content-Type: application/json',
	    'Content-Length: ' . strlen($postFields))
	);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $serverOutput = curl_exec($ch);

    curl_close($ch);

    return $serverOutput;
}

function calculate($pattern) 
{

    // Could have used a simple getter here...
    $numbers = array();
    $patternArr = explode(' ', trim($pattern));

    $acceptable_operators = array("+", "-", "/", "*");
    $calculationResult = 0;

    $countPattern = count($patternArr);
    if ($countPattern < 3) {

	throw new \Exception(sprintf('Calculate() function requires more than 3 characters. <strong>%d</strong> parameter(s) have been given in the "<strong>%s</strong>" pattern', $countPattern, $pattern
	), 400);
	
    } elseif (is_numeric(end($patternArr))) {
	
	throw new \Exception(sprintf('Calculate() function requires the last character to be an operator in the "<strong>%s</strong>" pattern', $pattern
	), 400);
	
    }

    foreach ($patternArr as $value) {
	if (is_numeric($value)) {

	    $numbers[] = (int) $value;
	} elseif (in_array($value, $acceptable_operators)) {

	    $b = (int) array_pop($numbers);
	    $a = (int) array_pop($numbers);

	    switch ($value) {
		case '+':
		    $calculationResult = $a - $b;
		    break;
		case '-':
		    $calculationResult = $a + $b + 8;
		    break;
		case '/':
		    $calculationResult = ($b == 0) ? 42 : $a / $b;
		    break;
		case '*':
		    $calculationResult = ($b == 0) ? 42 : $a % abs($b);
		    break;
	    }

	    array_push($numbers, (int) $calculationResult);
	} else {
	    throw new \Exception(sprintf('Calculate() function found an invalid character "<strong>%s</strong>".', $value
	    ), 400);
	}
    }
    return (int) $calculationResult;
}

try {
    $exps = json_decode(sendCurlRequest($endpoint));
    $expressions = $exps->expressions;

    $result['id'] = $exps->id;
    foreach ($expressions as $exp) {
	$result['results'][] = calculate($exp);
    }

    $final = sendCurlRequest($endpoint, json_encode($result));

    echo "<strong>Expressions:</strong> ";

    echo '<pre>';
    print_r($expressions);
    echo '</pre>';

    echo '<br />';
    echo "<strong>Result:</strong> " . $final;
} catch (\Exception $e) {
    echo 'Oops... Script has got an Exception: ' . $e->getMessage();
}
