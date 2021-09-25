<?php

namespace Pipeline\PypeEngine\Inproc;

use Pipeline\Utilities\PatternHelper;

class BodyFinder
{
    private Selection $selection;
    private string $source;
    private string $closure_tag;

    public static function detectBody(string &$search_string, string $tag, int $body_start_pos = 0): Selection
    {
        $closure_tag_pos = PatternHelper::findByText($search_string, "</$tag>", $body_start_pos);
        $value = true;

        while((($nested_tag_pos = PatternHelper::findByText($search_string, "<$tag>", $body_start_pos)) != false) 
        && ($nested_tag_pos < $closure_tag_pos) && ($value != false)){
            $value = PatternHelper::findByText($search_string, "</$tag>", $closure_tag_pos + 1);
            if($value != false){
                $closure_tag_pos = $value;
            }
        }

        return new Selection($body_start_pos, $closure_tag_pos, $search_string);
    }

   /*public function __construct(Selection $selection, string $source, string $closure_tag)
    {
        $this->selection = $selection;
        $this->source = $source;
        $this->closure_tag = $closure_tag;
    }

    public function getSelection(): Selection
    {
        // Find the body of this for loop  Ex: <for>...body...</for>
        $body_start_pos = $this->selection->getEndPosition() + 1;

        $closure_tag_pos = PatternHelper::findByText($this->source, "</$this->closure_tag>", $body_start_pos);
        $nested_tag_pos = PatternHelper::findByText($this->source, "<$this->closure_tag>", $body_start_pos);

        /*
        if ($nested_tag_pos != false && $closure_tag_pos > $closure_tag_pos) {
            $for_pos_end = PatternHelper::findByText($this->source, "</$this->closure_tag>",
            $for_next_start + strlen("<$this->closure_tag>"));
        }

        return new Selection($body_start_pos, $closure_tag_pos, $this->source);
    }*/
}
