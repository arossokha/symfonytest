<?php
namespace Art\JobtestBundle\Tests\Utils;

use Art\JobtestBundle\Utils\Jobtest;

class JobtestTest extends \PHPUnit_Framework_TestCase
{
    public function testSlugify()
    {
        $this->assertEquals('sensio', Jobtest::slugify('Sensio'));
        $this->assertEquals('sensio-labs', Jobtest::slugify('sensio labs'));
        $this->assertEquals('sensio-labs', Jobtest::slugify('sensio labs'));
        $this->assertEquals('paris-france', Jobtest::slugify('paris,france'));
        $this->assertEquals('sensio', Jobtest::slugify(' sensio'));
        $this->assertEquals('sensio', Jobtest::slugify('sensio '));
        $this->assertEquals('n-a', Jobtest::slugify(''));
        $this->assertEquals('n-a', Jobtest::slugify(' - '));
        if (function_exists('iconv')) {
            $this->assertEquals('developpeur-web', Jobtest::slugify('Développeur Web'));
        }
    }
}