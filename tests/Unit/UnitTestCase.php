<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use PHPUnit\Framework\TestCase;

abstract class UnitTestCase extends TestCase
{
    use PHPMatcherAssertions;
}
