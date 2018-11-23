<?php declare(strict_types=1);

namespace Fop\Nomad;

use Location\Coordinate;
use Location\Polygon;

final class NomadPolygonFactory
{
    /**
     * @var mixed[]
     */
    private $topLeftCorner = [];

    /**
     * @var mixed[]
     */
    private $bottomRightCorner = [];

    /**
     * @param mixed[] $topLeftCorner
     * @param mixed[] $bottomRightCorner
     */
    public function __construct(array $topLeftCorner, array $bottomRightCorner)
    {
        $this->topLeftCorner = $topLeftCorner;
        $this->bottomRightCorner = $bottomRightCorner;
    }

    public function create(): Polygon
    {
        $lats = [$this->topLeftCorner['latitude'], $this->bottomRightCorner['latitude']];
        $longs = [$this->topLeftCorner['longitude'], $this->bottomRightCorner['longitude']];

        $polygon = new Polygon();
        $polygon->addPoint(new Coordinate(min($lats), min($longs)));
        $polygon->addPoint(new Coordinate(min($lats), max($longs)));
        $polygon->addPoint(new Coordinate(max($lats), min($longs)));
        $polygon->addPoint(new Coordinate(max($lats), max($longs)));

        return $polygon;
    }
}
