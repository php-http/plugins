<?php

namespace spec\Http\Client\Plugin;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EncoderPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Http\Client\Plugin\EncoderPlugin');
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }
}
