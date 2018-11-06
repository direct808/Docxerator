<?php

namespace Direct808\Docxerator;

use Direct808\Docxerator\Exception\MarkNotFoundException;
use Direct808\Docxerator\Exception\NoMarksFoundException;
use ZipArchive, SimpleXMLElement;

class Docxerator
{
    protected $tempDir;
    private static $defaultMarkPattern = '/#([.\w]+)#/';
    private $marks;
    private $markPattern;

    private $rawText;
    private $textMap;
    private $filePath;

    /** @var ZipArchive $zip */
    private $zip;
    /** @var SimpleXMLElement $xml */
    private $xml;
    private $xmlStr;


    function __construct()
    {
        $this->tempDir = sys_get_temp_dir();
        $this->markPattern = static::$defaultMarkPattern;
    }

    public static function setDefaultMarkPattern($pattern)
    {
        static::$defaultMarkPattern = $pattern;
    }

    public function setMarkPattern($markPattern)
    {
        $this->markPattern = $markPattern;
    }

    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;
        return $this;
    }

    public function open($srcDocx)
    {
        $this->filePath = $destDocx = tempnam($this->tempDir, 'foo');

        if (!copy($srcDocx, $destDocx))
            throw  new \Exception("Can not copy file $srcDocx to $destDocx");

        $this->zip = $zip = new ZipArchive();
        if ($zip->open($destDocx) !== true) {
            throw new \Exception('Can not open docx file');
        }
        $xml = $zip->getFromName('word/document.xml');
        $this->xml = simplexml_load_string($xml);
        $this->normalizeMarks();
        $this->xmlStr = $this->xml->asXML();
        return $this;
    }

    function replace($key, $value)
    {
        $value = htmlspecialchars($value);
        if (!array_key_exists($key, $this->marks))
            throw new MarkNotFoundException("Mark '$key' not found");

        $this->xmlStr = str_replace($this->marks[$key], $value, $this->xmlStr);
//        file_put_contents(__DIR__ . '/asd.xml', $this->xmlStr);
        return $this;
    }

    function save($filePath = null)
    {
        $this->zip->deleteName('word/document.xml');
        $this->zip->addFromString('word/document.xml', $this->xmlStr);
        $this->zip->close();

        if (!$filePath)
            return $this->filePath;

        if (!copy($this->filePath, $filePath))
            throw  new \Exception('Can not copy file');
        return $filePath;
    }

    private function parseMarks()
    {
        if (preg_match_all($this->markPattern, $this->rawText, $matches)) {
            foreach (array_unique($matches[1]) as $i => $item) {
                $this->marks[$item] = $matches[0][$i];
            }
            return;
        }
        throw new NoMarksFoundException('No marks found');
    }

    public function getMarks()
    {
        return array_keys($this->marks);
    }

    private function getTextNodes()
    {
        return $this->xml->xpath(".//w:t");
    }

    private function createTextMap()
    {
        $currentPosition = 0;
        $this->rawText = '';
        $this->textMap = [];
        $textNodes = $this->getTextNodes();
        foreach ($textNodes as $textNodeAr) {
            list($textNode) = $textNodeAr;
            $length = mb_strlen($textNode);

            $this->textMap[$currentPosition] = $textNode;
            $currentPosition += $length;
            $this->rawText .= $textNode;
        }
    }

    private function normalizeMarks()
    {
        $this->createTextMap();
        $this->parseMarks();
        foreach ($this->marks as $mark) {
            $offset = 0;
            while (true) {
                $positionStart = mb_strpos($this->rawText, $mark, $offset);
                if ($positionStart === false) {
                    break;
                }
                $this->normalizeOneMark($mark, $positionStart);
                $this->createTextMap();
                $offset = $positionStart + mb_strlen($mark);
            }
        }

    }

    private function normalizeOneMark($mark, $positionStart)
    {
        $positionEnd = $positionStart + mb_strlen($mark) - 1;

        $blockStartIndex = $this->getNearestArrayValue(array_keys($this->textMap), $positionStart);
        $blockEndIndex = $this->getNearestArrayValue(array_keys($this->textMap), $positionEnd);

        //normalization is not required
        if ($blockStartIndex == $blockEndIndex) {
            return;
        }

        $blockStart = $this->textMap[$blockStartIndex];

        $blockStart[0] = mb_substr($blockStart[0], 0, $positionStart - $blockStartIndex) . $mark;

        foreach ($this->textMap as $index => $block) {
            if ($index <= $blockStartIndex || $index > $blockEndIndex)
                continue;
            $block[0] = substr($block[0], $positionEnd - $index + 1);
        }
    }

    private function getNearestArrayValue($array, $value)
    {
        $prevIndex = null;
        foreach ($array as $index) {

            if ($index > $value)
                return $prevIndex;

            if ($index == $value)
                return $index;

            $prevIndex = $index;
        }
        return $prevIndex;
    }
}