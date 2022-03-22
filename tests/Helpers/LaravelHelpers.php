<?php /** @noinspection ALL */

namespace Tests\Helpers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Mockery as m;
use Mockery\MockInterface;

trait LaravelHelpers
{
    private function createViewFactory(): ViewFactory&MockInterface
    {
        return m::spy(ViewFactory::class);
    }

    private function mockViewFactoryMake(
        ViewFactory&MockInterface $viewFactory,
        View $view,
        string $viewName,
        array $data = null
    ): MockInterface&ViewFactory {
        $arguments = [$viewName];
        if ($data !== null) {
            $arguments[] = $data;
        }

        return $viewFactory
            ->shouldReceive('make')
            ->withArgs($arguments)
            ->andReturn($view)
            ->getMock();
    }

    /**
     * @return View|MockInterface
     */
    private function createView(): View
    {
        return m::spy(View::class);
    }
}
