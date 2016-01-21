<?php

namespace WordCloud\Model;

/**
 * Defines a rectangle in Cartesian coordinates.
 */
class Rectangle
{
    public $x1;
    public $y1;
    public $x2;
    public $y2;

    /**
     * Constructs a new rectangle where x1 and y1 define one corner, and
     * x2 and y2 define the opposite corner.
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     */
    public function __construct($x1, $y1, $x2, $y2)
    {
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
    }

    /**
     * Gets the absolute width of this rectangle.
     *
     * @return float
     */
    public function width()
    {
        return abs($this->x2 - $this->x1);
    }

    /**
     * Gets the absolute height of this rectangle.
     *
     * @return float
     */
    public function height()
    {
        return abs($this->y2 - $this->y1);
    }

    /**
     * Gets the area of this rectangle.
     *
     * @return float
     */
    public function area()
    {
        return $this->width() * $this->height();
    }

    /**
     * Returns an identical rectangle which has x1, y1 in the lower left and
     * x2, y2 in the upper right.
     *
     * @return Rectangle
     */
    public function normalized()
    {
        $minX = min($this->x1, $this->x2);
        $maxX = max($this->x1, $this->x2);
        $minY = min($this->y1, $this->y2);
        $maxY = max($this->y1, $this->y2);

        return new Rectangle(
            $minX,
            $minY,
            $maxX,
            $maxY
        );
    }

    /**
     * Checks to see if the rectangle is intersecting another rectangle.
     *
     * @param  Rectangle $other
     * @return bool
     */
    public function intersects(Rectangle $other)
    {
        $normThis = $this->normalized();
        $normOther = $other->normalized();

        return $normThis->x1 < $normOther->x2
            && $normThis->x2 > $normOther->x1
            && $normThis->y1 < $normOther->y2
            && $normThis->y2 > $normOther->y1;
    }

    /**
     * Checks to see if the rectangle fully contains another rectangle.
     *
     * @param  Rectangle $other
     * @return bool
     */
    public function contains(Rectangle $other)
    {
        $normThis = $this->normalized();
        $normOther = $other->normalized();

        return $normThis->x1 < $normOther->x1
            && $normThis->x2 > $normOther->x2
            && $normThis->y1 < $normOther->y1
            && $normThis->y2 > $normOther->y2;
    }

    /**
     * Returns a non-normalized copy of this rectangle which has been translated
     * by the given x and y coordinates.
     *
     * @param  float $x
     * @param  float $y
     * @return Rectangle
     */
    public function translated($x, $y)
    {
        return new Rectangle(
            $this->x1 + $x,
            $this->y1 + $y,
            $this->x2 + $x,
            $this->y2 + $y
        );
    }

    /**
     * Returns a non-normalized copy of this rectangle which has been scaled
     * about the origin by the given x and y values.
     *
     * @param  float $x
     * @param  float $y
     * @return Rectangle
     */
    public function scaledOrigin($x, $y)
    {
        return new Rectangle(
            $this->x1 * $x,
            $this->y1 * $y,
            $this->x2 * $x,
            $this->y2 * $y
        );
    }

    /**
     * Returns a non-normalized copy of this rectangle which has been scaled
     * about the rectangle's center point by the given x and y values.
     *
     * @param  float $x
     * @param  float $y
     * @return Rectangle
     */
    public function scaledCenter($x, $y)
    {
        $centerX = ($this->x1 + $this->x2) / 2;
        $centerY = ($this->y1 + $this->y2) / 2;

        return $this
            ->translated(-$centerX, -$centerY)
            ->scaledOrigin($x, $y)
            ->translated($centerX, $centerY);
    }

    /**
     * Returns a normalized copy of this rectangle whose dimensions have been
     * extended on all sides by the given x and y amounts.
     *
     * @param  float $x  The amount by which to expand the left and right sides.
     * @param  float $y  The amount by which to expand the top and bottom sides.
     * @return Rectangle
     */
    public function inflated($x, $y)
    {
        $norm = $this->normalized();
        return new Rectangle(
            $norm->x1 - $x,
            $norm->y1 - $y,
            $norm->x2 + $x,
            $norm->y2 + $y
        );
    }
}
