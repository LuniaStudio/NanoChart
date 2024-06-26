<?php

namespace Vendors\NanoChart;

class NanoChart
{
    private array $values;             // Array to store numeric values for the chart segments
    private int $totalValue;           // Total sum of values in $values array
    private array $keys;               // Keys or labels corresponding to values
    private string $legend = '';       // HTML string for legend markup
    private string $class = '';        // HTML string for CSS styles
    private string $direction = 'row'; // Direction of chart layout ('row' or 'column')
    private string $style = '';        // Chart style ('' or 'doughnut')
    private int $size = 200;           // Size of the chart in pixels
    private int $gap = 32;             // Gap between chart and legend
    private int $hue = 220;            // Hue value for colour generation
    private array $palette = [];       // Array of colours for chart segments
    private int $startAngle = 270;     // Starting angle for chart segments
    private int $cx;                   // X-coordinate of the chart center
    private int $cy;                   // Y-coordinate of the chart center
    private int $radius;               // Outer radius of the chart
    private int $innerRadius;          // Inner radius of the chart (for doughnut style)

    /**
     * Constructor function.
     * Initialises the object with provided values and calculates totalValue.
     *
     * @param array $values Array of values for chart segments
     */
    public function __construct(array $values)
    {
        $this->keys = $values;
        $this->values = array_values($values);

        // Ensure there's at least one value to prevent division by zero
        if (count($this->values) === 1) {

            $this->values[] = 0;
        }

        // Calculate totalValue and ensure values array is not empty
        $this->totalValue = array_sum($this->values) !== 0 ? array_sum($this->values) : 1;
        $this->values = $this->totalValue === 0 ? [] : $this->values;
    }

    /**
     * Sets the size of the chart.
     *
     * @param int $size Size of the chart in pixels
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * Sets the gap between chart elements.
     *
     * @param int $gap Gap between chart elements in pixels
     */
    public function setGap(int $gap): void
    {
        $this->gap = $gap;
    }

    /**
     * Sets the direction of chart layout ('row' or 'column').
     *
     * @param string $direction Direction of chart layout
     */
    public function setDirection(string $direction): void
    {
        $directions = ['column', 'row'];

        if (in_array($direction, $directions, true)) {

            $this->direction = $direction;
        }
    }

    /**
     * Sets the chart style ('' for pie chart, 'doughnut' for doughnut chart).
     *
     * @param string $style Chart style ('' or 'doughnut')
     */
    public function setStyle(string $style): void
    {
        $this->style = $style;
    }

    /**
     * Sets the starting position of the first chart segment.
     *
     * @param string $startPosition Starting position ('top', 'right', 'bottom', 'left')
     */
    public function setStartPosition(string $startPosition): void
    {
        $angles = [
            'top' => 270,
            'right' => 360,
            'bottom' => 90,
            'left' => 180,
        ];

        if (isset($angles[$startPosition])) {

            $this->startAngle = $angles[$startPosition];
        }
    }

    /**
     * Sets the hue value for colour generation.
     *
     * @param int $hue Hue value (0-360)
     */
    public function setHue(int $hue): void
    {
        $this->hue = $hue;
    }

    /**
     * Sets the custom palette of colours for chart segments.
     *
     * @param array $palette Array of colours in hexadecimal or RGB format
     */
    public function setPalette(array $palette): void
    {
        $this->palette = $palette;
    }

    /**
     * Builds and returns the complete SVG chart markup as a string.
     *
     * @return string SVG chart markup
     */
    public function build(): string
    {
        $this->buildDimensions();
        $this->buildPalette();
        $this->buildLegend();
        $this->buildCssStyles();

        $chart = $this->makeSvgOpenTag();
        $chart = $this->totalValue === 0 ? $this->makeEmptyChart($chart) : $this->makeChart($chart);
        $chart = $this->makeSvgCloseTag($chart);
        $chart = $this->addCssStyles($chart);
        $chart = $this->addLegend($chart);

        return $this->buildParentTag($chart);
    }

    /**
     * Builds dimensions for the chart based on the provided size.
     */
    private function buildDimensions(): void
    {
        $this->cx = round($this->size / 2);
        $this->cy = round($this->size / 2);
        $this->radius = min($this->cx, $this->cy) - 4;
        $this->innerRadius = $this->style === 'doughnut' ? round($this->radius / 2) : 0;
    }

    /**
     * Builds the palette of colours for chart segments based on the number of values.
     */
    private function buildPalette(): void
    {
        $count = 100 / count($this->values);
        $range = range(10, 90, $count);

        foreach ($range as $lightness) {

            $this->palette[] = 'hsl(' . $this->hue . ', 100%, ' . $lightness . '%)';
        }
    }

    /**
     * Generates the opening SVG tag for the chart.
     *
     * @return string SVG opening tag
     */
    private function makeSvgOpenTag(): string
    {
        return '<svg style="aspect-ratio:1" width="' . $this->size . '" viewBox="0 0 ' . $this->size . ' ' . $this->size . '" xmlns="http://www.w3.org/2000/svg">';
    }

    /**
     * Generates the closing SVG tag for the chart.
     *
     * @param string $chart Current SVG markup
     * @return string SVG markup with closing tag
     */
    private function makeSvgCloseTag(string $chart): string
    {
        return $chart . '</svg>';
    }

    /**
     * Adds additional CSS styles to the chart markup.
     *
     * @param string $chart Current SVG markup
     * @return string SVG markup with additional CSS styles
     */
    private function addCssStyles(string $chart): string
    {
        if (!empty($this->class)) {

            $chart .= $this->class;
        }

        return $chart;
    }

    /**
     * Adds legend markup to the chart.
     *
     * @param string $chart Current SVG markup
     * @return string SVG markup with legend
     */
    private function addLegend(string $chart): string
    {
        if (!empty($this->legend)) {

            $chart .= $this->legend;
        }

        return $chart;
    }

    /**
     * Wraps the chart markup in a parent container with specified layout.
     *
     * @param string $chart Current SVG markup
     * @return string Complete HTML markup with SVG chart
     */
    private function buildParentTag(string $chart): string
    {
        $wrapper = '<div style="display:flex;flex-direction:' . $this->direction . ';gap:' . $this->gap . 'px;justify-content:center;align-items:center;width:fit-content">';

        return $wrapper . $chart . '</div>';
    }

    /**
     * Generates an empty chart (e.g., when no values are provided).
     *
     * @param string $chart Current SVG markup
     * @return string SVG markup for an empty chart
     */
    private function makeEmptyChart(string $chart): string
    {
        return $chart . '<circle cx="50%" cy="50%" r="50%" fill="hsla(0, 0%, 100%, .025)" />';
    }

    /**
     * Generates the SVG path for each chart segment based on provided values.
     *
     * @param string $chart Current SVG markup
     * @return string SVG markup with chart segments
     */
    private function makeChart(string $chart): string
    {
        $startAngle = $this->startAngle;
        $endAngle = 0;

        $positiveCounts = array_filter($this->values, fn($value) => $value > 0);

        if (count($positiveCounts) === 1) {

            $index = array_key_first($positiveCounts);
            $colour = $this->palette[$index];

            $chart .= '<path d="M' . $this->cx . ',' . ($this->cy - $this->radius) . ' A' . $this->radius . ',' . $this->radius . ' 0 1,1 ' . $this->cx . ',' . ($this->cy + $this->radius) . ' A' . $this->radius . ',' . $this->radius . ' 0 1,1 ' . $this->cx . ',' . ($this->cy - $this->radius) . ' L' . $this->cx . ',' . ($this->cy - $this->innerRadius) . ' A' . $this->innerRadius . ',' . $this->innerRadius . ' 0 1,0 ' . $this->cx . ',' . ($this->cy + $this->innerRadius) . ' A' . $this->innerRadius . ',' . $this->innerRadius . ' 0 1,0 ' . $this->cx . ',' . ($this->cy - $this->innerRadius) . ' Z" fill="' . $colour . '"/>';

            return $chart;
        }

        foreach ($this->values as $index => $value) {

            $endAngle = $startAngle + ($value / $this->totalValue) * 360;

            $x1 = $this->cx + $this->radius * cos(deg2rad($startAngle));
            $y1 = $this->cy + $this->radius * sin(deg2rad($startAngle));
            $x2 = $this->cx + $this->radius * cos(deg2rad($endAngle));
            $y2 = $this->cy + $this->radius * sin(deg2rad($endAngle));

            $x1Inner = $this->cx + $this->innerRadius * cos(deg2rad($startAngle));
            $y1Inner = $this->cy + $this->innerRadius * sin(deg2rad($startAngle));
            $x2Inner = $this->cx + $this->innerRadius * cos(deg2rad($endAngle));
            $y2Inner = $this->cy + $this->innerRadius * sin(deg2rad($endAngle));

            $largeArc = ($endAngle - $startAngle) > 180 ? 1 : 0;

            $chart .= '<path d="M' . $x1 . ',' . $y1 . ' A' . $this->radius . ',' . $this->radius . ' 0 ' . $largeArc . ',1 ' . $x2 . ',' . $y2 . ' L' . $x2Inner . ',' . $y2Inner . ' A' . $this->innerRadius . ',' . $this->innerRadius . ' 0 ' . $largeArc . ',0 ' . $x1Inner . ',' . $y1Inner . ' Z" fill="' . $this->palette[$index] . '"/>';

            $startAngle = $endAngle;
        }

        return $chart;
    }

    /**
     * Builds the legend HTML markup based on the keys and values provided.
     */
    private function buildLegend(): void
    {
        if (!empty($this->keys)) {

            $this->legend = '<ul>';

            foreach ($this->keys as $key => $value) {

                if (is_int($key)) {

                    $this->legend .= '<li>' . $value . '</li>';

                } else {

                    $this->legend .= '<li><span>' . $key . '</span><span>' . $value . '</span></li>';
                }
            }

            $this->legend .= '</ul>';
        }
    }

    /**
     * Builds additional CSS styles for the legend based on the generated palette.
     */
    private function buildCssStyles(): void
    {
        if (!empty($this->palette)) {

            $this->class .= '<style>';

            foreach ($this->palette as $index => $colour) {

                $this->class .= 'ul li {display: flex; justify-content: space-between; gap: 1.5rem} ul li:nth-child(' . ($index + 1) . ') {position:relative;left:-1rem;list-style:none; color:' . $colour . ';}';
            }

            $this->class .= '</style>';
        }
    }
}
?>
