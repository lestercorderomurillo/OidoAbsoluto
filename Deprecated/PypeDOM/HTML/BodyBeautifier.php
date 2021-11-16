<?php

namespace Pipeline\PypeDOM\HTML;

class BodyBeautifier
{
    private $options;
    private $current_mode;
    private $tags;
    private $tag_type;
    private $token_text;
    private $last_token;
    private $last_text;
    private $token_type;
    private $newlines;
    private $indent_content;
    private $indent_level;
    private $line_char_count;
    private $indent_string;

    private $whitespace = ["\n", "\r", "\t", " "];

    private $single_token = [
        'br', 'input', 'link', 'meta', '!doctype', 'basefont', 'base', 'area',
        'hr', 'wbr', 'param', 'img', 'isindex', '?xml', 'embed', '?php', '?', '?='
    ];
    
    private $extra_liners = ['head', 'body', '/html'];

    public function __construct()
    {
        $this->setOptions();

        $this->position = 0;
        $this->token = '';
        $this->current_mode = 'CONTENT';

        $this->tags = [
            'parent'        => 'parent1',
            'parentcount'   => 1,
            'parent1'       => ''
        ];

        $this->tag_type = '';
        $this->token_text = $this->last_token = $this->last_text = $this->token_type = '';
        $this->newlines = 0;
        $this->indent_content = $this->options['indent_inner_html'];
        $this->indent_level = 0;
        $this->line_char_count = 0;
        $this->indent_string = str_repeat($this->options['indent_char'], $this->options['indent_size']);
    }

    public function setOptions()
    {
        $this->options['indent_inner_html'] = false;
        $this->options['indent_size'] = 4;
        $this->options['indent_char'] = ' ';
        $this->options['indent_scripts'] = 'normal';
        $this->options['wrap_line_length'] = 32786;
        $this->options['unformatted'] = ['script'];
        $this->options['max_preserve_newlines'] = 0;
    }

    private function traverseWhitespace()
    {
        $input_char = isset($this->input[$this->position]) ? $this->input[$this->position] : '';
        if ($input_char && in_array($input_char, $this->whitespace)) {
            $this->newlines = 0;
            while ($input_char && in_array($input_char, $this->whitespace)) {
                $this->position++;
                $input_char = isset($this->input[$this->position]) ? $this->input[$this->position] : '';
            }
            return true;
        }
        return false;
    }

    private function getContent()
    {
        $input_char = '';
        $content = array();
        $space = false; 

        while (isset($this->input[$this->position]) && $this->input[$this->position] !== '<') {

            if ($this->position >= $this->input_length) {
                return count($content) ? implode('', $content) : array('', 'TK_EOF');
            }

            if ($this->traverseWhitespace()) {
                if (count($content)) {
                    $space = true;
                }
                continue;
            }

            $input_char = $this->input[$this->position];
            $this->position++;

            if ($space) {
                if ($this->line_char_count >= $this->options['wrap_line_length']) { 
                    $this->printNewline(false, $content);
                    $this->printIndentation($content);
                } else {
                    $this->line_char_count++;
                    $content[] = ' ';
                }
                $space = false;
            }
            $this->line_char_count++;
            $content[] = $input_char;
        }

        return count($content) ? implode('', $content) : '';
    }

    private function getContentsOf($name)
    {
        if ($this->position === $this->input_length) {
            return ['', 'TK_EOF'];
        }

        $content = '';
        $reg_array = [];
        preg_match('#</' . preg_quote($name, '#') . '\\s*>#im', $this->input, $reg_array, PREG_OFFSET_CAPTURE, $this->position);
        $end_script = $reg_array ? ($reg_array[0][1]) : $this->input_length;

        if ($this->position < $end_script) {
            $content = substr($this->input, $this->position, max($end_script - $this->position, 0));
            $this->position = $end_script;
        }

        return $content;
    }


    private function recordTag($tag)
    {
        if (isset($this->tags[$tag . 'count'])) {
            $this->tags[$tag . 'count']++;
            $this->tags[$tag . $this->tags[$tag . 'count']] = $this->indent_level;
        } else {
            $this->tags[$tag . 'count'] = 1;
            $this->tags[$tag . $this->tags[$tag . 'count']] = $this->indent_level;
        }
        $this->tags[$tag . $this->tags[$tag . 'count'] . 'parent'] = $this->tags['parent'];
        $this->tags['parent'] = $tag . $this->tags[$tag . 'count'];
    }

    private function retrieveTag($tag)
    {
        if (isset($this->tags[$tag . 'count'])) {
            $temp_parent = $this->tags['parent'];
            while ($temp_parent) {
                if ($tag . $this->tags[$tag . 'count'] === $temp_parent) {
                    break;
                }
                $temp_parent = isset($this->tags[$temp_parent . 'parent']) ? $this->tags[$temp_parent . 'parent'] : '';
            }
            if ($temp_parent) {
                $this->indent_level = $this->tags[$tag . $this->tags[$tag . 'count']];
                $this->tags['parent'] = $this->tags[$temp_parent . 'parent'];
            }
            unset($this->tags[$tag . $this->tags[$tag . 'count'] . 'parent']);
            unset($this->tags[$tag . $this->tags[$tag . 'count']]);
            if ($this->tags[$tag . 'count'] === 1) {
                unset($this->tags[$tag . 'count']);
            } else {
                $this->tags[$tag . 'count']--;
            }
        }
    }

    private function getTag($peek = false)
    {

        $tag_start = $tag_end = 0;
        $input_char = $comment = '';
        $content = [];
        $space = $tag_start_char = false;
        $original_position = $this->position;
        $original_line_char_count = $this->line_char_count;

        do {
            if ($this->position >= $this->input_length) {
                if ($peek) {
                    $this->position = $original_position;
                    $this->line_char_count = $original_line_char_count;
                }
                return count($content) ? implode('', $content) : ['', 'TK_EOF'];
            }

            $input_char = $this->input[$this->position];
            $this->position++;

            if (in_array($input_char, $this->whitespace)) {
                $space = true;
                continue;
            }

            if ($input_char === "'" || $input_char === '"') {
                $input_char .= $this->getUnformatted($input_char);
                $space = true;
            }

            if ($input_char === '=') {
                $space = false;
            }

            if (count($content) && $content[count($content) - 1] !== '=' && $input_char !== '>' && $space) {
                if ($this->line_char_count >= $this->options['wrap_line_length']) {
                    $this->printNewline(false, $content);
                    $this->printIndentation($content);
                } else {
                    $content[] = ' ';
                    $this->line_char_count++;
                }
                $space = false;
            }

            if ($input_char === '<' && !$tag_start_char) {
                $tag_start = $this->position - 1;
                $tag_start_char = '<';
            }

            $this->line_char_count++;
            $content[] = $input_char;

            if (isset($content[1]) && $content[1] === '!') {
                $content = array($this->getComment($tag_start));
                break;
            }
        } while ($input_char !== '>');

        $tag_complete = implode('', $content);

        if (strpos($tag_complete, ' ') !== false) {
            $tag_index = strpos($tag_complete, ' ');
        } else {
            $tag_index = strpos($tag_complete, '>');
        }

        if ($tag_complete[0] === '<') {
            $tag_offset = 1;
        } else {
            $tag_offset = $tag_complete[2] === '#' ? 3 : 2;
        }

        $tag_check = strtolower(substr($tag_complete, $tag_offset, max($tag_index - $tag_offset, 0)));

        if ($tag_complete[strlen($tag_complete) - 2] === '/' || in_array($tag_check, $this->single_token)) {
            if (!$peek) {
                $this->tag_type = 'SINGLE';
            }
        } else if ($tag_check === 'script') {
            if (!$peek) {
                $this->recordTag($tag_check);
                $this->tag_type = 'SCRIPT';
            }
        } else if ($tag_check === 'style') {
            if (!$peek) {
                $this->recordTag($tag_check);
                $this->tag_type = 'STYLE';
            }
        } else if ($this->isUnformatted($tag_check)) {
            $comment = $this->getUnformatted('</' . $tag_check . '>', $tag_complete);
            $content[] = $comment;
            if ($tag_start > 0 && in_array($this->input[$tag_start - 1], $this->whitespace)) {
                array_splice($content, 0, 0, $this->input[$tag_start - 1]);
            }
            $tag_end = $this->position - 1;
            if (in_array($this->input[$tag_end + 1], $this->whitespace)) {
                $content[] = $this->input[$tag_end + 1];
            }
            $this->tag_type = 'SINGLE';
        } else if ($tag_check && $tag_check[0] === '!') {
            if (!$peek) {
                $this->tag_type = 'SINGLE';
                $this->traverseWhitespace();
            }
        } else if (!$peek) {
            if ($tag_check && $tag_check[0] === '/') {
                $this->retrieveTag(substr($tag_check, 1));
                $this->tag_type = 'END';
                $this->traverseWhitespace();
            } else {
                $this->recordTag($tag_check);
                if (strtolower($tag_check) !== 'html') {
                    $this->indent_content = true;
                }
                $this->tag_type = 'START';
                $this->traverseWhitespace();
            }
            if (in_array($tag_check, $this->extra_liners)) {
                $this->printNewline(false, $this->output);
                if (count($this->output) && $this->output[count($this->output) - 2] !== "\n") {
                    $this->printNewline(true, $this->output);
                }
            }
        }

        if ($peek) {
            $this->position = $original_position;
            $this->line_char_count = $original_line_char_count;
        }

        return implode('', $content);
    }

    private function getComment($start_pos)
    {
        $comment = '';
        $delimiter = '>';
        $matched = false;

        $this->position = $start_pos;
        $input_char = $this->input[$this->position];
        $this->position++;

        while ($this->position <= $this->input_length) {
            $comment .= $input_char;

            if (
                $comment[strlen($comment) - 1] === $delimiter[strlen($delimiter) - 1] &&
                strpos($comment, $delimiter) !== false
            ) {
                break;
            }

            if (!$matched && strlen($comment) < 10) {
                if (strpos($comment, '<![if') === 0) {
                    $delimiter = '<![endif]>';
                    $matched = true;
                } else if (strpos($comment, '<![cdata[') === 0) {
                    $delimiter = ']]>';
                    $matched = true;
                } else if (strpos($comment, '<![') === 0) {
                    $delimiter = ']>';
                    $matched = true;
                } else if (strpos($comment, '<!--') === 0) {
                    $delimiter = '-->';
                    $matched = true;
                }
            }

            $input_char = $this->input[$this->position];
            $this->position++;
        }

        return $comment;
    }

    private function getUnformatted($delimiter, $orig_tag = false)
    {
        if ($orig_tag && strpos(strtolower($orig_tag), $delimiter) !== false) {
            return '';
        }

        $input_char = $content = '';
        $min_index = 0;
        $space = true;

        do {
            if ($this->position >= $this->input_length) {
                return $content;
            }

            $input_char = $this->input[$this->position];
            $this->position++;

            if (in_array($input_char, $this->whitespace)) {
                if (!$space) {
                    $this->line_char_count--;
                    continue;
                }
                if ($input_char === "\n" || $input_char === "\r") {
                    $content .= "\n";
                    $this->line_char_count = 0;
                    continue;
                }
            }
            $content .= $input_char;
            $this->line_char_count++;
            $space = true;

            if (preg_match('/^data:image\/(bmp|gif|jpeg|png|svg\+xml|tiff|x-icon);base64$/', $content)) {
                $content .= substr($this->input, $this->position, strpos($this->input, $delimiter, $this->position) - $this->position);
                $this->line_char_count = strpos($this->input, $delimiter, $this->position) - $this->position;
                $this->position = strpos($this->input, $delimiter, $this->position);
                continue;
            }
        } while (strpos(strtolower($content), $delimiter, $min_index) === false);

        return $content;
    }

    private function getToken()
    {
        if ($this->last_token === 'TK_TAG_SCRIPT' || $this->last_token === 'TK_TAG_STYLE') {
            $type = substr($this->last_token, 7);
            $token = $this->getContentsOf($type);
            if (!is_string($token)) {
                return $token;
            }
            return array($token, 'TK_' . $type);
        }
        if ($this->current_mode === 'CONTENT') {
            $token = $this->getContent();
            if (!is_string($token)) {
                return $token;
            } else {
                return array($token, 'TK_CONTENT');
            }
        }

        if ($this->current_mode === 'TAG') {
            $token = $this->getTag();
            if (!is_string($token)) {
                return $token;
            } else {
                $tag_name_type = 'TK_TAG_' . $this->tag_type;
                return array($token, $tag_name_type);
            }
        }
    }

    private function getIndent($level)
    {
        $level = ($this->indent_level + $level);
        return ($level < 1) ? "" : str_repeat($this->indent_string, $level);
    }

    private function isUnformatted($tag_check)
    {
        if (!in_array($tag_check, $this->options['unformatted'])) {
            return false;
        }

        if (strtolower($tag_check) !== 'a' || !in_array('a', $this->options['unformatted'])) {
            return true;
        }

        $next_tag = $this->getTag(true);

        $matches = array();
        preg_match('/^\s*<\s*\/?([a-z]*)\s*[^>]*>\s*$/', ($next_tag ? $next_tag : ""), $matches);
        $tag = $matches ? $matches : null;

        if (!$tag || in_array($tag, $this->options['unformatted'])) {
            return true;
        } else {
            return false;
        }
    }

    private function printNewline($force, &$arr)
    {
        $this->line_char_count = 0;
        if (!$arr || !count($arr)) {
            return;
        }
        if ($force || ($arr[count($arr) - 1] !== "\n")) {
            $arr[] = "\n";
        }
    }

    private function printIndentation(&$arr)
    {
        for ($i = 0; $i < $this->indent_level; $i++) {
            $arr[] = $this->indent_string;
            $this->line_char_count += strlen($this->indent_string);
        }
    }

    private function printToken($text)
    {
        if ($text || $text !== '') {
            if (count($this->output) && $this->output[count($this->output) - 1] === "\n") {
                $this->printIndentation($this->output);
                $text = ltrim($text);
            }
        }
        $this->printRawToken($text);
    }

    private function printRawToken($text)
    {
        if ($text != null && $text !== '') {
            if (strlen($text) > 1 && $text[strlen($text) - 1] === "\n") {
                $this->output[] = substr($text, 0, -1);
                $this->printNewline(false, $this->output);
            } else {
                $this->output[] = $text;
            }
        }

        for ($n = 0; $n < $this->newlines; $n++) {
            $this->printNewline($n > 0, $this->output);
        }
        $this->newlines = 0;
    }

    private function indent()
    {
        $this->indent_level++;
    }

    public function beautifyString(string $input): string
    {
        return preg_replace("/^\n+|^[\t\s]*\n+/m", "", $this->beautify($input));
    }

    private function beautify($input)
    {
        $this->input = $input; 
        $this->input_length = strlen($this->input);
        $this->output = array();

        while (true) {
            $t = $this->getToken();

            $this->token_text = $t[0];
            $this->token_type = $t[1];

            if ($this->token_type === 'TK_EOF') {
                break;
            }

            switch ($this->token_type) {
                case 'TK_TAG_START':
                    $this->printNewline(false, $this->output);
                    $this->printToken($this->token_text);
                    if ($this->indent_content) {
                        $this->indent();
                        $this->indent_content = false;
                    }
                    $this->current_mode = 'CONTENT';
                    break;
                case 'TK_TAG_STYLE':
                case 'TK_TAG_SCRIPT':
                    $this->printNewline(false, $this->output);
                    $this->printToken($this->token_text);
                    $this->current_mode = 'CONTENT';
                    break;
                case 'TK_TAG_END':
                    if ($this->last_token === 'TK_CONTENT' && $this->last_text === '') {
                        $matches = array();
                        preg_match('/\w+/', $this->token_text, $matches);
                        $tag_name = isset($matches[0]) ? $matches[0] : null;

                        $tag_extracted_from_last_output = null;
                        if (count($this->output)) {
                            $matches = array();
                            preg_match('/(?:<|{{#)\s*(\w+)/', $this->output[count($this->output) - 1], $matches);
                            $tag_extracted_from_last_output = isset($matches[0]) ? $matches[0] : null;
                        }
                        if ($tag_extracted_from_last_output === null || $tag_extracted_from_last_output[1] !== $tag_name) {
                            $this->printNewline(false, $this->output);
                        }
                    }
                    $this->printToken($this->token_text);
                    $this->current_mode = 'CONTENT';
                    break;
                case 'TK_TAG_SINGLE':
                    $matches = array();
                    preg_match('/^\s*<([a-z]+)/i', $this->token_text, $matches);
                    $tag_check = $matches ? $matches : null;

                    if (!$tag_check || !in_array($tag_check[1], $this->options['unformatted'])) {
                        $this->printNewline(false, $this->output);
                    }
                    $this->printToken($this->token_text);
                    $this->current_mode = 'CONTENT';
                    break;
                case 'TK_CONTENT':
                    $this->printToken($this->token_text);
                    $this->current_mode = 'TAG';
                    break;
                case 'TK_STYLE':
                case 'TK_SCRIPT':
                    if ($this->token_text !== '') {
                        $this->printNewline(false, $this->output);
                        $text = trim($this->token_text);
                        $script_indent_level = 1;

                        if ($this->options['indent_scripts'] === "keep") {
                            $script_indent_level = 0;
                        } else if ($this->options['indent_scripts'] === "separate") {
                            $script_indent_level = $this->indent_level;
                        }

                        $indentation = $this->getIndent($script_indent_level);
                        $matches = array();
                        preg_match('/^\s*/', $text, $matches);
                        $white = isset($matches[0]) ? $matches[0] : null;

                        $matches = array();
                        preg_match('/[^\n\r]*$/', $white, $matches);
                        $dummy = isset($matches[0]) ? $matches[0] : null;

                        $_level = count(explode($this->indent_string, $dummy)) - 1;
                        $reindent = $this->getIndent($script_indent_level - $_level);

                        $text = preg_replace('/^\s*/', $indentation, $text);
                        $text = preg_replace('/\r\n|\r|\n/', "\n" . $reindent, $text);
                        $text = preg_replace('/\s+$/', '', $text);
                        
                        if ($text) {
                            $this->printRawToken($indentation . trim($text));
                            $this->printNewline(false, $this->output);
                        }
                    }
                    $this->current_mode = 'TAG';
                    break;
            }

            $this->last_token = $this->token_type;
            $this->last_text = $this->token_text;
        }

        return implode('', $this->output);
    }
}
