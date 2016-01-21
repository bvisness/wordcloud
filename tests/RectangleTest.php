<?php

namespace WordCloudTests;

use WordCloud\Model\Rectangle;

class RectangleTest extends \PHPUnit_Framework_TestCase
{
    private function assertNormalized(Rectangle $rect)
    {
        $this->assertTrue($rect->x1 <= $rect->x2);
        $this->assertTrue($rect->y1 <= $rect->y2);
    }

    private function assertIdentical(Rectangle $expected, Rectangle $actual)
    {
        $expectedMinX = min($expected->x1, $expected->x2);
        $expectedMinY = min($expected->y1, $expected->y2);
        $expectedMaxX = max($expected->x1, $expected->x2);
        $expectedMaxY = max($expected->y1, $expected->y2);
        $actualMinX = min($actual->x1, $actual->x2);
        $actualMinY = min($actual->y1, $actual->y2);
        $actualMaxX = max($actual->x1, $actual->x2);
        $actualMaxY = max($actual->y1, $actual->y2);

        $this->assertSame($expectedMinX, $actualMinX);
        $this->assertSame($expectedMinY, $actualMinY);
        $this->assertSame($expectedMaxX, $actualMaxX);
        $this->assertSame($expectedMaxY, $actualMaxY);
    }

    public function testWidth()
    {
        $rect1 = new Rectangle(0, 0, 4, 4);
        $this->assertSame(4, $rect1->width());

        $rect2 = new Rectangle(0, 0, -4, -4);
        $this->assertSame(4, $rect2->width());
    }

    public function testHeight()
    {
        $rect1 = new Rectangle(0, 0, 4, 4);
        $this->assertSame(4, $rect1->height());

        $rect2 = new Rectangle(0, 0, -4, -4);
        $this->assertSame(4, $rect2->height());
    }

    public function testArea()
    {
        $rect1 = new Rectangle(0, 0, 4, 4);
        $this->assertSame(16, $rect1->area());

        $rect2 = new Rectangle(0, 0, 4, -4);
        $this->assertSame(16, $rect2->area());
    }

    public function testNormalized()
    {
        // All four possibilities for defining corners
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect1Norm = $rect1->normalized();
        $this->assertNormalized($rect1Norm);
        $this->assertNotSame($rect1Norm, $rect1);
        $this->assertIdentical($rect1Norm, $rect1);

        $rect2 = new Rectangle(4, 4, 0, 0);
        $rect2Norm = $rect2->normalized();
        $this->assertNormalized($rect2Norm);
        $this->assertNotSame($rect2Norm, $rect2);
        $this->assertIdentical($rect2Norm, $rect2);

        $rect3 = new Rectangle(0, 4, 4, 0);
        $rect3Norm = $rect3->normalized();
        $this->assertNormalized($rect3Norm);
        $this->assertNotSame($rect3Norm, $rect3);
        $this->assertIdentical($rect3Norm, $rect3);

        $rect4 = new Rectangle(4, 0, 0, 4);
        $rect4Norm = $rect4->normalized();
        $this->assertNormalized($rect4Norm);
        $this->assertNotSame($rect4Norm, $rect4);
        $this->assertIdentical($rect4Norm, $rect4);
    }

    public function testIntersects()
    {
        // No overlap
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect2 = new Rectangle(5, 0, 9, 4);
        $this->assertFalse($rect1->intersects($rect2));

        // Edges touch
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect2 = new Rectangle(4, 0, 8, 4);
        $this->assertFalse($rect1->intersects($rect2));

        // Normal case
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect2 = new Rectangle(2, 2, 6, 6);
        $this->assertTrue($rect1->intersects($rect2));

        // Non-normalized rectangles
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect2 = new Rectangle(6, 6, 2, 2);
        $this->assertTrue($rect1->intersects($rect2));
    }

    public function testContains()
    {
        // No overlap
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect2 = new Rectangle(5, 0, 9, 4);
        $this->assertFalse($rect1->contains($rect2));

        // Partial overlap
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect2 = new Rectangle(2, 2, 6, 6);
        $this->assertFalse($rect1->contains($rect2));

        // Fully contained
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect2 = new Rectangle(1, 1, 3, 3);
        $this->assertTrue($rect1->contains($rect2));

        // Non-normalized rectangles
        $rect1 = new Rectangle(0, 0, 4, 4);
        $rect2 = new Rectangle(3, 3, 1, 1);
        $this->assertTrue($rect1->contains($rect2));
    }

    public function testTranslated()
    {
        $rect = new Rectangle(4, 4, 0, 0);
        $rectTranslated = $rect->translated(2, 2);
        $this->assertIdentical(new Rectangle(6, 6, 2, 2), $rectTranslated);
        $this->assertSame(6, $rectTranslated->x1);
        $this->assertNotSame($rectTranslated, $rect);
    }

    public function testScaledOrigin()
    {
        $rect = new Rectangle(1, 1, 2, 2);
        $rectScaled = $rect->scaledOrigin(2, 3);
        $this->assertIdentical(new Rectangle(2, 3, 4, 6), $rectScaled);
        $this->assertNotSame($rectScaled, $rect);
    }

    public function testScaledCenter()
    {
        $rect = new Rectangle(1, 1, 3, 3);
        $rectScaled = $rect->scaledCenter(2, 3);
        $this->assertIdentical(new Rectangle(0, -1, 4, 5), $rectScaled);
        $this->assertNotSame($rectScaled, $rect);
    }

    public function testInflated()
    {
        $rect = new Rectangle(1, 1, 3, 3);
        $rectInflated = $rect->inflated(1, 2);
        $this->assertIdentical(new Rectangle(0, -1, 4, 5), $rectInflated);
        $this->assertNotSame($rectInflated, $rect);
    }
}
