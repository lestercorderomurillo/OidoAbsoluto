<?php

namespace Cosmic\DOM\HTML;

class Beautifier
{
    private $options;
    private $currentMode;
    private $tags;
    private $tagType;
    private $tokenText;
    private $lastToken;
    private $lastText;
    private $tokenType;
    private $newlines;
    private $indentContent;
    private $indentLevel;
    private $lineCharCount;
    private $indentString;

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
        $this->currentMode = 'CONTENT';

        $this->tags = [
            'parent'        => 'parent1',
            'parentcount'   => 1,
            'parent1'       => ''
        ];

        $this->tagType = '';
        $this->tokenText = $this->lastToken = $this->lastText = $this->tokenType = '';
        $this->newlines = 0;
        $this->indentContent = $this->options['indent_inner_html'];
        $this->indentLevel = 0;
        $this->lineCharCount = 0;
        $this->indentString = str_repeat($this->options['indent_char'], $this->options['indent_size']);
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
                if ($this->lineCharCount >= $this->options['wrap_line_length']) {
                    $this->printNewline(false, $content);
                    $this->printIndentation($content);
                } else {
                    $this->lineCharCount++;
                    $content[] = ' ';
                }
                $space = false;
            }
            $this->lineCharCount++;
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
            $this->tags[$tag . $this->tags[$tag . 'count']] = $this->indentLevel;
        } else {
            $this->tags[$tag . 'count'] = 1;
            $this->tags[$tag . $this->tags[$tag . 'count']] = $this->indentLevel;
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
                $this->indentLevel = $this->tags[$tag . $this->tags[$tag . 'count']];
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
        $original_lineCharCount = $this->lineCharCount;

        do {
            if ($this->position >= $this->input_length) {
                if ($peek) {
                    $this->position = $original_position;
                    $this->lineCharCount = $original_lineCharCount;
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
                if ($this->lineCharCount >= $this->options['wrap_line_length']) {
                    $this->printNewline(false, $content);
                    $this->printIndentation($content);
                } else {
                    $content[] = ' ';
                    $this->lineCharCount++;
                }
                $space = false;
            }

            if ($input_char === '<' && !$tag_start_char) {
                $tag_start = $this->position - 1;
                $tag_start_char = '<';
            }

            $this->lineCharCount++;
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
                $this->tagType = 'SINGLE';
            }
        } else if ($tag_check === 'script') {
            if (!$peek) {
                $this->recordTag($tag_check);
                $this->tagType = 'SCRIPT';
            }
        } else if ($tag_check === 'style') {
            if (!$peek) {
                $this->recordTag($tag_check);
                $this->tagType = 'STYLE';
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
            $this->tagType = 'SINGLE';
        } else if ($tag_check && $tag_check[0] === '!') {
            if (!$peek) {
                $this->tagType = 'SINGLE';
                $this->traverseWhitespace();
            }
        } else if (!$peek) {
            if ($tag_check && $tag_check[0] === '/') {
                $this->retrieveTag(substr($tag_check, 1));
                $this->tagType = 'END';
                $this->traverseWhitespace();
            } else {
                $this->recordTag($tag_check);
                if (strtolower($tag_check) !== 'html') {
                    $this->indentContent = true;
                }
                $this->tagType = 'START';
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
            $this->lineCharCount = $original_lineCharCount;
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
                    $this->lineCharCount--;
                    continue;
                }
                if ($input_char === "\n" || $input_char === "\r") {
                    $content .= "\n";
                    $this->lineCharCount = 0;
                    continue;
                }
            }
            $content .= $input_char;
            $this->lineCharCount++;
            $space = true;

            if (preg_match('/^data:image\/(bmp|gif|jpeg|png|svg\+xml|tiff|x-icon);base64$/', $content)) {
                $content .= substr($this->input, $this->position, strpos($this->input, $delimiter, $this->position) - $this->position);
                $this->lineCharCount = strpos($this->input, $delimiter, $this->position) - $this->position;
                $this->position = strpos($this->input, $delimiter, $this->position);
                continue;
            }
        } while (strpos(strtolower($content), $delimiter, $min_index) === false);

        return $content;
    }

    private function getToken()
    {
        if ($this->lastToken === 'TK_TAG_SCRIPT' || $this->lastToken === 'TK_TAG_STYLE') {
            $type = substr($this->lastToken, 7);
            $token = $this->getContentsOf($type);
            if (!is_string($token)) {
                return $token;
            }
            return array($token, 'TK_' . $type);
        }
        if ($this->currentMode === 'CONTENT') {
            $token = $this->getContent();
            if (!is_string($token)) {
                return $token;
            } else {
                return array($token, 'TK_CONTENT');
            }
        }

        if ($this->currentMode === 'TAG') {
            $token = $this->getTag();
            if (!is_string($token)) {
                return $token;
            } else {
                $tag_name_type = 'TK_TAG_' . $this->tagType;
                return array($token, $tag_name_type);
            }
        }
    }

    private function getIndent($level)
    {
        $level = ($this->indentLevel + $level);
        return ($level < 1) ? "" : str_repeat($this->indentString, $level);
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
        $this->lineCharCount = 0;
        if (!$arr || !count($arr)) {
            return;
        }
        if ($force || ($arr[count($arr) - 1] !== "\n")) {
            $arr[] = "\n";
        }
    }

    private function printIndentation(&$arr)
    {
        for ($i = 0; $i < $this->indentLevel; $i++) {
            $arr[] = $this->indentString;
            $this->lineCharCount += strlen($this->indentString);
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
        $this->indentLevel++;
    }

    /**
     * Fixes the indentation level for this input string.
     * 
     * @param int $input The HTML string to fix before doing hard indenting.
     * 
     * @return int The fixed string.
     */
    public function prefixIndent(string $input): string
    {
        $matches = null;
        $lines = preg_split('#\r?\n#', $input, 0);
        $firstLine = $lines[0];

        $indent = 0;
        if (preg_match("/ +(?=<)/", $firstLine, $matches)) {
            $indent = strlen($matches[0]);
        }

        $fixedLines = [];
        foreach ($lines as $line) {
            $fixedLines[] = substr($line, $indent);
        }

        return implode("\n", $fixedLines);
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

            $this->tokenText = $t[0];
            $this->tokenType = $t[1];

            if ($this->tokenType === 'TK_EOF') {
                break;
            }

            switch ($this->tokenType) {
                case 'TK_TAG_START':
                    $this->printNewline(false, $this->output);
                    $this->printToken($this->tokenText);
                    if ($this->indentContent) {
                        $this->indent();
                        $this->indentContent = false;
                    }
                    $this->currentMode = 'CONTENT';
                    break;
                case 'TK_TAG_STYLE':
                case 'TK_TAG_SCRIPT':
                    $this->printNewline(false, $this->output);
                    $this->printToken($this->tokenText);
                    $this->currentMode = 'CONTENT';
                    break;
                case 'TK_TAG_END':
                    if ($this->lastToken === 'TK_CONTENT' && $this->lastText === '') {
                        $matches = array();
                        preg_match('/\w+/', $this->tokenText, $matches);
                        $tag_name = isset($matches[0]) ? $matches[0] : null;

                        $tag_extracted_from_last_output = null;
                        if (count($this->output)) {
                            $matches = array();
                            preg_match('/(?:<|<<<HTML#)\s*(\w+)/', $this->output[count($this->output) - 1], $matches);
                            $tag_extracted_from_last_output = isset($matches[0]) ? $matches[0] : null;
                        }
                        if ($tag_extracted_from_last_output === null || $tag_extracted_from_last_output[1] !== $tag_name) {
                            $this->printNewline(false, $this->output);
                        }
                    }
                    $this->printToken($this->tokenText);
                    $this->currentMode = 'CONTENT';
                    break;
                case 'TK_TAG_SINGLE':
                    $matches = array();
                    preg_match('/^\s*<([a-z]+)/i', $this->tokenText, $matches);
                    $tag_check = $matches ? $matches : null;

                    if (!$tag_check || !in_array($tag_check[1], $this->options['unformatted'])) {
                        $this->printNewline(false, $this->output);
                    }
                    $this->printToken($this->tokenText);
                    $this->currentMode = 'CONTENT';
                    break;
                case 'TK_CONTENT':
                    $this->printToken($this->tokenText);
                    $this->currentMode = 'TAG';
                    break;
                case 'TK_STYLE':
                case 'TK_SCRIPT':
                    if ($this->tokenText !== '') {
                        $this->printNewline(false, $this->output);
                        $text = trim($this->tokenText);
                        $script_indentLevel = 1;

                        if ($this->options['indent_scripts'] === "keep") {
                            $script_indentLevel = 0;
                        } else if ($this->options['indent_scripts'] === "separate") {
                            $script_indentLevel = $this->indentLevel;
                        }

                        $indentation = $this->getIndent($script_indentLevel);
                        $matches = array();
                        preg_match('/^\s*/', $text, $matches);
                        $white = isset($matches[0]) ? $matches[0] : null;

                        $matches = array();
                        preg_match('/[^\n\r]*$/', $white, $matches);
                        $dummy = isset($matches[0]) ? $matches[0] : null;

                        $_level = count(explode($this->indentString, $dummy)) - 1;
                        $reindent = $this->getIndent($script_indentLevel - $_level);

                        $text = preg_replace('/^\s*/', $indentation, $text);
                        $text = preg_replace('/\r\n|\r|\n/', "\n" . $reindent, $text);
                        $text = preg_replace('/\s+$/', '', $text);

                        if ($text) {
                            $this->printRawToken($indentation . trim($text));
                            $this->printNewline(false, $this->output);
                        }
                    }
                    $this->currentMode = 'TAG';
                    break;
            }

            $this->lastToken = $this->tokenType;
            $this->lastText = $this->tokenText;
        }

        return implode('', $this->output);
    }
}
