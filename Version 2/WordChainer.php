<?php

class WordChainer {

	protected $LLMclient;
	protected $LLM;

	function get_LLMclient() {return $this->LLMclient;}
    function get_LLM() {return $this->LLM;}

    function set_LLMclient($LLMclient) {$this->LLMclient = $LLMclient;}
	function set_LLM($LLM) {$this->LLM = $LLM;}

	function scoredChains(string $first,string $last,int $length,int $tries) {
        if ($first == '' & $last == '') throw new Exception('No words provided.');
        if ($first == '') throw new Exception('No first word provided.');
        if ($last == '') throw new Exception('No last word provided.');
        if ($length<3) throw new Exception('Minimum chain length is 3!');
        if ($tries<1) throw new Exception('Number of chains to generate cannot be less than 1!');

		$bad = '';
		$good = '';
		$badExamples = '';
		$goodExamples = '';
		for ($i=1; $i<=$tries; $i++) {
			
			if (!empty($badExamples)) {
				$bad = ' Here are some evaluated and annotated examples of arrays which do not completely fulfill the requirements, although some of the connections may be strong: '.$badExamples;
			}
			
			if (!empty($goodExamples)) {
				$good = ' The following arrays may fulfill the requirements, but do your best to come up with a better one: '.$goodExamples;
			}
		
			$result = $this->LLMclient->chat()->create([
				'model' => $this->LLM,
				'messages' => [['role' => 'user', 'content' => 'Reply with an array of '.$length.' words (use square brackets and commas). The first one must be "'.$first.'" and the last one must be "'.$last.'". Each word must be conceptually related to its immediate predecessor (this of course does not apply to the first one). Beginning or ending with the same letter or having the same number of letters does not count as being "conceptually related". Explain your reasoning.'.$bad.$good]],
			]);
			$reply = $result['choices'][0]['message']['content'];
			$chains[] = $reply;
			$wordArray = preg_replace("/](.|\n)*/",'',$reply);
	    
			$result = $this->LLMclient->chat()->create([
				'model' => $this->LLM,
				'messages' => [['role' => 'user', 'content' => 'Consider the following array of '.$length.' words and the stated reasoning behind its construction: '.$reply.'. Score each word (except the first one) with an integer from 0 to 9, as to how strongly it is conceptually related to its immediate predecessor in the array. Observe only the word and its predecessor, you must completely disregard the other words in the array, both individually or as forming any particular theme or context. Also disregard the ordering of the two words, e.g. "tree","leaf" is equivalent to "leaf","tree". The words do not need to be synonyms for the score to be high. Beginning or ending with the same letter or having the same number of letters does not count as being "conceptually related". Reply with two lines: The first one with just an array containing the scores in the same order as the words. The second line with your reasoning for each score. Example: for the array [correct,right,left] the scores should be very high, e.g. [9,9], since "right" is a synonym of "correct" and "left" is strongly related to another meaning of "right".']],
			]);
			$scoreArray = $result['choices'][0]['message']['content'];
			$scores[] = $scoreArray;
			
			if (preg_match('/[0-2]/',$scoreArray)) $badExamples .= '; '.$wordArray.$scoreArray;
			else $goodExamples .= '; '.$wordArray.$scoreArray;
		}

		return [$chains,$scores];
	}
	
	function __construct($LLMclient,$LLM) {
        $this->LLMclient = $LLMclient;
        $this->LLM = $LLM;
	}
	
}

?>