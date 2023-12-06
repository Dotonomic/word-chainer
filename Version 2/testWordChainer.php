<?php

require 'WordChainer.php';

require 'vendor/autoload.php';
$LLMclient = OpenAI::client('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');
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