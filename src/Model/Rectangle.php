<?php

namespace WordCloud\Model;

/**
 * x1, y1 is the bottom left corner. x2, y2 is the top right.
 */
class Rectangle
{
    public $x1;
    public $y1;
    public $x2;
    public $y2;

    public function __construct($x1, $y1, $x2, $y2)
    {
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
    }

    public function width()
    {
        return abs($this->x2 - $this->x1);
    }

    public function height()
    {
        return abs($this->y2 - $this->y1);
    }

    public function area()
    {
        return $this->width() * $this->height();
    }

    public function intersects(Rectangle $other)
    {
        return $this->x1 < $other->x2
            && $this->x2 > $other->x1
            && $this->y1 < $other->y2
            && $this->y2 > $other->y1;
    }

    public function contains(Rectangle $other)
    {
        return $this->x1 < $other->x1
            && $this->x2 > $other->x2
            && $this->y1 < $other->y1
            && $this->y2 > $other->y2;
    }

    public function translated($x, $y)
    {
        return new Rectangle(
            $this->x1 + $x,
            $this->y1 + $y,
            $this->x2 + $x,
            $this->y2 + $y
        );
    }
}
