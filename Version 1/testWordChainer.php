<?php

require 'WordChainer.php';

// https://github.com/openai-php/client
require 'vendor/autoload.php';
$LLMclient = OpenAI::client(/* OpenAI API key */);
$LLM = 'gpt-4-1106-preview';

$WordChainer = new WordChainer($LLMclient,$LLM);

$firstWord = 'anger';
$lastWord = 'ball';
$chainLength = 7;
$tries = 5;
$scoredChains = $WordChainer->scoredChains($firstWord,$lastWord,$chainLength,$tries);

$chains = $scoredChains[0];
$scores = $scoredChains[1];

for ($i=1; $i<=$tries; $i++) {
	echo preg_replace('/]/',']<br>',$chains[$i-1]).'<br><br>';
	echo preg_replace('/]/',']<br>',$scores[$i-1]).'<br><br><br>';
}

?>