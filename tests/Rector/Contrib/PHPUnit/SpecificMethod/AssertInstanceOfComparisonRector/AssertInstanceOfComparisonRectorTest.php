<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\PHPUnit\SpecificMethod\AssertInstanceOfComparisonRector;

use Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertInstanceOfComparisonRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertInstanceOfComparisonRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideWrongToFixedFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    /**
     * @return string[][]
     */
    public function provideWrongToFixedFiles(): array
    {
        return [
            [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'],
        ];
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [AssertInstanceOfComparisonRector::class];
    }
}
