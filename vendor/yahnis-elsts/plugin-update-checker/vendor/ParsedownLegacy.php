***REMOVED***

#
#
# Parsedown
# http://parsedown.org
#
# (c) Emanuil Rusev
# http://erusev.com
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

class Parsedown
***REMOVED***
    # ~

    const version = '1.5.0';

    # ~

    function text($text)
    ***REMOVED***
        # make sure no definitions are set
        $this->DefinitionData = array();

        # standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        # remove surrounding line breaks
        $text = trim($text, "\n");

        # split text into lines
        $lines = explode("\n", $text);

        # iterate through lines to identify blocks
        $markup = $this->lines($lines);

        # trim line breaks
        $markup = trim($markup, "\n");

        return $markup;
    ***REMOVED***

    #
    # Setters
    #

    function setBreaksEnabled($breaksEnabled)
    ***REMOVED***
        $this->breaksEnabled = $breaksEnabled;

        return $this;
    ***REMOVED***

    protected $breaksEnabled;

    function setMarkupEscaped($markupEscaped)
    ***REMOVED***
        $this->markupEscaped = $markupEscaped;

        return $this;
    ***REMOVED***

    protected $markupEscaped;

    function setUrlsLinked($urlsLinked)
    ***REMOVED***
        $this->urlsLinked = $urlsLinked;

        return $this;
    ***REMOVED***

    protected $urlsLinked = true;

    #
    # Lines
    #

    protected $BlockTypes = array(
        '#' => array('Header'),
        '*' => array('Rule', 'List'),
        '+' => array('List'),
        '-' => array('SetextHeader', 'Table', 'Rule', 'List'),
        '0' => array('List'),
        '1' => array('List'),
        '2' => array('List'),
        '3' => array('List'),
        '4' => array('List'),
        '5' => array('List'),
        '6' => array('List'),
        '7' => array('List'),
        '8' => array('List'),
        '9' => array('List'),
        ':' => array('Table'),
        '<' => array('Comment', 'Markup'),
        '=' => array('SetextHeader'),
        '>' => array('Quote'),
        '[' => array('Reference'),
        '_' => array('Rule'),
        '`' => array('FencedCode'),
        '|' => array('Table'),
        '~' => array('FencedCode'),
    );

    # ~

    protected $DefinitionTypes = array(
        '[' => array('Reference'),
    );

    # ~

    protected $unmarkedBlockTypes = array(
        'Code',
    );

    #
    # Blocks
    #

    private function lines(array $lines)
    ***REMOVED***
        $CurrentBlock = null;

        foreach ($lines as $line)
        ***REMOVED***
            if (chop($line) === '')
            ***REMOVED***
                if (isset($CurrentBlock))
                ***REMOVED***
                    $CurrentBlock['interrupted'] = true;
***REMOVED***

                continue;
***REMOVED***

            if (strpos($line, "\t") !== false)
            ***REMOVED***
                $parts = explode("\t", $line);

                $line = $parts[0];

                unset($parts[0]);

                foreach ($parts as $part)
                ***REMOVED***
                    $shortage = 4 - mb_strlen($line, 'utf-8') % 4;

                    $line .= str_repeat(' ', $shortage);
                    $line .= $part;
***REMOVED***
***REMOVED***

            $indent = 0;

            while (isset($line[$indent]) and $line[$indent] === ' ')
            ***REMOVED***
                $indent ++;
***REMOVED***

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($CurrentBlock['incomplete']))
            ***REMOVED***
                $Block = $this->***REMOVED***'block'.$CurrentBlock['type'].'Continue'***REMOVED***($Line, $CurrentBlock);

                if (isset($Block))
                ***REMOVED***
                    $CurrentBlock = $Block;

                    continue;
***REMOVED***
                else
                ***REMOVED***
                    if (method_exists($this, 'block'.$CurrentBlock['type'].'Complete'))
                    ***REMOVED***
                        $CurrentBlock = $this->***REMOVED***'block'.$CurrentBlock['type'].'Complete'***REMOVED***($CurrentBlock);
  ***REMOVED***

                    unset($CurrentBlock['incomplete']);
***REMOVED***
***REMOVED***

            # ~

            $marker = $text[0];

            # ~

            $blockTypes = $this->unmarkedBlockTypes;

            if (isset($this->BlockTypes[$marker]))
            ***REMOVED***
                foreach ($this->BlockTypes[$marker] as $blockType)
                ***REMOVED***
                    $blockTypes []= $blockType;
***REMOVED***
***REMOVED***

            #
            # ~

            foreach ($blockTypes as $blockType)
            ***REMOVED***
                $Block = $this->***REMOVED***'block'.$blockType***REMOVED***($Line, $CurrentBlock);

                if (isset($Block))
                ***REMOVED***
                    $Block['type'] = $blockType;

                    if ( ! isset($Block['identified']))
                    ***REMOVED***
                        $Blocks []= $CurrentBlock;

                        $Block['identified'] = true;
  ***REMOVED***

                    if (method_exists($this, 'block'.$blockType.'Continue'))
                    ***REMOVED***
                        $Block['incomplete'] = true;
  ***REMOVED***

                    $CurrentBlock = $Block;

                    continue 2;
***REMOVED***
***REMOVED***

            # ~

            if (isset($CurrentBlock) and ! isset($CurrentBlock['type']) and ! isset($CurrentBlock['interrupted']))
            ***REMOVED***
                $CurrentBlock['element']['text'] .= "\n".$text;
***REMOVED***
            else
            ***REMOVED***
                $Blocks []= $CurrentBlock;

                $CurrentBlock = $this->paragraph($Line);

                $CurrentBlock['identified'] = true;
***REMOVED***
        ***REMOVED***

        # ~

        if (isset($CurrentBlock['incomplete']) and method_exists($this, 'block'.$CurrentBlock['type'].'Complete'))
        ***REMOVED***
            $CurrentBlock = $this->***REMOVED***'block'.$CurrentBlock['type'].'Complete'***REMOVED***($CurrentBlock);
        ***REMOVED***

        # ~

        $Blocks []= $CurrentBlock;

        unset($Blocks[0]);

        # ~

        $markup = '';

        foreach ($Blocks as $Block)
        ***REMOVED***
            if (isset($Block['hidden']))
            ***REMOVED***
                continue;
***REMOVED***

            $markup .= "\n";
            $markup .= isset($Block['markup']) ? $Block['markup'] : $this->element($Block['element']);
        ***REMOVED***

        $markup .= "\n";

        # ~

        return $markup;
    ***REMOVED***

    #
    # Code

    protected function blockCode($Line, $Block = null)
    ***REMOVED***
        if (isset($Block) and ! isset($Block['type']) and ! isset($Block['interrupted']))
        ***REMOVED***
            return;
        ***REMOVED***

        if ($Line['indent'] >= 4)
        ***REMOVED***
            $text = substr($Line['body'], 4);

            $Block = array(
                'element' => array(
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => array(
                        'name' => 'code',
                        'text' => $text,
                    ),
                ),
            );

            return $Block;
        ***REMOVED***
    ***REMOVED***

    protected function blockCodeContinue($Line, $Block)
    ***REMOVED***
        if ($Line['indent'] >= 4)
        ***REMOVED***
            if (isset($Block['interrupted']))
            ***REMOVED***
                $Block['element']['text']['text'] .= "\n";

                unset($Block['interrupted']);
***REMOVED***

            $Block['element']['text']['text'] .= "\n";

            $text = substr($Line['body'], 4);

            $Block['element']['text']['text'] .= $text;

            return $Block;
        ***REMOVED***
    ***REMOVED***

    protected function blockCodeComplete($Block)
    ***REMOVED***
        $text = $Block['element']['text']['text'];

        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

        $Block['element']['text']['text'] = $text;

        return $Block;
    ***REMOVED***

    #
    # Comment

    protected function blockComment($Line)
    ***REMOVED***
        if ($this->markupEscaped)
        ***REMOVED***
            return;
        ***REMOVED***

        if (isset($Line['text'][3]) and $Line['text'][3] === '-' and $Line['text'][2] === '-' and $Line['text'][1] === '!')
        ***REMOVED***
            $Block = array(
                'markup' => $Line['body'],
            );

            if (preg_match('/-->$/', $Line['text']))
            ***REMOVED***
                $Block['closed'] = true;
***REMOVED***

            return $Block;
        ***REMOVED***
    ***REMOVED***

    protected function blockCommentContinue($Line, array $Block)
    ***REMOVED***
        if (isset($Block['closed']))
        ***REMOVED***
            return;
        ***REMOVED***

        $Block['markup'] .= "\n" . $Line['body'];

        if (preg_match('/-->$/', $Line['text']))
        ***REMOVED***
            $Block['closed'] = true;
        ***REMOVED***

        return $Block;
    ***REMOVED***

    #
    # Fenced Code

    protected function blockFencedCode($Line)
    ***REMOVED***
        if (preg_match('/^(['.$Line['text'][0].']***REMOVED***3,***REMOVED***)[ ]*([\w-]+)?[ ]*$/', $Line['text'], $matches))
        ***REMOVED***
            $Element = array(
                'name' => 'code',
                'text' => '',
            );

            if (isset($matches[2]))
            ***REMOVED***
                $class = 'language-'.$matches[2];

                $Element['attributes'] = array(
                    'class' => $class,
                );
***REMOVED***

            $Block = array(
                'char' => $Line['text'][0],
                'element' => array(
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => $Element,
                ),
            );

            return $Block;
        ***REMOVED***
    ***REMOVED***

    protected function blockFencedCodeContinue($Line, $Block)
    ***REMOVED***
        if (isset($Block['complete']))
        ***REMOVED***
            return;
        ***REMOVED***

        if (isset($Block['interrupted']))
        ***REMOVED***
            $Block['element']['text']['text'] .= "\n";

            unset($Block['interrupted']);
        ***REMOVED***

        if (preg_match('/^'.$Block['char'].'***REMOVED***3,***REMOVED***[ ]*$/', $Line['text']))
        ***REMOVED***
            $Block['element']['text']['text'] = substr($Block['element']['text']['text'], 1);

            $Block['complete'] = true;

            return $Block;
        ***REMOVED***

        $Block['element']['text']['text'] .= "\n".$Line['body'];;

        return $Block;
    ***REMOVED***

    protected function blockFencedCodeComplete($Block)
    ***REMOVED***
        $text = $Block['element']['text']['text'];

        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

        $Block['element']['text']['text'] = $text;

        return $Block;
    ***REMOVED***

    #
    # Header

    protected function blockHeader($Line)
    ***REMOVED***
        if (isset($Line['text'][1]))
        ***REMOVED***
            $level = 1;

            while (isset($Line['text'][$level]) and $Line['text'][$level] === '#')
            ***REMOVED***
                $level ++;
***REMOVED***

            if ($level > 6)
            ***REMOVED***
                return;
***REMOVED***

            $text = trim($Line['text'], '# ');

            $Block = array(
                'element' => array(
                    'name' => 'h' . min(6, $level),
                    'text' => $text,
                    'handler' => 'line',
                ),
            );

            return $Block;
        ***REMOVED***
    ***REMOVED***

    #
    # List

    protected function blockList($Line)
    ***REMOVED***
        list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]+[.]');

        if (preg_match('/^('.$pattern.'[ ]+)(.*)/', $Line['text'], $matches))
        ***REMOVED***
            $Block = array(
                'indent' => $Line['indent'],
                'pattern' => $pattern,
                'element' => array(
                    'name' => $name,
                    'handler' => 'elements',
                ),
            );

            $Block['li'] = array(
                'name' => 'li',
                'handler' => 'li',
                'text' => array(
                    $matches[2],
                ),
            );

            $Block['element']['text'] []= & $Block['li'];

            return $Block;
        ***REMOVED***
    ***REMOVED***

    protected function blockListContinue($Line, array $Block)
    ***REMOVED***
        if ($Block['indent'] === $Line['indent'] and preg_match('/^'.$Block['pattern'].'(?:[ ]+(.*)|$)/', $Line['text'], $matches))
        ***REMOVED***
            if (isset($Block['interrupted']))
            ***REMOVED***
                $Block['li']['text'] []= '';

                unset($Block['interrupted']);
***REMOVED***

            unset($Block['li']);

            $text = isset($matches[1]) ? $matches[1] : '';

            $Block['li'] = array(
                'name' => 'li',
                'handler' => 'li',
                'text' => array(
                    $text,
                ),
            );

            $Block['element']['text'] []= & $Block['li'];

            return $Block;
        ***REMOVED***

        if ($Line['text'][0] === '[' and $this->blockReference($Line))
        ***REMOVED***
            return $Block;
        ***REMOVED***

        if ( ! isset($Block['interrupted']))
        ***REMOVED***
            $text = preg_replace('/^[ ]***REMOVED***0,4***REMOVED***/', '', $Line['body']);

            $Block['li']['text'] []= $text;

            return $Block;
        ***REMOVED***

        if ($Line['indent'] > 0)
        ***REMOVED***
            $Block['li']['text'] []= '';

            $text = preg_replace('/^[ ]***REMOVED***0,4***REMOVED***/', '', $Line['body']);

            $Block['li']['text'] []= $text;

            unset($Block['interrupted']);

            return $Block;
        ***REMOVED***
    ***REMOVED***

    #
    # Quote

    protected function blockQuote($Line)
    ***REMOVED***
        if (preg_match('/^>[ ]?(.*)/', $Line['text'], $matches))
        ***REMOVED***
            $Block = array(
                'element' => array(
                    'name' => 'blockquote',
                    'handler' => 'lines',
                    'text' => (array) $matches[1],
                ),
            );

            return $Block;
        ***REMOVED***
    ***REMOVED***

    protected function blockQuoteContinue($Line, array $Block)
    ***REMOVED***
        if ($Line['text'][0] === '>' and preg_match('/^>[ ]?(.*)/', $Line['text'], $matches))
        ***REMOVED***
            if (isset($Block['interrupted']))
            ***REMOVED***
                $Block['element']['text'] []= '';

                unset($Block['interrupted']);
***REMOVED***

            $Block['element']['text'] []= $matches[1];

            return $Block;
        ***REMOVED***

        if ( ! isset($Block['interrupted']))
        ***REMOVED***
            $Block['element']['text'] []= $Line['text'];

            return $Block;
        ***REMOVED***
    ***REMOVED***

    #
    # Rule

    protected function blockRule($Line)
    ***REMOVED***
        if (preg_match('/^(['.$Line['text'][0].'])([ ]*\1)***REMOVED***2,***REMOVED***[ ]*$/', $Line['text']))
        ***REMOVED***
            $Block = array(
                'element' => array(
                    'name' => 'hr'
                ),
            );

            return $Block;
        ***REMOVED***
    ***REMOVED***

    #
    # Setext

    protected function blockSetextHeader($Line, array $Block = null)
    ***REMOVED***
        if ( ! isset($Block) or isset($Block['type']) or isset($Block['interrupted']))
        ***REMOVED***
            return;
        ***REMOVED***

        if (chop($Line['text'], $Line['text'][0]) === '')
        ***REMOVED***
            $Block['element']['name'] = $Line['text'][0] === '=' ? 'h1' : 'h2';

            return $Block;
        ***REMOVED***
    ***REMOVED***

    #
    # Markup

    protected function blockMarkup($Line)
    ***REMOVED***
        if ($this->markupEscaped)
        ***REMOVED***
            return;
        ***REMOVED***

        if (preg_match('/^<(\w*)(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*(\/)?>/', $Line['text'], $matches))
        ***REMOVED***
            if (in_array($matches[1], $this->textLevelElements))
            ***REMOVED***
                return;
***REMOVED***

            $Block = array(
                'name' => $matches[1],
                'depth' => 0,
                'markup' => $Line['text'],
            );

            $length = strlen($matches[0]);

            $remainder = substr($Line['text'], $length);

            if (trim($remainder) === '')
            ***REMOVED***
                if (isset($matches[2]) or in_array($matches[1], $this->voidElements))
                ***REMOVED***
                    $Block['closed'] = true;

                    $Block['void'] = true;
***REMOVED***
***REMOVED***
            else
            ***REMOVED***
                if (isset($matches[2]) or in_array($matches[1], $this->voidElements))
                ***REMOVED***
                    return;
***REMOVED***

                if (preg_match('/<\/'.$matches[1].'>[ ]*$/i', $remainder))
                ***REMOVED***
                    $Block['closed'] = true;
***REMOVED***
***REMOVED***

            return $Block;
        ***REMOVED***
    ***REMOVED***

    protected function blockMarkupContinue($Line, array $Block)
    ***REMOVED***
        if (isset($Block['closed']))
        ***REMOVED***
            return;
        ***REMOVED***

        if (preg_match('/^<'.$Block['name'].'(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*>/i', $Line['text'])) # open
        ***REMOVED***
            $Block['depth'] ++;
        ***REMOVED***

        if (preg_match('/(.*?)<\/'.$Block['name'].'>[ ]*$/i', $Line['text'], $matches)) # close
        ***REMOVED***
            if ($Block['depth'] > 0)
            ***REMOVED***
                $Block['depth'] --;
***REMOVED***
            else
            ***REMOVED***
                $Block['closed'] = true;
***REMOVED***

            $Block['markup'] .= $matches[1];
        ***REMOVED***

        if (isset($Block['interrupted']))
        ***REMOVED***
            $Block['markup'] .= "\n";

            unset($Block['interrupted']);
        ***REMOVED***

        $Block['markup'] .= "\n".$Line['body'];

        return $Block;
    ***REMOVED***

    #
    # Reference

    protected function blockReference($Line)
    ***REMOVED***
        if (preg_match('/^\[(.+?)\]:[ ]*<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*$/', $Line['text'], $matches))
        ***REMOVED***
            $id = strtolower($matches[1]);

            $Data = array(
                'url' => $matches[2],
                'title' => null,
            );

            if (isset($matches[3]))
            ***REMOVED***
                $Data['title'] = $matches[3];
***REMOVED***

            $this->DefinitionData['Reference'][$id] = $Data;

            $Block = array(
                'hidden' => true,
            );

            return $Block;
        ***REMOVED***
    ***REMOVED***

    #
    # Table

    protected function blockTable($Line, array $Block = null)
    ***REMOVED***
        if ( ! isset($Block) or isset($Block['type']) or isset($Block['interrupted']))
        ***REMOVED***
            return;
        ***REMOVED***

        if (strpos($Block['element']['text'], '|') !== false and chop($Line['text'], ' -:|') === '')
        ***REMOVED***
            $alignments = array();

            $divider = $Line['text'];

            $divider = trim($divider);
            $divider = trim($divider, '|');

            $dividerCells = explode('|', $divider);

            foreach ($dividerCells as $dividerCell)
            ***REMOVED***
                $dividerCell = trim($dividerCell);

                if ($dividerCell === '')
                ***REMOVED***
                    continue;
***REMOVED***

                $alignment = null;

                if ($dividerCell[0] === ':')
                ***REMOVED***
                    $alignment = 'left';
***REMOVED***

                if (substr($dividerCell, - 1) === ':')
                ***REMOVED***
                    $alignment = $alignment === 'left' ? 'center' : 'right';
***REMOVED***

                $alignments []= $alignment;
***REMOVED***

            # ~

            $HeaderElements = array();

            $header = $Block['element']['text'];

            $header = trim($header);
            $header = trim($header, '|');

            $headerCells = explode('|', $header);

            foreach ($headerCells as $index => $headerCell)
            ***REMOVED***
                $headerCell = trim($headerCell);

                $HeaderElement = array(
                    'name' => 'th',
                    'text' => $headerCell,
                    'handler' => 'line',
                );

                if (isset($alignments[$index]))
                ***REMOVED***
                    $alignment = $alignments[$index];

                    $HeaderElement['attributes'] = array(
                        'style' => 'text-align: '.$alignment.';',
                    );
***REMOVED***

                $HeaderElements []= $HeaderElement;
***REMOVED***

            # ~

            $Block = array(
                'alignments' => $alignments,
                'identified' => true,
                'element' => array(
                    'name' => 'table',
                    'handler' => 'elements',
                ),
            );

            $Block['element']['text'] []= array(
                'name' => 'thead',
                'handler' => 'elements',
            );

            $Block['element']['text'] []= array(
                'name' => 'tbody',
                'handler' => 'elements',
                'text' => array(),
            );

            $Block['element']['text'][0]['text'] []= array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $HeaderElements,
            );

            return $Block;
        ***REMOVED***
    ***REMOVED***

    protected function blockTableContinue($Line, array $Block)
    ***REMOVED***
        if (isset($Block['interrupted']))
        ***REMOVED***
            return;
        ***REMOVED***

        if ($Line['text'][0] === '|' or strpos($Line['text'], '|'))
        ***REMOVED***
            $Elements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]+`|`)+/', $row, $matches);

            foreach ($matches[0] as $index => $cell)
            ***REMOVED***
                $cell = trim($cell);

                $Element = array(
                    'name' => 'td',
                    'handler' => 'line',
                    'text' => $cell,
                );

                if (isset($Block['alignments'][$index]))
                ***REMOVED***
                    $Element['attributes'] = array(
                        'style' => 'text-align: '.$Block['alignments'][$index].';',
                    );
***REMOVED***

                $Elements []= $Element;
***REMOVED***

            $Element = array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $Elements,
            );

            $Block['element']['text'][1]['text'] []= $Element;

            return $Block;
        ***REMOVED***
    ***REMOVED***

    #
    # ~
    #

    protected function paragraph($Line)
    ***REMOVED***
        $Block = array(
            'element' => array(
                'name' => 'p',
                'text' => $Line['text'],
                'handler' => 'line',
            ),
        );

        return $Block;
    ***REMOVED***

    #
    # Inline Elements
    #

    protected $InlineTypes = array(
        '"' => array('SpecialCharacter'),
        '!' => array('Image'),
        '&' => array('SpecialCharacter'),
        '*' => array('Emphasis'),
        ':' => array('Url'),
        '<' => array('UrlTag', 'EmailTag', 'Markup', 'SpecialCharacter'),
        '>' => array('SpecialCharacter'),
        '[' => array('Link'),
        '_' => array('Emphasis'),
        '`' => array('Code'),
        '~' => array('Strikethrough'),
        '\\' => array('EscapeSequence'),
    );

    # ~

    protected $inlineMarkerList = '!"*_&[:<>`~\\';

    #
    # ~
    #

    public function line($text)
    ***REMOVED***
        $markup = '';

        $unexaminedText = $text;

        $markerPosition = 0;

        while ($excerpt = strpbrk($unexaminedText, $this->inlineMarkerList))
        ***REMOVED***
            $marker = $excerpt[0];

            $markerPosition += strpos($unexaminedText, $marker);

            $Excerpt = array('text' => $excerpt, 'context' => $text);

            foreach ($this->InlineTypes[$marker] as $inlineType)
            ***REMOVED***
                $Inline = $this->***REMOVED***'inline'.$inlineType***REMOVED***($Excerpt);

                if ( ! isset($Inline))
                ***REMOVED***
                    continue;
***REMOVED***

                if (isset($Inline['position']) and $Inline['position'] > $markerPosition) # position is ahead of marker
                ***REMOVED***
                    continue;
***REMOVED***

                if ( ! isset($Inline['position']))
                ***REMOVED***
                    $Inline['position'] = $markerPosition;
***REMOVED***

                $unmarkedText = substr($text, 0, $Inline['position']);

                $markup .= $this->unmarkedText($unmarkedText);

                $markup .= isset($Inline['markup']) ? $Inline['markup'] : $this->element($Inline['element']);

                $text = substr($text, $Inline['position'] + $Inline['extent']);

                $unexaminedText = $text;

                $markerPosition = 0;

                continue 2;
***REMOVED***

            $unexaminedText = substr($excerpt, 1);

            $markerPosition ++;
        ***REMOVED***

        $markup .= $this->unmarkedText($text);

        return $markup;
    ***REMOVED***

    #
    # ~
    #

    protected function inlineCode($Excerpt)
    ***REMOVED***
        $marker = $Excerpt['text'][0];

        if (preg_match('/^('.$marker.'+)[ ]*(.+?)[ ]*(?<!'.$marker.')\1(?!'.$marker.')/s', $Excerpt['text'], $matches))
        ***REMOVED***
            $text = $matches[2];
            $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            $text = preg_replace("/[ ]*\n/", ' ', $text);

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'code',
                    'text' => $text,
                ),
            );
        ***REMOVED***
    ***REMOVED***

    protected function inlineEmailTag($Excerpt)
    ***REMOVED***
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<((mailto:)?\S+?@\S+?)>/i', $Excerpt['text'], $matches))
        ***REMOVED***
            $url = $matches[1];

            if ( ! isset($matches[2]))
            ***REMOVED***
                $url = 'mailto:' . $url;
***REMOVED***

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[1],
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        ***REMOVED***
    ***REMOVED***

    protected function inlineEmphasis($Excerpt)
    ***REMOVED***
        if ( ! isset($Excerpt['text'][1]))
        ***REMOVED***
            return;
        ***REMOVED***

        $marker = $Excerpt['text'][0];

        if ($Excerpt['text'][1] === $marker and preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches))
        ***REMOVED***
            $emphasis = 'strong';
        ***REMOVED***
        elseif (preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches))
        ***REMOVED***
            $emphasis = 'em';
        ***REMOVED***
        else
        ***REMOVED***
            return;
        ***REMOVED***

        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => $emphasis,
                'handler' => 'line',
                'text' => $matches[1],
            ),
        );
    ***REMOVED***

    protected function inlineEscapeSequence($Excerpt)
    ***REMOVED***
        if (isset($Excerpt['text'][1]) and in_array($Excerpt['text'][1], $this->specialCharacters))
        ***REMOVED***
            return array(
                'markup' => $Excerpt['text'][1],
                'extent' => 2,
            );
        ***REMOVED***
    ***REMOVED***

    protected function inlineImage($Excerpt)
    ***REMOVED***
        if ( ! isset($Excerpt['text'][1]) or $Excerpt['text'][1] !== '[')
        ***REMOVED***
            return;
        ***REMOVED***

        $Excerpt['text']= substr($Excerpt['text'], 1);

        $Link = $this->inlineLink($Excerpt);

        if ($Link === null)
        ***REMOVED***
            return;
        ***REMOVED***

        $Inline = array(
            'extent' => $Link['extent'] + 1,
            'element' => array(
                'name' => 'img',
                'attributes' => array(
                    'src' => $Link['element']['attributes']['href'],
                    'alt' => $Link['element']['text'],
                ),
            ),
        );

        $Inline['element']['attributes'] += $Link['element']['attributes'];

        unset($Inline['element']['attributes']['href']);

        return $Inline;
    ***REMOVED***

    protected function inlineLink($Excerpt)
    ***REMOVED***
        $Element = array(
            'name' => 'a',
            'handler' => 'line',
            'text' => null,
            'attributes' => array(
                'href' => null,
                'title' => null,
            ),
        );

        $extent = 0;

        $remainder = $Excerpt['text'];

        if (preg_match('/\[((?:[^][]|(?R))*)\]/', $remainder, $matches))
        ***REMOVED***
            $Element['text'] = $matches[1];

            $extent += strlen($matches[0]);

            $remainder = substr($remainder, $extent);
        ***REMOVED***
        else
        ***REMOVED***
            return;
        ***REMOVED***

        if (preg_match('/^[(]((?:[^ (]|[(][^ )]+[)])+)(?:[ ]+("[^"]+"|\'[^\']+\'))?[)]/', $remainder, $matches))
        ***REMOVED***
            $Element['attributes']['href'] = $matches[1];

            if (isset($matches[2]))
            ***REMOVED***
                $Element['attributes']['title'] = substr($matches[2], 1, - 1);
***REMOVED***

            $extent += strlen($matches[0]);
        ***REMOVED***
        else
        ***REMOVED***
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches))
            ***REMOVED***
                $definition = $matches[1] ? $matches[1] : $Element['text'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
***REMOVED***
            else
            ***REMOVED***
                $definition = strtolower($Element['text']);
***REMOVED***

            if ( ! isset($this->DefinitionData['Reference'][$definition]))
            ***REMOVED***
                return;
***REMOVED***

            $Definition = $this->DefinitionData['Reference'][$definition];

            $Element['attributes']['href'] = $Definition['url'];
            $Element['attributes']['title'] = $Definition['title'];
        ***REMOVED***

        $Element['attributes']['href'] = str_replace(array('&', '<'), array('&amp;', '&lt;'), $Element['attributes']['href']);

        return array(
            'extent' => $extent,
            'element' => $Element,
        );
    ***REMOVED***

    protected function inlineMarkup($Excerpt)
    ***REMOVED***
        if ($this->markupEscaped or strpos($Excerpt['text'], '>') === false)
        ***REMOVED***
            return;
        ***REMOVED***

        if ($Excerpt['text'][1] === '/' and preg_match('/^<\/\w*[ ]*>/s', $Excerpt['text'], $matches))
        ***REMOVED***
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        ***REMOVED***

        if ($Excerpt['text'][1] === '!' and preg_match('/^<!---?[^>-](?:-?[^-])*-->/s', $Excerpt['text'], $matches))
        ***REMOVED***
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        ***REMOVED***

        if ($Excerpt['text'][1] !== ' ' and preg_match('/^<\w*(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*\/?>/s', $Excerpt['text'], $matches))
        ***REMOVED***
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        ***REMOVED***
    ***REMOVED***

    protected function inlineSpecialCharacter($Excerpt)
    ***REMOVED***
        if ($Excerpt['text'][0] === '&' and ! preg_match('/^&#?\w+;/', $Excerpt['text']))
        ***REMOVED***
            return array(
                'markup' => '&amp;',
                'extent' => 1,
            );
        ***REMOVED***

        $SpecialCharacter = array('>' => 'gt', '<' => 'lt', '"' => 'quot');

        if (isset($SpecialCharacter[$Excerpt['text'][0]]))
        ***REMOVED***
            return array(
                'markup' => '&'.$SpecialCharacter[$Excerpt['text'][0]].';',
                'extent' => 1,
            );
        ***REMOVED***
    ***REMOVED***

    protected function inlineStrikethrough($Excerpt)
    ***REMOVED***
        if ( ! isset($Excerpt['text'][1]))
        ***REMOVED***
            return;
        ***REMOVED***

        if ($Excerpt['text'][1] === '~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $Excerpt['text'], $matches))
        ***REMOVED***
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'del',
                    'text' => $matches[1],
                    'handler' => 'line',
                ),
            );
        ***REMOVED***
    ***REMOVED***

    protected function inlineUrl($Excerpt)
    ***REMOVED***
        if ($this->urlsLinked !== true or ! isset($Excerpt['text'][2]) or $Excerpt['text'][2] !== '/')
        ***REMOVED***
            return;
        ***REMOVED***

        if (preg_match('/\bhttps?:[\/]***REMOVED***2***REMOVED***[^\s<]+\b\/*/ui', $Excerpt['context'], $matches, PREG_OFFSET_CAPTURE))
        ***REMOVED***
            $Inline = array(
                'extent' => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[0][0],
                    'attributes' => array(
                        'href' => $matches[0][0],
                    ),
                ),
            );

            return $Inline;
        ***REMOVED***
    ***REMOVED***

    protected function inlineUrlTag($Excerpt)
    ***REMOVED***
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<(\w+:\/***REMOVED***2***REMOVED***[^ >]+)>/i', $Excerpt['text'], $matches))
        ***REMOVED***
            $url = str_replace(array('&', '<'), array('&amp;', '&lt;'), $matches[1]);

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        ***REMOVED***
    ***REMOVED***

    #
    # ~

    protected $unmarkedInlineTypes = array("\n" => 'Break', '://' => 'Url');

    # ~

    protected function unmarkedText($text)
    ***REMOVED***
        if ($this->breaksEnabled)
        ***REMOVED***
            $text = preg_replace('/[ ]*\n/', "<br />\n", $text);
        ***REMOVED***
        else
        ***REMOVED***
            $text = preg_replace('/(?:[ ][ ]+|[ ]*\\\\)\n/', "<br />\n", $text);
            $text = str_replace(" \n", "\n", $text);
        ***REMOVED***

        return $text;
    ***REMOVED***

    #
    # Handlers
    #

    protected function element(array $Element)
    ***REMOVED***
        $markup = '<'.$Element['name'];

        if (isset($Element['attributes']))
        ***REMOVED***
            foreach ($Element['attributes'] as $name => $value)
            ***REMOVED***
                if ($value === null)
                ***REMOVED***
                    continue;
***REMOVED***

                $markup .= ' '.$name.'="'.$value.'"';
***REMOVED***
        ***REMOVED***

        if (isset($Element['text']))
        ***REMOVED***
            $markup .= '>';

            if (isset($Element['handler']))
            ***REMOVED***
                $markup .= $this->***REMOVED***$Element['handler']***REMOVED***($Element['text']);
***REMOVED***
            else
            ***REMOVED***
                $markup .= $Element['text'];
***REMOVED***

            $markup .= '</'.$Element['name'].'>';
        ***REMOVED***
        else
        ***REMOVED***
            $markup .= ' />';
        ***REMOVED***

        return $markup;
    ***REMOVED***

    protected function elements(array $Elements)
    ***REMOVED***
        $markup = '';

        foreach ($Elements as $Element)
        ***REMOVED***
            $markup .= "\n" . $this->element($Element);
        ***REMOVED***

        $markup .= "\n";

        return $markup;
    ***REMOVED***

    # ~

    protected function li($lines)
    ***REMOVED***
        $markup = $this->lines($lines);

        $trimmedMarkup = trim($markup);

        if ( ! in_array('', $lines) and substr($trimmedMarkup, 0, 3) === '<p>')
        ***REMOVED***
            $markup = $trimmedMarkup;
            $markup = substr($markup, 3);

            $position = strpos($markup, "</p>");

            $markup = substr_replace($markup, '', $position, 4);
        ***REMOVED***

        return $markup;
    ***REMOVED***

    #
    # Deprecated Methods
    #

    function parse($text)
    ***REMOVED***
        $markup = $this->text($text);

        return $markup;
    ***REMOVED***

    #
    # Static Methods
    #

    static function instance($name = 'default')
    ***REMOVED***
        if (isset(self::$instances[$name]))
        ***REMOVED***
            return self::$instances[$name];
        ***REMOVED***

        $instance = new self();

        self::$instances[$name] = $instance;

        return $instance;
    ***REMOVED***

    private static $instances = array();

    #
    # Fields
    #

    protected $DefinitionData;

    #
    # Read-Only

    protected $specialCharacters = array(
        '\\', '`', '*', '_', '***REMOVED***', '***REMOVED***', '[', ']', '(', ')', '>', '#', '+', '-', '.', '!', '|',
    );

    protected $StrongRegex = array(
        '*' => '/^[*]***REMOVED***2***REMOVED***((?:\\\\\*|[^*]|[*][^*]*[*])+?)[*]***REMOVED***2***REMOVED***(?![*])/s',
        '_' => '/^__((?:\\\\_|[^_]|_[^_]*_)+?)__(?!_)/us',
    );

    protected $EmRegex = array(
        '*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
        '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
    );

    protected $regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*(?:\s*=\s*(?:[^"\'=<>`\s]+|"[^"]*"|\'[^\']*\'))?';

    protected $voidElements = array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source',
    );

    protected $textLevelElements = array(
        'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
        'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing',
        'i', 'rp', 'del', 'code',          'strike', 'marquee',
        'q', 'rt', 'ins', 'font',          'strong',
        's', 'tt', 'sub', 'mark',
        'u', 'xm', 'sup', 'nobr',
                   'var', 'ruby',
                   'wbr', 'span',
                          'time',
    );
***REMOVED***
