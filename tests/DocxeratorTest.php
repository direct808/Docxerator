<?php

use Direct808\Docxerator\TestUtils;
use PHPUnit\Framework\TestCase;
use Direct808\Docxerator\Docxerator;


final class DocxeratorTest extends TestCase
{

    public function testMain()
    {
//        DocxTemplateProcessor::setDefaultMarkPattern('/ ?(procedure) ?/iu');
        $docs = new Docxerator();
//        $docs->setMarkPattern('/\$\{(\w+)\}/');
        $docs->open(__DIR__ . '/POLYA.docx');

        foreach ($docs->getMarks() as $i => $mark) {
            $docs->replace($mark, '!!REPLACED!!');
        }

//        $docs->replace('asdasdasdasd', '!!REPLACED!!');

        $docs->save(__DIR__ . '/POLYA_replased.docx');
        $this->assertTrue(true);
    }


    public function testNormalizeMarks()
    {
        $object = new Docxerator();
        $class = new ReflectionClass(Docxerator::class);
        $method = $class->getMethod('normalizeMarks');
        $method->setAccessible(true);

        $xmlProperty = $class->getProperty('xml');
        $xmlProperty->setAccessible(true);
        $xmlProperty->setValue($object, simplexml_load_file(__DIR__ . '/test.xml'));

        $method->invokeArgs($object, []);

        $textMap = $class->getProperty('textMap');
        $textMap->setAccessible(true);

        $textMap = $textMap->getValue($object);
        $expected = [
            'Равным образом сложившаяся структура организации',
            'обеспечивает широкому кругу специалистов участие в формировании развития. #MARK1#',
            ' постоянный количественный рост',
            ' квартиры, расположенной #NORMAL_MARK# по адресу: #ADDRESS_2#',
            ', Краснодарский #CONTRACT_NUMBER#',
            ' край',
        ];

        $this->assertEquals($expected, TestUtils::convertTextMapToArray($textMap));
    }


    public function testGetNearestArrayValue()
    {
        $data = [6, 15, 88, 123];
        $object = new Docxerator();
        $class = new ReflectionClass(Docxerator::class);
        $method = $class->getMethod('getNearestArrayValue');
        $method->setAccessible(true);

        $result = $method->invokeArgs($object, [$data, 15]);
        $this->assertEquals(15, $result);

        $result = $method->invokeArgs($object, [$data, 50]);
        $this->assertEquals(15, $result);

        $result = $method->invokeArgs($object, [$data, 6]);
        $this->assertEquals(6, $result);

        $result = $method->invokeArgs($object, [$data, 123]);
        $this->assertEquals(123, $result);

        $result = $method->invokeArgs($object, [$data, 122]);
        $this->assertEquals(88, $result);

        $result = $method->invokeArgs($object, [$data, 140]);
        $this->assertEquals(123, $result);

    }

}
