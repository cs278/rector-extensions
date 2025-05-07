<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class MyTest extends TestCase
{
    public static function provideSomeTest1(): iterable
    {
        yield [0];
    }

    #[Test]
    #[DataProvider('provideSomeTest1')]
    #[DataProvider('provideSomeTest2')]
    public function someTest(int $input): void
    {

    }

    /**
     * @return iterable The test data
     */
    public static function provideSomeTest2(): iterable
    {
        yield [1, false];
    }

    #[DataProvider('provideSomeTest2')]
    public function anotherTst(string $input, bool $aFlag): void
    {

    }
}

final class NotATest
{
    public function somethingThatIsNotATest(): void {}
}

?>
-----
<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class MyTest extends TestCase
{
    /**
     * @return iterable<array-key, array{int}>
     */
    public static function provideSomeTest1(): iterable
    {
        yield [0];
    }

    #[Test]
    #[DataProvider('provideSomeTest1')]
    #[DataProvider('provideSomeTest2')]
    public function someTest(int $input): void
    {

    }

    /**
     * @return iterable<array-key, array{(int | string), bool}> The test data
     */
    public static function provideSomeTest2(): iterable
    {
        yield [1, false];
    }

    #[DataProvider('provideSomeTest2')]
    public function anotherTst(string $input, bool $aFlag): void
    {

    }
}

final class NotATest
{
    public function somethingThatIsNotATest(): void {}
}

?>
