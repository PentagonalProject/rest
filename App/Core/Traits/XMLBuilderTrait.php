<?php
/**
 * MIT License
 *
 * Copyright (c) 2017, Pentagonal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author pentagonal <org@pentagonal.org>
 */

declare(strict_types=1);

namespace PentagonalProject\App\Rest\Traits;

/*
 * XML EXAMPLE FOR RETURNING GET REAL DATA VALUE
 * This for Tricky XML Return Data
 * if got invalid attribute will be use tag as
 * <tag key="key name of tag" type="type value"></tag>
 *
// SET DATA
[
    'Array1' => [               # array
        'ArrayNested2' => [     # array
            'Array With Invalid Tag Nested 2' => [ # array
                'No Array Key', # string
                'With Array Key Spaced' => [ # array
                    'Value 1',  # string
                    1,          # integer
                    true,       # boolean
                    false,      # boolean
                    null        # null
                ]
            ]
        ]
    ]
]

// XML RESPONSE
<?xml version="1.0" encoding="utf-8"?>
<root>
  <tag type="array" key="Array1">
    <tag type="array" key="ArrayNested2">
      <tag type="array" key="Array With Invalid Tag Nested 2">
        <integer key="0" type="string">No Array Key</integer>
        <tag type="array" key="With Array Key Spaced">
          <integer key="0" type="string">Value 1</integer>
          <integer key="1" type="integer">1</integer>
          <integer key="2" type="boolean">1</integer>
          <integer key="3" type="boolean">0</integer>
          <integer key="4" type="null"></integer>
        </tag>
      </tag>
    </tag>
  </tag>
</root>
 */

/**
 * Trait XmlBuilderTrait
 * @package PentagonalProject\App\Rest\Traits
 */
trait XmlBuilderTrait
{
    /**
     * root tag
     *
     * @var string
     */
    protected $xmlHierarchyRootTag = 'root';

    /**
     * Open Document
     *
     * @var string
     */
    protected $xmlOpenDoc = '<?xml version="1.0" encoding="{encoding}"?>';

    /**
     * Replacer ShortHand
     *
     * @var array
     */
    protected $xmlOpenDocumentReplacer = [
        '{encoding}' => 'utf-8'
    ];

    /**
     * Get XML keyType
     *
     * @param string $type
     * @return string
     */
    protected function getXMLKeyFor(string $type) : string
    {
        $return = $type;
        switch ($type) {
            case 'int':
                return 'integer';
            case 'bool':
                return 'boolean';
            case 'NULL':
            case 'null':
                return 'null';
        }

        return $return;
    }

    /**
     * Generate Pair XML
     *
     * @param mixed $content
     * @param int $counted
     * @return string
     */
    protected function generatePairXML($content, int $counted = 0)
    {
        $returnValue = "";
        if (is_array($content)) {
            $counted_array = count($content);
            $c = 1;
            foreach ($content as $key => $value) {
                $count = $counted + 1;
                $tab  = str_repeat("  ", $count);
                $keyType = gettype($key);
                $valueType = $this->getXMLKeyFor(gettype($value));
                if (! is_string($key) || is_numeric($key)) {
                    $keyType   = is_numeric($key) ? 'integer' : $keyType;
                    $returnValue .= "\n{$tab}<{$this->getXMLKeyFor($keyType)} key=\"{$key}\" type=\"{$valueType}\">";
                    $returnValue .= $this->generatePairXML($value, $count);
                    $endVal  = "</{$this->getXMLKeyFor($keyType)}>";
                } elseif (preg_match('/^[^a-z]|[^a-z0-9\_]|[^a-z]$/i', $key) !== 0) {
                    $key     = htmlspecialchars($key, ENT_QUOTES);
                    $returnValue .= "\n{$tab}<tag type=\"{$valueType}\" key=\"{$key}\">";
                    $returnValue .= $this->generatePairXML($value, $count);
                    $endVal  = "</tag>";
                } else {
                    $returnValue .= "\n{$tab}<{$key} type=\"{$valueType}\">";
                    $returnValue .= $this->generatePairXML($value, $count);
                    $endVal = "</{$key}>";
                }
                if ($c === $counted_array) {
                    $endVal .= "\n";
                }
                $returnValue .= (is_array($value) ? $tab : '') .$endVal;
                $c++;
            }
        } else {
            if (is_bool($content)) {
                return $content ? 1 : 0;
            }

            return htmlspecialchars($content, ENT_QUOTES);
        }

        return $returnValue;
    }

    /**
     * Generate XML Data
     *
     * @param string $encoding
     * @param array $data
     * @return string
     */
    protected function generateXML(string $encoding, array $data) : string
    {
        $replacer = $this->xmlOpenDocumentReplacer;
        $replacer['{encoding}'] = $encoding;
        $openDoc = str_replace(
            array_keys($replacer),
            array_values($replacer),
            $this->xmlOpenDoc
        );

        return $openDoc
            . "\n<!--\n  @use htmlspecialchars_decode((string) string, ENT_QUOTES); to decode the value\n-->"
            . "\n<{$this->xmlHierarchyRootTag}>"
            . $this->generatePairXML($data)
            . "</{$this->xmlHierarchyRootTag}>";
    }
}
